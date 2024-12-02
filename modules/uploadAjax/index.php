<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Upload Ajax</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"> <!-- v5.3.2 -->
    <style>

        .col {
            word-wrap: break-word;
        }

        /* Preview */
        .preview {
            width: 100%;
            max-height: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border: 1px solid black;
            margin: 0 auto;
            background: white;
        }

        /* Button */
        .button {
            border: 0px;
            background-color: deepskyblue;
            color: white;
            padding: 5px 15px;
            margin-left: 10px;
            margin-bottom: 5rem;
        }

        h6{
            font-weight: bold;
            font-size: 1.4rem;
        }
    </style>
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Upload Ajax</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container p-5">
        <div class="row" id="template1">
            <h2>Template 1</h2>

            <!-- Column 1 -->
            <div class="col-4">
                <form method="post" action="" enctype="multipart/form-data" id="myFormCover">
                    <div>
                        <img class="preview" src="default.png" id="imgCover" alt="place">
                        <p id="picNameCover"></p>
                        <h6 id="prefixCover">Cover</h6>
                        <input type="file" id="fileCover" />
                        <button type="submit" class="button" id="btnUploadCover">Upload</button>
                    </div>
                </form>
            </div>

            <!-- Column 2 -->
            <div class="col-4">
                <form method="post" action="" enctype="multipart/form-data" id="myFormSS">
                    <div>
                        <img class="preview" src="default.png" id="imgSS" alt="place">
                        <p id="picNameSS"></p>
                        <h6 id="prefixSS">Screenshot</h6>
                        <input type="file" id="fileSS" />
                        <button type="submit" class="button" id="btnUploadSS">Upload</button>
                    </div>
                </form>
            </div>

            <!-- Column 3 -->
            <div class="col-4">
                <form method="post" action="" enctype="multipart/form-data" id="myFormModel">
                    <div>
                        <img class="preview" src="default.png" id="imgModel" alt="place">
                        <p id="picNameModel"></p>
                        <h6 id="prefixModel">Model</h6>
                        <input type="file" id="fileModel" />
                        <button type="submit" class="button" id="btnUploadModel">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script> <!-- v5.3.2 -->
    <script>
        $(() => { //document ready

            function handleFormSubmit(formId, fileInputId, imgId, prefixId, picNameId) {
                $("#" + formId).on("submit", function (e) { // จะทำงานก็ต่อเมื่อกด submit ฟอร์ม
                    e.preventDefault(); // ปิดการใช้งาน submit ปกติ เพื่อใช้งานผ่าน ajax

                    let fd = new FormData(); // สร้างตัวแปรมาเตรียมไว้ที่เราจะเก็บข้อมูลในฟอร์ม (รูปที่เลือกมา)
                    let files = $("#" + fileInputId)[0].files; //เป็นการดึงข้อมูลรูปภาพเพื่อเตรียมเช็คไฟล์ก่อนทำงานส่วน Ajax
                    let projectId = 55;

                    if (files.length > 0) { // เช็คว่ามีไฟล์รูปภาพอยู่หรือไม่

                        fd.append('file', files[0]); //ดึงไฟล์รูปภาพไปใส่ตัวแปรที่เตรียมไว้
                        //fd.append('prefix', $prefix);
                        fd.append('projectId', projectId);
                        fd.append('prefixId', prefixId);

                        $.ajax({
                            url: 'upload.php',
                            type: 'post',
                            data: fd, //ข้อมูลไฟล์รูปภาพจาก input ที่อ่านมา
                            contentType: false,
                            processData: false,
                            success: function(response) { //พอทำงาน ajax สำเร็จ จะรับค่ามาจาก JSON เก็บในตัวแปร response

                                if (response !== "0") {

                                    $("#" + imgId).attr("src", response);
                                    $("#" + picNameId).html(newName);
                                } else {
                                    alert("File not uploaded");
                                }
                            }
                        });
                    } else {
                        alert("Please select a file.");
                    }
                });
            }

            handleFormSubmit("myFormCover", "fileCover", "imgCover", "prefixCover", "picNameCover");
            handleFormSubmit("myFormSS", "fileSS", "imgSS", "prefixSS", "picNameSS");
            handleFormSubmit("myFormModel", "fileModel", "imgModel", "prefixModel", "picNameModel");

        }); //ready
    </script>
</body>

</html>