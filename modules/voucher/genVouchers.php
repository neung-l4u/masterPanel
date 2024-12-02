<?php
date_default_timezone_set("Asia/Bangkok");
$timestamp = time();
$filename = date("Y-m-d-His", $timestamp);
$image = ImageCreateFromJpeg("https://signup.localforyou.com/voucher/assets/img/Voucher2-frame.jpg"); // Path Images
$font = "./Roboto-Black.ttf";

$code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
$valid = !empty($_REQUEST['valid']) ? trim($_REQUEST['valid']) : '';
$discount = !empty($_REQUEST['discount']) ? trim($_REQUEST['discount']) : '';
$url = !empty($_REQUEST['url']) ? trim($_REQUEST['url']) : '';
$phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';

$apiResponse = [
    'success' => true,
    'data' => [
        'message' => 'API request successful!',
        'result' => [
            'code' => $code,
            'valid' => $valid,
            'discount' => $discount,
            'url' => $url,
            'phone' => $phone
        ]
    ],
    'error' => [
        'message' => 'OK',
        'code' => '200'
    ]
];

if(
        empty($apiResponse['data']['result']['code'])
        or empty($apiResponse['data']['result']['valid'])
        or empty($apiResponse['data']['result']['discount'])
        or empty($apiResponse['data']['result']['url'])
        or empty($apiResponse['data']['result']['phone'])
){
    $apiResponse['success'] = false;
    $apiResponse['data']['message'] = 'Missing params';
    $apiResponse['error']['message'] = 'Bad Request';
    $apiResponse['error']['code'] = '400';
}

if ($apiResponse['success']) {
    $color["white"] = ImageColorAllocate($image, 255, 255, 255); // Text Color
    $color["gray"] = ImageColorAllocate($image, 237, 237, 237); // Text Color
    $color["black"] = ImageColorAllocate($image, 77, 77, 77); // Text Color
    $color["yellow"] = ImageColorAllocate($image, 250, 255, 153); // Text Color

    $param = [
        "code" => [
            "size" => 14,
            "angle" => 0,
            "value" => $code,
            "x" => Imagesx($image) - 180,
            "y" => 50,
            "color" => $color["yellow"],
            "font" => $font
        ],
        "valid" => [
            "size" => 16,
            "angle" => 0,
            "value" => $valid,
            "x" => 235,
            "y" => Imagesy($image) - 237,
            "color" => $color["black"],
            "font" => $font
        ],
        "discount" => [
            "size" => 16,
            "angle" => 0,
            "value" => $discount,
            "x" => 260,
            "y" => Imagesy($image) - 158,
            "color" => $color["yellow"],
            "font" => $font
        ],
        "url" => [
            "size" => 12,
            "angle" => 0,
            "value" => $url,
            "x" => 68,
            "y" => Imagesy($image) - 128,
            "color" => $color["black"],
            "font" => $font
        ],
        "phone" => [
            "size" => 10,
            "angle" => 0,
            "value" => $phone,
            "x" => Imagesx($image) - 155,
            "y" => Imagesy($image) - 43,
            "color" => $color["gray"],
            "font" => $font
        ],
    ];

    imagettftext(
        $image,
        $param["code"]["size"],
        $param["code"]["angle"],
        $param["code"]["x"],
        $param["code"]["y"],
        $param["code"]["color"],
        $param["code"]["font"],
        $param["code"]["value"]
    );
    imagettftext(
        $image,
        $param["valid"]["size"],
        $param["valid"]["angle"],
        $param["valid"]["x"],
        $param["valid"]["y"],
        $param["valid"]["color"],
        $param["valid"]["font"],
        $param["valid"]["value"]
    );
    imagettftext(
        $image,
        $param["discount"]["size"],
        $param["discount"]["angle"],
        $param["discount"]["x"],
        $param["discount"]["y"],
        $param["discount"]["color"],
        $param["discount"]["font"],
        $param["discount"]["value"]
    );
    imagettftext(
        $image,
        $param["url"]["size"],
        $param["url"]["angle"],
        $param["url"]["x"],
        $param["url"]["y"],
        $param["url"]["color"],
        $param["url"]["font"],
        $param["url"]["value"]
    );
    imagettftext(
        $image,
        $param["phone"]["size"],
        $param["phone"]["angle"],
        $param["phone"]["x"],
        $param["phone"]["y"],
        $param["phone"]["color"],
        $param["phone"]["font"],
        $param["phone"]["value"]
    );

    imagePng($image, "assets/genImg/" . $timestamp . ".jpg"); //save image
    ImageDestroy($image); // clear temp
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://signup.localforyou.com/voucher/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://signup.localforyou.com/voucher/assets/css/genVoucher.css">
    <title>L4U Voucher</title>
</head>
<body>
<?php if ($apiResponse['success']) { ?>
    <div class="container pt-5 pb-5">
        <div class="row mb-3">
            <div class="col">
                <div class="card" style="width:70%">
                    <img src="https://signup.localforyou.com/voucher/assets/genImg/<?php echo $timestamp; ?>.jpg" alt="Voucher" title="<?php echo $param['url']['value']; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="card" style="width:70%">
                    <div class="card-header">
                        Voucher Instruction
                    </div>
                    <div class="card-body p-4">
                        <div class="card-text mb-5">
                            <div class="text-primary mb-3">
                                Voucher <?php echo $param['discount']['value']; ?> is valid until <?php echo $param['valid']['value']; ?>
                            </div>
                            <strong>You can use this voucher in the following 2 ways.</strong>
                            <ol class="txtIns mb-3">
                                <li>Book a massage appointment through the shop's website. (<?php echo $param['url']['value']; ?>)</li>
                                <li>Show it at the store to the employee when paying.</li>
                            </ol>
                            <ul class="txtWarning text-muted mb-4">
                                <li>This voucher can only be used once.</li>
                                <li>Gift vouchers cannot be exchanged for cash.</li>
                                <li>The company reserves the right to change any conditions. without having to notify in advance</li>
                            </ul>
                            <div class="text-primary">
                                <small>
                                    if you have any questions please call : <?php echo $param['phone']['value']; ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a id="download" class="btn btn-primary" href="#" download="https://signup.localforyou.com/voucher/assets/genImg/<?php echo $timestamp; ?>.jpg" role="button">Save file</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }else{ echo json_encode($apiResponse); } ?>
<script src="https://signup.localforyou.com/voucher/assets/js/jquery-3.7.1.min.js"></script>
<script src="https://signup.localforyou.com/voucher/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>