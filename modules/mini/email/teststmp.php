<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>STMP</title>
</head>
<body>
<h2>STMP</h2>
<p>
    result is
    <?php
    $f = fsockopen('smtp host', 25) ;
    if ($f !== false) {
        $res = fread($f, 1024) ;
        if (strlen($res) > 0 && strpos($res, '220') === 0) {
            echo "Success!" ;
        }
        else {
            echo "Error: " . $res ;
        }
    }
    fclose($f) ;

    ?>
</p>

</body>
</html>