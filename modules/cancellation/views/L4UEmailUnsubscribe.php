<?php
session_start();

$json = $_REQUEST["allData"];

$data['mode'] = !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : 'confirm';
$data['shopName'] = !empty($_REQUEST['shopName']) ? $_REQUEST['shopName'] : null;
$data['fullName'] = !empty($_REQUEST['fullName']) ? $_REQUEST['fullName'] : null;
$data['email'] = !empty($_REQUEST['email']) ? $_REQUEST['email'] : null; //customer email

// $data['adminEmail'] = "admin@localforyou.com";
$data['adminEmail'] = "noreply@localforyou.com";
$data['l4uStaff'] = "bas@localforyou.com";  //อีเมล์ผู้ดูแลระบบ
$date['l4uDev'] = "mark@localforyou.com"; //อีเมล์ผู้ดูแลระบบ
$data['l4uSuperAdmin'] = "neung@localforyou.com";  //อีเมล์ผู้ดูแลระบบ


/// default result value ///
$result['result'] = 0;
$result['msg'] = "";
$result['email'] = $data['email'];
$result['mode'] = $data['mode'];

/////////////////////////


//////// test data /////
// $data['adminEmail'] = "bas@localforyou.com";
// $data['l4uStaff'] = "bas@localforyou.com";
// $data['l4uSuperAdmin'] = "bas@localforyou.com";
// $result['email'] = "bas@localforyou.com";
// $result['mode'] = "send";
// $data['mode'] = "send";
// $data['shopName'] = "Bas Shop";
// $data['fullName'] = "Neung";
// $data['email'] = "bas@localforyou.com";
///// end test data ////

if(!empty($data['email'])) {
    $data['subject'] = "L4U : Unsubscribe Request";

    $headers = [
        'From' => 'Local For You <'.$data['adminEmail'].'>',
        'Cc' => $data['l4uSuperAdmin'].','.$date['l4uDev'].','.$data['l4uStaff'],
        'Bcc' => $data['l4uStaff'],
        'Reply-To' => $data['adminEmail'],
        'X-Sender' => 'Local For You <'.$data['adminEmail'].'>',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1',
        'Return-Path' => $data['l4uSuperAdmin'], //for error
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=utf-8'
    ];

    $message = '
<!DOCTYPE html><html lang="en"><head><title></title><meta content="text/html; charset=utf-8" http-equiv="Content-Type"><meta content="width=device-width,initial-scale=1" name="viewport"><!--[if mso]><xml><o:officedocumentsettings><o:pixelsperinch>96</o:pixelsperinch><o:allowpng></o:officedocumentsettings></xml><![endif]--><link rel="stylesheet" href="../assets/css/email_cancellation.css"></head><body style="background-color:#d9dffa;margin:0;padding:0;-webkit-text-size-adjust:none;text-size-adjust:none"><table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;background-color:#d9dffa" width="100%"><tbody><tr><td><table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-1" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;background-color:#cfd6f4" width="100%"><tbody><tr><td><table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px" width="600"><tbody><tr><td class="column column-1" style="mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-top:20px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0" width="100%"><table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0" width="100%"><tr><td class="pad" style="width:100%;padding-right:0;padding-left:0"><div align="left" class="alignment" style="line-height:10px"><img src="https://signup.localforyou.com/assets/img/newL4U-logo-100x100.png" style="display:block;height:auto;border:0;width:100px;max-width:100%" width="100"></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-2 tbl1" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0" width="100%"><tbody><tr><td><table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px" width="600"><tbody><tr><td class="column column-1" style="mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-bottom:15px;padding-left:50px;padding-right:50px;padding-top:15px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0" width="100%"><table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word" width="100%"><tr><td class="pad"><div style="font-family:sans-serif"><div class="" style="font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#506bec;line-height:1.2"><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px"><strong><span style="font-size:38px"><span style="font-size:20px">' . $data['shopName'] . '</span></span></strong></p></div></div></td></tr></table><table border="0" cellpadding="10" cellspacing="0" class="text_block block-2" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word" width="100%"><tr><td class="pad"><div style="font-family:sans-serif"><div class="" style="font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#40507a;line-height:1.2"><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px"><strong>Subject :</strong>Unsubscribe email</p></div></div><div style="font-family:sans-serif"><p style="font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;font-weight:700;color:#506bec">Dear Team</p></div></td></tr></table><table border="0" cellpadding="10" cellspacing="0" class="text_block block-8" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word" width="100%"><tr><td class="pad"><div style="font-family:sans-serif"><div class="" style="font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#40507a;line-height:1.2"><p>Our Customer requests to unsubscribed our services</p><p>Please Check on<a href="https://report.localforyou.com/">L4U Master Panel</a></p></p><p style="margin-top:30px"><strong>Warm regards,</strong></p><p><strong>IT Team</strong></p><p style="margin:0;font-size:10px;mso-line-height-alt:16.8px;text-align:right">Having trouble?<a href="mailto:' . $data['adminEmail'] . '?subject=Problem%20:%20Confirmation%20email" rel="noopener" style="text-decoration:underline;color:#40507a" target="_blank" title="' . $data['adminEmail'] . '">' . $data['adminEmail'] . '</a></p></div></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-4" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0" width="100%"><tbody><tr><td><table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px" width="600"><tbody><tr><td class="column column-1" style="mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-left:10px;padding-right:10px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0" width="100%"><table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0" width="100%"><tr><td class="pad" style="width:100%;padding-right:0;padding-left:0"><div align="center" class="alignment" style="line-height:10px"><a href="http://www.localforyou.com/" style="outline:0" tabindex="-1" target="_blank"><img src="https://signup.localforyou.com/assets/img/newL4U-logo-100x100.png" style="display:block;height:auto;border:0;width:100px;max-width:100%" width="100"></a></div></td></tr></table><table border="0" cellpadding="10" cellspacing="0" class="text_block block-2" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word" width="100%"><tr><td class="pad"><div style="font-family:sans-serif"><div class="" style="font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#97a2da;line-height:1.2"><p style="margin:0;text-align:center;font-size:12px;mso-line-height-alt:14.399999999999999px"><span style="font-size:12px">Copyright© 2021 Local For You.</span></p></div></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>
            ';

    $result['email'] = $data['email'];
    $result['mode'] = $data['mode'];

    if (mail($data['email'], $data['subject'], $message, $headers)) {
        $result['result'] = 1;
        $result['msg'] = "Send email successful";
    } else {
        $result['result'] = 0;
        $result['msg'] = "Send email fail!!";
    }
}
echo json_encode($result);
?>