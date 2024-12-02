<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pageSize = $_POST['pageSize'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dine In Tables</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="process_form.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 mb-5">
        <h1 class="mb-4">Dine In Tables</h1>
        <div class="<?php echo $pageSize == 'A5' ? 'a5-block' : 'a4-block'; ?>">
            
        </div>
    </div>
</body>
</html>
