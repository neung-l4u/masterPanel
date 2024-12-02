<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $nub = '1.2.0';
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
                    $updateDate = "2024-01-12";
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
		$strYear = date("Y",strtotime($strDate))+543;
		$strMonth= date("n",strtotime($strDate));
		$strDay= date("j",strtotime($strDate));
		$strHour= date("H",strtotime($strDate));
		$strMinute= date("i",strtotime($strDate));
		$strSeconds= date("s",strtotime($strDate));
		$strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
        // $strDayCut = Array("","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์","อาทิตย์");
        
		$strMonthThai=$strMonthCut[$strMonth];
        // $strDayThai=$strDayCut[$strDay];

		return "$strDay $strMonthThai $strYear";
	}
    
    function DayThai($dayThai){

    //$strDays = date("j",strtotime($dayThai));
    $strDayCut = Array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");

    $strDayThai=$strDayCut[$dayThai];

    return "$strDayThai";
    }

   
    $strDate = "2024-01-12";
    //$strDate2 = "2024-01-12";
    $dateObj=date_create($strDate);
//echo "<h1>".date_format($date,"w")."</h1>";

$dayThai = date_format($dateObj,"w");

	
    
    //echo "<h1>ttttt".date_format($strDate,"Y/m/d H:i:s")."</h1>";

	echo "อัพเดทล่าสุด :" .DayThai($dayThai) .' '  .DateThai($strDate);
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
                    <ol>
                        <li>เลย์เอ้าของเว็บไซต์จะมีตาม Template ที่เรากำหนดให้เท่านั้น</li>
                        <li>หากต้องรูปแบบอื่นนอกจากเทมเพลตที่มีอยู่<span class="redtext">จะเป็นอีก 1 บริการที่มีค่าใช้จ่ายเพิ่มเติม ($995)</span></li>
                        <li>เว็บไซต์จะใช้เวลาในการสร้างประมาณ 7 วันทำการ</li>
                        <li>จะสามารถเริ่มนับวันที่ 1 เมื่อได้เราได้รับข้อมูลที่จำเป็นครบถ้วนแล้วเท่านั้น</li>
                        <li>ข้อมูลที่จำเป็นในการใช้ทำเว็บไซต์
                            <ul>
                                <li>ชื่อร้าน</li>
                                <li>โทนสีที่ต้องการ</li>
                                <li>โลโก้ของร้าน </li>
                                <li>รูปภาพต่างๆของร้าน เช่น บรรยากาศร้าน, อาหาร,​ ฯลฯ ที่ต้องการให้ใส่ในเว็บไซต์</li>
                                <li>ข้อมูลเมนู, บริการ <a href="example.php" target="_blank">ตัวอย่าง</a></li>
                                <li>เวลาทำการของร้าน</li>
                                <li>ที่อยู่และช่องทางการติดต่อ</li>
                                <li>Social media URL , GMB ฯลฯ</li>
                                <li>ระบุว่าต้องการใช้ระบบ Online ordering, Booking System บนเว็บไซต์หรือไม่?</li>
                                <ul>
                                    <li>เราจะส่งดราฟเว็บไซต์ให้ตรวจก่อนจะส่งขึ้น Live หากต้องการปรับรูปแบบ เช่น เปลี่ยนสี,​ ปรับเปลี่ยนรูปแบบคอนเทนต์ สามารถแก้ได้ไม่เกิน 3 ครั้ง</li>
                                </ul>
                            </ul>
                        </li>
                        <li>การแก้ไขที่ไม่ใช่การปรับเปลี่ยนรูปแบบเว็บ เช่น อัพเดทคอนเทน, แก้ไขคำผิด,​ แก้ไขข้อมูลร้าน, ปรับเปลี่ยนเวลาเปิด/ปิด, แก้ไขราคา/รายละเอียดบริการ สามารถทำได้ไม่จำกัดจำนวนครั้ง</li>
                        <li>เมื่อส่งเว็บไซต์ให้ตรวจ เจ้าของร้านจำเป็นต้องตอบยืนยันภายใน 7 วันทำการ<br>
                            <span class="redtext">** หากเกิน 7 วันแล้ว ไม่ได้ตอบกลับ จะถือว่าได้รับการยืนยันแล้วและส่งขึ้น Live ต่อไป</span>
                            <ul>
                                <li>หลังจากที่ไลฟ์เว็บไซต์แล้ว หากมีจุดที่ต้องการปรับเปลี่ยน เจ้าของยังสามารถขอแก้ไขเพิ่มเติมได้ (ถ้ายังไม่เกินจำนวนครั้งที่กำหนดข้างต้น) ภายในระยะเวลาไม่เกิน 14 วัน</li>
                                <li class="redtext">การแก้ไขเกินจำนวนครั้งที่กำหนดอาจมีค่าใช้จ่ายเพิ่มเติม</li>
                            </ul>
                        </li>
                        <!-- <li>เมื่อส่งเว็บไซต์ให้ตรวจ เจ้าของร้านจำเป็นต้องตอบยืนยันภายใน 7 วันทำการ </li>
                        <li>
                            <ul>
                                <li>โลโก้ของร้าน</li>
                                <li>รูปภาพ</li>
                                <li>ข้อมูลเมนู / บริการของทางร้าน <a href="example.php" target="_blank">ตัวอย่าง</a></li>
                            </ul>
                        </li>
                        <li>หลังจากที่ตัวเว็บไซต์เสร็จแล้วหากต้องการปรับดีไซน์หรือแก้ไขใดสามารถแก้ได้ 3 ครั้ง</li>
                        <li>เมื่อส่งเว็บไซต์ให้ตรวจแล้วเจ้าของร้านต้องตอบรับว่า คอนเฟิร์มหรือไม่ภายใน 7 วันหากเกิน 7 วันแล้วเจ้าของไม่ได้คอนเฟิร์มกลับมาจะถือว่าได้รับการยืนยันแล้ว</li>
                        <li>หลังจากที่ไลฟ์เว็บไซต์ไปแล้วเจ้าของมีระยะเวลา 14 วันในการขอปรับเปลี่ยนหรือแก้ไขเพิ่มเติมได้</li>
                        <li>เมื่อไลฟ์เว็บไซต์ไปแล้วจะมีระยะเวลาประมาณ 4 วัน ในการรอแอคทีฟโดเมนเพื่อให้บริการใช้งานได้ครบถ้วน</li>
                        <li>การแก้ไขคำผิดหรือการแก้ไขที่ไม่ใช่การปรับเปลี่ยนเลเอาท์เช่น การอัพเดทคอนเทนต่างๆสามารถทำได้ไม่จำกัดจำนวนครั้ง</li>
                        <li>การแก้ไขปรับเปลี่ยนดีไซน์หรือเลเอาท์สามารถทำได้แต่ต้องเป็นการเปลี่ยนแปลงที่ไม่เกิน 20%</li>
                        <li>หากต้องการย้ายโดเมนจากผู้ให้บริการเจ้าอื่นมาไว้ที่ Localforyou สามารถทำได้แต่จะใช้เวลาในการย้าย 7 วันทำการ</li>
                        <li>การย้ายโดเมนมาไว้ที่ Localforyou จะช่วยให้เจ้าของร้านสะดวกขึ้นในกรณีที่โดเมนหมดอายุหรือต้องการปรับเปลี่ยนการตั้งค่าของโดเมน</li>
                        <li>เลเอาท์ของเว็บไซต์จะเป็นไปตามเทมเพลตที่เรามีไว้ให้เท่านั้นหากต้องการปรับเปลี่ยนนอกจากเทมเพลตที่มีอยู่จะเป็นอีก 1 บริการที่มีค่าใช้จ่ายเพิ่มเติม</li> -->
                    </ol>
                </div>

            </div>

            <div id="th2" class="nono card mt-4">
                <div class="card-header">
                    <h5>ข้อชี้แจง</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                    <ul>
                        <li>เมื่อไลฟ์เว็บไซต์ จะต้องรอให้โดเมนเนทแอคทีฟ เพื่อให้บริการทุกส่วนสามารถใช้งานได้ครบถ้วน ภายในไม่เกิน 4 วัน
                            <ul>
                                <li>หากต้องการย้ายโดเมนจากผู้ให้บริการเจ้าอื่นมาไว้ที่ Localforyou จะใช้เวลาในการย้าย 7 วันทำการ </li>
                                <li>ข้อดีของการย้ายโดเมนมาไว้ที่ Localforyou เช่น ต่ออายุโดเมน,​ ปรับเปลี่ยนการตั้งค่า ฯลฯ เราจะสามารถช่วยเหลือคุณได้ทันที</li>
                                <li>คุณสามารถแจ้งปรับเปลี่ยนแก้ไขเว็บไซต์ของท่านได้ที่ Account Manager ของคุณ หรือ ติดต่อ Customer Support ได้ตลอด 24 ชั่วโมง</li>
                            </ul>
                        </li>
                    </ul>
                    </p>
                </div>
            </div>


            <div id="en" class="card">
                <div class="card-header">
                    <h5 class="font-weight-bold">Terms and conditions for making a website</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>The layout of the website will be according to the template that we provide only.</li>
                        <li>If you want a format other than the existing template we provided,<span class="redtext">it will be another service that costs an additional fee ($995).</span></li>
                        <li>The website creation will take approximately 7 business days to complete.</li>
                        <li>You will be able to start counting day 1 when we have received all necessary information from you.</li>
                        <li>Require Information for website creation
                            <ul>
                                <li>Shop name</li>
                                <li>Desired color , mood & tone</li>
                                <li>Logo of the store (if you have raw file version eg. .ai, .pdf is the best)</li>
                                <li>Various pictures of the your store such as the store landscape, food, etc.</li>
                                <li>Menu information, service <a href="example.php" target="_blank">Example</a></li>
                                <li>Opening hours</li>
                                <li>Address and Contact methods</li>
                                <li>Social media URL , GMB etc.</li>
                                <li>Specify whether you want to use the Online ordering system, Booking System on the website or not?</li>
                                <ul>
                                    <li>We will send a draft of the website for review before sending it live. If you want to adjust the format, such as changing colors,​ modifying the content format. (limit 3 times)</li>
                                    <li>For the adjustment that is not affect with the website layout, such as updating content, correcting typos, modifying store information, modifying opening/closing hours, modifying prices/service details. It’s unlimited times for request</li>
                                    <li>When submitting a website for review, The store owner is required to respond within 7 business days.<br>
                                        <span class="redtext">** If it's been more than 7 days and you haven't responded. It will be considered confirmed and sent on Live.</span>
                                    </li>
                                    <li>After the website is live If there is you have something that needs to be changed You can also request additional amendments. (if not over the limitation specified above) within a period not exceeding 14 days</li>
                                    <li class="redtext">Request of editing more than the limitation may incur additional fees.</li>
                                </ul>
                            </ul>
                        </li>

                        <!-- <li>Counting will begin on day 1 only when complete information is received.</li>
                        <li>Information needed to make a website</li>
                        <ul>
                            <li>Shop logo</li>
                            <li>Picture</li>
                            <li>Menu information / Shop services <a href="example.php" target="_blank">example</a></li>
                        </ul>
                        <li>After the website is finished, if you want to adjust the design or make any changes, you can edit it 3 times.</li>
                        <li>When submitting a website for review, the owner must answer that Confirm or not within 7 days. If after more than 7 days the owner does not confirm and returns, it will be considered confirmed.</li>
                        <li>After the website goes live, the owner has 14 days to request changes or additions.
                        </li>
                        <li>Once the website is live, there will be approximately 4 days of waiting for the active domain to be fully usable.</li>
                        <li>Correcting typos or editing that is not a modification of the layout, such as Content updates can be done an unlimited number of times.</li>
                        <li>Modifications to the design or layout are allowed but must not exceed 20%.</li>
                        <li>If you want to transfer your domain from another provider to Localforyou, you can do so but it will take 7 business days.</li>
                        <li>Moving your domain to Localforyou will help store owners more easily in the event that their domain expires or they need to change their domain settings.</li>
                        <li>The layout of the website will be based on the template we provide only. If you want to modify it unless the existing template is another service that costs an additional fee.</li> -->
                    </ol>
                </div>
            </div>
            <div id="en2" class="card mt-4">
                <div class="card-header">
                    <h5>Notice</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                    <ol>
                        <li>When the website is live It’s need to wait for the domain name to be active to make all service will be fully working (no more than 4 days.)</li>
                        <li>If you want to transfer your domain from another provider to Localforyou, it will take 7 business days.</li>
                        <li>Advantages of transfer your domain domains to Localforyou e.g. domain renewal,​ modify settings, etc., we will be able to help you immediately when you need.</li>
                        <li>you can request of website modifications with your account manager or L4U customer support (24 hrs)</li>
                    </ol>
                    </p>
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