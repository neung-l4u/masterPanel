<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$date = date('Y-m-d H:i:s');

$data = $_REQUEST['frm'];
$response['send'] = false;

$country = !empty($data['country']) ? $data['country'] : '-';
$shopName = !empty($data['shopName']) ? $data['shopName'] : '-';
$fullName = !empty($data['fullName']) ? $data['fullName'] : '-';
$email = !empty($data['email']) ? $data['email'] : '-';
$admin_email = "admin@localforyou.com";
?>

<?php
$mail_from = 'Admin Local For You <'.$admin_email.'>';
$mail_to = $email;
$mail_cc = '';
$mail_bcc = 'neung@localforyou.com';
$mail_reply = 'bas@localforyou.com'.'neung@localforyou.com'.'mark@localforyou.com';

$subject = "Cancellation request - ".$shopName;

/*$headers = [
    'From' => $mail_from,
    'To' => $mail_to,
    'Cc' => $mail_cc,
    'Bcc' => $mail_bcc,
    'Reply-To' => $mail_reply,
    'X-Sender' => 'Admin L4U <admin@localforyou.com>',
    'X-Mailer' => 'PHP/' . phpversion(),
    'X-Priority' => '1',
    'Return-Path' => 'neung@localforyou.com', //for error
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=utf-8'
];*/

$headers = [
    'From' => $mail_from,
    'To' => $mail_to,
    'Cc' => $mail_cc,
    'Bcc' => $mail_bcc,
    'Reply-To' => $mail_reply,
    'X-Sender' => 'Admin L4U <bas@localforyou.com>',
    'X-Mailer' => 'PHP/' . phpversion(),
    'X-Priority' => '1',
    'Return-Path' => 'neung@localforyou.com', //for error
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=utf-8'
];

$message = '<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en">

<head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"><!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]-->
</head>

<body style="background-color: #d9dffa; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;">
	<table class="nl-container" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #d9dffa;">
		<tbody>
			<tr>
				<td>
					<table class="row row-1" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #cfd6f4;">
						<tbody>
							<tr>
								<td>
									<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 600px;" width="600">
										<tbody>
											<tr>
												<td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-top: 20px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
													<table class="image_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
														<tr>
															<td class="pad" style="width:100%;padding-right:0px;padding-left:0px;">
																<div class="alignment" align="left" style="line-height:10px"><img src="https://signup.localforyou.com/assets/img/newL4U-logo-100x100.png" style="display: block; height: auto; border: 0; width: 100px; max-width: 100%;" width="100" alt="Card Header with Border and Shadow Animated" title="Card Header with Border and Shadow Animated"></div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="row row-2" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #d9dffa; background-image: url(https://d1oco4z2z1fhwp.cloudfront.net/templates/default/3991/body_background_2.png); background-position: top center; background-repeat: repeat;">
						<tbody>
							<tr>
								<td>
									<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 600px;" width="600">
										<tbody>
											<tr>
												<td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 15px; padding-left: 50px; padding-right: 50px; padding-top: 15px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
													<table class="text_block block-1" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																	<div class style="font-size: 14px; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; mso-line-height-alt: 16.8px; color: #506bec; line-height: 1.2;">
																		<p style="margin: 0; font-size: 14px; mso-line-height-alt: 16.8px;"><strong><span style="font-size:38px;"><span style="font-size:20px;"><a data-special-link="true" title="Pongjira Rattanadet" target="_blank" rel="noopener noreferrer" href="https://localforyou.lightning.force.com/lightning/r/0039s000005WZhyAAG/view" data-aura-rendered-by="6984:0" data-aura-class="forceOutputLookup" style="text-decoration: underline; color: #506bec;">Hi '.$fullName.',</a></span></span></strong></p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
													<table class="text_block block-2" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
														<tr>
															<td class="pad">
																<div style="font-family: Arial, sans-serif">
																	<div class style="font-size: 14px; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; mso-line-height-alt: 16.8px; color: #40507a; line-height: 1.2;">
																		<p style="margin: 0; font-size: 14px; mso-line-height-alt: 16.8px;">We received your cancellation request on '.$date.'.<br><br>After this, when were done.<br>We will contact you again to send you a confirmation email and return the marketing materials belonging to you.<br><br>Thank you for working together.</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
													<table class="divider_block block-3" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
														<tr>
															<td class="pad">
																<div class="alignment" align="center">
																	<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
																		<tr>
																			<td class="divider_inner" style="font-size: 1px; line-height: 1px; border-top: 1px solid #dddddd;"><span>&#8202;</span></td>
																		</tr>
																	</table>
																</div>
															</td>
														</tr>
													</table>
													<table class="text_block block-4" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																	<div class style="font-size: 14px; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; mso-line-height-alt: 16.8px; color: #40507a; line-height: 1.2;">
																		<p style="margin: 0; font-size: 14px; text-align: right; mso-line-height-alt: 16.8px;"><span style="font-size:14px;color:#1194f9;"><span style="font-size:12px;">If you do not request this cancellation please let me know </span></span></p>
																		<p style="margin: 0; font-size: 14px; text-align: right; mso-line-height-alt: 16.8px;"><span style="font-size:12px;"><a href="mailto:neung@localforyou.com?subject=Problem%20:%20Cancellation%20email" target="_blank" title="neung@localforyou.com" style="text-decoration: underline; color: #40507a;" rel="noopener">admin@localforyou.com</a></span></p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="row row-3" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
						<tbody>
							<tr>
								<td>
									<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 600px;" width="600">
										<tbody>
											<tr>
												<td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
													<table class="image_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
														<tr>
															<td class="pad" style="width:100%;padding-right:0px;padding-left:0px;">
																<div class="alignment" align="center" style="line-height:10px"><img class="big" src="https://d1oco4z2z1fhwp.cloudfront.net/templates/default/3991/bottom_img.png" style="display: block; height: auto; border: 0; width: 600px; max-width: 100%;" width="600" alt="Card Bottom with Border and Shadow Image" title="Card Bottom with Border and Shadow Image"></div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="row row-4" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
						<tbody>
							<tr>
								<td>
									<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 600px;" width="600">
										<tbody>
											<tr>
												<td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 20px; padding-left: 10px; padding-right: 10px; padding-top: 10px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
													<table class="image_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
														<tr>
															<td class="pad" style="width:100%;padding-right:0px;padding-left:0px;">
																<div class="alignment" align="center" style="line-height:10px"><a href="http://www.example.com/" target="_blank" style="outline:none" tabindex="-1"><img src="https://signup.localforyou.com/assets/img/newL4U-logo-100x100.png" style="display: block; height: auto; border: 0; width: 100px; max-width: 100%;" width="100" alt="Your Logo" title="Your Logo"></a></div>
															</td>
														</tr>
													</table>
													<table class="text_block block-2" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																	<div class style="font-size: 14px; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; mso-line-height-alt: 16.8px; color: #97a2da; line-height: 1.2;">
																		<p style="margin: 0; text-align: center; font-size: 12px; mso-line-height-alt: 14.399999999999999px;"><span style="font-size:12px;">Copyright© 2021 Local For You.</span></p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="row row-5" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
						<tbody>
							<tr>
								<td>
									<table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; width: 600px;" width="600">
										<tbody>
											<tr>
												<td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
													<table class="icons_block block-1" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
														<tr>
															<td class="pad" style="vertical-align: middle; color: #9d9d9d; font-family: inherit; font-size: 15px; padding-bottom: 5px; padding-top: 5px; text-align: center;">
																<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
																	<tr>
																		<td class="alignment" style="vertical-align: middle; text-align: center;"><!--[if vml]><table align="left" cellpadding="0" cellspacing="0" role="presentation" style="display:inline-block;padding-left:0px;padding-right:0px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><![endif]-->
																			<!--[if !vml]><!-->
																			<table class="icons-inner" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; display: inline-block; margin-right: -4px; padding-left: 0px; padding-right: 0px;" cellpadding="0" cellspacing="0" role="presentation"><!--<![endif]-->
																				<tr>
																					<td style="vertical-align: middle; text-align: center; padding-top: 5px; padding-bottom: 5px; padding-left: 5px; padding-right: 6px;"><a href="https://www.designedwithbee.com/" target="_blank" style="text-decoration: none;"><img class="icon" alt="Designed with BEE" src="https://d15k2d11r6t6rl.cloudfront.net/public/users/Integrators/BeeProAgency/53601_510656/Signature/bee.png" height="32" width="34" align="center" style="display: block; height: auto; margin: 0 auto; border: 0;"></a></td>
																					<td style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 15px; color: #9d9d9d; vertical-align: middle; letter-spacing: undefined; text-align: center;"><a href="https://www.designedwithbee.com/" target="_blank" style="color: #9d9d9d; text-decoration: none;">Designed with BEE</a></td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table><!-- End -->
</body>

</html>';

if(mail($mail_to, $subject, $message, $headers)){
    $response['send'] = true;
}else{
    $response['send'] = false;
}

$response['status'] = true;
$response['timestamp'] = $date;
echo json_encode($response);
?>