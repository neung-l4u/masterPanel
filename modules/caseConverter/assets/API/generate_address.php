<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['country'])) {
    $country = $_POST['country'];

    $fakeAddresses = [
        'Australia' => [
            "12 Smith St, Sydney NSW 2000",
            "88 George St, Brisbane QLD 4000",
            "50 Collins St, Melbourne VIC 3000",
            "120 King William St, Adelaide SA 5000",
            "45 Hay St, Perth WA 6000",
            "67 Macquarie St, Hobart TAS 7000",
            "33 Limestone St, Ipswich QLD 4305",
            "15 Barrack St, Fremantle WA 6160",
            "77 Grenfell St, Adelaide SA 5000",
            "99 Lygon St, Carlton VIC 3053",
            "101 Crown St, Wollongong NSW 2500",
            "55 Queen St, Brisbane QLD 4000",
            "123 Bourke St, Melbourne VIC 3000",
            "98 Anzac Hwy, Glenelg SA 5045",
            "60 Flinders St, Adelaide SA 5000",
            "70 Albert St, Brisbane QLD 4000",
            "80 Victoria Ave, Perth WA 6000",
            "10 Hunter St, Newcastle NSW 2300",
            "36 George St, Parramatta NSW 2150",
            "25 Chapel St, South Yarra VIC 3141"
        ],
        'New Zealand' => [
            "45 Queen St, Auckland 1010",
            "99 Lambton Quay, Wellington 6011",
            "12 Colombo St, Christchurch 8011",
            "88 Great King St, Dunedin 9016",
            "66 Devon St, New Plymouth 4310",
            "33 Emerson St, Napier 4110",
            "20 Victoria St, Hamilton 3204",
            "77 Church St, Timaru 7910",
            "55 High St, Lower Hutt 5010",
            "11 Oxford Tce, Christchurch 8011",
            "100 Cashel St, Christchurch 8011",
            "222 Queen St, Auckland 1010",
            "333 George St, Dunedin 9016",
            "14 Main St, Gore 9710",
            "17 Wharf St, Tauranga 3110",
            "59 Gill St, New Plymouth 4310",
            "71 Victoria Ave, Wanganui 4500",
            "38 Cuba St, Wellington 6011",
            "23 Cameron Rd, Tauranga 3110",
            "29 Fenton St, Rotorua 3010"
        ],
        'USA' => [
            "123 Main St, Springfield, IL 62704",
            "789 Elm St, Los Angeles, CA 90001",
            "1600 Pennsylvania Ave NW, Washington, DC 20500",
            "405 Lexington Ave, New York, NY 10174",
            "1 Market St, San Francisco, CA 94105",
            "350 5th Ave, New York, NY 10118",
            "1000 Peachtree St, Atlanta, GA 30309",
            "200 Congress Ave, Austin, TX 78701",
            "500 S Hope St, Los Angeles, CA 90071",
            "600 Brickell Ave, Miami, FL 33131",
            "75 State St, Boston, MA 02109",
            "10 N Michigan Ave, Chicago, IL 60602",
            "222 S Main St, Salt Lake City, UT 84101",
            "99 Summer St, Boston, MA 02110",
            "777 6th St NW, Washington, DC 20001",
            "121 King St, San Francisco, CA 94107",
            "1301 K St NW, Washington, DC 20005",
            "4100 Hillsboro Pike, Nashville, TN 37215",
            "9800 Fredericksburg Rd, San Antonio, TX 78288",
            "1900 5th Ave, Seattle, WA 98101"
        ],
        'UK' => [
            "221B Baker St, London NW1 6XE",
            "10 Downing St, London SW1A 2AA",
            "160 Queen Victoria St, London EC4V 4BJ",
            "50 Princes St, Edinburgh EH2 2BY",
            "1 St Andrew’s St, Cambridge CB2 3BZ",
            "40 Deansgate, Manchester M3 2BA",
            "25 George Square, Glasgow G2 1EG",
            "70 High Holborn, London WC1V 6LG",
            "15 Broad St, Oxford OX1 3AS",
            "200 Piccadilly, London W1J 9HU",
            "88 Mount St, London W1K 2SR",
            "120 Victoria St, London SW1E 5LA",
            "65 North St, Brighton BN1 1AG",
            "105 Buchanan St, Glasgow G1 3HF",
            "30 Cornmarket St, Oxford OX1 3EY",
            "3 Brindleyplace, Birmingham B1 2JB",
            "98 King St, Manchester M2 4WU",
            "73 Foregate St, Chester CH1 1HE",
            "19 The Headrow, Leeds LS1 6PU",
            "43 St. Mary Axe, London EC3A 8AA"
        ],
        'Canada' => [
            "100 King St W, Toronto, ON M5X 1A9",
            "2500 Boul de Maisonneuve, Montreal, QC H3H 1N1",
            "999 Canada Pl, Vancouver, BC V6C 3E1",
            "123 Jasper Ave, Edmonton, AB T5J 1Y8",
            "88 Albert St, Ottawa, ON K1P 5E9",
            "77 Main St, Winnipeg, MB R3C 2R1",
            "450 1 St SW, Calgary, AB T2P 5H1",
            "1500 W Georgia St, Vancouver, BC V6G 2Z6",
            "66 Wellington St W, Toronto, ON M5K 1A1",
            "333 Laurier Ave W, Ottawa, ON K1P 1C1",
            "59 Rideau St, Ottawa, ON K1N 5A9",
            "5 Bay St, Toronto, ON M5J 2L7",
            "88 Queen St, Charlottetown, PE C1A 4B5",
            "25 University Ave, Halifax, NS B3H 4R2",
            "345 Main St, Moncton, NB E1C 1B8",
            "17 King St, Saint John, NB E2L 1G8",
            "140 Water St, St. John's, NL A1C 1A8",
            "60 Yonge St, Toronto, ON M5E 1H5",
            "12 Sparks St, Ottawa, ON K1P 5A5",
            "1100 René-Lévesque Blvd, Montreal, QC H3B 4N4"
        ],
        'Thailand' => [
            "99 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110",
            "88 หมู่ 3 ตำบลลำลูกกา อำเภอลำลูกกา ปทุมธานี 12150",
            "12 ซอยเอกมัย 10 เขตวัฒนา กรุงเทพฯ 10110",
            "234 หมู่บ้านสีวลี ถนนรังสิต-นครนายก ปทุมธานี 12130",
            "55/5 ถนนราชดำเนินกลาง แขวงบวรนิเวศ เขตพระนคร กรุงเทพฯ 10200",
            "78 ถนนเจริญนคร แขวงบางลำภูล่าง เขตคลองสาน กรุงเทพฯ 10600",
            "1 หมู่ 2 ตำบลแม่เหียะ อำเภอเมืองเชียงใหม่ เชียงใหม่ 50100",
            "45 ถนนห้วยแก้ว ตำบลสุเทพ อำเภอเมือง เชียงใหม่ 50200",
            "62 ซอยประชาราษฎร์บำเพ็ญ ห้วยขวาง กรุงเทพฯ 10310",
            "133 หมู่ 4 ตำบลหนองบัว อำเภอเมืองอุดรธานี อุดรธานี 41000",
            "19 ถนนสีลม เขตบางรัก กรุงเทพฯ 10500",
            "9 ถนนประชาชื่น เขตบางซื่อ กรุงเทพฯ 10800",
            "81 ถนนนิมมานเหมินท์ อำเภอเมือง เชียงใหม่ 50200",
            "202 ถนนเพชรบุรี ราชเทวี กรุงเทพฯ 10400",
            "55/1 หมู่บ้านชัยพฤกษ์ รังสิต คลอง 4 ปทุมธานี",
            "148 ถนนมิตรภาพ อำเภอเมือง ขอนแก่น 40000",
            "77/7 ถนนเทพารักษ์ อำเภอเมือง สมุทรปราการ 10270",
            "93 หมู่ 5 ตำบลบ่อวิน อำเภอศรีราชา ชลบุรี 20230",
            "12 ซอยสุขุมวิท 33 เขตวัฒนา กรุงเทพฯ 10110",
            "5 ถนนพระราม 9 ห้วยขวาง กรุงเทพฯ 10310"
        ]
    ];

    if (isset($fakeAddresses[$country])) {
        $randomAddress = $fakeAddresses[$country][array_rand($fakeAddresses[$country])];
        echo $randomAddress;
    } else {
        echo "Unknown country";
    }
} else {
    echo "Invalid request";
}