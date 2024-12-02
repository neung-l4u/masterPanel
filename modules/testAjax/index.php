<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ajax</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"> <!-- v5.3.2 -->
</head>

<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Ajax</a>
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
            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="shopType" class="form-label">Shop Type</label>
                        <select id="shopType" class="form-select" aria-label="Default select example">
                            <option value="0" selected disabled>Open this select menu</option>
                            <option value="Massage">Massage</option>
                            <option value="Restaurant">Restaurant</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="template" class="form-label">Template</label>
                        <select id="template" class="form-select" aria-label="Default select example">
                            <option value="0" selected disabled>Open this select menu</option>
                            <option value="1">Massage 1</option>
                            <option value="2">Massage 2</option>
                            <option value="3">Massage 3</option>
                            <option value="4">Restaurant 1</option>
                            <option value="5">Restaurant 2</option>
                            <option value="6">Restaurant 3</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="po" class="form-label">Project Owner</label>
                        <input type="text" class="form-control" id="po" placeholder="Your name here">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="shopName" class="form-label">shop Name</label>
                        <input type="text" class="form-control" id="shopName" placeholder="shop name here">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="address" class="form-label">Shop Address</label>
                        <textarea class="form-control" id="address" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <input id="cmdSubmit" class="btn btn-primary" type="button" value="Save">
                </div>
            </div>
        </div>
        <div class="col">
            <div class="mb-3">
                <button type="button" id="loadHTML" class="btn btn-primary" data-toggle="button">Load HTML</button>
                <button type="button" id="loadArray" class="btn btn-primary" data-toggle="button">Load Array</button>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Data5</h5>
                    <div id="allData">
                        <table border="1" id="dataTable">
                            <tbody>

                            </tbody>
                        </table>
                    </div>

                    <div id="name"></div>
                    <div id="shopType2"></div>
                </div>
            </div>
        </div>
    </div>

</div><!--container-->

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script> <!-- v5.3.2 -->
    <script>
        $("#cmdSubmit").click(function () {

            //console.log("Form Submitted");

            let payload = {
                mode : "save",
                shop_name : $("#shopName").val(),
                shop_type : $("#shopType").val(),
                template : $("#template").val(),
                po_name : $("#po").val(),
                address : $("#address").val(),
                token: Math.random()
            };

            //console.log("Payload", payload);

            const callAjax = $.ajax({
                url: "assets/php/actionAjax.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload
            });

            callAjax.done(function(res) {
                console.log("return = ",res);
                resetForm();
                return true;
            });

            callAjax.fail(function(xhr, status, error) {
                console.log("ajax fail!!");
                console.log(status + ': ' + error);
                return false;
            });

        });//cmdSubmit.click

        $("#loadHTML").click(function () {
            $("#allData").empty();

            let payload = {
                mode : "loadHTML",
                token: Math.random()
            };

            const callAjax = $.ajax({
                url: "assets/php/loadHTML.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload
            });

            callAjax.done(function(res) {
                console.log("return = ",res.result);
                $("#allData").html(res.table);
                return true;
            });

            callAjax.fail(function(xhr, status, error) {
                console.log("ajax fail!!");
                console.log(status + ': ' + error);
                return false;
            });

        });//loadHTML.click

        $("#loadArray").click(function () {
            
            let payload = {
                mode : "loadArray",
                token: Math.random(),
                id : 44
            };

            const callAjax = $.ajax({
                url: "assets/php/loadArray.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload
            });

            callAjax.done(function(res) {
                console.log("return = ",res);

                let allData = res.data.length;
                let row = res.data;
                let i = 0;

                if (allData>0) {
                    row.forEach(item => {
                        $('#name').html(item.projectName);
                        $('#shopType2').html(item.projectID);

                    });//foreach
                }

                return true;
            });

            callAjax.fail(function(xhr, status, error) {
                console.log("ajax fail!!");
                console.log(status + ': ' + error);
                return false;
            });

        });//loadArray.click


        function resetForm() {
            $("#shopName").val('');
            $("#shopType").val(0);
            $("#template").val(0);
            $("#po").val('Steve');
            $("#address").val('');
        }//resetForm
    </script>
</body>
</html>