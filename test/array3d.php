<?php
$tower['Kitak'] = array(
    'Tower1' => array(
        'Floor1' => array(
            '01' => 'Seven',
            '02' => 'Eight',
            '03' => 'Nine'
        ),
        'Floor2' => array(
            '01' => 'Clinic',
            '02' => 'Pet shop',
            '03' => 'Studio'
        ),
        'Floor3' => array(
            '01' => 'Kitchen',
            '02' => 'Bathroom',
            '03' => 'Local For You'
        )
    )
)
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<pre>
    <?php print_r($tower); ?>
</pre>
<p>
    <?php echo json_encode($tower); ?>
</p>
</body>
</html>