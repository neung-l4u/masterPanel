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
        /* Preview */
        .preview{
            width: 300px;
            height: 300px;
            border: 1px solid black;
            margin: 0 auto;
            background: white;
        }

        /* Button */
        .button{
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
    
        <div class="row">
            <div class="col">
                <form method="post" action="" enctype="multipart/form-data" id="myForm">
                    <div>
                        <img class="preview" src="default.png" id="img" alt="place">
                        <p id="picName"></p>
                        <h6>Cover</h6>
                        <input type="file" id="fileCover" />
                        <input type="button" class="button" value="Upload" onclick="uploadIT('myForm', 11, 'cover', 'imgCover');" id="but_upload">
                    </div>
                </form>
            </div>
            <div class="col">
                <form method="post" action="" enctype="multipart/form-data" id="myForm2">
                    <div >
                        <img class="preview" src="default.png" id="img2" alt="place">
                        <p id="picName2"></p>
                        <h6>Screenshot</h6>
                        <input type="file" id="fileSS" />
                        <input type="button" class="button" value="Upload" onclick="uploadIT('myForm2', 11, 'ss', 'imgSS1');" id="but_upload2">
                    </div>
                </form>
            </div>
            <div class="col">
                <form method="post" action="" enctype="multipart/form-data" id="myForm3">
                    <div >
                        <img class="preview" src="default.png" id="img3" alt="place">
                        <p id="picName3"></p>
                        <h6>Model</h6>
                        <input type="file" id="fileModel" />
                        <input type="button" class="button" value="Upload" onclick="uploadIT('myForm3', 11, 'model', 'imgModel');" id="but_upload3">
                    </div>
                </form>
            </div>
        </div>
    
    </div><!--container-->

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script> <!-- v5.3.2 -->
    <script>
        let fd = new FormData(); // สร้างตัวแปรมาเตรียมไว้ที่เราจะเก็บข้อมูลในฟอร์ม (รูปที่เลือกมา)

        $(()=>{ //document ready
            $("#myForm").on("submit",function(e){
                e.preventDefault();// ปิดการใช้งาน submit ปกติ เพื่อใช้งานผ่าน ajax
            });

            $("#myForm2").on("submit",function(e){
                e.preventDefault();// ปิดการใช้งาน submit ปกติ เพื่อใช้งานผ่าน ajax
            });

            $("#myForm3").on("submit",function(e){
                e.preventDefault();// ปิดการใช้งาน submit ปกติ เพื่อใช้งานผ่าน ajax
            });

        });//ready

        const uploadIT = (formID, projectID, prefix, folder) => {
            if (formID === 'myForm') {
                let files = $('#fileCover')[0].files;
            }else if (formID === 'myForm2') {
                let files = $('#fileSS')[0].files;
            }else if (formID === 'myForm3') {
                let files = $('#fileModel')[0].files;
            }

            if(files.length > 0 ){ // เช็คว่ามีไฟล์รูปภาพอยู่หรือไม่
                fd.append('file',files[0]); //ดึงไฟล์รูปภาพไปใส่ตัวแปรที่เตรียมไว้
            }else{
                alert("Please select a file.");
                return false;
            }

            $.ajax({
                url:'upload.php',
                type:'post',
                data: {
                    file: fd,//ข้อมูลไฟล์รูปภาพจาก input ที่อ่านมา
                    form: formID,
                    prefix: prefix,
                    folder: folder
                    projectID: projectID
                }
                contentType: false,
                processData: false,
                success:function(response){ //พอทำงาน ajax สำเร็จ จะรับค่ามาจาก JSON เก็บในตัวแปร response

                    if(response !== 0){
                        $("#img").attr("src",response);
                        $('#picName').html(response);
                    }else{
                        alert('File not uploaded');
                    }
                }
            });

        }//uploadIT

            /*$("#myForm").on("submit",function(e){ // จะทำงานก็ต่อเมื่อกด submit ฟอร์ม
                e.preventDefault(); // ปิดการใช้งาน submit ปกติ เพื่อใช้งานผ่าน ajax
                let fd = new FormData(); // สร้างตัวแปรมาเตรียมไว้ที่เราจะเก็บข้อมูลในฟอร์ม (รูปที่เลือกมา)

                let files = $('#file')[0].files; //เป็นการดึงข้อมูลรูปภาพเพื่อเตรียมเช็คไฟล์ก่อนทำงานส่วน Ajax

                if(files.length > 0 ){ // เช็คว่ามีไฟล์รูปภาพอยู่หรือไม่

                    fd.append('file',files[0]); //ดึงไฟล์รูปภาพไปใส่ตัวแปรที่เตรียมไว้

                    $.ajax({
                        url:'upload.php',
                        type:'post',
                        data:fd, //ข้อมูลไฟล์รูปภาพจาก input ที่อ่านมา
                        contentType: false,
                        processData: false,
                        success:function(response){ //พอทำงาน ajax สำเร็จ จะรับค่ามาจาก JSON เก็บในตัวแปร response

                            if(response !== 0){
                                $("#img").attr("src",response);
                                $('#picName').html(response);
                            }else{
                                alert('File not uploaded');
                            }
                        }
                    });
                }else{
                    alert("Please select a file.");
                }
            });
*/

    </script>
</body>
</html>