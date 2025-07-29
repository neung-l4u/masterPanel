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
    <?php
    $nub = '1.4.0';
    $lastUpdate = '16/07/2025';
    $effectiveDate = '01/08/2025';
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Website</title>
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
    <script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
    <script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
    <style>
        p {
            font-family: Arial, Helvetica, sans-serif;
        }

        h1 {
            font-family: Arial, Helvetica, sans-serif;
        }

        li {
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 0.7rem;
        }

        #btn_en {
            font-family: Arial, Helvetica, sans-serif;
        }

        #btn_th {
            font-family: Arial, Helvetica, sans-serif;
        }

        .nono {
            display: none;
        }

        footer {
            position: fixed;
            height: 50px;
            background-color: white;
            bottom: 0px;
            left: 0px;
            right: 0px;
            margin-bottom: 0px;
        }

        #logol4u {
            width: 10%;
            height: auto;
        }

        body {
            margin-bottom: 50px;
        }

        #en,#th {
            border: none !important;
        }


        @media print {
            .no-print,
            .no-print * {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 20mm 15mm 20mm 15mm;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                font-size: 12pt;
            }

            #logol4u {
                width: 10%;
            }

            #headtopic2 {
                color: #000000 !important;
            }


            #headtopic {
                color: #000000 !important;
            }

            .card-header {
                break-after: avoid;
            }

            li {
                margin-bottom: 0.5rem;
                break-inside: avoid;
            }

            h1, h2, h3, p {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }

    </style>
</head>

<body>
    <div class="container pt-5 mb-5">

        <div id="lastUpdate_en">
            <p class="text-right">
                <small class="text-muted no-print">
                    Document Version: <?php echo $nub; ?> <br>
                    Last Updated: <?php echo $lastUpdate; ?><br>
                    Effective Date: <?php echo $effectiveDate; ?>




                    <?php
//                    $updateDate = "2025-07-15";
//                    $date = date_create($updateDate);
//                    echo date_format($date, "l, d  F  Y");
                    ?>
                </small>
            </p>
        </div>
        <div id="lastUpdate_th" class="nono">
            <p class="text-right">
                <small class="text-muted no-print">
                    เวอร์ชั่นเอกสาร: <?php echo $nub; ?><br>
                    วันที่ปรับปรุงเอกสาร: <?php echo $lastUpdate; ?><br>
                    วันที่มีผลบังคับใช้: <?php echo $effectiveDate; ?>

                    <?php
/*                    function DateThai($strDate)
                    {
                        $strYear = date("Y", strtotime($strDate)) + 543;
                        $strMonth = date("n", strtotime($strDate));
                        $strDay = date("j", strtotime($strDate));
                        $strHour = date("H", strtotime($strDate));
                        $strMinute = date("i", strtotime($strDate));
                        $strSeconds = date("s", strtotime($strDate));
                        $strMonthCut = array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
                        // $strDayCut = Array("","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์","อาทิตย์");

                        $strMonthThai = $strMonthCut[$strMonth];
                        // $strDayThai=$strDayCut[$strDay];

                        return "$strDay $strMonthThai $strYear";
                    }

                    function DayThai($dayThai)
                    {

                        //$strDays = date("j",strtotime($dayThai));
                        $strDayCut = array("อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์");

                        $strDayThai = $strDayCut[$dayThai];

                        return "$strDayThai";
                    }


                    $strDate = "2025-07-15";
                    //$strDate2 = "2024-01-12";
                    $dateObj = date_create($strDate);
                    //echo "<h1>".date_format($date,"w")."</h1>";

                    $dayThai = date_format($dateObj, "w");



                    //echo "<h1>ttttt".date_format($strDate,"Y/m/d H:i:s")."</h1>";

                    echo "อัพเดทล่าสุด :" . DayThai($dayThai) . ' '  . DateThai($strDate);
                    */?>
                    <!-- ศุกร์, 12 มกราคม 2567 -->
                </small>
            </p>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between no-print">
                <button id="btn_en" class="btn btn-primary nono" onclick="switchContent();">English</button>
                <button id="btn_th" class="btn btn-primary" onclick="switchContent();">ไทย</button>
                <button id="btn_print" class="btn btn-info" onclick="window.print()">Print</button>
            </div>
        </div>

        <div class="d-flex justify-content-center" style="margin-top: 1rem;">
            <img src="../pic/l4ulogo.png" alt="" id="logol4u">
        </div>


        <div id="boxAllContent" style="margin-top: 2rem;">
            <div id="th" class="nono">

                <div class="card">
                    <div class="card-header bg-primary text-white" >
                        <h5 class="font-weight-bold" id="headtopic"><b>1. ข้อมูลที่ต้องจัดเตรียม</b></h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bolder"><b>ชื่อร้าน</b></li>
                                <li class="font-weight-bold"><b>โทนสีและอารมณ์ของเว็บไซต์ที่ต้องการ</b></li>
                                <li class="font-weight-bold"><b>โลโก้ร้าน (ถ้ามีไฟล์ต้นฉบับ เช่น .ai, .pdf จะดีที่สุด)</b></li>
                                <li class="font-weight-bold"><b>รูปภาพร้าน เช่น ภาพหน้าร้าน อาหาร ฯลฯ</b></li>
                                <li class="font-weight-bold"><b>ข้อมูลเมนูหรือบริการ</b></li>
                                <li class="font-weight-bold"><b>วัน-เวลาเปิดทำการ</b></li>
                                <li class="font-weight-bold"><b>ที่อยู่และช่องทางติดต่อ</b></li>
                                <li class="font-weight-bold"><b>ลิงก์โซเชียลมีเดีย, Google My Business ฯลฯ</b></li>
                                <li class="font-weight-bold"><b>ระบุว่าต้องการใช้ระบบสั่งอาหารออนไลน์หรือระบบจองคิวนัดหมายในเว็บไซต์หรือไม่</b></li>
                                <li class="font-weight-bold"><b>สำหรับเว็บไซต์ Fully Customize กรุณาระบุตัวอย่างเว็บไซต์ที่ต้องการ (Reference) เพื่อใช้เป็นแนวทางในการออกแบบ</b></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">2. ขอบเขตบริการ</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">โครงร่างของเว็บไซต์จะเป็นไปตามเทมเพลตที่บริษัทจัดเตรียมไว้เท่านั้น</li>
                                <li class="font-weight-bold">หากต้องการรูปแบบที่นอกเหนือจากเทมเพลตที่มี จะถือเป็นบริการเพิ่มเติมที่ชื่อว่า Fully Customize ซึ่งมีค่าใช้จ่ายเพิ่มเติม $995</li>
                                <li class="font-weight-bold">เราจะใช้เวลาสร้างเว็บไซต์ประมาณ 10 วันทำการเพื่อส่งแบบร่างให้ตรวจ โดยจะเริ่มนับวันที่ 1 ถัดจากวันที่เราได้รับข้อมูลครบถ้วนจากคุณ และใช้ 15 วันทำการสำหรับเว็บไซต์ Fully Customize</li>
                                <li class="font-weight-bold">การเผยแพร่เว็บไซต์ (Live) จะดำเนินการหลังจากเจ้าของอนุมัติแบบร่างเรียบร้อยแล้ว</li>
                                <li class="font-weight-bold">เว็บไซต์ Fully Customize หมายถึงเว็บไซต์ที่ออกแบบหน้าตาได้ตามที่ต้องการ แต่ไม่ได้รวมถึงการพัฒนาระบบใหม่เพิ่มเติม เช่น ระบบขายของที่มีฟังก์ชั่นแบบ Lazada, Shopee</li>
                            </ul>
                        </div>
                    </div>
                </div>



                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">3. ขั้นตอนดำเนินงาน</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">เราจะจัดส่งเว็บไซต์ร่าง (Draft) ให้ตรวจสอบก่อนเผยแพร่จริง</li>
                                <li class="font-weight-bold">การปรับที่ไม่กระทบโครงร่าง เช่น แก้ข้อความ แก้คำผิด เปลี่ยนข้อมูลร้าน เปลี่ยนเวลาเปิด-ปิด เปลี่ยนราคา/รายละเอียดบริการ สามารถแจ้งได้ไม่จำกัดจำนวนครั้ง</li>
                                <li class="font-weight-bold">หากต้องการปรับรูปแบบที่กระทบโครงสร้างเนื้อหา เช่น เปลี่ยนโทนสีทั้งเว็บไซต์, ปรับสลับเลย์เอ้าท์ หรือการแก้ไขที่มากกว่า 20% สามารถแจ้งได้ไม่เกิน 3 ครั้ง</li>
                                <li class="font-weight-bold">หากมีการใช้งานตะกร้าสินค้าหรือ WooCommerce ในขั้นตอนตรวจแบบร่างนี้ เราอาจจะเพิ่มสินค้าทดลองให้เท่านั้น</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">4. การเผยแพร่เว็บไซต์</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">หลังส่งเว็บไซต์ร่างให้ตรวจสอบ เจ้าของร้านต้องตอบกลับภายใน 7 วันทำการ</li>
                                <li class="font-weight-bold">หากเกิน 7 วันโดยไม่มีการตอบกลับ จะถือว่าเจ้าของร้านได้ยืนยันแบบร่างแล้ว และระบบจะดำเนินการเผยแพร่เว็บไซต์โดยอัตโนมัติ</li>
                            </ul>
                            <h6 class="font-weight-bold">หลังเว็บไซต์ออนไลน์:</h6>
                            <ul>
                                <li class="font-weight-bold">หากต้องการปรับแก้เพิ่มเติม (ภายใต้เงื่อนไขที่กำหนดข้างต้น) สามารถแจ้งได้ภายใน 14 วัน</li>
                                <li class="font-weight-bold">การขอแก้ไขที่เกินขอบเขต อาจมีค่าใช้จ่ายเพิ่มเติม</li>
                                <li class="font-weight-bold">หลังเว็บไซต์ออนไลน์ ผู้จัดทำจะส่ง Username และ Password เพื่อให้เจ้าของสามารถเข้าจัดการเนื้อหาหรือดูข้อมูลได้ตามต้องการ</li>
                                <li class="font-weight-bold">หากต้องการติดตั้งปลั๊กอินเพิ่ม สามารถแจ้งผู้จัดทำได้</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">5. หมายเหตุเพิ่มเติม</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">หลังเว็บไซต์ออนไลน์แล้ว อาจมีช่วงเวลาการแพร่กระจายชื่อโดเมน (Domain Name Propagation) เพื่อการทำงานที่สมบูรณ์ ซึ่งจะใช้เวลาไม่เกิน 4 วัน</li>
                                <li class="font-weight-bold">หากต้องการย้ายโดเมนจากผู้ให้บริการอื่นมายัง Localforyou จะใช้เวลาประมาณ 7 วันทำการ</li>
                                <li class="font-weight-bold">ข้อดีของการย้ายโดเมนมาไว้กับ Localforyou เช่น การต่ออายุหรือแก้ไขค่าต่าง ๆ เราจะสามารถช่วยเหลือได้ทันทีเมื่อต้องการ</li>
                                <li class="font-weight-bold">ลูกค้าสามารถแจ้งขอแก้ไขเว็บไซต์ผ่านผู้ดูแลบัญชีของคุณ หรือฝ่ายสนับสนุนลูกค้า Localforyou ได้ตลอด 24 ชม.</li>
                                <li class="font-weight-bold">การทำงานบางส่วนเช่น ระบบแจ้งเตือนทางอีเมล, ระบบจ่ายเงินออนไลน์, ระบบบัตรกำนัล จำเป็นจะต้อง Live ก่อนเพื่อการทำงานที่สมบูรณ์</li>
                                <li class="font-weight-bold">ฟีเจอร์ตะกร้าสินค้าที่เรามีให้เป็นส่วนเสริมเท่านั้น จึงอาจมีข้อจำกัดด้านการปรับแต่งในบางกรณี</li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>


            <div id="en" class="card">

                <div class="card">
                    <div class="card-header bg-primary text-white" >
                        <h5 class="font-weight-bold" id="headtopic2"><b>1. Required Information for Website Creation</b></h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold"><b>Store name</b></li>
                                <li class="font-weight-bold"><b>Preferred color scheme, mood, and tone</b></li>
                                <li class="font-weight-bold"><b>Store logo (original file formats such as .ai or .pdf are highly preferred)</b></li>
                                <li class="font-weight-bold"><b>Store photos (e.g. storefront, food, interior, etc.)</b></li>
                                <li class="font-weight-bold"><b>Menu or service details</b></li>
                                <li class="font-weight-bold"><b>Business hours</b></li>
                                <li class="font-weight-bold"><b>Address and contact information</b></li>
                                <li class="font-weight-bold"><b>Social media URLs, Google My Business link, etc.</b></li>
                                <li class="font-weight-bold"><b>Specify whether you would like to include an Online Ordering System or Booking System on the website</b></li>
                                <li class="font-weight-bold"><b>For Fully Customize websites, please provide example websites (references) for design guidance</b></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">2. Scope of Service</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text px-3">
                            <p>
                                The website layout will follow the templates provided by the company only.<br><br>
                                If you require a design outside the available templates, it will be considered an additional service under the name <b>Fully Customize</b>, which incurs an extra fee of $995.<br><br>
                                The development time is approximately <b>10 business days</b> to deliver a draft for review, starting from next of the day we receive all required information from you.<br><br>
                                For <b>Fully Customize</b> websites, the development time is <b>15 business days.</b><br><br>
                                The website will go live only after the draft is approved by the client.<br><br>
                                “Fully Customize” means the visual layout can be custom-designed, but it <b>does not include new system development</b>, such as complex e-commerce functions like Lazada or Shopee.
                            </p>
                        </div>
                    </div>
                </div>



                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">3. Workflow & Revisions</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">We will deliver a <b>website draft</b> for your review before it is published.</li>
                                <li class="font-weight-bold">Revisions that <b>do not affect the layout</b>, such as text edits, typo fixes, business hour updates, or pricing/service changes, can be requested unlimited times.</li>
                                <li class="font-weight-bold">Revisions that <b>affect the layout</b>, such as a full color theme change, layout restructuring, or changes exceeding 20% of the content/design, are limited to <b>3 rounds.</b></li>
                                <li class="font-weight-bold">If using the shopping cart (WooCommerce), we may only insert sample/demo products during the draft phase.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">4. Website Launch</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">After the draft is sent for review, the client must respond <b>within 7 business days.</b></li>
                                <li class="font-weight-bold">If no response is received within 7 business days, the draft will be considered approved, and the website will be published automatically.</li>
                            </ul>
                            <h6 class="font-weight-bold">After the website is live:</h6>
                            <ul>
                                <li class="font-weight-bold">You can request further changes (within the original scope) <b>within 14 days</b></li>
                                <li class="font-weight-bold">Additional charges may apply for requests beyond the scope</li>
                                <li class="font-weight-bold">We will provide you with a <b>Username & Password</b> to access your site and manage content</li>
                                <li class="font-weight-bold">If you wish to install additional plugins, please inform us directly</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="font-weight-bold">5. Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                            <ul>
                                <li class="font-weight-bold">After going live, domain name propagation may take up to <b>4 days</b> for all services to function properly</li>
                                <li class="font-weight-bold">Transferring a domain to Localforyou from another provider may take approximately <b>7 business days</b></li>
                                <li class="font-weight-bold">Benefits of transferring your domain to Localforyou include faster support with domain renewals, DNS adjustments, and technical assistance</li>
                                <li class="font-weight-bold">You may request changes via your <b>Account Manager</b> or <b>L4U 24/7 Support Team</b></li>
                                <li class="font-weight-bold">Some features—such as email notifications, online payment systems, and voucher systems—require the website to be live in order to function properly</li>
                                <li class="font-weight-bold">The shopping cart system we provide is an <b>optional add-on</b> and may have limitations in terms of customization</li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>



        </div>


        <div class="no-print d-flex justify-content-around mt-4">
            <div><a href="https://localforyou.com/en/privacy-policy-en/" target="_blank">Privacy Policy</a></div>
            <div><a href="https://localforyou.com/en/terms-and-conditions-en/" target="_blank">Terms and Conditions</a></div>
        </div>
    </div>
    <footer class="no-print">
        <div class="footer-copyright text-center py-3">
            © 2024 Copyright:
            <a href="https://localforyou.com/">
                Local For You Co., Ltd.
            </a>
        </div>

    </footer>
    <script>
        function switchContent() {
            $('#page_header').removeClass('text-primary').addClass('text-danger');
            $('#th').toggle();
            $('#th2').toggle();
            $('#en').toggle();
            $('#en2').toggle();
            $('#btn_th').toggle();
            $('#btn_en').toggle();
            $('#lastUpdate_en').toggle();
            $('#lastUpdate_th').toggle();
        }
    </script>
</body>

</html>