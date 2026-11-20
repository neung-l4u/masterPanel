# competitorScan — Competitor Scan / Lost Customer Tracking

ติดตามลูกค้าที่ยกเลิก L4U ว่าตอนนี้ใช้ระบบหรือทำการตลาดกับใคร โดยใช้แค่ข้อมูลสาธารณะ
โครงเดียวกับ `modules/viewcreditmake/`: **ไม่ใช้ฐานข้อมูลของตัวเอง** (อ่านตาราง `Cancellation` อย่างเดียว)
ผลเก็บเป็นไฟล์ใน `cache/` ซึ่งอยู่ใน `.gitignore` แล้ว เพราะมีข้อมูลลูกค้าและ repo นี้เป็น public

## ติดตั้งบน production

1. ไม่ต้องรัน SQL migration
2. ใช้ monday token จาก `config/domain_report.php` (ไฟล์เดียวกับ domain_report bot) ไม่ต้องตั้งค่าเพิ่ม
3. `cache/` จะถูกสร้างเอง web server และ cron user ต้องเขียนได้
4. เพิ่ม cron (เรียกได้ทุกวัน — สคริปต์เริ่มรอบใหม่เองเมื่อรอบก่อนเสร็จเกิน 30 วัน):

   ```
   0 2 * * * php <path-to-masterPanel>/modules/competitorScan/cron.php >> <log-file> 2>&1
   ```
   (ใช้ path เดียวกับ cron ของ `assets/php/domain_report.php`)
5. เปิด `modules/competitorScan/` (ต้อง login masterPanel)

ต้องรันบน production: เว็บลูกค้าที่ยังอยู่บน A2 จะขึ้นหน้า bot-check ให้ IP ออฟฟิศ

## ขั้นตอนของหนึ่งรอบ

1. **monday** — Projects 6 ประเทศ, Active Subscriptions, งาน "rejoin" (~40–60 API calls ต่อรอบ จาก quota 10,000/วัน)
   แล้วเลือกร้านตามกฎใน `cs_build_shops()`:
   - Cancelled และไม่มี subscription ที่ยัง live, หรือยังไม่ Cancelled แต่มี Marketing/System Cancel Date (ยกเลิกบางส่วน)
   - ตัด Test Data, Cancel before live, ร้านที่มี project ใหม่ที่ยังใช้งานอยู่
   - package จาก subscription ที่ยกเลิกล่าสุด → Solo = Marketing, Bundle = Marketing + System, Hosting/Makeover/Ad Credit/Yelp = Add-on
     ไม่รู้ package → ตรวจทั้งสองส่วน
2. **lfy** — เก็บ Pixel / GTM / Google Ads ID จากเว็บลูกค้าที่ยัง live เพื่อตัดของ LFY ที่ค้างอยู่ในเว็บเก่าออก
3. **scan** — หน้าแรก + หน้า order/book/menu ของร้าน ทีละ 20 ร้าน
   - System: provider ใน `SYSTEMS` (`models/detect.php`) = Confirmed, ลิงก์ปุ่มสั่ง/จองไปโดเมนอื่น = Likely
   - Marketing (ชั้น 0): Google Ads tag / Meta Pixel = Likely (มีบัญชี ≠ มีโฆษณาที่รันอยู่)
   - Agency: เครดิต "Website by / Designed by …" = Confirmed
4. **finish** — `current.json` แทนที่ทั้งไฟล์, ร้านที่ผลเปลี่ยนต่อท้าย `history.jsonl`, ร้านที่ error ลง `errors.json`

แต่ละ request ทำงาน ~20 วินาทีแล้วบันทึกจุดที่ทำถึงไว้ใน `cache/run.json` หน้าเว็บ poll ต่อจนเสร็จ cron ก็ทำต่อจากจุดเดิมได้
lock กันไม่ให้หน้าเว็บกับ cron ทำงานซ้อนกัน

## รันเทสต์

```bash
php modules/competitorScan/tests/detect_test.php
```

## Google Places lookup (shops whose own site can't be checked)

`places.php` looks up each such shop's Google listing once (Places API (New) Text Search) and stores its website /
phone in `cache/links.json`; scans then check that page. Key: `config/competitor_scan.php` (gitignored)
`<?php return ['places_key' => '...'];`. The website field is billed as Text Search Enterprise (1,000 free / month):
the script stops at 1,000 a month (`CS_PLACES_MONTHLY`) and 40 a day, counted in `cache/places_usage.json`.

    php modules/competitorScan/places.php --dry   # who is next, no API calls
    php modules/competitorScan/places.php         # daily from cron
