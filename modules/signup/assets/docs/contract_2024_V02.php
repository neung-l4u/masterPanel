<?php
date_default_timezone_set("Asia/Bangkok");
$SignedDate = date('d/m/Y');
$abn = "ABN";

$log["timestamp"] =  date('Y-m-d H:i:s');
$log["timestampDash"] =  date('Y-m-d His');

$data["customerFullName"] = !empty($_REQUEST["customerFullName"]) ? trim($_REQUEST["customerFullName"]) : "-- No Name --";
$data["ShopName"] = !empty($_REQUEST["ShopName"]) ? trim($_REQUEST["ShopName"]) : "-- No ShopName --";
$data["contractPeriod"] = isset($_REQUEST["contractPeriod"]) ? trim($_REQUEST["contractPeriod"]) : "-- No contractPeriod --";
$data["registrationNumber"] = !empty($_REQUEST["registrationNumber"]) ? trim($_REQUEST["registrationNumber"]) : "-- No registrationNumber --";
$data["State"] = !empty($_REQUEST["State"]) ? trim($_REQUEST["State"]) : "-- No State --";
$data["Country"] = !empty($_REQUEST["Country"]) ? trim($_REQUEST["Country"]) : "-- No Country --";

$data["registrationNumber"] = cutColon($data["registrationNumber"]);

$contractTitle = "";
$contractSubTitle = "";
$abn = "";
$companyName = "";
$companyAddress = "";
$companyNumber = "";

switch ($_REQUEST["Country"]){
    case "AU":
        $data["Country"] = "Australia";
        $contractTitle = "Marketing Service Agreement - Local For You Pty Ltd";
        $contractSubTitle = "in Australia, by and between:";
        $abn = "ABN";
        $companyName = "Manaexito Pty Ltd T/as Local For You Pty Ltd";
        $companyAddress = "9/204 Alice Street, Brisbane, QLD 4000, Australia";
        $companyNumber = "60 606 095 943";
        $companyABN = "ABN";
        break;
    case "NZ":
        $data["Country"] = "New Zealand";
        $contractTitle = "Marketing Service Agreement - Local For You Pty Ltd";
        $contractSubTitle = "in Australia, by and between:";
        $abn = "NZBN";
        $companyName = "Manaexito Pty Ltd T/as Local For You Pty Ltd";
        $companyAddress = "9/204 Alice Street, Brisbane, QLD 4000, Australia";
        $companyNumber = "60 606 095 943";
        $companyABN = "ABN";
        break;
    case "US" :
        $data["Country"] = "United States";
        $contractTitle = "USA Marketing Service Agreement - Local For You LLC";
        $contractSubTitle = "in Delaware, USA by and between:";
        $abn = "EIN";
        $companyName = "Local For You LLC";
        $companyAddress = "8 The Green STE R, Dover, DE 19901 USA";
        $companyNumber = "35-2789835";
        $companyABN = "EIN";
        break;
    case "TH":
        $data["Country"] = "Thailand";
        $contractTitle = "Marketing Service Agreement - Local Eats Co., Ltd";
        $contractSubTitle = "in Thailand, by and between:";
        $abn = "Company Registration Number";
        $companyName = "Local Eats Co., Ltd";
        $companyAddress = "216/61, Bond Street Road, Pak Kret, Nonthaburi 11120, Thailand";
        $companyNumber = "0105551234567";
        $companyABN = "Company Registration Number";
        break;  
    default :
        $contractTitle = "Marketing Service Agreement - Local For You Pty Ltd";
        $contractSubTitle = "in Australia, by and between:";
        $abn = "ABN";
        $companyName = "Manaexito Pty Ltd T/as Local For You Pty Ltd";
        $companyAddress = "9/204 Alice Street, Brisbane, QLD 4000, Australia";
        $companyNumber = "60 606 095 943";
        $companyABN = "ABN";
}

$title = 'Contract # '.$log["timestampDash"].' '.$data["ShopName"];
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title><?php echo $title; ?></title>
    <style>
        *{
            font-size: small;
        }
        .pageNumber{
            font-size: smaller !important;
            color: #cccccc;
        }
        td,th,li{
            font-size: smaller !important;
        }
        @media print {
            .break {page-break-after: always;}
        }
    </style>
</head>
<body>
<div class="container-fluid py-5 px-5">
    <section>
        <div class="row mb-3">
            <div class="col">
                <div class="d-flex align-items-center mb-5">
                    <img src="newL4U-logo-100x100.png" alt="">
                    <h3 class=""><?php echo $contractTitle; ?></h3>
                </div>
            </div>
            <div class="row">
                <p>This Marketing Services Agreement ("<span class="fw-bold">Agreement</span>") is hereby executed on
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
                                <!--<small>("<span class="fw-bold">Service Provider</span>")</small>-->
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 fw-bold">&</div>
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
                                <small>(collectively referred to as the "<span class="fw-bold">Parties</span>").</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="break">
        <div class="mt-5 break">
                <h5 class="mb-2">1. Term</h5>
                <p class="mb-4">1.1 The term of this Agreement shall span an agreed minimum of  <?php echo $data["contractPeriod"]; ?>  months.
                    The client understands that the contract will continue past the end date by month to month at the same agreed price. 
                    The Agreement for subsequent terms of 0,3 or 12 months can be agreed upon by mutual concurrence and the advertised price
                     will be charged unless otherwise agreed to.
                </p>

                <h5 class="mb-2">2. Services</h5>
                <p class="mb-4">2.1 The Service Provider undertakes to furnish marketing services to the client throughout the duration of this Agreement.
                     The specifics of the marketing services and strategies will be determined through mutual agreement and may evolve over time. However,
                      the client is obligated to consistently engage in some form of marketing services provided by the Service Provider.
                </p>
                <p>2.2 The Client has the option to pause the marketing plan for a limited period, subject to the following conditions:</p>
                <ol type="A">
                    <li>For clients on a 3-month plan, the Client can pause the marketing plan for a maximum of 1 month during the term of this Agreement.</li>
                    <li>For clients on a 12-month plan, the Client can pause the marketing plan for a maximum of 2 months. The pauses can be taken for any 2 
                        non-consecutive months within the term of this Agreement.</li>
                    <li>The length of the Agreement will be extended by the number of months paused. The new end date will be determined based on the 
                        total term of the Agreement plus any months paused.</li>
                </ol>

                <h5 class="mb-2">3. Payment</h5>
                <p class="mb-4">3.1 The client commits to remit monthly payments to the Service Provider for the entire term of this Agreement.
                     The monthly fee is as determined by the agreed marketing plan. Payment for each month is due monthly from the campaign start 
                     date agreed upon by both parties. Payments made more than 10 days in arrears will attract late fees equal to 10% of the monthly payment.
                      In the event of non-payment, the Service Provider reserves the right to refer the outstanding amount to a credit collector or third-party 
                      agency for collection. The client shall bear any costs or fees associated with engaging a credit collector
                </p>
            <br>
            <p class="text-center pageNumber">
                Local for you agreement : 01 of 03
            </p>
        </div>
        <br><br>
        <div class="mt-5">
            <h5 class="mb-2">4. Termination</h5>
            <p class="mb-4">4.1 Either party may terminate this Agreement if it is proven that the promised services were not achieved,
                written notice is required to the other party for consideration of early termination.
            </p>
            <p class="mb-4">4.2 If the client terminates the Agreement before the contract maturity date, the Client is obligated to pay 
                the Service Provider the remaining contract value, including any applicable late fees.
            </p>
            <p class="mb-4">4.3 Additionally, in the event of early termination by the Client, an early termination fee shall apply. 
                The early termination fee is calculated as follows:
            </p>
                <ol type="A">
                    <li>The remaining months in the contract are calculated as the difference between the total agreed term and the elapsed months at the time of termination.</li>
                    <li>The early termination fee is equal to 50% of the monthly fee multiplied by the remaining months.</li>
                    <li>As an example, using a monthly fee of $199 and 7 months remaining:
                        Early Termination Fee = (50% of $199) * 7 = $696.50</li>
                </ol>
            <p class="mb-4">4.4 The early termination fee shall be payable within 7 days of the termination date.</p>
            <p class="mb-4">4.5 The Service Provider reserves the right to deduct the early termination fee from any 
                outstanding payments or seek legal action to recover the amount.
            </p>
            
            <h5 class="mb-2">5. Obligations</h5>
            <p class="mb-4">5.1 The Service Provider pledges to deliver marketing services in a punctual and professional manner, ensuring compliance with minimum industry standards.
                 Failure to do so may result in the termination of this Agreement.  See services provided in Schedule 1.
            </p>

            <h5 class="mb-2">6. Confidentiality</h5>
            <p class="mb-4">6.1 Both Parties commit to keeping any confidential information disclosed during the course of this Agreement confidential 
                and not disclosing it to any third party without prior written consent.
            </p>

            <h5 class="mb-2">7. Governing Law and Jurisdiction</h5>
            <p class="mb-4">7.1 This Agreement shall be governed by and construed in accordance with the laws of Australia.
                 Any disputes arising out of or in connection with this Agreement shall be subject to the exclusive jurisdiction 
                 of the courts of Australia.
            </p>

            <h5 class="mb-2">8. Entire Agreement</h5>
            <p class="mb-4">8.1 This Agreement constitutes the entire understanding between the parties, superseding all prior agreements, whether written or oral, related to the subject matter herein.
            </p>
        </div>
    </section>

    <section class="my-5 break">
        <div class="row">
            <div class="col mt-5">
                    <h3 class="mb-5">Signatures</h3>
                    <p class="fw-bold">
                        By signing below, the Parties acknowledge and agree to the terms and conditions of this Marketing
                        Services Agreement.
                    </p>
            </div>
        </div>
        <div class="my-3">
            <div class="d-flex justify-content-evenly align-content-center">
                <div class="card" style="width: 47%;">
                    <div class="card-body">
                        <div>
                            <span class="fw-bold">Shop Name:</span>
                            <?php echo $data["ShopName"]; ?>
                        </div>
                        <div>
                            <span class="fw-bold">Client Signature:</span>
                            <img src="blank-80.jpg" height="80" alt="">
                        </div>
                        <div class="col">
                            <span class="fw-bold">Printed Name:</span>
                            <?php echo $data["customerFullName"]; ?>
                        </div>
                        <div>&nbsp;</div>
                        <div class="col">
                            <span class="fw-bold">Date:</span>
                            <img src="blank-30.jpg" height="30" alt="">
                        </div>

                    </div>
                </div>


                <div class="card" style="width: 47%;">
                    <div class="card-body">
                        <div>
                            <span class="fw-bold">Service Provider:</span>
                            <?php echo $companyName; ?>
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
        <br><br><br><br><br><br><br><br>
        <p class="text-center pageNumber">
            Local for you agreement : 02 of 03
        </p>
    </section>


    <section class="mt-5">
        <br><br>
        <div class="row">
            <div class="col">
                <div>
                    <h4 class="mb-5">Schedule 1: Summary of Services Provided by Local For You 2024</h4>
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Service Package</th>
                            <th scope="col">Social Media Marketing</th>
                            <th scope="col">Direct Marketing</th>
                            <th scope="col">Mega Marketing</th>
                            <th scope="col">Social Media Posting</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">Objective</th>
                            <td>
                            Attract local customers and enhance brand reliability
                             through local advertising.  Send our local promotions
                            </td>
                            <td>
                            Increase customer retention, direct communication, and loyalty building.
                            </td>
                            <td>
                            Comprehensive marketing strategy with a focus on reviews and online ads.
                            </td>
                            <td>
                            To engage with social channels and encourage brand presence
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Package Highlights</th>
                            <td>
                                <ol>
                                    <li>Management of Facebook, Google and <br>Yelp Ads and or other paid ad sites as needed</li>
                                    <li>Google Review Management</li>
                                    <li>Google My Business Posts (2x/ month)</li>
                                    <li>Monitor and correct Digital Footprint Management</li>
                                    <li>Sales Reports.</li>
                                    <li>Personally assigned account Manager<br>to work with directly on store success campaigns</li>
                                </ol>
                            </td>
                            <td>
                                <ol>
                                    <li>Managed Email Marketing Campaigns sent Weekly</li>
                                    <li>Managed SMS Marketing Weekly campaigns or as deemed by Customer (Extra per SMS).</li>
                                    <li>Unlimited Promotions addon</li>
                                    <li>Unlocked Loyalty Programs.</li>
                                    <li>Personally assigned account Manager<br>to work with directly on store success campaigns</li>
                                </ol>
                            </td>
                            <td>
                                <ol>
                                    <li>Same benefits as Social Media & Direct Marketing</li>
                                    <li>Campaign Sync across all platforms in the inside ordering system.</li>
                                    <li>Social Media Posting (4x/month).</li>
                                    <li>Google My Business Posts (4x/month)</li>
                                    <li>Personally assigned Account Manager<br>to work with directly on store success campaigns</li>
                                </ol>
                            </td>
                            <td>
                            2 x posting per month on this package.  Content can be promo or content created<br>and relevance to the Audience selected
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Extra Chrages <br>Ads Budget</th>
                            <td>TBA <br>- Minimum is $200</td>
                            <td>- SMS <br>- at current Rate</td>
                            <td>TBA <br>- Minimum $200 </td>
                            <td>None</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                <p class="text-center pageNumber">
                    Local for you agreement : 03 of 03
                </p>
            </div>
        </div>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

</body>
</html>

<?php
function cutColon($param){
    $temp = explode(":",$param);
    $arr = array_reverse($temp);
    return trim($arr[0]);
}
?>