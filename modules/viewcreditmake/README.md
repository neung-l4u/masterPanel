# viewcreditmake — แดชบอร์ดการใช้เครดิต Make.com

พอร์ตมาจากโปรเจกต์ Node/React ที่ `~/Desktop/viewtoken` เป็น PHP + jQuery
ตามโครงของ `modules/empty/`

## ติดตั้ง

โมดูลนี้ **ไม่ใช้ฐานข้อมูล** จึงไม่ต้องรัน SQL migration ใดๆ
ทั้งบนเครื่องและบน production

1. คัดลอก config แล้วใส่ token:

   ```bash
   cp modules/viewcreditmake/config_example.php modules/viewcreditmake/config.php
   # แก้ $makeToken ในไฟล์ config.php
   ```

   `config.php` ถูกใส่ใน `.gitignore` แล้ว (ห้าม commit token)

2. โฟลเดอร์ `cache/` ถูกสร้างอัตโนมัติ ต้องให้ web server เขียนได้

3. เปิด `modules/viewcreditmake/` (index.php จะ redirect ไป `views/index.php`)

## สิ่งที่ต้องรู้

- ใช้ session กลางของ masterPanel (`$_SESSION['id']`) ไม่มีระบบ login แยก
- เรียก Make API แบบ **GET เท่านั้น** ไม่มี endpoint ที่ run/แก้/เปิดปิด scenario
- token อยู่ฝั่ง server เท่านั้น ไม่เคยถูกส่งไปที่ browser
- กราฟใช้ Chart.js v2.9.4 ที่มีอยู่แล้วใน `plugins/chart.js/` (ไม่เพิ่ม dependency)

## โหลดครั้งแรกใช้เวลานาน

องค์กรมี ~234 scenario และ Make จำกัด rate limit ทำให้ต้องดึงทีละตัว
(~0.9 วิ/scenario รวมประมาณ 3–4 นาที) ซึ่งเกิน `max_execution_time` (30 วิ)
ของ Apache จึงออกแบบให้ **ดึงทีละส่วนต่อ request** แล้วฝั่ง client poll
จนครบ (`complete: true`) ผลลัพธ์ cache ไว้ 10 นาที

## รันเทสต์

```bash
php modules/viewcreditmake/tests/aggregate_test.php
```

ครอบคลุม: แปลง `DD-MM-YYYY`, แปลง ISO ที่มี offset โดยวันไม่เลื่อน,
อ่าน offset ขององค์กร, รวมยอด/หารเฉลี่ย, forecast (ขาดและเกิน),
สรุป scheduling ทั้ง 7 แบบ, และการเลื่อนช่วง epoch ของ `/logs`
