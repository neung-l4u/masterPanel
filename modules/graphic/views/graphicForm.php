<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/graphicForm.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <title>Graphic - Form</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
    </div>
</nav>

<div class="container">
    <main>
        <section style="min-height: 50vh;">
            <div class="form-div">
                <img src="../assets/img/L4U-Site-Icon.png" alt="Logo Image" class="mb-4 logo-img">
                <h3 class="mb-4 text-center text-uppercase form-title">Graphic Form</h3>
                <p class="text-muted mt-3">
                    Our graphic request system allows you to easily share your design ideas, requests, or concerns. Every submission helps us craft visuals that better match your vision and elevate your experience.
                </p>
                <form id="graphicForm" name="graphicForm" method="post" enctype="multipart/form-data">

                    <div class="mb-4">
                        <label for="name" class="form-label"><i class="bi bi-file-person"></i> ชื่อผู้ติดต่อ</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="เช่น " required>
                    </div>

                    <div class="mb-4">
                        <label for="shopName" class="form-label"><i class="bi bi-shop-window"></i> ชื่อร้าน</label>
                        <input type="text" class="form-control" id="shopName" name="shopName" placeholder="เช่น The Calm Spa" required>
                    </div>

                    <div class="mb-4">
                        <label for="phoneNumber" class="form-label"><i class="bi bi-telephone"></i> เบอร์โทรศัพท์ติดต่อ</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="เช่น 082-345-6789" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label"><i class="bi bi-envelope"></i> อีเมลสำหรับติดต่อกลับ</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="เช่น hello@gmail.com" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-brush"></i> ประเภทงานที่ต้องการออกแบบ</label>
                        <small class="form-text text-muted mb-2">(เลือกได้มากกว่า 1 ข้อ)</small>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="type[]" value="โลโก้" id="typeLogo">
                            <label class="form-check-label" for="typeLogo">โลโก้</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="type[]" value="โปสเตอร์ / ใบปลิว" id="typePoster">
                            <label class="form-check-label" for="typePoster">โปสเตอร์ / ใบปลิว</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="type[]" value="เมนูอาหาร" id="typeMenu">
                            <label class="form-check-label" for="typeMenu">เมนูอาหาร</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="type[]" value="โซเชียลมีเดียโพสต์" id="typeSocial">
                            <label class="form-check-label" for="typeSocial">โซเชียลมีเดียโพสต์</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="type[]" value="ป้ายหน้าร้าน / สติ๊กเกอร์" id="typeSign">
                            <label class="form-check-label" for="typeSign">ป้ายหน้าร้าน / สติ๊กเกอร์</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="อื่นๆ" id="typeOtherCheckbox">
                            <label class="form-check-label" for="typeOtherCheckbox">อื่นๆ (โปรดระบุด้านล่าง)</label>
                        </div>

                        <div class="mt-2 d-none" id="typeOtherWrapper">
                            <input type="text" class="form-control" id="typeOtherInput" name="type[]" placeholder="ระบุประเภทงานอื่นๆ ที่ต้องการ">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="size" class="form-label"><i class="bi bi-aspect-ratio"></i> ขนาดของงาน</label>
                        <small class="form-text text-muted mb-2">(ถ้าทราบ)</small>
                        <input type="text" class="form-control mb-2" id="size" name="size" placeholder="เช่น A4, A3, 1080x1080px ฯลฯ">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="ยังไม่แน่ใจ / ให้ทีมแนะนำ" id="sizeUnsure" name="sizeUnsure">
                            <label class="form-check-label" for="sizeUnsure">ยังไม่แน่ใจ / ให้ทีมแนะนำ</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="deadline" class="form-label"><i class="bi bi-calendar-event"></i> วันที่ต้องการใช้งาน</label>
                        <input type="date" class="form-control mb-2" id="deadline" name="deadline">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="ยังไม่เร่งด่วน / คุยเพิ่มเติมก่อน" id="deadlineUnsure" name="deadlineUnsure">
                            <label class="form-check-label" for="deadlineUnsure">ยังไม่เร่งด่วน / คุยเพิ่มเติมก่อน</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-cash-stack"></i> งบประมาณที่มีสำหรับงานนี้</label>
                        <small class="form-text text-muted mb-2">(โดยประมาณ)</small>
                        <div class="form-check"><input class="form-check-input" type="radio" name="budget" value="ต่ำกว่า 3,000 บาท" id="budget1"><label class="form-check-label" for="budget1">ต่ำกว่า 3,000 บาท</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="budget" value="3,000-5,000 บาท" id="budget2"><label class="form-check-label" for="budget2">3,000 - 5,000 บาท</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="budget" value="5,000-10,000 บาท" id="budget3"><label class="form-check-label" for="budget3">5,000 - 10,000 บาท</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="budget" value="มากกว่า 10,000 บาท" id="budget4"><label class="form-check-label" for="budget4">มากกว่า 10,000 บาท</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="budget" value="ยังไม่แน่ใจ ขอให้เสนอราคา" id="budget5"><label class="form-check-label" for="budget5">ยังไม่แน่ใจ ขอให้เสนอราคา</label></div>
                    </div>

                    <div class="mb-3" id="imageDiv">
                        <label for="image" class="form-label"><i class="bi bi-file-earmark-arrow-up"></i> แนบตัวอย่างงานที่ชอบ (ถ้ามี)</label>
                        <small class="form-text text-muted mb-2">ลิงก์ผลงาน / เว็บไซต์ / ไฟล์ภาพ</small>
                        <input type="file" id="image" name="image" class="form-control file-input mb-2" onchange="handleFileUpload(this)" accept="image/*">
                        <input type="hidden" name="filePath" class="filePath w-100">
                        <input type="hidden" name="fileName" class="fileName w-100">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label"><i class="bi bi-chat-right-text"></i> หมายเหตุเพิ่มเติม / ข้อมูลอื่นๆ ที่อยากให้ทีมทราบ</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" id="cmdSubmit" class="btn btn-submit">Submit</button>
                        <div id="result" class="mt-3"></div>
                    </div>

                    <input type="hidden" name="formVersion" value="1.1.0">
                    <input type="hidden" name="emailVersion" value="1.1.0">
                </form>

            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/graphicForm.js?v=1.0.0"></script>
<script>
$(function() {
    $('#typeOtherCheckbox').on('change', function () {
        if ($(this).is(':checked')) {
            $('#typeOtherWrapper').removeClass('d-none');
            $('#typeOtherInput').prop('required', true);
        } else {
            $('#typeOtherWrapper').addClass('d-none');
            $('#typeOtherInput').prop('required', false).val('');
        }
    });
});
</script>
</body>
</html>