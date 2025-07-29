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

    const selectedPackage = formData.package === "other" ? formData.otherInput : formData.package;
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
    if (select.value === 'other') {
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

// $(() => {
// 
// }); //ready