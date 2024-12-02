<?php
date_default_timezone_set("Asia/Bangkok");
$timestamp = time();
$filename = date("Y-m-d-His", $timestamp);
$image = ImageCreateFromJpeg("https://signup.localforyou.com/voucher/assets/img/Voucher3-frame.jpg"); // Path Images
//$image = ImageCreateFromJpeg("assets/img/Voucher3-frame.jpg"); // Path Images
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
    $color["black"] = ImageColorAllocate($image, 77, 77, 77); // Text Color
    $color["yellow"] = ImageColorAllocate($image, 250, 255, 153); // Text Color

    $param = [
        "code" => [
            "size" => 14,
            "angle" => 0,
            "value" => $code,
            "x" => 260,
            "y" => Imagesy($image) - 158,
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
            "size" => 32,
            "angle" => 0,
            "value" => $discount,
            "x" => Imagesx($image) - 208,
            "y" => 134,
            "color" => $color["black"],
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
        "issueBy" => [
            "size" => 12,
            "angle" => 0,
            "value" => "ISSUE BY: ".$issueBy,
            "x" => 68,
            "y" => Imagesy($image) - 108,
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
<?php }else{ echo json_encode($apiResponse); } ?>