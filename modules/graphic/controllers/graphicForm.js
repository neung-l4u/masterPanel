$('#graphicForm').on('submit', function (e) {
    e.preventDefault();
    const result = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    result.html(`<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>`);
    cmdSubmit.hide();

    const formData = $(this).serializeArray().reduce((obj, item) => {
        if (obj[item.name]) {
            if (!Array.isArray(obj[item.name])) {
                obj[item.name] = [obj[item.name]];
            }
            obj[item.name].push(item.value);
        } else {
            obj[item.name] = item.value;
        }
        return obj;
    }, {});

    const payload = JSON.stringify(formData);
    //sendMail(formData);
    saveToDB(payload, result, cmdSubmit);
});

function sendMail(formData) {
    const now = new Date();
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}-${String(now.getMonth() + 1).padStart(2, '0')}-${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    const selectedShopType = formData.shopType === "Other" ? "Other: " + formData.shopTypeOtherInput : formData.shopType;
    const selectedPackage = formData.package === "Other" ? "Other: " + formData.packageOtherInput : formData.package;
    //const modulePath = "http://localhost/masterPanel/modules/graphic"; // Local path
    const modulePath = "https://report.localforyou.com/modules/graphic"; // Server path
    const filePath = formData.filePath || "";
    const attachFile = filePath ? `<a href="${modulePath}${filePath}" target="_blank">View Attachment</a>` : 'No File Attached';

    const jsonData = {
        name: formData.name || "No Name",
        shopName: formData.shopName || "No Shop Name",
        email: formData.email || "No Email",
        shopType: selectedShopType || "No Shop Type",
        package: selectedPackage || "No Package",
        description: formData.description || "No Description",
        uploadFile: attachFile,
        formVersion: formData.formVersion || "1.0.0",
        emailVersion: formData.emailVersion || "1.0.0",
        date: formattedDate,
    };

    $.ajax({
        url: "https://hook.us1.make.com/opwbmhhvkwwkhkms6mc47lrkicczaujg",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonData),
        success: () => {
            console.log("✅ Webhook sent successfully.");
            window.location.replace("thankyou.php");

        },
        error: () => {
            console.error("❌ Webhook sending failed.");
        }
    });
}

function saveToDB(payload, result, cmdSubmit) {
    $.ajax({
        url: "../models/graphicForm.php",
        type: "POST",
        data: { payload },
        success: () => {
            result.html(`<div class="alert alert-warning mt-2"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Saving data <span id="countDown">3</span>...</div>`);
            let countdown = 3;
            const countdownInterval = setInterval(() => {
                countdown--;
                $('#countDown').text(countdown);
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    //window.location.reload();
                }
            }, 1000);
        },
        error: () => {
            result.html(`<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> An error occurred. Please try again.</div>`);
            cmdSubmit.show();
        }
    });
}

const handleFileUpload = (input) => {
    const $form = $(input).closest("form");
    const $filePath = $form.find(".filePath");
    const $fileName = $form.find(".fileName");
    const files = input.files;
    const shopName = $form.find("[name='shopName']").val();

    if (files.length === 0) {
        alert("Please select a file.");
        return;
    }

    const fd = new FormData();
    fd.append('file', files[0]);
    fd.append('shopName', shopName);

    $.ajax({
        url: '../models/upload.php',
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response !== "0" && response !== "") {
                const filePath = response;
                const fileName = response.split("/").pop();
                $filePath.val(filePath);
                $fileName.val(fileName);
            } else {
                alert("File not uploaded.");
            }
        },
        error: function() {
            alert("Upload failed. Try again.");
        }
    });
};

// Validation
// document.addEventListener('DOMContentLoaded', function () {
//     const form = document.getElementById('graphicForm');
    
//     const shopTypeSelect = document.getElementById('shopType');
//     const shopTypeOtherWrapper = document.getElementById('shopTypeOtherWrapper');
//     const shopTypeOtherInput = document.getElementById('shopTypeOtherInput');

//     const packageSelect = document.getElementById('package');
//     const packageOtherWrapper = document.getElementById('packageOtherWrapper');
//     const packageOtherInput = document.getElementById('packageOtherInput');

//     form.addEventListener('submit', function (e) {
//         let isValid = true;

//         const requiredFields = ['name', 'shopName', 'email', 'shopType', 'package', 'description'];
//         requiredFields.forEach(id => {
//             const el = document.getElementById(id);
//             if (el && !el.value.trim()) {
//                 el.classList.add('is-invalid');
//                 isValid = false;
//             } else if (el) {
//                 el.classList.remove('is-invalid');
//             }
//         });

//         const email = document.getElementById('email').value;
//         const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//         if (!emailPattern.test(email)) {
//             document.getElementById('email').classList.add('is-invalid');
//             isValid = false;
//         }

//         if (shopTypeSelect.value === 'Other') {
//             if (!shopTypeOtherInput.value.trim()) {
//                 shopTypeOtherInput.classList.add('is-invalid');
//                 shopTypeOtherWrapper.classList.remove('d-none');
//                 isValid = false;
//             } else {
//                 shopTypeOtherInput.classList.remove('is-invalid');
//             }
//         }

//         if (packageSelect.value === 'Other') {
//             if (!packageOtherInput.value.trim()) {
//                 packageOtherInput.classList.add('is-invalid');
//                 packageOtherWrapper.classList.remove('d-none');
//                 isValid = false;
//             } else {
//                 packageOtherInput.classList.remove('is-invalid');
//             }
//         }

//         if (!isValid) {
//             e.preventDefault();
//             document.getElementById('result').innerHTML = `
//                 <div class="alert alert-danger">Please fill in all required fields correctly.</div>
//             `;
//         }
//     });

//     shopTypeSelect.addEventListener('change', function () {
//         if (this.value === 'Other') {
//             shopTypeOtherWrapper.classList.remove('d-none');
//             shopTypeOtherInput.setAttribute('required', 'required');
//         } else {
//             shopTypeOtherWrapper.classList.add('d-none');
//             shopTypeOtherInput.removeAttribute('required');
//             shopTypeOtherInput.classList.remove('is-invalid');
//         }
//     });

//     packageSelect.addEventListener('change', function () {
//         if (this.value === 'Other') {
//             packageOtherWrapper.classList.remove('d-none');
//             packageOtherInput.setAttribute('required', 'required');
//         } else {
//             packageOtherWrapper.classList.add('d-none');
//             packageOtherInput.removeAttribute('required');
//             packageOtherInput.classList.remove('is-invalid');
//         }
//     });
// });
