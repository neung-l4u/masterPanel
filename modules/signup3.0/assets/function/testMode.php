<?php
global $testMode;
if ($testMode){
    $randomNumber = sprintf("%03d", mt_rand(1, 999));
    $test["firstname"] = "Neung L4U";
    $test["lastname"] = "Test - ".$randomNumber;
    $test["mobile"] = rand(1000000000,9999999999);
    $test["email"] = "neung@gmail.com";
    $test["time"] = "11:12";
    $test["shop"] = "Test Shop".$randomNumber;
    $test["abn"] = sprintf("%06d", mt_rand(1, 999999));
    $test["trading"] = "L4U Test";
    $test["shopnumber"] = rand(1000000000,9999999999);
    $test["website"] = "www.localforyou.com";
    $test["address"] = "11/22 abc street";
    $test["city"] = "ABC Village";
    $test["zip"] = "10110";
    //$test["card"] = "4242424242424242";
    $test["card"] = "";
    //$test["cvv"] = "123";
    $test["cvv"] = "";
    $test["note"] = "test mode";
    $test["person"] = randomName();
    $test["refShop"] = randomShop();
}else{
    $randomNumber = "";
    $test["firstname"] = "";
    $test["lastname"] = "";
    $test["mobile"] = "";
    $test["email"] = "";
    $test["time"] = "";
    $test["shop"] = "";
    $test["abn"] = "";
    $test["trading"] = "";
    $test["shopnumber"] = "";
    $test["website"] = "";
    $test["address"] = "";
    $test["city"] = "";
    $test["zip"] = "";
    $test["card"] = "";
    $test["cvv"] = "";
    $test["note"] = "";
    $test["person"] = "";
    $test["refShop"] = "";
}

function generateRandomString($length = 10) {
    $randomString = md5(uniqid(rand(), true));
    $randomString = substr($randomString, 0, $length);

    return $randomString;
}

function randomName() {
    $firstname = array(
        'Johnathon',
        'Anthony',
        'Erasmo',
        'Raleigh',
        'Nancie',
        'Tama',
        'Camellia',
        'Augustine',
        'Christeen',
        'Luz',
        'Diego',
        'Lyndia',
        'Thomas',
        'Georgianna',
        'Leigha',
        'Alejandro',
        'Marquis',
        'Joan',
        'Stephania',
        'Elroy',
        'Zonia',
        'Buffy',
        'Sharie',
        'Blythe',
        'Gaylene',
        'Elida',
        'Randy',
        'Margarete',
        'Margarett',
        'Dion',
        'Tomi',
        'Arden',
        'Clora',
        'Laine',
        'Becki',
        'Margherita',
        'Bong',
        'Jeanice',
        'Qiana',
        'Lawanda',
        'Rebecka',
        'Maribel',
        'Tami',
        'Yuri',
        'Michele',
        'Rubi',
        'Larisa',
        'Lloyd',
        'Tyisha',
        'Samatha',
    );

    $lastname = array(
        'Mischke',
        'Serna',
        'Pingree',
        'Mcnaught',
        'Pepper',
        'Schildgen',
        'Mongold',
        'Wrona',
        'Geddes',
        'Lanz',
        'Fetzer',
        'Schroeder',
        'Block',
        'Mayoral',
        'Fleishman',
        'Roberie',
        'Latson',
        'Lupo',
        'Motsinger',
        'Drews',
        'Coby',
        'Redner',
        'Culton',
        'Howe',
        'Stoval',
        'Michaud',
        'Mote',
        'Menjivar',
        'Wiers',
        'Paris',
        'Grisby',
        'Noren',
        'Damron',
        'Kazmierczak',
        'Haslett',
        'Guillemette',
        'Buresh',
        'Center',
        'Kucera',
        'Catt',
        'Badon',
        'Grumbles',
        'Antes',
        'Byron',
        'Volkman',
        'Klemp',
        'Pekar',
        'Pecora',
        'Schewe',
        'Ramage',
    );

    $name = $firstname[rand ( 0 , count($firstname) -1)];
    $name .= ' ';
    $name .= $lastname[rand ( 0 , count($lastname) -1)];

    return $name;
}

function randomShop() {
    $shopName = array(
        'Spice Delight',
        'Zesty Thai',
        'Thai Bistro',
        'Lotus Thai',
        'Taste Fusion',
        'Flavor Haven',
        'Siam Spice',
        'Thai Orchid',
        'Lemongrass Grill',
        'Chili Pepper',
        'Thai Street',
        'Thai Elephant',
        'Thai Basil',
        'Sawasdee Thai',
        'Tuk Tuk Thai',
        'Bangkok Bites',
        'Thai Smile',
        'Rice & Spice',
        'Thai Express',
        'Thai Sizzle',
        'Thai Spicebox',
        'Thai Kitchen',
        'Thai Delights',
        'Thai Oasis',
        'Thai Charm',
        'Relaxing Touch',
        'Healing Hands',
        'Tranquil Retreat',
        'Harmony Haven',
        'Serenity Spa',
        'Thai Bliss',
        'Zen Garden',
        'Lotus Wellness',
        'Peaceful Oasis',
        'Silk Serenade',
        'Blissful Balance',
        'Gentle Palms',
        'Golden Thai',
        'Euphoria Massage',
        'Thai Tranquility',
        'Soothing Siam',
    );

    return $shopName[rand ( 0 , count($shopName) -1)];

}

?>