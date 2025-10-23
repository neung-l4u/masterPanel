<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Log | ระบบจัดการอัปเดต</title>

    <!-- CSS -->
    <style>
        body {
            font-family: "Prompt", sans-serif;
            margin: 0;
            background: #f6f8fa;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2d3436;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            padding: 12px 15px;
            margin-bottom: 8px;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.2s;
        }
        .sidebar li:hover, .sidebar li.active {
            background: #0984e3;
        }
        .content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
        }
        #logContent {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>

<div class="container">
    <div class="sidebar">
        <h2>Change Log</h2>
        <ul>
            <li data-page="signup" class="active">Signup Form</li>
            <li data-page="website">Website Submission</li>
            <li data-page="voucher">Gift Voucher</li>
            <li data-page="amelia">Amelia</li>
        </ul>
    </div>

    <div class="content">
        <div id="logContent">กำลังโหลดข้อมูล...</div>
    </div>
</div>

<script>
    $(document).ready(function(){
        // โหลดหน้าแรก
        loadLog("signup");

        // เมื่อคลิกเมนู
        $(".sidebar li").click(function(){
            $(".sidebar li").removeClass("active");
            $(this).addClass("active");
            let page = $(this).data("page");
            loadLog(page);
        });

        function loadLog(page){
            $("#logContent").html("⏳ กำลังโหลด...");
            $.ajax({
                url: "logs/" + page + ".php",
                type: "GET",
                success: function(data){
                    $("#logContent").html(data);
                },
                error: function(){
                    $("#logContent").html("❌ ไม่พบข้อมูลของ " + page);
                }
            });
        }
    });
</script>

</body>
</html>
