<?php

// ======================================================================
// 🍯 HONEYPOT CHECK — ต้องอยู่บนสุดก่อนโค้ดอื่นใดๆ
// ถ้า field 'website_url_confirm' มีค่า = บอท → reject ทันที
// มนุษย์กรอกไม่ได้เพราะ field ซ่อนด้วย CSS + tabindex=-1 + aria-hidden
// ======================================================================
// error_log('[monday_data] website_url_confirm value: ' . ($_POST['website_url_confirm'] ?? '(empty)'));
// TODO: Re-enable honeypot after fixing Chrome autofill issue
// if (!empty($_POST['website_url_confirm'])) {
//     $honeypotVal = substr((string) $_POST['website_url_confirm'], 0, 200);
//     $ip         = $_SERVER['REMOTE_ADDR']    ?? 'unknown';
//     $ua         = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
//     $referer    = $_SERVER['HTTP_REFERER']    ?? 'unknown';
//     error_log(sprintf(
//         '[HONEYPOT] signup form spam blocked | ip=%s | ua=%s | referer=%s | value=%s',
//         $ip, $ua, $referer, $honeypotVal
//     ));
//     http_response_code(200);
//     echo 'OK';
//     exit;
// }

//echo "<pre>";print_R($_POST);die;

// ********** Getting All Data ***********



// --------------- Step- 1 -----------------**

// --------------- Step- 1 -----------------**

$countryTextOnlyStep01_00N2v00000IyVqF = $_POST['countryTextOnly'] ?? '';

$industrialTypeStep01_00N9s000000QRyY = $_POST['formType'] ?? '';

$currency = $_POST['currency'] ?? '';



// --------------- Step- 2 -----------------**

$first_name = $_POST['first_name'] ?? '';

$last_name = $_POST['last_name'] ?? '';

$mobile = $_POST['mobile'] ?? '';

$email = $_POST['email'] ?? '';

$BestTimeToContactStep02_00N9s000000Nl1G = $_POST['bestTimeContact'] ?? '';

$BusinessInformationShopNameStep02_00N2v00000IyVqB = $_POST['shopName'] ?? '';

$company = $_POST['company'] ?? ''; //10

$BusinessInformationStep02_00N9s000000QPWu = $_POST['businessNumber'] ?? '';

$BusinessInformationStep02_00N2v00000IyVqO = $_POST['tradingName'] ?? '';

$BusinessInformationStep02_phone = $_POST['phone'] ?? '';

$BusinessInformationStep02_url = $_POST['url'] ?? '';

$BusinessInformationStep02_lang00N2v00000IyVqN = $_POST['supportLanguage'] ?? '';

$BusinessInformationStep02_ShopNum00N2v00000IyVqK = $_POST['physicalShopNumber'] ?? '';

$BusinessInformationStep02_Street = $_POST['street'] ?? '';

$BusinessInformationStep02_city = $_POST['city'] ?? '';

$BusinessInformationStep02_state_codexxx = $_POST['state_codexxx'] ?? '';

$BusinessInformationStep02_zip = $_POST['zip'] ?? ''; //20

$BusinessInformationStep02_shopCountry = $_POST['shopCountry'] ?? '';

$BusinessInformationStep02_shipNumber = $_POST['shipNumber'] ?? '';

$BusinessInformationStep02_shipAdd00N2v00000IyVqE = $_POST['shipAddress1'] ?? '';



// --------------- Step- 3 -----------------**

$ProductDetailsStep03_contractPeriod = $_POST['contractPeriod'] ?? '';

$ProductDetailsStep03_product = $_POST['product'] ?? '';

$BookingSystemStep03_email00N9s000000VUeh = $_POST['emailBooking'] ?? '';

$BookingSystemStep03_pass00N9s000000VUem = $_POST['passwordBooking'] ?? '';

$BookingSystemStep03_PaymentOptions00N2v00000IyVq8 = $_POST['payCheck'] ?? '';

$BookingSystemStep03_SocialNetworks00N2v00000IyVq6 = $_POST['socialFacebook'] ?? '';

$BookingSystemStep03_SocialNetworks00N9s000000Qp8x = $_POST['socialInstagram'] ?? ''; //3

$BookingSystemStep03_SocialNetworks00N9s000000Qoxu = $_POST['socialTikTok'] ?? '';

$BookingSystemStep03_SocialNetworks00N9s000000Qoxz = $_POST['socialYelp'] ?? '';

$DomainNameStep03_SocialNetworks00N2v00000IyVqP = $_POST['websiteDomainName'] ?? ''; // website

$DomainNameStep03_SocialNetworks00N2v00000IyVq3 = $_POST['newDomain'] ?? ''; // domain name

$DomainNameStep03_SocialNetworks00N9s000000VK1z = $_POST['ref_Domain_U'] ?? ''; //Username

$DomainNameStep03_SocialNetworks00N9s000000VK29 = $_POST['ref_Domain_P'] ?? ''; //pass

$DomainNameStep03_SocialNetworks00N9s000000VL1q = $_POST['ref_Domain_Comments'] ?? ''; //comments

$DomainNameStep03_SocialNetworks00N9s000000VL1v = $_POST['ref_Domain_Name_Registered'] ?? ''; //Registered

$DomainNameStep03_SocialNetworks00N9s000000QQaH = $_POST['addonFlyers'] ?? ''; //Add on products

$DomainNameStep03_SocialNetworks00N2v00000IyVpz = $_POST['discount'] ?? ''; //Discount 40





//---------------------- Step -4----------------------------------

$Step04_SocialNetworks00N2u000000mNZ0 = $_POST['couponCode'] ?? ''; //Coupun code

$Step04_SocialNetworkscouponCode2 = $_POST['couponCode2'] ?? ''; //Coupun code 2

$Step04_SocialNetworks00N2v00000IyVq7 = $_POST['paymentMethod'] ?? ''; // payment method

$Step04_SocialNetworkscreditCardNumber = $_POST['creditCardNumber'] ?? ''; //  Card number

$Step04_SocialNetworkscreditExpireDate = $_POST['creditExpireDate'] ?? ''; //  creditExpireDate

$Step04_SocialNetworkscreditCCV = $_POST['creditCCV'] ?? ''; //  creditExpireDate

$Step04_SocialNetworkscreditFullName = $_POST['creditFullName'] ?? ''; //  creditFullName

$Step04_SocialNetworksemailDirectDebit = $_POST['emailDirectDebit'] ?? ''; //  emailDirectDebit

$Step04_SocialNetworksbsbDirectDebit = $_POST['bsbDirectDebit'] ?? ''; //  bsbDirectDebit

$Step04_SocialNetworksacnDirectDebit = $_POST['acnDirectDebit'] ?? ''; //  acnDirectDebit

$Step04_SocialNetworksroutingDirectDebit = $_POST['routingDirectDebit'] ?? ''; //  routingDirectDebit

$Step04_SocialNetworksstripeAccount = $_POST['stripeAccount'] ?? ''; //  stripeAccount

$Step04_SocialNetworksstripePassword = $_POST['stripePassword'] ?? ''; //  stripePassword

$Step04_SocialNetworksemailInvoiceOther = $_POST['emailInvoiceOther'] ?? ''; //  emailInvoiceOther

$Step04_Comments00N2v00000IyVpq = $_POST['additionComment'] ?? ''; //  Additional comments

$Step04_SocialNetworks00N2u000000mNZG = $_POST['byAgent'] ?? ''; //  Marketing Agent

$Step04_SocialNetworks00N2v00000IyVq9 = $_POST['byPerson'] ?? ''; //  Referred  by person

$Step04_SocialNetworks00N2v00000IyVqA = $_POST['byRestaurant'] ?? ''; //  Referred  by shop

$Step04_SocialNetworks00N2u000000mNZ5 = $_POST['firstTimePayment'] ?? ''; //  First payment

$Step04_SocialNetworks00N9s000000PDD3 = $_POST['customerStripeEmail'] ?? ''; //  Customer stripe email

$Step04_Stripecustomerid00N9s000000QYtB = $_POST['customerStripeID'] ?? ''; //  Customer stripe ID

$Step04_SocialNetworks00N9s000000VWOl = $_POST['customerStripeIDUSA'] ?? ''; //  customerStripeIDUSA

$Step04_SocialNetworks00N9s000000QQl5 = $_POST['selectedPackages'] ?? ''; //  Selected package

$Step04_SocialNetworks00NMq000000UrZx = $_POST['agreementGenerated'] ?? ''; //  Agreement generated

$Step04_SocialNetworks00N9s000000QgXl = $_POST['currentlyPackage'] ?? ''; //  Currently package







$IntrestingIn00NMq000000R4yn = 'Testing data';// Intresting In 



// ******** Create Lead in Monday.com ********

$curl = curl_init();

curl_setopt_array($curl, array(

  CURLOPT_URL => 'https://api.monday.com/v2',

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => '',

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => 'POST',

  CURLOPT_POSTFIELDS =>'{"query":"mutation {\\r\\n  create_item (\\r\\n    board_id: 1855480037,\\r\\n    group_id: \\"topics\\",\\r\\n    item_name: \\"'.$BusinessInformationShopNameStep02_00N2v00000IyVqB.'\\",\\r\\n    column_values: \\"{\\\\\\"status7\\\\\\":\\\\\\"Working\\\\\\",\\\\\\"text52\\\\\\":\\\\\\"'.$countryTextOnlyStep01_00N2v00000IyVqF.'\\\\\\",\\\\\\"text53\\\\\\":\\\\\\"'.$first_name.'\\\\\\",\\\\\\"text11\\\\\\":\\\\\\"'.$last_name.'\\\\\\",\\\\\\"text54\\\\\\":\\\\\\"'.$email.'\\\\\\",\\\\\\"text46\\\\\\":\\\\\\"'.$mobile.'\\\\\\",\\\\\\"text24\\\\\\":\\\\\\"'.$BestTimeToContactStep02_00N9s000000Nl1G.'\\\\\\",\\\\\\"text09\\\\\\":\\\\\\"'.$BusinessInformationStep02_city.'\\\\\\",\\\\\\"text6\\\\\\":\\\\\\"'.$currency.'\\\\\\",\\\\\\"website\\\\\\":\\\\\\"'.$BusinessInformationStep02_url.'\\\\\\",\\\\\\"text445\\\\\\":\\\\\\"'.$BusinessInformationShopNameStep02_00N2v00000IyVqB.'\\\\\\",\\\\\\"text90\\\\\\":\\\\\\"'.$company.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$industrialTypeStep01_00N9s000000QRyY.'\\\\\\",\\\\\\"text8\\\\\\":\\\\\\"'.$Step04_Stripecustomerid00N9s000000QYtB.'\\\\\\",\\\\\\"text976\\\\\\":\\\\\\"'.$Step04_Comments00N2v00000IyVpq.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_00N9s000000QPWu.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_00N2v00000IyVqO.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_phone.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_lang00N2v00000IyVqN.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_ShopNum00N2v00000IyVqK.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_Street.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_state_codexxx.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_zip.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_shopCountry.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_shipNumber.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BusinessInformationStep02_shipAdd00N2v00000IyVqE.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$ProductDetailsStep03_contractPeriod.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$ProductDetailsStep03_product.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BookingSystemStep03_email00N9s000000VUeh.'\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"'.$BookingSystemStep03_pass00N9s000000VUem.'\\\\\\",\\\\\\"tex26\\\\\\":\\\\\\"' . $BookingSystemStep03_PaymentOptions00N2v00000IyVq8 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $BookingSystemStep03_SocialNetworks00N2v00000IyVq6 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $BookingSystemStep03_SocialNetworks00N9s000000Qp8x . '\\\\\\", \\\\\\"text26\\\\\\":\\\\\\"' . $BookingSystemStep03_SocialNetworks00N9s000000Qoxu . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $BookingSystemStep03_SocialNetworks00N9s000000Qoxz . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N2v00000IyVqP . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N2v00000IyVq3 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N9s000000VK1z . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N9s000000VK29 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N9s000000VL1q . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N9s000000VL1v . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N9s000000QQaH . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $DomainNameStep03_SocialNetworks00N2v00000IyVpz . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N2u000000mNZ0 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworkscouponCode2 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N2v00000IyVq7 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworkscreditCardNumber . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworkscreditExpireDate . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworkscreditCCV . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworkscreditFullName . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksemailDirectDebit . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksbsbDirectDebit . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksacnDirectDebit . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksroutingDirectDebit . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksstripeAccount . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksstripePassword . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworksemailInvoiceOther . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_Comments00N2v00000IyVpq . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N2u000000mNZG . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N2v00000IyVq9 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N2v00000IyVqA . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N2u000000mNZ5 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N9s000000PDD3 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_Stripecustomerid00N9s000000QYtB . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N9s000000VWOl . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N9s000000QQl5 . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00NMq000000UrZx . '\\\\\\",\\\\\\"text26\\\\\\":\\\\\\"' . $Step04_SocialNetworks00N9s000000QgXl . '\\\\\\"}\\"\\r\\n  ) {\\r\\n    id\\r\\n  }\\r\\n}\\r\\n","variables":{}}',

  CURLOPT_HTTPHEADER => array(

    'Content-Type: application/json',

    'Authorization: eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM0MjgwMDgyMSwiYWFpIjoxMSwidWlkIjo1NzY0ODkyNCwiaWFkIjoiMjAyNC0wNC0wNVQwNzo0NzowMC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.u7Gp_ftfX4t7sEdfKLM3l59bgBVT3I1jSh5lWZ-XREA',

    'Cookie: __cf_bm=Q5yOzWX_3IlEaSZ3h5.3wcf4A72v6l7CDWGbDYU2crk-1712839991-1.0.1.1-UiurDJnpRJ82m.cxTPqDxuhUe.ReOFtwABGOsRvXnM2gXbRrecRvVLLupORaYJNXYmvhIQhYC.qyD4m4ykNeJWzYCMqfP.I0fuyvdrn9SUc'

  ),

));



$response = curl_exec($curl);



curl_close($curl);

//echo $response;

// Extract form data

  $formData = $_POST;

  // รวบรวม addon ทุกตัวให้เป็น field เดียว "addons" (คั่นด้วย , )
  // ครอบคลุม 2 รูปแบบ:
  //   1) key ที่ขึ้นต้นด้วย "addon" (addonPOS, addonPricingDesign ฯลฯ)
  //      หรือ "addWebsite" (addWebsiteMakeoverTemplate, addWebsiteMakeoverFully ฯลฯ)
  //   2) key พิเศษที่ไม่ได้ขึ้นต้นด้วย addon แต่เป็น addon จริง
  //      (มาจาก form_name ใน assets/API/B-Price-*.json)
  //      *หมายเหตุ: "email" ถูกตัดออก เพราะชื่อชนกับ owner email
  $extraAddonKeys = ['promotions', 'webEmail', 'mob'];

  $isAddonKey = function ($key) use ($extraAddonKeys) {
      // กัน key รวม 'addons' (พหูพจน์) ไม่ให้ถูกลบ เพราะก็ขึ้นต้นด้วย 'addon'
      if ($key === 'addons') return false;
      return strpos($key, 'addon') === 0
          || strpos($key, 'addWebsite') === 0
          || in_array($key, $extraAddonKeys, true);
  };

  // ลบ field addon รายตัวออกก่อน แล้วค่อย set 'addons' ทีหลัง
  // เพื่อป้องกันความผิดพลาดแบบ prefix-match อีก
  $addonsList = [];
  $addonKeysFound = [];
  foreach ($formData as $key => $value) {
      if ($isAddonKey($key) && is_string($value) && trim($value) !== '') {
          $addonsList[] = trim($value);
          $addonKeysFound[] = $key;
      }
  }

  foreach (array_keys($formData) as $key) {
      if ($isAddonKey($key)) {
          unset($formData[$key]);
      }
  }

  // Set 'addons' หลังจาก unset เสร็จแล้ว → ไม่ถูกลบซ้ำ
  $formData['addons'] = implode(', ', $addonsList);

  // --- Security: ลบข้อมูลบัตรเครดิต/credentials ที่ sensitive ออกก่อนส่ง Make.com ---
  // PCI DSS: ห้ามส่ง/เก็บ PAN (card number) เต็ม และ "ห้ามเก็บ CVV/CVC" เด็ดขาด
  $sensitiveKeys = [
      'creditCardNumber',
      'creditExpireDate',
      'creditCCV',
      'stripePassword',
      'ref_Domain_P',   // domain password
      'ref_IHD_Password',
      'passwordBooking',
      'bsbDirectDebit',
      'acnDirectDebit',
      'routingDirectDebit',
  ];
  foreach ($sensitiveKeys as $sk) {
      unset($formData[$sk]);
  }

  // Debug markers — ใช้ยืนยันว่า production รัน monday_data.php เวอร์ชันใหม่จริง
  // และช่วยให้เห็นว่า $_POST มี addon key ตัวไหนมาบ้าง
  $formData['_serverCodeVersion'] = 'addons-v3-' . date('Ymd-His', filemtime(__FILE__));
  $formData['_addonKeysFound'] = implode('|', $addonKeysFound);
  $formData['_postKeysCount'] = (string) count($_POST);

  // Log ดิบๆ ลง error log (ดูได้ผ่าน tail -f /var/log/php/*.log)
  error_log('monday_data.php $_POST keys: ' . implode(',', array_keys($_POST)));
  error_log('monday_data.php addons computed: ' . $formData['addons']);

  // Extract productName from $_POST['product'] (format: "Name - Price")
  $productValue = $_POST['product'] ?? '';
  $productName = '';
  if (!empty($productValue)) {
      $parts = explode(' - ', $productValue);
      $productName = $parts[0] ?? '';
  }
  $formData['productName'] = $productName;

  // Extract addonsName from $formData['addons'] (format: "Name - Price, Name - Price")
  // For Monday.com - send as comma-separated string
  $addonsValue = $formData['addons'] ?? '';
  $addonsNames = [];
  if (!empty($addonsValue)) {
      $addonList = explode(', ', $addonsValue);
      foreach ($addonList as $addon) {
          $parts = explode(' - ', $addon);
          if (!empty($parts[0])) {
              $addonsNames[] = $parts[0];
          }
      }
  }
  // Send as comma-separated string
  $formData['addonsName'] = implode(', ', $addonsNames);



  // Webhook URL

  //$webhookUrl = "https://hook.eu1.make.com/xmrmdjm7lst6gg8qj1yxy1fdklcsoudo";
  //$webhookUrl = "https://hook.us1.make.com/w5le4gjhr9m4oprgwn2ogzpl6un7xtab";
  //$webhookUrl = "https://hook.us1.make.com/udh53vpfeeeithv4rvu5oqsbb7z308vf";
  //$webhookUrl = "https://hook.us1.make.com/myqks921kn2ono23uszzxnfg1dmseag8";
    $webhookUrl = "https://hook.us1.make.com/izna04q2cdj68bqylepknapxa4l0wkaz";



  // Make POST request to webhook URL with form data

  $ch = curl_init($webhookUrl);

  curl_setopt($ch, CURLOPT_POST, 1);

  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);

  curl_close($ch);



  // Check if the request was successful

  if ($response === false) {

      // Handle error

      // Failed to send data to webhook

  } else {

      // Webhook request successful

      // Data sent to webhook successfully

  }

  // Send to TH invoice webhook if country is Thailand
  if (($formData['country_code'] ?? '') === 'TH') {
    try {
      // Load DB and build invoice payload
      if (!class_exists('db')) {
          include_once __DIR__ . '/../assets/db/db.php';
      }
      include_once __DIR__ . '/../assets/db/initDB.php';
      global $db;
      $convertBahtPath = __DIR__ . '/../../../../api/invoice/convertToBahtText.php';
      if (file_exists($convertBahtPath)) {
          require_once $convertBahtPath;
      }

      $email = $formData['email'] ?? $formData['quotationEmail'] ?? $formData['emailShoppingCart'] ?? '';

      // Find latest invoice for this email
      $invRows = $db->query(
          'SELECT i.*, c.`name`, c.`address`, c.`taxNumber`, c.`email` AS customerEmail,
                  c.`phone` AS customerPhone, c.`type`, c.`sale`,
                  c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName
           FROM `thInvoice` i
           JOIN `thCustomer` c ON c.`id` = i.`customer_id`
           WHERE c.`email` = ?
           ORDER BY i.`id` DESC LIMIT 1',
          $email
      )->fetchAll();

      if (!empty($invRows[0])) {
          $row        = $invRows[0];
          $productArr = json_decode($row['product'] ?? '', true) ?? [];
          $summary    = $productArr['summary']   ?? [];
          $rawItems   = $productArr['table']     ?? [];
          $quotation  = $productArr['quotation'] ?? [];

          $receiptRow = $db->query(
              'SELECT * FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
              $row['id']
          )->fetchAll();
          $receipt      = $receiptRow[0] ?? [];
          $receiptID    = $receipt['receiptID']  ?? $row['invoiceID'];
          $thBathRe     = $receipt['thBathRe']   ?? '';
          $thBathIn     = $row['thBathIn']        ?? convertToBahtText((float)($summary['net_payment'] ?? 0));
          $slipPath     = $receipt['slip']        ?? '';
          $slipUrl      = $slipPath ? 'https://report.localforyou.com/modules/signup2/assets/uploads/' . $slipPath : '';

          $thPayload = [
              'invoice_id'    => (int)$row['id'],
              'invoiceID'     => $row['invoiceID'],
              'name'          => $row['name'],
              'address'       => $row['address'],
              'taxNumber'     => $row['taxNumber'],
              'type'          => $row['type'],
              'sale'          => $row['sale'],
              'customerEmail' => $row['customerEmail'],
              'customerPhone' => $row['customerPhone'],
              'bankName'      => $row['bankName'],
              'bankThaiNumber'=> $row['bankThaiNumber'],
              'bankThaiName'  => $row['bankThaiName'],
              'createdAt'     => $row['createdAt'],
              'subtotal'      => $summary['subtotal']            ?? '',
              'vat'           => $summary['vat']                 ?? '',
              'grandtotal'    => $summary['grandtotal_inc_vat']  ?? '',
              'withholdingTax'=> $summary['withholdingTax']      ?? '',
              'net_payment'   => $summary['net_payment']         ?? '',
              'items'         => $rawItems,
              'quotation'     => $quotation,
              'receiptID'     => $receiptID,
              'receipt_url'   => 'https://report.localforyou.com/pages/receiptTH.php?invoice_id=' . $row['id'],
              'slip_url'      => $slipUrl,
              'base_url'      => 'https://report.localforyou.com/',
              'thBathIn'      => $thBathIn,
              'thBathRe'      => $thBathRe,
          ];

          $thWebhookUrl = 'https://hook.us1.make.com/gnxfafua86k6mutxg8tr65cuxmrk3v2k';
          $thCh = curl_init($thWebhookUrl);
          curl_setopt($thCh, CURLOPT_POST, 1);
          curl_setopt($thCh, CURLOPT_POSTFIELDS, json_encode($thPayload));
          curl_setopt($thCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
          curl_setopt($thCh, CURLOPT_RETURNTRANSFER, true);
          curl_exec($thCh);
          curl_close($thCh);
      }
    } catch (\Throwable $e) {
        error_log('[TH webhook] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
  }

// Redirect to the specified link

    

header("Location: https://localforyou.com/thank-you/");

exit; // Make sure to exit after the redirect to prevent further execution of the script

?>