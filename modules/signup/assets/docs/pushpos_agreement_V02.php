<?php
date_default_timezone_set("Asia/Bangkok");
$SignedDate = date('d/m/Y');

// Guarded: the agreement templates share this helper, and both can be included
// in the same request when rendering PDFs for DocuSign.
if (!function_exists('cutColon')) {
    function cutColon($param){
        $temp = explode(":",$param);
        $arr = array_reverse($temp);
        return trim($arr[0]);
    }
}

$log["timestamp"] =  date('Y-m-d H:i:s');
$log["timestampDash"] =  date('Y-m-d His');

$data["customerFullName"] = !empty($_REQUEST["customerFullName"]) ? trim($_REQUEST["customerFullName"]) : "-- No Name --";
$data["ShopName"] = !empty($_REQUEST["ShopName"]) ? trim($_REQUEST["ShopName"]) : "-- No ShopName --";
$data["legalEntity"] = !empty($_REQUEST["legalEntity"]) ? trim($_REQUEST["legalEntity"]) : "";
$data["registrationNumber"] = !empty($_REQUEST["registrationNumber"]) ? trim($_REQUEST["registrationNumber"]) : "-- No registrationNumber --";
$data["State"] = !empty($_REQUEST["State"]) ? trim($_REQUEST["State"]) : "-- No State --";
$data["Country"] = !empty($_REQUEST["Country"]) ? trim($_REQUEST["Country"]) : "-- No Country --";

$data["registrationNumber"] = cutColon($data["registrationNumber"]);

// When rendered for DocuSign, swap the blank signature images for anchor strings
// (/sig1/, /date1/) that DocuSign uses to position the signature tabs.
// Set by api/docusign/ContractPdf.php; unset for normal browser previews.
$docusignMode = !empty($_REQUEST["docusignMode"]);

$contractTitle = "Push POS Customer Agreement - Local For You Pty Ltd";
$contractSubTitle = "in Australia, by and between:";
$abn = "ABN";
$companyName = "Local For You Pty Ltd";
$companyAddress = "9/204 Alice Street, Brisbane, QLD 4000, Australia";
$companyNumber = "60 606 095 943";
$companyABN = "ABN";

switch ($_REQUEST["Country"]) {
    case "AU":
        $data["Country"] = "Australia";
        $abn = "ABN";
        break;
    case "NZ":
        $data["Country"] = "New Zealand";
        $abn = "NZBN";
        break;
    case "UK":
        $data["Country"] = "United Kingdom";
        $abn = "CRN";
        break;
    case "US":
        $data["Country"] = "United States";
        $abn = "EIN";
        break;
    case "TH":
        $data["Country"] = "Thailand";
        $abn = "Company Registration Number";
        break;
    default:
        $data["Country"] = "Australia";
        $abn = "ABN";
}

$title = 'Push POS Customer Agreement # ' . $log["timestampDash"] . ' ' . $data["ShopName"];
?>
<!doctype html>
<html lang="en">

<head>
     <!-- Required meta tags -->
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">

     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

     <title><?php echo $title; ?></title>
     <style>
     * {
          font-size: small;
     }

     .pageNumber {
          font-size: smaller !important;
          color: #cccccc;
          margin-top: 1rem;
     }

     /* DocuSign anchor strings must exist in the PDF text layer for tab
        placement, but should not be visible to the reader. White-on-white keeps
        them selectable by DocuSign while staying invisible on the page —
        display:none or visibility:hidden would remove them from the text layer. */
     .ds-anchor {
          color: #ffffff;
     }

     td,
     th,
     li {
          font-size: smaller !important;
     }

     section {
          margin-bottom: 1.5rem;
     }

     @media print {
          .break {
               page-break-after: always;
          }

          .break-after {
               page-break-after: always;
          }

          section {
               margin-bottom: 0;
          }

          h5 {
               page-break-after: avoid;
          }

          ol,
          table {
               page-break-inside: avoid;
          }
     }
     </style>
</head>

<body>
     <div class="container-fluid py-5 px-5">
          <section>
               <div class="row mb-3">
                    <div class="col">
                         <div class="d-flex align-items-center mb-4">
                              <img src="newL4U-logo-100x100.png" alt="">
                              <h3 class=""><?php echo $contractTitle; ?></h3>
                         </div>
                    </div>
                    <div class="row">
                         <p class="mb-1">Version 2.0 &ndash; Commercial Draft. Subject to Legal Review.</p>
                         <p>This Customer Agreement ("<span class="fw-bold">Agreement</span>") is hereby executed on
                              this <?php echo $SignedDate; ?>, <?php echo $contractSubTitle; ?>
                         </p>
                    </div>
               </div>

               <div class="row">
                    <div class="col">
                         <div class="d-flex justify-content-evenly align-content-center">
                              <div class="card" style="width: 47%;">
                                   <div class="card-body">
                                        <div>
                                             <small>
                                                  <span class="fw-bold">Company Name:</span>
                                                  <?php echo $companyName; ?>
                                             </small>
                                        </div>
                                        <div>
                                             <small>
                                                  <span class="fw-bold"><?php echo $companyABN; ?>:</span>
                                                  <?php echo $companyNumber; ?>
                                             </small>
                                        </div>
                                        <div>
                                             <small>
                                                  <span class="fw-bold">Address:</span>
                                                  <?php echo $companyAddress; ?>
                                             </small>
                                        </div>
                                        <div>
                                             <small>("<span class="fw-bold">Local For You</span>")</small>
                                        </div>
                                   </div>
                              </div>
                              <div class="mt-5 fw-bold">&amp;</div>
                              <div class="card" style="width: 47%;">
                                   <div class="card-body">
                                        <div>
                                             <small>
                                                  <span class="fw-bold">Client Name:</span>
                                                  <?php echo $data["customerFullName"]; ?>
                                             </small>
                                        </div>

                                        <div>
                                             <small>
                                                  <span class="fw-bold">Shop Name:</span>
                                                  <?php echo $data["ShopName"]; ?>
                                             </small>
                                        </div>

                                        <?php if (!empty($data["legalEntity"])) { ?>
                                        <div>
                                             <small>
                                                  <span class="fw-bold">Legal Entity:</span>
                                                  <?php echo $data["legalEntity"]; ?>
                                             </small>
                                        </div>
                                        <?php } ?>

                                        <div>
                                             <small>
                                                  <span class="fw-bold"><?php echo $abn; ?>:</span>
                                                  <?php echo $data["registrationNumber"]; ?>
                                             </small>
                                        </div>

                                        <div>
                                             <small>
                                                  <span class="fw-bold">State:</span>
                                                  <?php echo $data["State"]; ?>
                                             </small>
                                        </div>

                                        <div>
                                             <small>
                                                  <span class="fw-bold">Country:</span>
                                                  <?php echo $data["Country"]; ?>
                                             </small>
                                        </div>

                                        <div>
                                             <small>(collectively referred to as the "<span
                                                       class="fw-bold">Parties</span>").</small>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>

               <div class="mt-4">
                    <h5 class="mb-2">Important Notice</h5>
                    <p class="mb-2">This Customer Agreement ("Agreement") is a legally binding agreement between Local
                         For You Pty Ltd
                         (ABN: 60 606 095 943) ("Local For You", "we", "our" or "us") and the Customer identified in the
                         applicable Order Form,
                         quotation, online registration or other purchasing method ("Customer", "you" or "your").</p>
                    <p class="mb-2">Please read this Agreement carefully before purchasing or using the Services.</p>
                    <p class="mb-2">By signing an Order Form, accepting a quotation, creating an account, clicking "I
                         Accept", making payment
                         or using the Services, you acknowledge that you have read, understood and agree to be bound by
                         this Agreement.</p>
               </div>

          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">1. Definitions</h5>
                    <p class="mb-2">In this Agreement, unless the context requires otherwise:</p>
                    <p class="mb-2"><span class="fw-bold">Agreement</span> means this Customer Agreement, together with
                         any applicable Order Form,
                         quotation, Service Schedule or policy expressly incorporated by reference.</p>
                    <p class="mb-2"><span class="fw-bold">Authorised User</span> means any employee, contractor or
                         representative authorised by the
                         Customer to access or use the Services.</p>
                    <p class="mb-2"><span class="fw-bold">Business Day</span> means a day other than a Saturday, Sunday
                         or public holiday in
                         Queensland, Australia.</p>
                    <p class="mb-2"><span class="fw-bold">Confidential Information</span> means all non-public
                         information disclosed by one party to
                         the other, including commercial, financial, technical and operational information.</p>
                    <p class="mb-2"><span class="fw-bold">Customer</span> means the individual, company or legal entity
                         acquiring the Services.</p>
                    <p class="mb-2"><span class="fw-bold">Customer Data</span> means all information entered, uploaded,
                         processed or generated by the
                         Customer through the Services, including menus, products, pricing, customer details,
                         reservations, loyalty information,
                         order history, reports, photographs, branding and other business information.</p>
                    <p class="mb-2"><span class="fw-bold">Hardware</span> means any equipment supplied by Local For You
                         including POS terminals,
                         tablets, receipt printers, kitchen printers, cash drawers, scanners, payment terminals,
                         networking equipment and accessories.</p>
                    <p class="mb-2"><span class="fw-bold">Initial Term</span> means the initial twenty-four (24) month
                         subscription period commencing
                         on the Service Commencement Date.</p>
                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 01 of 11
                    </p>
                    <p class="mb-2"><span class="fw-bold">Intellectual Property Rights</span> includes all copyright,
                         trademarks, patents, trade
                         secrets, know-how, software, databases, source code, designs and confidential information.</p>
                    <p class="mb-2"><span class="fw-bold">Order Form</span> means any signed proposal, quotation, online
                         order, electronic acceptance
                         or other purchasing document accepted by both parties.</p>
                    <p class="mb-2"><span class="fw-bold">Services</span> means all products and services supplied by
                         Local For You including Push POS,
                         Push Online Ordering, Push Payments, AI Receptionist, booking systems, loyalty, marketing
                         services, websites, hardware,
                         implementation, training and any future products released by Local For You.</p>
                    <p class="mb-2"><span class="fw-bold">Service Commencement Date</span> means the date the Customer's
                         Services are activated or such
                         other date specified in the applicable Order Form.</p>
                    <p class="mb-4"><span class="fw-bold">Subscription</span> means the Customer's ongoing right to
                         access and use the Services during
                         the Subscription Term.</p>

                    <h5 class="mb-2">2. Application of this Agreement</h5>
                    <p class="mb-2">2.1 This Agreement governs all Services supplied by Local For You unless expressly
                         agreed otherwise in writing.</p>
                    <p class="mb-2">2.2 This Agreement applies to every Order placed by the Customer.</p>
                    <p class="mb-2">2.3 In the event of inconsistency, the following order of precedence applies:</p>
                    <ol type="a">
                         <li>the applicable Order Form;</li>
                         <li>any Service Schedule;</li>
                         <li>this Agreement.</li>
                    </ol>
               </div>
          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">3. The Services</h5>
                    <p class="mb-2">3.1 Local For You provides cloud-based hospitality software and related services
                         designed to assist restaurants and
                         hospitality businesses manage operations, online ordering, customer engagement and business
                         performance.</p>
                    <p class="mb-2">3.2 Services may include:</p>
                    <ol type="a">
                         <li>Push POS;</li>
                         <li>Push Online Ordering;</li>
                         <li>Push Payments;</li>
                         <li>AI Receptionist;</li>
                         <li>Booking Systems;</li>
                         <li>Loyalty Programs;</li>
                         <li>Gift Cards;</li>
                         <li>QR Ordering;</li>
                         <li>Website Services;</li>
                         <li>Marketing Services;</li>
                         <li>Hardware;</li>
                         <li>Installation Services;</li>
                         <li>Implementation Services;</li>
                         <li>Migration Services;</li>
                         <li>Training Services;</li>
                         <li>Professional Services;</li>
                         <li>Future products and services developed or supplied by Local For You.</li>
                    </ol>
                    <p class="mb-4">3.3 Local For You may improve, modify, replace or discontinue features where
                         reasonably necessary to improve the
                         Services, provided the core functionality purchased by the Customer is not materially reduced.
                    </p>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 02 of 11
                    </p>
                    <h5 class="mb-2">4. Orders</h5>
                    <p class="mb-2">4.1 The Customer may purchase Services by:</p>
                    <ol type="a">
                         <li>signing an Order Form;</li>
                         <li>accepting a quotation;</li>
                         <li>accepting an electronic proposal;</li>
                         <li>completing an online checkout;</li>
                         <li>email confirmation; or</li>
                         <li>any other purchasing method approved by Local For You.</li>
                    </ol>
                    <p class="mb-2">4.2 Each accepted Order constitutes a separate purchase governed by this Agreement.
                    </p>
                    <p class="mb-4">4.3 Local For You reserves the right to reject any Order prior to acceptance.</p>

                    <h5 class="mb-2">5. Term and Renewal</h5>
                    <p class="mb-2">5.1 Unless otherwise agreed in writing, the Subscription for Push POS commences on
                         the Service Commencement Date and
                         continues for the Initial Term of twenty-four (24) months.</p>
                    <p class="mb-2">5.2 Following expiry of the Initial Term, the Subscription automatically renews on a
                         month-to-month basis unless
                         terminated in accordance with this Agreement.</p>
                    <p class="mb-2">5.3 Following the Initial Term, either party may terminate the Subscription by
                         providing not less than thirty (30)
                         days written notice.</p>
                    <p class="mb-2">5.4 If the Customer terminates the Agreement before expiry of the Initial Term
                         without Local For You's written
                         agreement, the Customer remains liable for all fees payable for the balance of the Initial Term
                         unless otherwise agreed in writing.</p>
                    <p class="mb-4">5.5 Suspension or interruption of the Services does not extend or reduce the
                         Subscription Term.</p>
               </div>
          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">6. Fees</h5>
                    <p class="mb-2">6.1 The Customer agrees to pay all fees specified in the applicable Order Form,
                         quotation or invoice.</p>
                    <p class="mb-2">6.2 Fees may include, without limitation:</p>
                    <ol type="a">
                         <li>Monthly Subscription Fees;</li>
                         <li>Push POS licence fees;</li>
                         <li>Hardware purchase fees;</li>
                         <li>Hardware rental or leasing fees;</li>
                         <li>Installation fees;</li>
                         <li>Implementation fees;</li>
                         <li>Training fees;</li>
                         <li>Data migration fees;</li>
                         <li>Professional Services;</li>
                         <li>Payment processing fees (where applicable);</li>
                         <li>Third-party charges approved by the Customer.</li>
                    </ol>
                    <p class="mb-2">6.3 Unless otherwise stated in the applicable Order Form:</p>
                    <ol type="a">
                         <li>Subscription Fees are payable monthly in advance;</li>
                         <li>Professional Services are payable in accordance with the applicable invoice;</li>
                         <li>Hardware charges are payable in accordance with the agreed payment terms.</li>
                    </ol>
                    <p class="mb-2">6.4 All prices are exclusive of GST and any other applicable taxes unless expressly
                         stated otherwise.</p>
                    <p class="mb-4">6.5 Local For You reserves the right to vary its pricing by providing the Customer
                         with not less than thirty (30)
                         days' written notice. Any price variation will not affect the Initial Term unless expressly
                         agreed by the parties or where the
                         variation relates to government charges, taxes or third-party costs outside Local For You's
                         reasonable control.</p>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 03 of 11
                    </p>

                    <h5 class="mb-2">7. Payment Terms</h5>
                    <p class="mb-2">7.1 The Customer must pay all invoices by the due date specified in the applicable
                         invoice or Order Form.</p>
                    <p class="mb-2">7.2 Payments may be made by direct debit, credit card, bank transfer or any other
                         payment method approved by Local For You.</p>
                    <p class="mb-2">7.3 If payment is not received by the due date, Local For You may, without limiting
                         any other rights:</p>
                    <ol type="a">
                         <li>charge interest on overdue amounts at the maximum rate permitted by law;</li>
                         <li>suspend access to the Services;</li>
                         <li>suspend customer support;</li>
                         <li>suspend implementation or onboarding activities;</li>
                         <li>recover reasonable debt collection costs and legal expenses incurred in recovering
                              outstanding amounts.</li>
                    </ol>
                    <p class="mb-2">7.4 Suspension of the Services does not relieve the Customer of its obligation to
                         pay outstanding fees.</p>
                    <p class="mb-2">7.5 If the Customer disputes an invoice, the Customer must notify Local For You in
                         writing within seven (7) days of
                         the invoice date, setting out the reasons for the dispute. Any undisputed portion of the
                         invoice remains payable.</p>
                    <p class="mb-4">7.6 Local For You may apply payments received to any outstanding invoices in the
                         order it considers appropriate.</p>
               </div>
          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">8. Hardware</h5>
                    <p class="mb-2">8.1 Local For You may sell, lease, rent or otherwise supply Hardware to the Customer
                         as specified in the applicable Order Form.</p>
                    <p class="mb-2">8.2 Title to purchased Hardware remains with Local For You until all amounts
                         relating to that Hardware have been paid in full.</p>
                    <p class="mb-2">8.3 Risk in the Hardware passes to the Customer upon delivery.</p>
                    <p class="mb-2">8.4 The Customer must:</p>
                    <ol type="a">
                         <li>use the Hardware only for its intended purpose;</li>
                         <li>keep the Hardware in good working order;</li>
                         <li>protect the Hardware from theft, loss or damage;</li>
                         <li>not modify, repair or alter the Hardware except as authorised by Local For You or the
                              manufacturer.</li>
                    </ol>
                    <p class="mb-2">8.5 Manufacturer warranties apply where available. Local For You will provide
                         reasonable assistance to the Customer in
                         making warranty claims but is not responsible for warranty decisions made by the manufacturer.
                    </p>
                    <p class="mb-2">8.6 Unless otherwise agreed in writing, Hardware purchases are non-refundable once
                         delivered and accepted, except where
                         required by applicable law.</p>
                    <p class="mb-4">8.7 Hardware leased or rented by Local For You remains the property of Local For You
                         at all times and must be returned in
                         good working order upon termination of the applicable lease or rental agreement, allowing for
                         fair wear and tear.</p>

                    <h5 class="mb-2">9. Payment Processing</h5>
                    <p class="mb-2">9.1 The Customer may choose any payment processor that is compatible with the
                         Services and approved by Local For You.</p>
                    <p class="mb-2">9.2 Local For You's preferred integrated payment provider is Zeller. The Customer is
                         under no obligation to use Zeller
                         unless otherwise agreed in writing.</p>
                    <p class="mb-2">9.3 Where the Customer elects to use a third-party payment provider, the Customer
                         acknowledges that:</p>
                    <ol type="a">
                         <li>the agreement with the payment provider is separate from this Agreement;</li>
                         <li>payment processing services are supplied solely by the payment provider;</li>
                         <li>Local For You is not responsible for payment processing delays, transaction failures,
                              chargebacks, payment disputes or fees
                              charged by any third-party payment provider.</li>
                    </ol>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 04 of 11
                    </p>
                    <p class="mb-2">9.4 The Customer is responsible for complying with the terms and conditions of its
                         chosen payment provider.</p>
                    <p class="mb-4">9.5 Local For You may add, remove or change supported payment providers from time to
                         time to improve functionality,
                         security or performance.</p>

                    <h5 class="mb-2">10. Customer Obligations</h5>
                    <p class="mb-2">10.1 The Customer agrees to:</p>
                    <ol type="a">
                         <li>provide complete and accurate information when registering for the Services;</li>
                         <li>promptly notify Local For You of any changes to business ownership, trading name, contact
                              details or billing information;</li>
                         <li>maintain a reliable internet connection suitable for operating the Services;</li>
                         <li>maintain compatible Hardware and equipment;</li>
                         <li>keep usernames, passwords and access credentials secure;</li>
                         <li>ensure only Authorised Users access the Services;</li>
                         <li>comply with all applicable laws and regulations;</li>
                         <li>ensure Customer Data is lawful, accurate and up to date;</li>
                         <li>promptly report any suspected security incident or unauthorised access;</li>
                         <li>maintain current backups of any Customer Data exported from the Services where required;
                         </li>
                         <li>pay all fees when due.</li>
                    </ol>
                    <p class="mb-2">10.2 The Customer is responsible for all activity undertaken using its account,
                         including activity by Authorised Users.</p>
                    <p class="mb-2">10.3 The Customer must not:</p>
                    <ol type="a">
                         <li>use the Services for any unlawful purpose;</li>
                         <li>interfere with or disrupt the operation of the Services;</li>
                         <li>attempt to gain unauthorised access to any Local For You systems;</li>
                         <li>reverse engineer, decompile or copy the Services except where permitted by law;</li>
                         <li>upload malicious software or code;</li>
                         <li>use the Services in a manner likely to damage Local For You's reputation or systems;</li>
                         <li>permit unauthorised third parties to use the Services.</li>
                    </ol>
               </div>
          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">11. Local For You Obligations</h5>
                    <p class="mb-2">11.1 Local For You will use reasonable care and skill in providing the Services.</p>
                    <p class="mb-2">11.2 Local For You will use commercially reasonable efforts to:</p>
                    <ol type="a">
                         <li>provide reliable access to the Services;</li>
                         <li>maintain the security and integrity of the platform;</li>
                         <li>provide technical support during published support hours;</li>
                         <li>deliver implementation and onboarding services where purchased;</li>
                         <li>continually improve the Services through updates, enhancements and security improvements.
                         </li>
                    </ol>
                    <p class="mb-2">11.3 Local For You may engage subcontractors, technology partners and third-party
                         service providers in delivering the Services.</p>
                    <p class="mb-2">11.4 Local For You will use best endeavours to minimise service interruptions but
                         does not guarantee uninterrupted or
                         error-free operation of the Services.</p>
                    <p class="mb-4">11.5 Scheduled maintenance may be undertaken from time to time. Where reasonably
                         practicable, Local For You will provide
                         advance notice of planned maintenance likely to materially affect the availability of the
                         Services.</p>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 05 of 11
                    </p>
                    <h5 class="mb-2">12. Updates, Upgrades and Platform Changes</h5>
                    <p class="mb-2">12.1 Local For You continually develops and improves the Services.</p>
                    <p class="mb-2">12.2 Local For You may at any time:</p>
                    <ol type="a">
                         <li>release software updates;</li>
                         <li>introduce new functionality;</li>
                         <li>improve existing features;</li>
                         <li>improve security;</li>
                         <li>improve performance;</li>
                         <li>improve integrations;</li>
                         <li>replace obsolete technology;</li>
                         <li>migrate the Services to replacement infrastructure or hosting providers;</li>
                         <li>upgrade the underlying software platform.</li>
                    </ol>
                    <p class="mb-2">12.3 Local For You will use reasonable efforts to ensure that updates do not
                         materially reduce the core functionality of
                         the Services purchased by the Customer.</p>
                    <p class="mb-2">12.4 The Customer acknowledges that software platforms evolve over time and agrees
                         that Local For You may migrate the
                         Services to replacement technology where reasonably necessary to improve reliability, security,
                         scalability or functionality.</p>
                    <p class="mb-2">12.5 Where reasonably practicable, Local For You will provide advance notice of
                         material changes affecting the Customer.</p>
                    <p class="mb-4">12.6 Continued use of the Services following any update, upgrade or migration
                         constitutes acceptance of those changes.</p>

                    <h5 class="mb-2">13. Third-Party Products and Integrations</h5>
                    <p class="mb-2">13.1 The Services may integrate with products or services supplied by third parties
                         including, without limitation:</p>
                    <ol type="a">
                         <li>Zeller;</li>
                         <li>Stripe;</li>
                         <li>Cloud Waitress;</li>
                         <li>Google;</li>
                         <li>Meta;</li>
                         <li>Microsoft;</li>
                         <li>Xero;</li>
                         <li>MYOB;</li>
                         <li>delivery providers;</li>
                         <li>SMS providers;</li>
                         <li>email providers; and</li>
                         <li>other technology providers approved by Local For You.</li>
                    </ol>
                    <p class="mb-2">13.2 Local For You is not responsible for the availability, performance or terms of
                         service of third-party products or services.</p>
                    <p class="mb-2">13.3 The Customer acknowledges that third-party providers may change, suspend or
                         discontinue their services at any time.</p>
                    <p class="mb-4">13.4 Local For You may replace or remove third-party integrations where reasonably
                         necessary to maintain or improve the Services.</p>

                    <h5 class="mb-2">14. Customer Data</h5>
                    <p class="mb-2">14.1 The Customer retains all right, title and interest in and to its Customer Data.
                    </p>
                    <p class="mb-2">14.2 Customer Data includes, without limitation: menus; products; pricing; customer
                         information; reservations; loyalty data;
                         gift card information; order history; reports; photographs; logos; marketing material; business
                         information entered into the Services.</p>
                    <p class="text-center pageNumber">
                         Push POS Customer Agreement : 06 of 11
                    </p>
                    <p class="mb-2">14.3 The Customer grants Local For You a non-exclusive, worldwide, royalty-free
                         licence to host, store, process, transmit,
                         reproduce and use Customer Data solely for the purpose of:</p>

                    <ol type="a">
                         <li>providing the Services;</li>
                         <li>supporting the Customer;</li>
                         <li>improving the Services;</li>
                         <li>maintaining security;</li>
                         <li>complying with applicable laws.</li>
                    </ol>
                    <p class="mb-2">14.4 Local For You does not acquire ownership of Customer Data.</p>
                    <p class="mb-2">14.5 The Customer warrants that it has all necessary rights to upload and use
                         Customer Data within the Services.</p>
                    <p class="mb-4">14.6 The Customer is solely responsible for the accuracy, legality and content of
                         Customer Data.</p>
               </div>

          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">15. Privacy and Data Protection</h5>
                    <p class="mb-2">15.1 Local For You will collect, use, disclose and store personal information in
                         accordance with its Privacy Policy and
                         applicable privacy legislation.</p>
                    <p class="mb-2">15.2 The Customer acknowledges that Local For You may collect information reasonably
                         necessary to:</p>
                    <ol type="a">
                         <li>provide the Services;</li>
                         <li>administer customer accounts;</li>
                         <li>provide support;</li>
                         <li>improve products and services;</li>
                         <li>maintain platform security;</li>
                         <li>comply with legal obligations.</li>
                    </ol>
                    <p class="mb-2">15.3 The Customer must ensure it has obtained all necessary consents required to
                         upload personal information into the Services.</p>
                    <p class="mb-2">15.4 Local For You will take commercially reasonable steps to protect Customer Data
                         against unauthorised access, loss or misuse.</p>
                    <p class="mb-4">15.5 Nothing in this Agreement prevents Local For You from using aggregated and
                         de-identified data for analytics,
                         benchmarking, reporting and product improvement, provided no individual Customer can be
                         identified.</p>

                    <h5 class="mb-2">16. Intellectual Property</h5>
                    <p class="mb-2">16.1 All Intellectual Property Rights in the Services remain the exclusive property
                         of Local For You or its licensors.</p>
                    <p class="mb-2">16.2 Nothing in this Agreement transfers ownership of any Intellectual Property
                         Rights to the Customer.</p>
                    <p class="mb-2">16.3 Local For You retains ownership of all: software; source code; object code;
                         APIs; documentation; workflows;
                         artificial intelligence models; automation; templates; branding; designs; training materials;
                         enhancements; improvements.</p>
                    <p class="mb-2">16.4 The Customer must not copy, modify, reverse engineer, decompile, create
                         derivative works, distribute, resell or
                         sublicense any part of the Services except as expressly permitted by this Agreement.</p>
                    <p class="mb-4">16.5 Any suggestions, recommendations, feature requests or feedback provided by the
                         Customer may be used by Local For You
                         without restriction or payment.</p>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 07 of 11
                    </p>
                    <h5 class="mb-2">17. Confidentiality</h5>
                    <p class="mb-2">17.1 Each party agrees to keep confidential all Confidential Information received
                         from the other party.</p>
                    <p class="mb-2">17.2 Confidential Information must not be disclosed except:</p>
                    <ol type="a">
                         <li>with prior written consent;</li>
                         <li>where required by law;</li>
                         <li>to professional advisers;</li>
                         <li>to employees or contractors who require access for the purposes of this Agreement.</li>
                    </ol>
                    <p class="mb-2">17.3 Each party must take reasonable steps to protect the confidentiality of the
                         other party's Confidential Information.</p>
                    <p class="mb-4">17.4 These obligations survive termination of this Agreement.</p>

                    <h5 class="mb-2">18. Artificial Intelligence Features</h5>
                    <p class="mb-2">18.1 Certain Services may include artificial intelligence functionality.</p>
                    <p class="mb-2">18.2 The Customer acknowledges that AI-generated responses:</p>
                    <ol type="a">
                         <li>may contain errors;</li>
                         <li>may be incomplete;</li>
                         <li>should be reviewed before publication or reliance;</li>
                         <li>do not constitute legal, financial, accounting or professional advice.</li>
                    </ol>
                    <p class="mb-2">18.3 The Customer remains solely responsible for verifying all AI-generated content
                         before using or publishing it.</p>
                    <p class="mb-2">18.4 Local For You does not warrant the accuracy, completeness or suitability of
                         AI-generated content.</p>
                    <p class="mb-4">18.5 Local For You may improve, retrain, replace or discontinue AI functionality
                         from time to time.</p>

                    <h5 class="mb-2">19. Support</h5>
                    <p class="mb-2">19.1 Local For You will provide support on a best endeavours basis.</p>
                    <p class="mb-2">19.2 Support is available during Local For You's published business hours unless
                         otherwise agreed.</p>
                    <p class="mb-2">19.3 Support includes reasonable assistance with: software issues; platform access;
                         system configuration; fault diagnosis;
                         implementation enquiries.</p>
                    <p class="mb-2">19.4 Support does not include: customer network issues; third-party hardware not
                         supplied by Local For You; third-party
                         software not supported by Local For You; training outside the agreed scope; custom development
                         unless separately purchased.</p>
                    <p class="mb-4">19.5 Local For You will use reasonable efforts to respond to support requests
                         promptly but does not guarantee specific
                         response or resolution times unless separately agreed in writing.</p>

                    <h5 class="mb-2">20. Warranties and Disclaimers</h5>
                    <p class="mb-2">20.1 Local For You warrants that it will provide the Services with reasonable care
                         and skill.</p>
                    <p class="mb-2">20.2 Except as expressly stated in this Agreement, the Services are provided on an
                         "as available" and "as is" basis.</p>
                    <p class="mb-2">20.3 To the maximum extent permitted by law, Local For You excludes all warranties,
                         representations and guarantees, whether
                         express, implied or statutory, including any implied warranties of merchantability, fitness for
                         a particular purpose or non-infringement.</p>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 08 of 11
                    </p>
                    <p class="mb-2">20.4 Local For You does not warrant that:</p>
                    <ol type="a">
                         <li>the Services will operate without interruption;</li>
                         <li>the Services will be error free;</li>
                         <li>every defect will be corrected;</li>
                         <li>the Services will meet every business requirement of the Customer;</li>
                         <li>third-party services will remain available.</li>
                    </ol>
                    <p class="mb-4">20.5 Nothing in this Agreement excludes any consumer guarantees that cannot lawfully
                         be excluded under the Australian
                         Consumer Law.</p>
               </div>
          </section>

          <section>
               <div class="mt-4">
                    <h5 class="mb-2">21. Limitation of Liability</h5>
                    <p class="mb-2">21.1 To the maximum extent permitted by law, Local For You is not liable for any:
                         loss of profits; loss of revenue;
                         loss of business opportunity; loss of anticipated savings; loss of goodwill; business
                         interruption; loss of data; indirect loss;
                         consequential loss; exemplary or punitive damages.</p>
                    <p class="mb-2">21.2 Local For You's total aggregate liability arising out of or in connection with
                         this Agreement is limited to the total
                         fees paid by the Customer to Local For You during the twelve (12) months immediately preceding
                         the event giving rise to the claim.</p>
                    <p class="mb-2">21.3 The limitations contained in this clause apply regardless of the legal basis of
                         the claim, including contract,
                         negligence, statute or otherwise.</p>
                    <p class="mb-4">21.4 Nothing in this Agreement limits liability where such limitation is prohibited
                         by law.</p>

                    <h5 class="mb-2">22. Indemnity</h5>
                    <p class="mb-2">22.1 The Customer indemnifies and holds harmless Local For You, its directors,
                         employees, contractors and agents from and
                         against any claim, loss, damage, liability, cost or expense arising directly or indirectly
                         from:</p>
                    <ol type="a">
                         <li>the Customer's breach of this Agreement;</li>
                         <li>unlawful use of the Services;</li>
                         <li>Customer Data;</li>
                         <li>infringement of intellectual property rights arising from Customer Data;</li>
                         <li>misuse of the Services;</li>
                         <li>negligence or wilful misconduct by the Customer or its Authorised Users.</li>
                    </ol>
                    <p class="mb-4">22.2 This indemnity survives termination of this Agreement.</p>

                    <h5 class="mb-2">23. Suspension of Services</h5>
                    <p class="mb-2">23.1 Local For You may suspend all or part of the Services immediately where:</p>
                    <ol type="a">
                         <li>payment remains overdue;</li>
                         <li>the Customer materially breaches this Agreement;</li>
                         <li>continued operation presents a security risk;</li>
                         <li>the Customer uses the Services unlawfully;</li>
                         <li>maintenance is required to protect the integrity of the Services;</li>
                         <li>required by law.</li>
                    </ol>
                    <p class="mb-2">23.2 Local For You will use reasonable efforts to notify the Customer before
                         suspension where practical.</p>
                    <p class="mb-4">23.3 Suspension does not relieve the Customer from paying any applicable fees.</p>
                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 09 of 11
                    </p>
                    <h5 class="mb-2">24. Termination</h5>
                    <p class="mb-2">24.1 Either party may terminate this Agreement following the Initial Term by
                         providing thirty (30) days' written notice.</p>


                    <p class="mb-2">24.2 Local For You may terminate this Agreement immediately where:</p>
                    <ol type="a">
                         <li>the Customer commits a material breach that is not remedied within fourteen (14) days after
                              written notice;</li>
                         <li>the Customer becomes insolvent or enters liquidation, administration or bankruptcy;</li>
                         <li>continued provision of the Services would be unlawful;</li>
                         <li>the Customer repeatedly breaches this Agreement.</li>
                    </ol>

                    <p class="mb-2">24.3 Upon termination:</p>
                    <ol type="a">
                         <li>the Customer's right to access the Services immediately ceases;</li>
                         <li>all outstanding amounts become immediately due and payable;</li>
                         <li>the Customer must return any Hardware owned by Local For You;</li>
                         <li>Local For You may deactivate Customer accounts following any applicable data retention
                              period.</li>
                    </ol>

                    <p class="mb-4">24.4 Termination does not affect any accrued rights or obligations.</p>

                    <h5 class="mb-2">25. Force Majeure</h5>
                    <p class="mb-2">25.1 Neither party is liable for any delay or failure to perform its obligations
                         where such delay or failure results from
                         events beyond its reasonable control.</p>
                    <p class="mb-2">25.2 Force Majeure Events include, without limitation: natural disasters; floods;
                         fire; pandemic; war; terrorism; cyber
                         attacks; internet outages; telecommunications failures; government action; industrial disputes;
                         widespread power failures.</p>
                    <p class="mb-4">25.3 The affected party must use reasonable efforts to minimise the impact of the
                         Force Majeure Event.</p>

                    <h5 class="mb-2">26. Compliance with Laws</h5>
                    <p class="mb-2">26.1 Each party must comply with all applicable laws and regulations relevant to its
                         obligations under this Agreement.</p>
                    <p class="mb-4">26.2 The Customer is responsible for ensuring its use of the Services complies with
                         all taxation, employment, privacy,
                         consumer protection and hospitality laws applicable to its business.</p>

                    <h5 class="mb-2">27. Publicity</h5>
                    <p class="mb-2">27.1 Unless the Customer notifies Local For You otherwise in writing, the Customer
                         grants Local For You permission to
                         identify the Customer as a customer of Local For You.</p>
                    <p class="mb-2">27.2 Local For You may use the Customer's business name and logo in: customer lists;
                         marketing material; presentations;
                         case studies; website content.</p>
                    <p class="mb-4">27.3 Local For You will not disclose confidential commercial information without the
                         Customer's prior written consent.</p>

                    <h5 class="mb-2">28. Notices</h5>
                    <p class="mb-2">28.1 Any notice under this Agreement must be given: by email; by registered post; or
                         through any customer portal designated
                         by Local For You.</p>
                    <p class="mb-4">28.2 Notices are deemed received: immediately upon successful electronic
                         transmission where sent by email during Business
                         Hours; otherwise on the next Business Day; five (5) Business Days after posting by registered
                         mail.</p>

               </div>
          </section>

          <p class="text-center pageNumber break-after">
               Push POS Customer Agreement : 10 of 11
          </p>
          <section>
               <div class="mt-4">
                    <h5 class="mb-2">29. Assignment</h5>
                    <p class="mb-2">29.1 The Customer must not assign, transfer, novate or otherwise deal with any of
                         its rights or obligations under this
                         Agreement without the prior written consent of Local For You.</p>
                    <p class="mb-2">29.2 Local For You may assign, transfer or novate this Agreement without the
                         Customer's consent in connection with:
                         the sale of all or part of its business; a merger or acquisition; a corporate restructure; the
                         transfer of assets or intellectual
                         property; or any successor entity.</p>
                    <p class="mb-4">29.3 This Agreement binds and benefits the parties and their permitted successors
                         and assigns.</p>

                    <h5 class="mb-2">30. Relationship of the Parties</h5>
                    <p class="mb-2">30.1 Nothing contained in this Agreement creates or is intended to create a
                         partnership, joint venture, employment
                         relationship, agency relationship or fiduciary relationship between Local For You and the
                         Customer.</p>
                    <p class="mb-4">30.2 Each party acts as an independent contractor.</p>

                    <h5 class="mb-2">31. Electronic Communications</h5>
                    <p class="mb-2">31.1 The Customer agrees that Local For You may communicate with the Customer
                         electronically, including by: email; SMS;
                         customer portal; electronic invoices; software notifications.</p>
                    <p class="mb-2">31.2 Electronic communications satisfy any legal requirement that communications be
                         provided in writing.</p>
                    <p class="mb-4">31.3 The Customer is responsible for ensuring its contact details remain current.
                    </p>

                    <h5 class="mb-2">32. Variation of Agreement</h5>
                    <p class="mb-2">32.1 Local For You may update this Agreement from time to time where reasonably
                         necessary to: reflect changes in law;
                         improve the Services; address security requirements; reflect changes to business operations;
                         incorporate new products or services.</p>
                    <p class="mb-2">32.2 Where a material change is made, Local For You will provide reasonable notice
                         to the Customer.</p>
                    <p class="mb-4">32.3 Continued use of the Services after the effective date of an updated Agreement
                         constitutes acceptance of the revised
                         Agreement.</p>

                    <h5 class="mb-2">33. Severability</h5>
                    <p class="mb-4">33.1 If any provision of this Agreement is held to be invalid, illegal or
                         unenforceable, that provision shall be severed
                         and the remaining provisions shall continue in full force and effect.</p>

                    <h5 class="mb-2">34. Waiver</h5>
                    <p class="mb-2">34.1 A failure or delay by either party to exercise any right under this Agreement
                         does not constitute a waiver of that right.</p>
                    <p class="mb-4">34.2 Any waiver must be in writing and signed by the party granting the waiver.</p>

                    <h5 class="mb-2">35. Entire Agreement</h5>
                    <p class="mb-2">35.1 This Agreement, together with any applicable Order Form and documents expressly
                         incorporated by reference, constitutes
                         the entire agreement between the parties regarding the Services.</p>
                    <p class="mb-4">35.2 This Agreement supersedes all previous discussions, proposals, negotiations,
                         representations and agreements relating
                         to the Services.</p>

                    <p class="text-center pageNumber break-after">
                         Push POS Customer Agreement : 11 of 11
                    </p>
                    <h5 class="mb-2">36. Governing Law</h5>
                    <p class="mb-2">36.1 This Agreement is governed by the laws of Queensland, Australia.</p>
                    <p class="mb-4">36.2 The parties submit to the exclusive jurisdiction of the courts of Queensland.
                    </p>

               </div>
          </section>

          <section>
               <div class="row">
                    <div class="col mt-5">
                         <h3 class="mb-4">Signatures</h3>
                         <p class="fw-bold">
                              By signing the applicable Order Form, quotation or electronic acceptance, or by using the
                              Services, the Customer agrees to be
                              legally bound by this Agreement. Where this Agreement is executed in writing, the parties
                              agree as follows.
                         </p>
                    </div>
               </div>
               <div class="my-3">
                    <div class="d-flex justify-content-evenly align-content-center">
                         <div class="card" style="width: 47%;">
                              <div class="card-body">
                                   <div>
                                        <span class="fw-bold">Customer</span>
                                   </div>
                                   <div>
                                        <span class="fw-bold">Business Name:</span>
                                        <?php echo $data["ShopName"]; ?>
                                   </div>
                                   <div>
                                        <span class="fw-bold">Legal Entity:</span>
                                        <?php echo !empty($data["legalEntity"]) ? $data["legalEntity"] : "______________________"; ?>
                                   </div>
                                   <div>
                                        <span class="fw-bold"><?php echo $abn; ?> (if applicable):</span>
                                        <?php echo $data["registrationNumber"]; ?>
                                   </div>
                                   <div>
                                        <span class="fw-bold">Client Signature:</span>
                                        <?php if ($docusignMode): ?>
                                        <span class="ds-anchor">/sig1/</span>
                                        <?php else: ?>
                                        <img src="blank-80.jpg" height="80" alt="">
                                        <?php endif; ?>
                                   </div>
                                   <div class="col">
                                        <span class="fw-bold">Printed Name:</span>
                                        <?php echo $data["customerFullName"]; ?>
                                   </div>
                                   <div>&nbsp;</div>
                                   <div class="col">
                                        <span class="fw-bold">Date:</span>
                                        <?php if ($docusignMode): ?>
                                        <span class="ds-anchor">/date1/</span>
                                        <?php else: ?>
                                        <img src="blank-30.jpg" height="30" alt="">
                                        <?php endif; ?>
                                   </div>
                              </div>
                         </div>

                         <div class="card" style="width: 47%;">
                              <div class="card-body">
                                   <div>
                                        <span class="fw-bold">Local For You Pty Ltd</span>
                                   </div>
                                   <div>
                                        <span class="fw-bold">Company Name:</span>
                                        <?php echo $companyName; ?>
                                   </div>
                                   <div>
                                        <span class="fw-bold">ABN:</span>
                                        <?php echo $companyNumber; ?>
                                   </div>
                                   <div>
                                        <span class="fw-bold">Signature:</span>
                                        <img src="signature-80.jpg" height="80" alt="">
                                   </div>
                                   <div>
                                        <span class="fw-bold">Printed Name:</span>
                                        Steven Waterson
                                   </div>
                                   <div>
                                        <span class="fw-bold">Position:</span>
                                        CEO Local For You
                                   </div>
                                   <div class="col">
                                        <span class="fw-bold">Date:</span>
                                        <?php echo $SignedDate; ?>
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>

          </section>

          <section>
               <div class="mt-4">
                    <h4 class="mb-3">Schedule 1: Commercial Summary</h4>
                    <p class="mb-2">Unless otherwise agreed in writing:</p>
                    <table class="table table-bordered">
                         <tbody>
                              <tr>
                                   <th scope="row" style="width: 30%;">Supplier</th>
                                   <td>Local For You Pty Ltd</td>
                              </tr>
                              <tr>
                                   <th scope="row">Product</th>
                                   <td>Push POS</td>
                              </tr>
                              <tr>
                                   <th scope="row">Initial Contract Term</th>
                                   <td>24 Months</td>
                              </tr>
                              <tr>
                                   <th scope="row">Renewal</th>
                                   <td>Automatically renews month-to-month after the Initial Term.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Termination</th>
                                   <td>30 days' written notice following the Initial Term.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Support</th>
                                   <td>Best endeavours during published support hours.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Preferred Payment Provider</th>
                                   <td>Zeller</td>
                              </tr>
                              <tr>
                                   <th scope="row">Payment Terms</th>
                                   <td>Monthly in advance unless otherwise agreed.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Hardware</th>
                                   <td>Sold, leased or rented as specified in the applicable Order Form.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Data Ownership</th>
                                   <td>Customer retains ownership of all Customer Data.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Software Ownership</th>
                                   <td>Local For You retains ownership of all software, source code, intellectual
                                        property and related technology.</td>
                              </tr>
                              <tr>
                                   <th scope="row">Governing Law</th>
                                   <td>Queensland, Australia.</td>
                              </tr>
                         </tbody>
                    </table>
               </div>
          </section>
     </div>
     <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
          integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
     </script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
          integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
     </script>

</body>

</html>

<?php
// cutColon() is declared near the top of this file so it can be guarded with
// function_exists() and still be available to the calls above.
?>