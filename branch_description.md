>250526  mark/feature/logStripeRes
>- เพิ่ม field เก็บ log response ของ Stripe

>250526 mark/feature/ITSeeAll
>- ปรับ Datatable หน้า Projects ให้ IT เห็นทุกโปรเจค

>250526 mark/feature/signupFlagTest
>- เพิ่ม field test ใน signup log เพื่อ filter

>250526 mark/feature/timeOff
>- ทำฟอร์มการลาของ part-time

>250526 mark/fix/projectSendingIcon
>- send mail ของหน้า project ช้า เลยเพิ่ม loading

>250526 mark/fix/multiUploadParam
>- แก้ URL การส่ง parameter จากหน้า detail ไปหน้า multiupload ค่าของ folderPath ไม่ได้ encode ทำให้ URL พัง

>250521 mark/feature/WLaction
>- ทำปุ่ม Add, Edit, Delete

>250520 mark/fix/templateCantSave
>- แก้ Template Res3 กด save ไม่ได้

>250519 mark/fix/signupMiniValidate
>- ลบ field last_name และการ validate ออก

>250515 mark/fix/TPtimestamp
>- แก้ datatable view logs ของ Template Submission เปลี่ยนคอลัมน์จาก duedate เป็น timestamp 

>250514 mark/feature/voucherLogs
>- สร้าง modules/voucherLogs และ database เพื่อเก็บ Logs การซื้อ Voucher แล้วเชื่อมต่อกับ API ของ make.com
>- เพิ่มหน้าสำหรับ view logs ใน masterpanel

>250513 basmark/fix/request
>- แก้ $_REQUEST ทุกไฟล์เป็น $_POST หรือ $_GET เนื่องจาก A2 อัพเดตระบบ

>250501 mark/feature/websiteLists
>- เพิ่ม datatable สำหรับ Website Lists เพิ่มหน้า page websiteList.php, dataWebsiteList.php, actionWebsiteList.php 

>250320 mark/feature/signupMini
>- ทำ form Signup Mini ยิงเข้า make.com > Leads Monday

>250220 bas/feature/formEmail
>- ทำ form สำหรับ ยิง mail ที่ไม่มา

>250219 mark/hotfix/stateEmail **[MERGED]**
>- เปลี่ยน section ในอีเมล จาก state เป็น street address

>250301 bas/add/posInfo  **[MERGED]**
>- เพิ่มช่อง POS ใน signup form, Log File และใน Email

>250216 bas/fix/cancellation  **[MERGED]**
>- รีเช็คฟอร์ม

>250219 Neung/fix/mondayReport **[MERGED]**
>- เพิ่มการแสดงผล Stat ในแบบต่างๆ

>250219 mark/hotfix/stateEmail **[MERGED]**
>- เปลี่ยน section ในอีเมล จาก state เป็น street address

>250220 mark/feature/contractDB **[MERGED]**
>- save link Contract ลง database, เพิ่ม modules generateAgreement, update policy เป็น v1.3.0

>250219 mark/feature/viewsLogs **[MERGED]**
>- สร้างหน้าสำหรับดู signup logs, stripe logs, contract

>250217 mark/feature/logsStripeDB **[MERGED]**
>- save logs ของ stripe ลง database

>250217 Neung/fix/signupProduct **[MERGED]**
>- แก้ปัญหาเรื่องบางครั้งตัดเงินไม่ได้

>250215 bas/fix/emailAlert **[MERGED]**
>- จัดการอีเมลที่ไม่แจ้งเตือนในไฟล์ชื่อ L4UEmailAlert.php

>250217 mark/feature/logsStripeDB **[MERGED]**
>- save logs ของ stripe ลง database

>250215 bas/fix/emailAlert **[MERGED]**
>- จัดการอีเมลที่ไม่แจ้งเตือนในไฟล์ชื่อ L4UEmailAlert.php

>241220 fix/layout (bas) **[MERGED]**
>- แก้ layout Master Panel, Gift list, Convent coin btn to icon box 
>- แก้เว็บไซต์ตัวอย่างสำหรับเซล