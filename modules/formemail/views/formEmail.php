<?php
$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form fix email sign up error</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container p-3 my-5">
    <form action="#" method="POST">
        <div class="row mb-3">
            <div class="col">
                <h3>Signup Form Email</h3>
            </div>
        </div>

        <h5 class="text">Setup</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row mb-3">
                <div class="col-4 d-flex align-items-start justify-content-start">
                    <b>Mode: </b>
                    <div class="form-check form-check-inline ml-3">
                        <input class="form-check-input" type="radio" name="emailMode" id="emailModeProd" value="prod">
                        <label class="form-check-label" for="emailModeProd">
                            Production
                        </label>
                    </div>
                    <div class="form-check form-check-inline ml-3">
                        <input class="form-check-input" type="radio" name="emailMode" id="emailModeDev" value="test" checked>
                        <label class="form-check-label" for="emailModeDev">
                            Test
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="mb-3 row g-3">
                        <div class="col row align-items-center">
                            <label class="col-2" for="formDate" >Date</label>
                            <input type="date" class="form-control col-10" id="formDate" name="formDate" placeholder="dd/mm/yyyy" value="<?php echo $today; ?>" >
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <input type="text" class="form-control" id="formVersion" name="formVersion" value="2.10 UK+Promotion">
                </div>
                <div class="col-4">
                    <input type="text" class="form-control" id="leadSource" name="leadSource" value="Sign Up Form">
                </div>
            </div>
        </div>

        <h5>Customer</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row mb-3">
                <div class="col">
                    <label for="formFullName" class="control-label">Full Name</label>
                    <input type="text" class="form-control" id="formFullName" name="formFullName" value="-">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="formEmail">Email</label>
                        <input type="text" class="form-control" id="formEmail" name="formEmail" value="-">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formMobile">Mobile</label>
                        <input type="text" class="form-control" id="formMobile" name="formMobile" value="-">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="formBestTime">BestTime</label>
                        <input type="text" class="form-control" id="formBestTime" name="formBestTime" value="-">
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="formNote">Note</label>
                        <textarea class="form-control" id="formNote" name="formNote" row="3" value="-"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <h5>Shop</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="formCustomerType">Customer Type</label>
                        <select id="formCustomerType" class="form-control" name="formCustomerType">
                            <option value="Thai Massage">Thai Massage</option>
                            <option value="Thai Restaurant & Take aways">Thai Restaurant & Take aways</option>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formShopName">Shop Name</label>
                        <input type="text" class="form-control" id="formShopName" name="formShopName" value="-">
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="formAddress">Address</label>
                        <textarea class="form-control" id="formAddress" name="formAddress" rows="3" value="-"></textarea>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formCounty">Country</label>
                        <select id="formCounty" class="form-control" name="00N2v00000IyVqF">
                            <option value="-">Please select Country</option>
                            <option value="Australia">Australia</option>
                            <option value="Canada">Canada</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="Thailand">Thailand</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="dateStart">dateStart</label>

                        <input type="date" class="form-control" id="dateStart" name="dateStart" placeholder="dd/mm/yyyy" value="<?php echo $today; ?>" >
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="formstartprojectNote">Start Project Note</label>
                        <textarea class="form-control" id="formstartprojectNote" name="formstartprojectNote" row="3" value="-"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <h5>Product</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="MainProduct">Main Product</label>
                        <input type="text" class="form-control" id="MainProduct" name="MainProduct" value="-">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formContractPeriod">Contract Period</label>
                        <select id="formContractPeriod" class="form-control" name="formContractPeriod">
                            <option value="No Contract">No Contract</option>
                            <option value="3 Months">3 Months</option>
                            <option value="6 Months">6 Months</option>
                            <option value="12 Months">12 Months</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="formFirstTimePayment">First Time Payment</label>
                        <input type="text" class="form-control" id="formFirstTimePayment"
                               name="formFirstTimePayment" placeholder="199.00 AUD" value="-">
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="formCoupon">Coupon</label>
                        <select id="formCoupon" class="form-control" name="formCoupon">
                            <option value="-">No Coupon</option>
                            <option value="1TRIAL">1TRIAL</option>
                            <option value="FREEWEB">FREEWEB</option>
                            <option value="PARTNER96">PARTNER96</option>
                            <option value="PARTNER98">PARTNER98</option>
                            <option value="PARTNER195">PARTNER195</option>
                            <option value="PARTNER198">PARTNER198</option>
                            <option value="PARTNER246">PARTNER246</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-3">
                    <div class="form-group">
                        <label for="formPaymentMethod">Payment Method</label>
                        <select id="formPaymentMethod" class="form-control" name="formPaymentMethod">
                            <option value="Invoice">Invoice</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Direct Debit">Direct Debit</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <h5>Reference</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="formRefPerson">RefPerson</label>
                        <input type="text" class="form-control" id="formRefPerson" name="formRefPerson" value="-">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formRefShop">RefShop</label>
                        <input type="text" class="form-control" id="formRefShop" name="formRefShop" value="-">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="formRefPartner">RefPartner</label>
                        <input type="text" class="form-control" id="formRefPartner" name="formRefPartner" value="-">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="formSales">Sales</label>
                        <select id="formSales"  name="formSales" class="form-control">
                            <option value="">--None--</option>
                            <option value="Boom Piyakorn">Boom Piyakorn</option>
                            <option value="Ball Anirut">Ball Anirut</option>
                            <option value="Gun Orana">Gun Orana</option>
                            <option value="Honey Tummaput">Honey Tummaput</option>
                            <option value="Lani Kunlanit">Lani Kunlanit</option>
                            <option value="Nan Chompunuch">Nan Chompunuch</option>
                            <option value="Naya Sanewong">Naya Sanewong</option>
                            <option value="Pluem Pluemkamol">Pluem Pluemkamol</option>
                            <option value="Pruek Patipatsinlapakit">Pruek Patipatsinlapakit</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>


        <h5>POS</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group">
                        <label for="formPOSBrand">POS Brand</label>
                        <input type="text" class="form-control" id="formPOSBrand" name="formPOSBrand" value="-">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formEndContract">End Contract</label>
                        <input type="text" class="form-control" id="formEndContract" name="formEndContract" value="-">
                    </div>
                </div>
            </div>
        </div>




        <h5>Add-Ons</h5>
        <div class="border rounded p-3 mb-5">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="formFlyer">Flyer</label>
                        <input type="text" class="form-control" id="formFlyer" name="formFlyer" value="Do not need">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formDineIn">DineIn</label>
                        <input type="text" class="form-control" id="formDineIn" name="formDineIn" value="Do not need">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="formMagnet">Magnet</label>
                        <input type="text" class="form-control" id="formMagnet" name="formMagnet" value="Do not need">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formSocialMedia">SocialMedia</label>
                        <input type="text" class="form-control" id="formSocialMedia" name="formSocialMedia" value="Do not need">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="formMenuDesign">MenuDesign</label>
                        <input type="text" class="form-control" id="formMenuDesign" name="formMenuDesign" value="Do not need">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formADVPromo">ADVPromo</label>
                        <input type="text" class="form-control" id="formADVPromo" name="formADVPromo" value="Do not need">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="formWebHosting">formWebHosting</label>
                        <input type="text" class="form-control" id="formWebHosting" name="formWebHosting" value="Do not need">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="formInfluencer">formInfluencer</label>
                        <input type="text" class="form-control" id="formInfluencer" name="formInfluencer" value="Do not need">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="formWebHosting">formWebsiteMakeOver</label>
                        <input type="text" class="form-control" id="formWebsiteMakeOver" name="formWebsiteMakeOver" value="Do not need">
                    </div>
                </div>
                
            </div>
        </div>
        <div class="row">
            <div class="col text-right">
                <button type="button" id="cmdSubmit" class="btn btn-primary mb-2">Submit</button>
            </div>
        </div>
    </form>


<script src="../assets/js/jquery3.5.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
    <script>
        let payload = {};

        $('#cmdSubmit').on('click', function () {
            // อ่านค่าจาก input, select, textarea ทั้งหมดในฟอร์ม
            $('form').find('input, select, textarea').each(function () {
                let name = $(this).attr('name');
                let value = $(this).val();

                // ตรวจสอบว่ามี name ไหม (ป้องกันค่า undefined)
                if (name) {
                    // เช็คว่าเป็น radio และถูกเลือกหรือไม่
                    if ($(this).is(':radio')) {
                        if ($(this).is(':checked')) {
                            payload[name] = value;
                        }
                    }
                    // เช็คว่าเป็น checkbox หรือไม่
                    else if ($(this).is(':checkbox')) {
                        payload[name] = $(this).is(':checked');
                    }
                    // ค่าอื่นๆ (text, select, textarea)
                    else {
                        payload[name] = value;
                    }
                }
            });


            // แสดง payload ใน console
            console.log(payload);

            ///////////


            let ans = confirm("send to webhook?");

            if(ans){
            callWebhook();
            }else{ console.log("no no no"); }
        
        });


        function callWebhook() {

            console.log("we call webhook");
            const callAjax = $.ajax({
                type: "POST",
                crossDomain: true,
                dataType: 'json',
                url: "https://hook.us1.make.com/hq8utfr6poa1sl1nie4fq8taplke1orz",
                data: payload

            });
            callAjax.done(function (res) {
                console.log("done");
            });
        }//saveToDB


    </script>
</body>
</html>