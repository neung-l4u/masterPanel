<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['country'])) {
    $country = $_POST['country'];

    $fakeNames = [
        'Australia' => [
            "Jack Thompson", "Emily Harris", "Liam Smith", "Olivia Brown", "Noah Wilson",
            "Ava Martin", "William Clark", "Isla Walker", "James Hall", "Chloe Lewis",
            "Lucas Young", "Sophie Allen", "Henry Wright", "Amelia Scott", "Ethan Robinson",
            "Zoe King", "Alexander Adams", "Lily Mitchell", "Benjamin White", "Grace Lee"
        ],
        'New Zealand' => [
            "Lachlan Taylor", "Sienna Anderson", "Mason Harris", "Aria Wilson", "Hunter Moore",
            "Ruby Thompson", "Cooper Walker", "Georgia Martin", "Flynn Lee", "Evie Scott",
            "Toby King", "Mila James", "Nate Campbell", "Ella Ward", "Jaxon Bennett",
            "Holly Green", "Oscar Hughes", "Abby Ross", "Levi Cox", "Emma Parker"
        ],
        'USA' => [
            "John Smith", "Emma Johnson", "Michael Williams", "Olivia Jones", "David Brown",
            "Sophia Garcia", "Daniel Miller", "Ava Martinez", "Matthew Davis", "Isabella Rodriguez",
            "Andrew Hernandez", "Mia Wilson", "Joseph Anderson", "Charlotte Thomas", "James Taylor",
            "Amelia Lee", "Benjamin Harris", "Emily Clark", "Logan Lewis", "Grace Hall"
        ],
        'UK' => [
            "Harry Smith", "Emily Jones", "George Taylor", "Isla Williams", "Jack Brown",
            "Freya Wilson", "Charlie Davies", "Lily Evans", "Thomas Roberts", "Sophie Johnson",
            "Jacob Walker", "Evie Thompson", "Alfie White", "Poppy Hughes", "Leo Green",
            "Jessica Hall", "Oscar Lewis", "Daisy Adams", "Harrison Moore", "Ruby King"
        ],
        'Canada' => [
            "Liam Martin", "Emma Anderson", "Noah Thompson", "Chloe Wilson", "Lucas Brown",
            "Ella Johnson", "Mason Lee", "Grace Campbell", "Jackson White", "Avery Young",
            "Benjamin Scott", "Hannah Davis", "Ethan Moore", "Sophie Taylor", "Oliver Wright",
            "Abigail Hall", "Logan Harris", "Lily King", "William Lewis", "Zoe Adams"
        ],
        'Thailand' => [
            "ณัฐวุฒิ ศรีทอง", "พรทิพย์ แสงใส", "วีรพล มหาวงศ์", "ชลธิชา ภูผา", "กิตติศักดิ์ อินทร",
            "อรอนงค์ วงศ์ดี", "ธนพล ศรีสม", "พัชรี สายสุนทร", "สมชาย เรืองแสง", "จุฑามาศ กลิ่นหอม",
            "ปกรณ์ พัฒนศักดิ์", "สุธิดา แก้วใส", "ณรงค์ชัย บุญมา", "นรีนุช ทองดี", "อนันต์ แก้วคำ",
            "ศศิธร วงศ์เพ็ญ", "ภาณุวัฒน์ แสงอรุณ", "ขวัญฤดี พรหมรักษา", "สิรินดา แซ่ลี้", "จิราวัฒน์ สายลม"
        ]
    ];

    if (isset($fakeNames[$country])) {
        $randomName = $fakeNames[$country][array_rand($fakeNames[$country])];
        echo $randomName;
    } else {
        echo "Unknown country";
    }
} else {
    echo "Invalid request";
}