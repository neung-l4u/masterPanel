<?php
/**
 * ส่วนลดของใบ TH = ราคาเต็มรวม VAT ของทุกรายการ - grandtotal_inc_vat
 * ใบจาก signup เก็บรายการเป็นราคาเต็ม แต่ summary หักคูปองแล้ว (คูปองไม่ได้เป็นแถวใน table)
 * จึงต้องส่งส่วนลดแยกให้อีเมล/PDF โชว์ ไม่งั้นรายการกับยอดรวมไม่ตรงกัน
 *
 * $items: รายการที่แปลงแล้ว [{qyt, amount(ราคาต่อหน่วยรวม VAT)}]
 * return ['fullTotal' => '4900.00', 'discount' => '2450.00', 'couponCode' => 'XXX']
 *        discount = '0.00' เมื่อไม่มีส่วนลด
 */
function invoiceDiscount(array $items, $grandtotal, array $productJson): array
{
    $full = 0.0;
    foreach ($items as $it) {
        $q = (float)($it['qyt'] ?? 1);
        $full += (float)($it['amount'] ?? 0) * ($q ?: 1);
    }
    $full = round($full, 2);
    $discount = round($full - (float)$grandtotal, 2);
    // ต่ำกว่า 0.01 คือเศษปัด VAT ไม่ใช่ส่วนลด
    if ($discount < 0.01) {
        $discount = 0.0;
    }
    return [
        'fullTotal'  => number_format($full, 2, '.', ''),
        'discount'   => number_format($discount, 2, '.', ''),
        'couponCode' => $discount > 0 ? (string)($productJson['coupon'] ?? '') : '',
    ];
}

if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    $d = invoiceDiscount([['qyt' => '1', 'amount' => '4900.00']], '2450.00', ['coupon' => 'HALF']);
    assert($d === ['fullTotal' => '4900.00', 'discount' => '2450.00', 'couponCode' => 'HALF']);
    $d = invoiceDiscount([['qyt' => '1', 'amount' => '4900.00']], '4900.00', ['coupon' => 'HALF']);
    assert($d['discount'] === '0.00' && $d['couponCode'] === '');
    $d = invoiceDiscount([['qyt' => '2', 'amount' => '100.00']], '199.995', []);
    assert($d['discount'] === '0.00');
    echo "ok\n";
}
