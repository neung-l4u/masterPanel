$('#feedbackForm').on('submit', function (e) {
    e.preventDefault();
    const result = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    result.html(`<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>`);
    cmdSubmit.hide();

    const formData = $(this).serializeArray().reduce((obj, item) => {
        obj[item.name] = item.value;
        return obj;
    }, {});
    const payload = JSON.stringify(formData);
    sendMail(formData);
    saveToDB(payload, result, cmdSubmit);
});

function sendMail(formData) {
    const now = new Date();
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}-${String(now.getMonth() + 1).padStart(2, '0')}-${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    const selectedPackage = formData.package === "Other" ? "Other: " + formData.otherInput : formData.package;
    //const modulePath = "http://localhost/masterPanel/modules/feedback"; // Local path
    const modulePath = "https://report.localforyou.com/modules/feedback"; // Server path
    const filePath = formData.filePath || "";
    const attachFile = filePath ? `<a href="${modulePath}${filePath}" target="_blank">View Attachment</a>` : 'No File Attached';

    const jsonData = {
        name: formData.name || "No Name",
        shopName: formData.shopName || "No Shop Name",
        email: formData.email || "No Email",
        shopType: formData.shopType || "No Shop Type",
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
        url: "../models/feedbackForm.php",
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
                    window.location.reload();
                }
            }, 1000);
        },
        error: () => {
            result.html(`<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> An error occurred. Please try again.</div>`);
            cmdSubmit.show();
        }
    });
}

function toggleOtherInput(select) {
    const otherInput = document.getElementById('otherInputWrapper');
    if (select.value === 'Other') {
      otherInput.classList.remove('d-none');
    } else {
      otherInput.classList.add('d-none');
    }
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

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('feedbackForm');
    const packageSelect = document.getElementById('package');
    const otherInputWrapper = document.getElementById('otherInputWrapper');
    const otherInput = document.getElementById('otherInput');

    form.addEventListener('submit', function (e) {
        let isValid = true;

        // Check required fields
        const requiredFields = ['name', 'shopName', 'email', 'shopType', 'package', 'description'];
        requiredFields.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                el.classList.add('is-invalid');
                isValid = false;
            } else {
                el.classList.remove('is-invalid');
            }
        });

        // Validate email pattern
        const email = document.getElementById('email').value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            document.getElementById('email').classList.add('is-invalid');
            isValid = false;
        }

        // Show "Other" field if "Other" is selected
        if (packageSelect.value === 'Other') {
            if (!otherInput.value.trim()) {
                otherInput.classList.add('is-invalid');
                otherInputWrapper.classList.remove('d-none');
                isValid = false;
            } else {
                otherInput.classList.remove('is-invalid');
            }
        }

        if (!isValid) {
            e.preventDefault(); // prevent form submission
            document.getElementById('result').innerHTML = `
                <div class="alert alert-danger">Please fill in all required fields correctly.</div>
            `;
        }
    });

    packageSelect.addEventListener('change', function () {
        if (this.value === 'Other') {
            otherInputWrapper.classList.remove('d-none');
            otherInput.setAttribute('required', 'required');
        } else {
            otherInputWrapper.classList.add('d-none');
            otherInput.removeAttribute('required');
            otherInput.classList.remove('is-invalid');
        }
    });
});
// $(() => {
// 
// }); //ready