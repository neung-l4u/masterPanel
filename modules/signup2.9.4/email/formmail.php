<?php
session_start();
if($_POST['action']){
    if($_POST['verifycode'] !=$_SESSION['total'] ){
        echo " Verify Code ไม่ถูกต้อง โปรดใสใหม่อีกครั้ง<br>";
        exit();
    }else{

        $mailto = "iamatomix@gmail.com"; # อีเมล์ผู้รับ
        $subject = "".$_POST['subj'];

        $headers = [
            'From' => 'Admin Local For You <admin@localforyou.com>',
            'To' => $mailto,
            'Cc' => 'Me <neung@localforyou.com>',
            'Bcc' => 'sorasak.tns@gmail.com',
            'Reply-To' => 'admin@localforyou.com',
            'X-Sender' => 'Admin L4U <admin@localforyou.com>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'X-Priority' => '1',
            'Return-Path' => 'neung@localforyou.com', //for error
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8'
        ];


        /*$message = " จากคุณ  ".$_POST['name'].'<br>';
        $message .= " โทร  ".$_POST['tel'].'<br>';
        $message .= "ข้อความ<br>".$_POST['msg'].'<hr>';*/
        $aaa = "Thai is Atomix";
        $countryText = "Thailand";
        $shopName = "Steak Co";
        $tradingName = "Justice";
        $address = "156/240 Sukthavorn road , Bang sao thong , Samutprakarn 10540";
        $fullName = "Sorasak Thanomsap";
        $mobile = "0895117447";
        $email = "sorasak.tns@gmail.com";
        $lastDate = "31 January 2022";
        $reason = "For Test Only";
        $comment = "No comment, Keep doing it bro!!";
        $message = "
        <!DOCTYPE html><html lang='en'><head><title></title><meta content='text/html; charset=utf-8' http-equiv='Content-Type'><meta content='width=device-width,initial-scale=1' name='viewport'><!--[if mso]><xml><o:officedocumentsettings><o:pixelsperinch>96</o:pixelsperinch><o:allowpng></o:officedocumentsettings></xml><![endif]--><link rel='stylesheet' href='https://signup.localforyou.com/assets/css/email_cancellation.css'></head><body style='background-color:#d9dffa;margin:0;padding:0;-webkit-text-size-adjust:none;text-size-adjust:none'><table border='0' cellpadding='0' cellspacing='0' class='nl-container' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;background-color:#d9dffa' width='100%'><tbody><tr><td><table align='center' border='0' cellpadding='0' cellspacing='0' class='row row-1' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;background-color:#cfd6f4' width='100%'><tbody><tr><td><table align='center' border='0' cellpadding='0' cellspacing='0' class='row-content stack' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px' width='600'><tbody><tr><td class='column column-1' style='mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-top:20px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0' width='100%'><table border='0' cellpadding='0' cellspacing='0' class='image_block block-1' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='pad' style='width:100%;padding-right:0;padding-left:0'><div align='left' class='alignment' style='line-height:10px'><img alt='Card Header with Border and Shadow Animated' src='https://signup.localforyou.com/assets/img/newL4U-logo-100x100.png' style='display:block;height:auto;border:0;width:100px;max-width:100%' title='Card Header with Border and Shadow Animated' width='100'></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table><table align='center' border='0' cellpadding='0' cellspacing='0' class='row row-2 tbl1' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tbody><tr><td><table align='center' border='0' cellpadding='0' cellspacing='0' class='row-content stack' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px' width='600'><tbody><tr><td class='column column-1' style='mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-bottom:15px;padding-left:50px;padding-right:50px;padding-top:15px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0' width='100%'><table border='0' cellpadding='10' cellspacing='0' class='text_block block-1' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;word-break:break-word' width='100%'><tr><td class='pad'><div style='font-family:sans-serif'><div class='' style='font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#506bec;line-height:1.2'><p style='margin:0;font-size:14px;mso-line-height-alt:16.8px'><strong><span style='font-size:38px'><span style='font-size:20px'>Thai Cottage - USA</span></span></strong></p></div></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='text_block block-2' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;word-break:break-word' width='100%'><tr><td class='pad'><div style='font-family:sans-serif'><div class='' style='font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#40507a;line-height:1.2'><p style='margin:0;font-size:14px;mso-line-height-alt:16.8px'><span style='font-size:16px'>Hey, we received a cancellation request from AU customer.</span></p></div></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='text_block block-3' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;word-break:break-word' width='100%'><tr><td class='pad'><div style='font-family:sans-serif'><div class='' style='font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#40507a;line-height:1.2'><p style='margin:0;font-size:14px;mso-line-height-alt:16.8px'><span style='font-size:16px'>as detailed below</span></p></div></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='divider_block block-4' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='pad'><div align='center' class='alignment'><table border='0' cellpadding='0' cellspacing='0' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='divider_inner' style='font-size:1px;line-height:1px;border-top:1px solid #bbb'><span> </span></td></tr></table></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='paragraph_block block-5' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;word-break:break-word' width='100%'><tr><td class='pad'><div style='color:#000;font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:400;line-height:120%;text-align:left;direction:ltr;letter-spacing:0;mso-line-height-alt:16.8px'><p style='margin:0;margin-bottom:16px'><strong>Country :</strong>$countryText<br><strong>Shop name :</strong>$shopName<br><strong>Trading name :</strong>$tradingName<br><strong>Address :</strong><br>$address</p><p style='margin:0;margin-bottom:16px'><strong>Full name :</strong>$fullName<br><strong>Mobile :</strong>$mobile<br><strong>Email :</strong><a href='$email' style='text-decoration:underline;color:#0068a5'>$email</a></p><p style='margin:0;margin-bottom:16px'><strong>Last date : $lastDate<br>Reason :</strong>$reason</p><p style='margin:0'><strong>Comment :</strong><br>$comment</p></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='divider_block block-6' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='pad'><div align='center' class='alignment'><table border='0' cellpadding='0' cellspacing='0' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='divider_inner' style='font-size:1px;line-height:1px;border-top:1px solid #bbb'><span> </span></td></tr></table></div></td></tr></table><table border='0' cellpadding='0' cellspacing='0' class='button_block block-7' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='pad' style='padding-bottom:20px;padding-left:10px;padding-right:10px;padding-top:20px;text-align:left'><div align='left' class='alignment'><!--[if mso]><v:roundrect xmlns:v='urn:schemas-microsoft-com:vml' xmlns:w='urn:schemas-microsoft-com:office:word' href='https://localforyou.lightning.force.com/lightning/page/home' style='height:47px;width:219px;v-text-anchor:middle' arcsize='35%' stroke='false' fillcolor='#506bec'><w:anchorlock><v:textbox inset='5px,0px,0px,0px'><center style='color:#fff;font-family:Arial,sans-serif;font-size:15px'><![endif]--><a href='https://localforyou.lightning.force.com/lightning/page/home' style='text-decoration:none;display:inline-block;color:#fff;background-color:#506bec;border-radius:16px;width:auto;border-top:0 solid TRANSPARENT;font-weight:undefined;border-right:0 solid TRANSPARENT;border-bottom:0 solid TRANSPARENT;border-left:0 solid TRANSPARENT;padding-top:8px;padding-bottom:8px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;font-size:15px;text-align:center;mso-border-alt:none;word-break:keep-all' target='_blank'><span style='padding-left:25px;padding-right:20px;font-size:15px;display:inline-block;letter-spacing:normal'><span dir='ltr' style='word-break:break-word'><span data-mce-style='' dir='ltr' style='line-height:30px'><strong>OPEN ON SALESFORCE</strong></span></span></span></a><!--[if mso]><![endif]--></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='text_block block-8' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;word-break:break-word' width='100%'><tr><td class='pad'><div style='font-family:sans-serif'><div class='' style='font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#40507a;line-height:1.2'><p style='margin:0;font-size:14px;mso-line-height-alt:16.8px'><span style='font-size:14px'>Having trouble?<a href='mailto:neung@localforyou.com?subject=Problem%20:%20Cancellation%20email' rel='noopener' style='text-decoration:underline;color:#40507a' target='_blank' title='neung@localforyou.com'>neung@localforyou.com</a></span></p></div></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table><table align='center' border='0' cellpadding='0' cellspacing='0' class='row row-3' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tbody><tr><td><table align='center' border='0' cellpadding='0' cellspacing='0' class='row-content stack' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px' width='600'><tbody><tr><td class='column column-1' style='mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-bottom:5px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0' width='100%'><table border='0' cellpadding='0' cellspacing='0' class='image_block block-1' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='pad' style='width:100%;padding-right:0;padding-left:0'><div align='center' class='alignment' style='line-height:10px'><img alt='Card Bottom with Border and Shadow Image' class='big' src='https://signup.localforyou.com/assets/img/bottom_img.png' style='display:block;height:auto;border:0;width:600px;max-width:100%' title='Card Bottom with Border and Shadow Image' width='600'></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table><table align='center' border='0' cellpadding='0' cellspacing='0' class='row row-4' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tbody><tr><td><table align='center' border='0' cellpadding='0' cellspacing='0' class='row-content stack' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;color:#000;width:600px' width='600'><tbody><tr><td class='column column-1' style='mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-bottom:20px;padding-left:10px;padding-right:10px;padding-top:10px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0' width='100%'><table border='0' cellpadding='0' cellspacing='0' class='image_block block-1' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0' width='100%'><tr><td class='pad' style='width:100%;padding-right:0;padding-left:0'><div align='center' class='alignment' style='line-height:10px'><a href='http://www.example.com/' style='outline:0' tabindex='-1' target='_blank'><img alt='Your Logo' src='https://signup.localforyou.com/assets/img/newL4U-logo-100x100.png' style='display:block;height:auto;border:0;width:100px;max-width:100%' title='Your Logo' width='100'></a></div></td></tr></table><table border='0' cellpadding='10' cellspacing='0' class='text_block block-2' role='presentation' style='mso-table-lspace:0;mso-table-rspace:0;word-break:break-word' width='100%'><tr><td class='pad'><div style='font-family:sans-serif'><div class='' style='font-size:14px;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;mso-line-height-alt:16.8px;color:#97a2da;line-height:1.2'><p style='margin:0;text-align:center;font-size:12px;mso-line-height-alt:14.399999999999999px'><span style='font-size:12px'>Copyright© 2021 Local For You.</span></p></div></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>
        ";


        if(mail($mailto, $subject, $message, $headers)){
            echo "ส่งสำเร็จ";
        }else{
            echo "ผิดพลาด";
        }
        exit();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>From Mail</title>
</head>
<body>
<?php
$num1 = rand(0,10);
$num2 = rand(0,10);
$_SESSION['total'] = ($num1 * $num2);
?>
<form action='formmail.php' method='post'>
    Subject : <input type='text' name='subj'><br>
    Name : <input type='text' name='name'><br>
    Email : <input type='text' name='email'><br>
    Tel : <input type='text' name='tel'><br>
    Message :   <textarea name="msg" rows="4" cols="30"></TEXTAREA><br>
    Verify Code : <?=$num1;?> * <?=$num2;?> = <input type='text' name='verifycode'>
    <input type='hidden' name='action' value='1'>
    <input type='submit' name="cmdsubmit" value='send'>
</form>
</body>
</html>