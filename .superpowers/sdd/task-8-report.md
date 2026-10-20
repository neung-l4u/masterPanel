# Task 8 report
Status: DONE. Commit 15bfd710. Observed count: 37 passed, 0 failed (brief's 20 + my 17).
Mutations: upsell-omit-when-empty -> "no upsell today is stated explicitly" FAILED (1 fail); sign dropped (abs) -> "a negative balance shows its sign" FAILED (1 fail). Both reverted. Extra: bare trim() on shop name -> 3 NBSP tests FAIL.
Regression loop (arayaBrief + upgrade-downgrade tests): no output.
Concerns: the brief gave no Thai text for headings other than the asserted phrases (ไม่มีวันนี้, ยังไม่ได้บันทึก, ดึงข้อมูลนาทีไม่ได้) and spec phrases (ไม่พบใน gcal, ค้าง N วัน, เหตุผล, ยังไม่ได้บันทึก live date). The other headings (📊 ภาพรวม, 🟢 ร้านที่ LIVE, 💰 โอกาส Upsell, 😴 ร้านใหม่ที่ยังใช้น้อย, 📉 ร้านเก่าที่เงียบลง, 🛠 Setup ที่ค้างอยู่, 🚪 รอยกเลิก, ⚠️ ข้อมูลที่ขาด) are my wording; the user's template was not available. Please have them checked.
Also: upsellExcluded shops are listed under the upsell heading with "ไม่นับ: reason"; gap details are the English strings from buildBrief.

## Task 8: team wording for the Chat message

Tests: 63 passed, 0 failed. Fallback proved (invented_kind prints its English detail; also a permanent test). Regression loop: clean.

Full rendered message (every section populated):

```
☀️ ARAYA DAILY BRIEF — 20 Oct 2026 (07:00)
━━━━━━━━━━━━━━━━━━━━━━━━

📊 OVERVIEW
LIVE: 4 ร้าน
แจ้งยกเลิก (pending): 1 ร้าน
Setup ค้างอยู่: 6 ร้าน
ยกเลิกแล้ว: 26 ร้าน (Cancel Before LIVE 15 · Canceled 11)
รวมลูกค้าทั้งหมด: 37 ร้าน

━━━━━━━━━━━━━━━━━━━━━━━━

✅ ร้านที่ LIVE (นาทีคงเหลือ · อยู่กับเรามานาน)
Aunya Shop (-109 mins · 4 ด. 19 วัน)
Ghost Spa (ไม่พบใน gcal ⚠️)

━━━━━━━━━━━━━━━━━━━━━━━━

💰 โอกาสอัพเซลล์ (นาที <70 และรีเฟรชอีก ≤15 วัน)
Siam Thai (50 mins · รีเฟรชอีก 7 วัน) · @Aim

━━━━━━━━━━━━━━━━━━━━━━━━

📉 ไลฟ์แล้วแต่ไม่มีการใช้งาน
New Nail (ใช้ 4% · live มา 12 วัน · เหลือ 240 mins) · @Namva

━━━━━━━━━━━━━━━━━━━━━━━━

📉 เคยใช้แล้วเงียบไป
Quiet Spa (รอบล่าสุดใช้ 5 นาที · เฉลี่ยก่อนหน้า 100) · @Aim

━━━━━━━━━━━━━━━━━━━━━━━━

🚧 ทำไมยังไม่ไลฟ์ (Setup ค้าง)
Stuck Shop — Stuck · ค้าง 187 วัน · @Namva
   รอลูกค้าส่งเมนู

━━━━━━━━━━━━━━━━━━━━━━━━

❌ แจ้งยกเลิก
Leaving Shop — Waiting to cancel · 9 ด. 19 วัน · @Aim
   เหตุผล: ยังไม่ได้บันทึก

━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ ข้อมูลไม่ครบ
• Shop unmatched_monday [unmatched_monday] อยู่ใน Monday แต่หาร้านที่ตรงกันใน gcal ไม่พบ จึงไม่ทราบนาทีคงเหลือ
• Shop unmatched_gcal [unmatched_gcal] มีอยู่ใน gcal แต่ไม่มี item ใน Monday board
• Shop no_live_date [no_live_date] ยังไม่มีวัน live จึงคำนวณอายุการใช้งานและเช็กกฎร้านเงียบไม่ได้
• Shop no_billing_day [no_billing_day] ยังไม่ได้ตั้งวันบิล จึงเช็กกฎอัพเซลล์ไม่ได้
• Shop no_allocation [no_allocation] ไม่ทราบจำนวนนาทีที่ได้รับ หรือนาทีคงเหลือมากกว่าที่ได้รับ จึงใช้เปอร์เซ็นต์การใช้งานไม่ได้
• Shop no_register_date [no_register_date] ยังไม่มีวันลงทะเบียน จึงคำนวณจำนวนวันที่ค้างไม่ได้
• Shop blank_status [blank_status] ช่อง Live Status ใน Monday ว่างอยู่
• Shop unknown_status [unknown_status] ช่อง Live Status มีค่าที่ระบบไม่รู้จัก
• Shop no_remaining_minutes [no_remaining_minutes] gcal ไม่มีข้อมูลนาทีคงเหลือ จึงเช็กอัพเซลล์และร้านเงียบไม่ได้
• Shop invented_kind [invented_kind] English detail for invented_kind
```
