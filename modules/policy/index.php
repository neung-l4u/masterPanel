<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $nub = '1.3.0';
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Website</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

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

        .redtext {
            color: red;
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
                margin-top: 0;
                margin-bottom: 5rem;
            }

            body {
                padding-top: 72px;
                padding-bottom: 72px;
            }

            #logol4u {
                width: 13%;
                height: auto;
            }

        }
    </style>
</head>

<body>
    <div class="container pt-5 mb-5">

        <div id="lastUpdate_en">
            <p class="text-right">
                <small class="text-muted">
                    version <?php echo $nub; ?> | Last update :
                    <?php
                    $updateDate = "2024-02-04";
                    $date = date_create($updateDate);
                    echo date_format($date, "l, d  F  Y");
                    ?>
                </small>
            </p>
        </div>
        <div id="lastUpdate_th" class="nono">
            <p class="text-right">
                <small class="text-muted">
                    เวอร์ชั่น <?php echo $nub; ?>

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


                    $strDate = "2024-02-04";
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

        <div class="d-flex justify-content-center" style="margin-top: 5rem;">
            <img src="pic/l4ulogo.png" alt="" id="logol4u">
        </div>


        <div style="margin-top: 5rem;">


            <div id="th" class="nono card">
                <div class="card-header">
                    <h5 class="font-weight-bold">ข้อกำหนดในการทำเว็บไซต์</h5>
                </div>
                <div class="card-body">
                    <div class="card-text">
                        <ol>
                            <div class="card-text mt-2 mb-3">
                                Local For You สามารถตอบสนองความต้องการในการสร้างเว็บไซต์ของคุณได้ โดยจะมีแนวทางและข้อกำหนดต่อไปนี้ เพื่อให้การทำงานราบรื่นและได้ผลลัพธ์ที่น่าพึงพอใจ การใช้บริการเว็บไซต์ของเราแสดงว่าคุณตกลงที่จะปฏิบัติตามข้อกำหนดเหล่านี้:
                            </div>
                            <li class="font-weight-bold">รูปแบบเค้าโครงเว็บไซต์ (เลย์เอ้า)</li>
                            <ol type="a">
                                <li>เค้าโครงเว็บไซต์จะยึดตามเทมเพลตที่ Local For You จัดเตรียมไว้ให้เท่านั้น</li>
                            </ol>
                            <li class="font-weight-bold">บริการปรับแต่ง</li>
                            <ol type="a">
                                <li>
                                    คำขอรูปแบบอื่นนอกเหนือจากเทมเพลตที่มีอยู่จะต้องเสียค่าธรรมเนียมเพิ่มเติม
                                </li>
                            </ol>
                            <li class="font-weight-bold">ระยะเวลา</li>
                            <ol type="a">
                                <li>กระบวนการสร้างเว็บไซต์จะใช้เวลาประมาณ 7 วันทำการ</li>
                                <li>วันที่ 1 เริ่มต้นเมื่อได้รับข้อมูลที่จำเป็นทั้งหมดจากลูกค้า</li>
                            </ol>
                            <li class="font-weight-bold">ข้อมูลที่จำเป็น:</li>
                            <ol type="a">
                                <li>ต้องจัดเตรียมสิ่งต่อไปนี้ก่อนที่จะเริ่มการสร้างเว็บไซต์:
                                    <ol type="i">
                                        <li>ชื่อร้าน</li>
                                        <li>สี อารมณ์ และโทนสีที่ต้องการ</li>
                                        <li>โลโก้ของร้านค้า (ควรใช้เวอร์ชันไฟล์ Raw เช่น .ai, .pdf)</li>
                                        <li>รูปภาพต่างๆ ของร้าน (ทิวทัศน์ อาหาร ฯลฯ)</li>
                                        <li>ข้อมูลเมนู <a href="example.php" target="_blank">ตัวอย่าง</a></li>
                                        <li>เวลาทำการ</li>
                                        <li>ที่อยู่และวิธีการติดต่อ</li>
                                        <li>URL โซเชียลมีเดีย, GMB ฯลฯ</li>
                                        <li>การตั้งค่าสำหรับระบบการสั่งซื้อออนไลน์หรือระบบการจอง</li>
                                    </ol>
                                </li>
                            </ol>
                            <li class="font-weight-bold">ส่งตรวจฉบับร่าง (ดราฟ)</li>
                            <ol type="a">
                                <li>ฉบับร่างของเว็บไซต์จะถูกส่งไปให้เจ้าของร้านตรวจสอบก่อนเผยแพร่จริง</li>
                                <li>อนุญาตให้ปรับรูปแบบ เช่น การเปลี่ยนสี ได้สูงสุด 3 ครั้ง</li>
                            </ol>
                            <li class="font-weight-bold">การปรับปรุงและแก้ไข</li>
                            <ol type="a">
                                <li>การปรับเปลี่ยนที่ไม่ส่งผลต่อเค้าโครงเว็บไซต์ (เช่น การอัปเดตเนื้อหา แก้ไขคำผิด) สามารถทำได้ไม่จำกัด</li>
                                <li>อนุญาตให้แก้ไขเค้าโครงเว็บไซต์ได้ภายใน 14 วันหลังจากเว็บไซต์เผยแพร่ โดยจำกัดเฉพาะข้อกำหนดที่กล่าวไว้ข้างต้น</li>
                                <li>คำขอที่นอกเหนือจากที่ระบุอาจต้องเสียค่าธรรมเนียมเพิ่มเติม</li>
                            </ol>
                            <li class="font-weight-bold">เวลาตอบสนองที่เหมาะสม</li>
                            <ol type="a">
                                <li>เจ้าของร้านค้าควรตอบกลับภายใน 7 วันทำการเมื่อส่งเว็บไซต์เพื่อรับบทวิจารณ์ฉบับร่าง</li>
                                <li>การไม่ตอบกลับภายในกรอบเวลานี้จะถือเป็นการยืนยันว่า คิวการสร้างเว็บไซต์ของท่านจะถูกย้ายไปท้ายคิว แลคิวอื่นๆ จะเลื่อนขึ้นมาแทน</li>
                            </ol>
                            <li class="font-weight-bold">การเปลี่ยนแปลงหลังการใช้งานจริง</li>
                            <ol type="a">
                                <li>หลังจากที่เว็บไซต์เผยแพร่แล้ว คุณสามารถขอแก้ไขการสร้างและการสร้างแบรนด์เพิ่มเติมได้ภายใน 14 วัน โดยเป็นไปตามข้อจำกัดเทมเพลตที่ระบุ</li>
                                <li>การแก้ไขอื่นๆ ทั้งหมด เช่น รูปภาพและการอัปเดตจะอยู่ในคิวงาน และจะดำเนินการตามเงื่อนไขการให้บริการมาตรฐานของเรา</li>
                                <li>คำขอแก้ไขเว็บไซต์สามารถทำได้ผ่านผู้จัดการบัญชีของคุณหรือฝ่ายสนับสนุนลูกค้า Local For You</li>
                            </ol>
                            <li class="font-weight-bold">โดเมน</li>
                            <ol type="a">
                                <li>การเปิดใช้งาน
                                    <ol type="i">
                                        <li>ฟังก์ชั่นเต็มรูปแบบของเว็บไซต์จะใช้งานได้เมื่อมีการเผยแพร่ชื่อโดเมน ภายในเวลาสูงสุด 48 ชั่วโมง</li>
                                        <li>การโอนโดเมนจากผู้ให้บริการรายอื่นไปยังเซิร์ฟเวอร์ Local For You อาจใช้เวลาสูงสุด 7 วันทำการ</li>
                                    </ol>
                                </li>
                                <li>ผลประโยชน์การโอน:
                                    <ol type="i">
                                        <li>คุณสามารถโอนย้ายโดเมนของคุณไปยัง Local For You ได้ ข้อดี ได้แก่ ความช่วยเหลือทันทีเกี่ยวกับการต่ออายุโดเมน การแก้ไขการตั้งค่า ใบรับรองความปลอดภัย และบริการ SSL</li>
                                    </ol>
                                </li>
                            </ol>
                            <div class="card-text mt-3">
                                ในการดำเนินการบริการสร้างเว็บไซต์ของเรา คุณรับทราบและยอมรับข้อกำหนดและเงื่อนไขเหล่านี้ เราหวังเป็นอย่างยิ่งว่าจะได้สร้างตัวตนบนโลกออนไลน์ที่มีเอกลักษณ์และปรับแต่งมาโดยเฉพาะสำหรับธุรกิจของคุณ
                            </div>
                        </ol>

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
                            <div class="card-text mt-2 mb-3">
                            Local For You can provide your website creation needs. The following guidelines and terms are set to ensure a smooth process and satisfactory results. By engaging in our Website services, you agree to abide by these terms:                            </div>
                            <li class="font-weight-bold">Website Layout</li>
                            <ol type="a">
                                <li>The layout of the website will strictly adhere to the templates provided by Local For You as presented at time of engagement.</li>
                            </ol>
                            <li class="font-weight-bold">Customization Services</li>
                            <ol type="a">
                                <li>
                                Any request for a format other than the existing template will incur an additional fee.
                                </li>
                            </ol>
                            <li class="font-weight-bold">Timeline</li>
                            <ol type="a">
                                <li>The website creation process will take approximately 7 business days.</li>
                                <li>Day 1 starts upon receiving all necessary information from the customer.</li>
                            </ol>
                            <li class="font-weight-bold">Required Information:</li>
                            <ol type="a">
                                <li>The following must be provided before website creation can commence:
                                    <ol type="i">
                                        <li>Shop name</li>
                                        <li>Desired color, mood & tone</li>
                                        <li>Logo of the store (raw file version preferred, e.g., .ai, .pdf)</li>
                                        <li>Various pictures of the store (landscape, food, etc.)</li>
                                        <li>Menu information, service <a href="example.php" target="_blank">Examples</a></li>
                                        <li>Opening hours</li>
                                        <li>Address and contact methods</li>
                                        <li>Social media URLs, GMB, etc.</li>
                                        <li>Preference for online ordering system or booking system</li>
                                    </ol>
                                </li>
                            </ol>
                            <li class="font-weight-bold">Draft Reviews</li>
                            <ol type="a">
                                <li>A draft of the website will be sent for review before going live.</li>
                                <li>Format adjustments, such as changing colors, are allowed up to 3 times.</li>
                            </ol>
                            <li class="font-weight-bold">Adjustments and Amendments</li>
                            <ol type="a">
                                <li>Adjustments not affecting the website layout (e.g., updating content, correcting typos) are unlimited.</li>
                                <li>Amendments to the website layout are allowed within 14 days after the website is live, limited to the terms mentioned above.</li>
                                <li>Requests exceeding the specified limitation may incur additional fees.</li>
                            </ol>
                            <li class="font-weight-bold">Reasonable Response Time</li>
                            <ol type="a">
                                <li>The store owner should respond within 7 business days when submitting the website for draft reviews.</li>
                                <li>Failure to respond within this timeframe will be considered confirmation for the website build will go to the back of the build que and other builds will take preference.</li>
                            </ol>
                            <li class="font-weight-bold">Post-Live Changes</li>
                            <ol type="a">
                                <li>After the website is live, additional build and branding amendments can be requested within 14 days, respecting the specified Template limitations.  </li>
                                <li>All other edits such as images and updates will be put in a service que and will be done as per our standard terms of service</li>
                                <li>Requests for website modifications can be made through your account manager or Local For You customer support</li>
                            </ol>
                            <li class="font-weight-bold">Domains</li>
                            <ol type="a">
                                <li>Activation
                                    <ol type="i">
                                        <li>The website's full functionality will be active when the domain name is live, within a maximum of 48 hours.</li>
                                        <li>Transferring a domain from another provider to Local For You servers can take up to  7 business days.</li>
                                    </ol>
                                </li>
                                <li>Transfer Benefits:
                                    <ol type="i">
                                        <li>You can transfer your domain to Local For You, advantages include immediate assistance with domain renewal, modifying settings, security certificate and SSL services.</li>
                                    </ol>
                                </li>
                            </ol>
                            <div class="card-text mt-3">
                            By proceeding with our website creation services, you acknowledge and agree to these terms and conditions. We look forward to creating a unique and tailored online presence for your business.
                            </div>
                        </ol>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <div class="footer-copyright text-center py-3">
            © 2024 Copyright:
            <a href="https://localforyou.com/">
                Local For You Co., Ltd.
            </a>
        </div>
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