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
    $lastupdate = '16/07/2025';
    $effectivedate = '01/08/2025';
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
        @media print {
            .no-print,
            .no-print * {
                display: none !important;
            }

            @page {
                margin: 0cm;
                size: A4 portrait;

            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
            }

            #logol4u {
                width: 10%; /* คงค่าไว้ที่ 10% สำหรับการพิมพ์ */
            }

            /* .card-body, ol, ul, li { break-inside: avoid; } ถูกคอมเมนต์ออกไป ซึ่งหมายความว่าองค์ประกอบเหล่านี้อาจถูกแบ่งคร่อมหน้าได้ */

            .card-header {
                break-after: avoid;
            }

            li {
                margin-bottom: 0.5rem;
                break-inside: avoid;
            }
        }

    </style>
</head>

<body>
    <div class="container pt-5 mb-5">

        <div id="lastUpdate_en">
            <p class="text-right">
                <small class="text-muted">
                    Document Version: <?php echo $nub; ?><br>
                    Last Updated: <?php echo $lastupdate; ?><br>
                    Effective Date : <?php echo $effectivedate; ?><br>

                    version <?php echo $nub; ?> | Last update :
                    <?php
                    $updateDate = "2025-07-15";
                    $date = date_create($updateDate);
                    echo date_format($date, "l, d  F  Y");
                    ?>
                </small>
            </p>
        </div>
        <div id="lastUpdate_th" class="nono">
            <p class="text-right">
                <small class="text-muted">
                    เวอร์ชั่นเอกสาร: <?php echo $nub; ?><br>
                    วันที่ปรับปรุงเอกสาร: <?php echo $lastupdate; ?><br>
                    วันที่มีผลบังคับใช้: <?php echo $effectivedate; ?><br>


                    <!--เวอร์ชั่น <?php echo $nub; ?>

                    <?php
                    function DateThai($strDate)
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
                    ?>
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

        <div class="d-flex justify-content-center" style="margin-top: 2rem;">
            <img src="../pic/l4ulogo.png" alt="" id="logol4u">
        </div>


        <div style="margin-top: 2rem;">
            <div id="th" class="nono card">
                <div class="card-header">
                    <h5 class="font-weight-bold">ข้อกำหนดในการทำเว็บไซต์</h5>
                </div>
                <div class="card-body">
                    <div class="card-text">
                        <ol>
                            <li class="font-weight-bold">เลย์เอ้าของเว็บไซต์จะมีตาม Template ที่เรากำหนดให้เท่านั้น</li>
                            <li class="font-weight-bold">หากต้องการรูปแบบอื่นนอกจากเทมเพลตที่มีอยู่ <span class="text-danger">จะเป็นอีก 1 บริการที่มีค่าใช้จ่ายเพิ่มเติม ($995)</span></li>
                            <li class="font-weight-bold">เว็บไซต์เทมเพลตจะใช้เวลาในการสร้างประมาณ 7 วันทำการ <span class="text-danger">กรณีเป็นฟูลลี่คัสตอมไมซ์จะใช้เวลาในการสร้างขั้นต่ำ 10 วันแล้วแต่ความยากง่าย</span></li>
                            <li class="font-weight-bold">จะสามารถเริ่มนับวันที่ 1 เมื่อเราได้รับข้อมูลที่จำเป็นครบถ้วนแล้วเท่านั้น</li>
                            <li class="font-weight-bold">ข้อมูลที่จำเป็นในการใช้ทำเว็บไซต์
                                <ol type="a">
                                    <li>ชื่อร้าน</li>
                                    <li>โทนสีที่ต้องการ</li>
                                    <li>โลโก้ของร้าน</li>
                                    <li>รูปภาพต่างๆของร้าน เช่น บรรยากาศร้าน, อาหาร, ฯลฯ ที่ต้องการให้ใส่ในเว็บไซต์</li>
                                    <li>ข้อมูลเมนู บริการ<a href="example.php" target="_blank">ตัวอย่าง</a></li>
                                    <li>เวลาทำการของร้าน</li>
                                    <li>ที่อยู่และช่องทางการติดต่อ</li>
                                    <li>Social media URL , GMB ฯลฯ</li>
                                    <li>ระบุว่าต้องการใช้ระบบ Online ordering, Booking System บนเว็บไซต์หรือไม่?</li>
                                    <ul>
                                        <li>เราจะส่งดราฟเว็บไซต์ให้ตรวจก่อนจะส่งขึ้น Live หากต้องการปรับรูปแบบ เช่น เปลี่ยนสี, ปรับเปลี่ยนรูปแบบคอนเทนต์ สามารถแก้ได้ไม่เกิน 3 ครั้ง</li>
                                    </ul>
                                </ol>
                            </li>

                            <li class="font-weight-bold">การแก้ไขที่ไม่ใช่การปรับเปลี่ยนรูปแบบเว็บ เช่น อัปเดตคอนเทนต์, แก้ไขคำผิด, แก้ไขข้อมูลร้าน, ปรับเปลี่ยนเวลาเปิด/ปิด, แก้ไขราคา/รายละเอียดบริการ สามารถทำได้ไม่จำกัดจำนวนครั้ง</li>
                            <li class="font-weight-bold">เมื่อส่งเว็บไซต์ให้ตรวจ เจ้าของร้านจำเป็นต้องตอบยืนยันภายใน 7 วันทำการ <span class="text-danger">** หากเกิน 7 วันแล้ว ไม่ได้ตอบกลับ จะถือว่าได้รับการยืนยันแล้วและส่งขึ้น Live ต่อไป</span>
                                <ol type="a">
                                    <li>หลังจากที่ไลฟ์เว็บไซต์แล้ว หากมีจุดที่ต้องการปรับเปลี่ยน เจ้าของยังสามารถขอแก้ไขเพิ่มเติมได้ (ถ้ายังไม่เกินจำนวนครั้งที่กำหนดข้างต้น) ภายในระยะเวลาไม่เกิน 14 วัน</li>
                                    <li><span class="text-danger">การแก้ไขเกินจำนวนครั้งที่กำหนดอาจมีค่าใช้จ่ายเพิ่มเติม</span></li>
                                </ol>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div id="th2" class="card mt-3 nono">
                <div class="card-header">
                    <h5 class="font-weight-bold">แจ้งให้ทราบ</h5>
                </div>
                <div class="card-body">
                    <div class="card-text">
                        <ul>
                            <li>เมื่อไลฟ์เว็บไซต์ จะต้องรอให้โดเมนเนมแอคทีฟ เพื่อให้บริการทุกส่วนสามารถใช้งานได้ครบถ้วน ภายในไม่เกิน 4 วัน</li>
                            <ul>
                                <li>หากต้องการย้ายโดเมนจากผู้ให้บริการเจ้าอื่นมาไว้ที่ Localforyou จะใช้เวลาในการย้าย 7 วันทำการ </li>
                                <li>ข้อดีของการย้ายโดเมนมาไว้ที่ Localforyou เช่น ต่ออายุโดเมน, ปรับเปลี่ยนการตั้งค่า ฯลฯ เราจะสามารถช่วยเหลือคุณได้ทันที</li>
                                <li>คุณสามารถแจ้งปรับเปลี่ยนแก้ไขเว็บไซต์ของท่านได้ที่ Account Manager ของคุณ หรือ ติดต่อ Customer Support ได้ตลอด 24 ชั่วโมง</li>
                            </ul>
                        </ul>
                    </div>
                </div>
            </div>


            <div id="en" class="card">
                <div class="card-header">
                    <h5 class="font-weight-bold">Terms and conditions for making a website</h5>
                </div>

                <div class="card-body">
                    <div class="card-text">
                        <ol>
                            <li class="font-weight-bold">The layout of the website will be according to the template that we provide only.</li>
                            <li class="font-weight-bold">If you want a format other than the existing template we provided, <span class="text-danger">it will be another service that costs an additional fee ($995).</span></li>
                            <li class="font-weight-bold">The website creation will take approximately 7 business days to complete.</li>
                            <li class="font-weight-bold">You will be able to start counting day 1 when we have received all necessary information from you.</li>
                            <li class="font-weight-bold">Require Information for website creation:
                                <ol type="a">
                                    <li>Shop name</li>
                                    <li>Desired color, mood & tone</li>
                                    <li>Logo of the store (if you have raw file version eg. .ai, .pdf is the best)</li>
                                    <li>Various pictures of your store such as the store landscape, food, etc.</li>
                                    <li>Menu information, service <a href="example.php" target="_blank">Examples</a></li>
                                    <li>Opening hours</li>
                                    <li>Address and contact methods</li>
                                    <li>Social media URLs, GMB, etc.</li>
                                    <li>Specify whether you want to use the Online ordering system, Booking System on the website or not?
                                        <ol type="i">
                                            <li>We will send a draft of the website for review before sending it live. If you want to adjust the format, such as changing colors, modifying the content format. (limit 3 times)</li>
                                            <li>For the adjustment that does not affect the website layout, such as updating content, correcting typos, modifying store information, modifying opening/closing hours, modifying prices/service details. You can request these changes unlimited times.</li>
                                            <li>When submitting a website for review, The store owner is required to respond within 7 business days.
                                                <span class="text-danger">** If it's been more than 7 days and you haven't responded. It will be considered confirmed and sent on Live.</span>
                                            </li>
                                            <li>After the website is live If you have anything that needs to be changed You can also request additional amendments. (if not over the limitation specified above) within a period not exceeding 14 days</li>
                                            <li><span class="text-danger">Requests for edits exceeding the limitation may incur additional fees.</span></li>
                                        </ol>
                                    </li>

                                </ol>
                            </li>

                        </ol>
                    </div>
                </div>
            </div>

            <div id="en2" class="card mt-3">
                <div class="card-header">
                    <h5 class="font-weight-bold">Notice</h5>
                </div>
                <div class="card-body">
                    <div class="card-text">
                        <ul>
                            <li>When the website is live It is necessary to wait for the domain name to be active for all services to be fully functional (no more than 4 days.)</li>
                            <li>If you want to transfer your domain from another provider to Localforyou, it will take 7 business days.</li>
                            <li>Advantages of transferring your domain to Localforyou e.g. domain renewal,modify settings, etc., we will be able to help you immediately when you need.</li>
                            <li>You can request website modifications with your account manager or L4U customer support (24 hrs)</li>
                        </ul>
                    </div>
                </div>
            </div>

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