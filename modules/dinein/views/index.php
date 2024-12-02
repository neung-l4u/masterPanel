<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineInGenNumTable</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
        <h1 class="mb-4">Enter Shop Details</h1>
        <form action="process_form.php" method="post">
            <div class="form-group">
                <label for="shopName">Shop Name:</label>
                <input type="text" id="shopName" name="shopName" class="form-control">
            </div>
            <div class="form-group">
                <label for="numTables">Number of Tables:</label>
                <input type="number" id="numTables" name="numTables" class="form-control">
            </div>
            <div class="form-group">
                <label for="pageSize">Page Size:</label>
                <select id="pageSize" name="pageSize" class="form-control">
                    <option value="A5">A5</option>
                    <option value="A4">A4</option>
                </select>
            </div>
            <div class="form-group">
                <label for="logo">Upload Logo:</label>
                <input type="file" id="logo" name="logo" class="form-control-file">
            </div>
            <div class="form-group">
                <label for="qrcode">Upload QR Code:</label>
                <input type="file" id="qrcode" name="qrcode" class="form-control-file">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</body>
</html>