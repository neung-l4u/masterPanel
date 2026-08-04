(function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput  = document.getElementById('slipFile');
    const fileName   = document.getElementById('fileName');
    const previewImg = document.getElementById('previewImg');
    const form       = document.getElementById('slipUploadForm');
    const btnSubmit  = document.getElementById('btnSubmit');
    const msgBox     = document.getElementById('uploadMessage');

    if (uploadArea) {
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
    }

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        fileName.textContent = file.name;
        uploadArea.classList.add('has-file');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.classList.add('hidden');
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!fileInput.files[0]) {
            showMsg('กรุณาเลือกไฟล์สลิป', 'error');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner"></span> กำลังส่ง...';

        const fd = new FormData(form);
        const actionUrl = form.getAttribute('action') || 'assets/php/upload.php';

        fetch(actionUrl, {
            method: 'POST',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                form.style.display = 'none';
                showMsg('✅ ส่งหลักฐานเรียบร้อยแล้ว ทีม Billing จะตรวจสอบและส่ง Receipt ให้ลูกค้าต่อไป', 'success');
            } else {
                showMsg('❌ ' + (res.message || 'เกิดข้อผิดพลาด'), 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '📤 ส่งหลักฐานการชำระเงิน';
            }
        })
        .catch(function(err) {
            showMsg('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '📤 ส่งหลักฐานการชำระเงิน';
        });
    });

    function showMsg(text, type) {
        msgBox.textContent = text;
        msgBox.className = 'message ' + type;
        msgBox.classList.remove('hidden');
    }
})();
