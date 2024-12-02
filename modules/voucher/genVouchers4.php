<?php
date_default_timezone_set("Asia/Bangkok");
$timestamp = time();
$filename = date("Y-m-d-His", $timestamp);
$image = ImageCreateFromJpeg("https://signup.localforyou.com/voucher/assets/img/Voucher4.2-frame.jpg"); // Path Images
//$image = ImageCreateFromJpeg("assets/img/Voucher4.2-frame.jpg"); // Path Images
$font = "./Roboto-Black.ttf";

$code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
$valid = !empty($_REQUEST['valid']) ? trim($_REQUEST['valid']) : '';
$discount = !empty($_REQUEST['discount']) ? trim($_REQUEST['discount']) : '';
$url = !empty($_REQUEST['url']) ? trim($_REQUEST['url']) : '';
$phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
$issueBy = !empty($_REQUEST['issueBy']) ? trim($_REQUEST['issueBy']) : '';

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
    $color["DarkGray"] = ImageColorAllocate($image, 153, 153, 153); // Text Color
    $color["black"] = ImageColorAllocate($image, 77, 77, 77); // Text Color
    $color["yellow"] = ImageColorAllocate($image, 250, 255, 153); // Text Color
    $color["DarkYellow"] = ImageColorAllocate($image, 219, 165, 19); // Text Color

    $param = [
        "code" => [
            "size" => 14,
            "angle" => 0,
            "value" => $code,
            "x" => 260,
            "y" => Imagesy($image) - 160,
            "color" => $color["yellow"],
            "font" => $font
        ],
        "valid" => [
            "size" => 11,
            "angle" => 0,
            "value" => $valid,
            "x" => 170,
            "y" => Imagesy($image) - 130,
            "color" => $color["black"],
            "font" => $font
        ],
        "discount" => [
            "size" => 80,
            "angle" => 0,
            "value" => $discount,
            "x" => Imagesx($image) - 400,
            "y" => 245,
            "color" => $color["DarkYellow"],
            "font" => $font
        ],
        "url" => [
            "size" => 12,
            "angle" => 0,
            "value" => $url,
            "x" => 68,
            "y" => Imagesy($image) - 28,
            "color" => $color["DarkGray"],
            "font" => $font
        ],
        "issueBy" => [
            "size" => 11,
            "angle" => 0,
            "value" => $issueBy,
            "x" => 152,
            "y" => Imagesy($image) - 218,
            "color" => $color["black"],
            "font" => $font
        ],
        "phone" => [
            "size" => 11,
            "angle" => 0,
            "value" => $phone,
            "x" => Imagesx($image) - 248,
            "y" => Imagesy($image) - 29,
            "color" => $color["DarkGray"],
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
        $param["issueBy"]["size"],
        $param["issueBy"]["angle"],
        $param["issueBy"]["x"],
        $param["issueBy"]["y"],
        $param["issueBy"]["color"],
        $param["issueBy"]["font"],
        $param["issueBy"]["value"]
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
<?php if ($apiResponse['success']) { ?>
                    <img src="https://signup.localforyou.com/voucher/assets/genImg/<?php echo $timestamp; ?>.jpg">
<!--                    <img src="assets/genImg/--><?php //echo $timestamp; ?><!--.jpg">-->
<?php }else{ echo json_encode($apiResponse); } ?>