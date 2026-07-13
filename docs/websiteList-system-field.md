# คู่มือฟีเจอร์ System Field — websiteList
# System Field Feature Documentation — websiteList

---

# ภาษาไทย (Thai)

## ภาพรวม

ฟีเจอร์ System Field ใช้สำหรับบันทึกว่าแต่ละ website ใช้ระบบ (plugin/platform) ใดบ้าง
ประกอบด้วย checkbox 5 ตัว: **Gloria Food**, **Amelia**, **Voucher**, **Cloudwaitress**, **Other**

---

## Database Columns (table: `websiteList`)

| Column | Type | ค่าเริ่มต้น | หมายเหตุ |
|---|---|---|---|
| `wSystemGloriaFood` | `tinyint(3) UNSIGNED` | `0` | 0 = ไม่ใช้, 1 = ใช้ |
| `wSystemAmelia` | `tinyint(3) UNSIGNED` | `0` | 0 = ไม่ใช้, 1 = ใช้ |
| `wSystemVoucher` | `tinyint(3) UNSIGNED` | `0` | 0 = ไม่ใช้, 1 = ใช้ |
| `wSystemCloudwaitress` | `tinyint(3) UNSIGNED` | `0` | 0 = ไม่ใช้, 1 = ใช้ |
| `wSystemOther` | `varchar(300)` | `NULL` | เก็บข้อความอิสระ ไม่เกิน 300 ตัวอักษร |

---

## UI — Form Modal (`#formModal`)

**ไฟล์:** `pages/websiteList.php`

### Checkbox ทั่วไป (Gloria Food / Amelia / Voucher / Cloudwaitress)

- ติ๊ก = บันทึกค่า `1`
- ไม่ติ๊ก = บันทึกค่า `0`

### Checkbox Other (พฤติกรรมพิเศษ)

1. **ติ๊ก checkbox "Other"** → กล่อง `otherInputGroup` จะแสดงขึ้นมา
2. **พิมพ์ข้อความ** ใน `textarea#inputOther` (จำกัด 300 ตัวอักษร มี counter แสดงแบบ real-time)
3. **กด Save changes** → ข้อความถูกบันทึก แล้ว textarea จะเปลี่ยนเป็น **label text** (`span#inputOtherDisplay`)
4. **ดับเบิ้ลคลิกที่ label text** → กลับไปโหมดแก้ไข (textarea แสดงอีกครั้ง)
5. **ไม่ติ๊ก checkbox** → กล่องซ่อน, ค่าที่บันทึกจะเป็น string ว่าง (`''`)

---

## Flow การทำงาน

### Add (เพิ่มข้อมูลใหม่)

```
User ติ๊ก checkbox → formSave() → POST ไป actionWebsiteList.php (act=save, formAction=add)
→ INSERT ลง websiteList รวม wSystemCloudwaitress, wSystemOther
```

### Edit (แก้ไขข้อมูล)

```
User คลิกปุ่ม Edit → setEdit(id) → AJAX (act=loadUpdate)
→ PHP คืนค่า wSystemCloudwaitress, wSystemOther
→ JS restore checkbox + แสดง label text ถ้า wSystemOther มีค่า
→ User แก้ไข → formSave() → POST (act=save, formAction=edit)
→ UPDATE websiteList
```

### View Detail

```
User คลิกดูรายละเอียด → viewDetail(id) → AJAX (act=viewDetail)
→ PHP คืนค่าทุก wSystem fields
→ JS รวมระบบที่ติ๊กทั้งหมดเป็น string คั่นด้วย ", " แสดงใน #wSystem
→ ถ้าไม่มีระบบใดเลย แสดง "-"
```

**ตัวอย่างการแสดงผล `#wSystem`:**
- `Amelia, Voucher, Cloudwaitress`
- `Gloria Food, OpenTable`
- `-`

---

## ไฟล์ที่เกี่ยวข้อง

| ไฟล์ | บทบาท |
|---|---|
| `pages/websiteList.php` | HTML form modal + JavaScript logic ทั้งหมด |
| `assets/php/actionWebsiteList.php` | Backend — รับ POST, query DB (viewDetail / loadUpdate / save) |

### จุดสำคัญในโค้ด

**HTML** (`pages/websiteList.php` ~line 419–446)
- `#inputCloudwaitress` — checkbox Cloudwaitress
- `#inputOtherCheck` — checkbox Other (onchange → `toggleOtherInput()`)
- `#otherInputGroup` — wrapper ที่ซ่อน/แสดงตาม checkbox
- `#inputOther` — textarea สำหรับกรอกข้อความ
- `#inputOtherDisplay` — span แสดงเป็น label หลัง save (ดับเบิ้ลคลิก → `editOtherInput()`)
- `#inputOtherCount` — counter ตัวอักษรแบบ real-time

**JavaScript** (`pages/websiteList.php` ~line 699–724)
- `toggleOtherInput()` — ซ่อน/แสดง otherInputGroup
- `editOtherInput()` — สลับจาก label เป็น textarea
- `inputOther.on('input')` — update counter real-time

**PHP** (`assets/php/actionWebsiteList.php`)
- `viewDetail` (line ~46–50) — return `wSystemCloudwaitress`, `wSystemOther`
- `loadUpdate` (line ~93–115) — return `wSystemCloudwaitress`, `wSystemOther`
- `save` (line ~145–164) — รับ `inputCloudwaitress`, `inputOther` + `substr(..., 0, 300)` เป็น safety limit

---

## ข้อควรระวัง

- `wSystemOther` จำกัดที่ **300 ตัวอักษร** ทั้งฝั่ง HTML (`maxlength="300"`) และ PHP (`substr(..., 0, 300)`)
- เมื่อ **ยกเลิก checkbox Other** ค่า `wSystemOther` จะถูกบันทึกเป็น `''` (ไม่ใช่ NULL)
- ถ้าต้องการเพิ่มระบบใหม่ในอนาคต ให้เพิ่ม column `tinyint` ใน DB แล้วทำตาม pattern เดิมของ Gloria Food / Amelia / Voucher / Cloudwaitress

---
---

# English

## Overview

The System Field feature is used to record which systems (plugins/platforms) each website uses.
It consists of 5 checkboxes: **Gloria Food**, **Amelia**, **Voucher**, **Cloudwaitress**, **Other**

---

## Database Columns (table: `websiteList`)

| Column | Type | Default | Description |
|---|---|---|---|
| `wSystemGloriaFood` | `tinyint(3) UNSIGNED` | `0` | 0 = not used, 1 = used |
| `wSystemAmelia` | `tinyint(3) UNSIGNED` | `0` | 0 = not used, 1 = used |
| `wSystemVoucher` | `tinyint(3) UNSIGNED` | `0` | 0 = not used, 1 = used |
| `wSystemCloudwaitress` | `tinyint(3) UNSIGNED` | `0` | 0 = not used, 1 = used |
| `wSystemOther` | `varchar(300)` | `NULL` | Free-text field, max 300 characters |

---

## UI — Form Modal (`#formModal`)

**File:** `pages/websiteList.php`

### Standard Checkboxes (Gloria Food / Amelia / Voucher / Cloudwaitress)

- Checked = saves value `1`
- Unchecked = saves value `0`

### Other Checkbox (Special Behaviour)

1. **Check "Other"** → `otherInputGroup` container becomes visible
2. **Type text** in `textarea#inputOther` (max 300 characters, real-time counter shown)
3. **Click Save changes** → text is saved; textarea switches to a **read-only label** (`span#inputOtherDisplay`)
4. **Double-click the label** → returns to edit mode (textarea reappears)
5. **Uncheck "Other"** → container hides, saved value becomes an empty string (`''`)

---

## Workflow

### Add (New Record)

```
User checks checkbox → formSave() → POST to actionWebsiteList.php (act=save, formAction=add)
→ INSERT into websiteList including wSystemCloudwaitress, wSystemOther
```

### Edit (Update Record)

```
User clicks Edit → setEdit(id) → AJAX (act=loadUpdate)
→ PHP returns wSystemCloudwaitress, wSystemOther
→ JS restores checkbox state + shows label text if wSystemOther has a value
→ User makes changes → formSave() → POST (act=save, formAction=edit)
→ UPDATE websiteList
```

### View Detail

```
User clicks View Detail → viewDetail(id) → AJAX (act=viewDetail)
→ PHP returns all wSystem fields
→ JS joins all checked systems into a comma-separated string displayed in #wSystem
→ If no system is selected, displays "-"
```

**Example `#wSystem` output:**
- `Amelia, Voucher, Cloudwaitress`
- `Gloria Food, OpenTable`
- `-`

---

## Related Files

| File | Role |
|---|---|
| `pages/websiteList.php` | HTML form modal + all JavaScript logic |
| `assets/php/actionWebsiteList.php` | Backend — handles POST, DB queries (viewDetail / loadUpdate / save) |

### Key Code References

**HTML** (`pages/websiteList.php` ~line 419–446)
- `#inputCloudwaitress` — Cloudwaitress checkbox
- `#inputOtherCheck` — Other checkbox (onchange → `toggleOtherInput()`)
- `#otherInputGroup` — wrapper shown/hidden based on checkbox state
- `#inputOther` — textarea for free-text input
- `#inputOtherDisplay` — span displayed as label after save (double-click → `editOtherInput()`)
- `#inputOtherCount` — real-time character counter

**JavaScript** (`pages/websiteList.php` ~line 699–724)
- `toggleOtherInput()` — shows/hides otherInputGroup
- `editOtherInput()` — switches from label to textarea
- `inputOther.on('input')` — updates counter in real-time

**PHP** (`assets/php/actionWebsiteList.php`)
- `viewDetail` (line ~46–50) — returns `wSystemCloudwaitress`, `wSystemOther`
- `loadUpdate` (line ~93–115) — returns `wSystemCloudwaitress`, `wSystemOther`
- `save` (line ~145–164) — receives `inputCloudwaitress`, `inputOther` + `substr(..., 0, 300)` as a safety limit

---

## Important Notes

- `wSystemOther` is limited to **300 characters** on both the HTML side (`maxlength="300"`) and PHP side (`substr(..., 0, 300)`)
- When **"Other" is unchecked**, `wSystemOther` is saved as `''` (not NULL)
- To add a new system in the future, add a `tinyint` column to the DB and follow the same pattern as Gloria Food / Amelia / Voucher / Cloudwaitress
