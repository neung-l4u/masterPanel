<?php
function fetchApi($url, $body = null)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $body ? json_encode($body) : json_encode(new stdClass()),
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function fetchAllProducts($base)
{
    $allProducts = [];
    $page = 1;
    $totalPages = 1;

    do {
        $data = fetchApi($base . '/api/get_website_products', ["page" => $page, "limit" => 500]);
        $totalPages = $data['result']['total_pages'] ?? 1;
        $products = $data['result']['products'] ?? [];
        $allProducts = array_merge($allProducts, $products);
        $page++;
    } while ($page <= $totalPages);

    return $allProducts;
}

$base = 'https://' . ($_GET['domain'] ?? 'staging.core.wooshfood.com');
$domainPrefix = explode('.', $_GET['domain'] ?? 'staging')[0];
$shopUrl = 'https://' . $domainPrefix . '.website.localforyouorders.com/';

$dataCompanyInfo       = fetchApi($base . '/api/get_company_info');
$dataProductCategories = fetchApi($base . '/api/get_website_products_category');
$dataWebsiteSettings   = fetchApi($base . '/api/website_settings');

$companyInfo       = $dataCompanyInfo['result'][0] ?? [];
$productCategories = $dataProductCategories['result']['categories'] ?? [];
$websiteSettings   = $dataWebsiteSettings['result'] ?? [];
$firstOpening      = $websiteSettings['first_opening'] ?? [];
$secondOpening     = $websiteSettings['second_opening'] ?? [];
$isSecondOpening   = !empty($websiteSettings['is_second_opening']);

// index opening by day name
$firstOpeningByDay  = [];
foreach ($firstOpening as $o) {
    if (!empty($o['day'])) $firstOpeningByDay[strtolower($o['day'])] = $o;
}
$secondOpeningByDay = [];
foreach ($secondOpening as $o) {
    if (!empty($o['day'])) $secondOpeningByDay[strtolower($o['day'])] = $o;
}

function formatOpeningHours($day, $firstOpeningByDay, $secondOpeningByDay, $isSecondOpening)
{
    $day = strtolower($day);
    $first = $firstOpeningByDay[$day] ?? null;
    $second = $secondOpeningByDay[$day] ?? null;

    if (!$first) return 'Closed';
    if (!empty($first['closed'])) return 'Closed';

    $from = $first['from'] ?? '';
    $to   = $first['to'] ?? '';
    if ($from === '' || $to === '' || ($from === '00:00' && $to === '00:00')) return 'Closed';

    $text = $from . ' - ' . $to;
    if ($isSecondOpening && $second && empty($second['closed'])) {
        $sf = $second['from'] ?? '';
        $st = $second['to'] ?? '';
        if ($sf !== '' && $st !== '' && !($sf === '00:00' && $st === '00:00')) {
            $text .= ', ' . $sf . ' - ' . $st;
        }
    }
    return $text;
}

$products          = fetchAllProducts($base);

// กรอง products ที่มี category เท่านั้น
$products = array_filter($products, function ($p) {
    return !empty($p['product_category_id']);
});

// นับจำนวน products ในแต่ละ category
$categoryCounts = [];
foreach ($products as $p) {
    if (!empty($p['product_category_id'][0]['id'])) {
        $catId = (int)$p['product_category_id'][0]['id'];
        $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
    }
}

// สุ่ม products ที่มีรูปสำหรับ showcase
$productsWithImage = array_values(array_filter($products, function ($p) {
    return !empty($p['image_url']);
}));
shuffle($productsWithImage);
$showcaseProducts = array_slice($productsWithImage, 0, min(6, count($productsWithImage)));

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($companyInfo['name'] ?? 'Shop Details') ?></title>
    <link rel="stylesheet" href="shopDetails.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,600;1,700&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    $shopName = trim($companyInfo['name'] ?? 'Shop');
    $shopInitial = function_exists('mb_substr') ? mb_substr($shopName, 0, 1, 'UTF-8') : substr($shopName, 0, 1);
    $shopInitial = $shopInitial !== '' ? $shopInitial : 'S';
    ?>
    <header class="site-header">
        <a href="#top" class="site-brand" aria-label="<?= htmlspecialchars($shopName) ?>">
            <span class="site-brand-mark"><?= htmlspecialchars(strtoupper($shopInitial)) ?></span>
            <span class="site-brand-name"><?= htmlspecialchars($shopName) ?></span>
        </a>
        <nav class="site-nav" aria-label="Primary navigation">
            <a href="#top">Home</a>
            <a href="#menu">Menu</a>
            <a href="#about">About</a>
            <a href="#hours">Hours</a>
        </nav>
        <a href="<?= htmlspecialchars($shopUrl) ?>" class="site-header-cta">Order</a>
    </header>

    <!-- Hero Slider -->
    <?php
    $heroSlides = array_slice($showcaseProducts, 0, min(3, count($showcaseProducts)));
    ?>
    <header class="hero" id="top">
        <?php if (!empty($heroSlides)): ?>
            <div class="hero-slider">
                <?php foreach ($heroSlides as $i => $slide): ?>
                    <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>">
                        <div class="hero-slide-bg">
                            <img src="<?= htmlspecialchars($slide['image_url']) ?>" alt="<?= htmlspecialchars($slide['name']) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="hero-subtitle">Welcome to</p>
            <h1 class="hero-title" style="padding-bottom: 20px;"><?= htmlspecialchars($companyInfo['name'] ?? 'Shop Name') ?></h1>
            <!-- <h1 class="hero-title-accent">Restaurant</h1> -->
            <div class="hero-meta">
                <span class="hero-meta-item">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <?= htmlspecialchars(trim(($companyInfo['city'] ?? '') . (!empty($companyInfo['city']) && !empty($companyInfo['state_name']) ? ', ' : '') . ($companyInfo['state_name'] ?? '') . (!empty($companyInfo['country_name']) ? ' ' . $companyInfo['country_name'] : '')) ?: 'Little Italy, New York') ?>
                </span>
                <span class="hero-meta-item">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 6v6l4 2" />
                    </svg>
                    <?= htmlspecialchars(formatOpeningHours(strtolower(date('l')), $firstOpeningByDay, $secondOpeningByDay, $isSecondOpening)) ?>
                </span>
            </div>
            <!-- <?php if (!empty($companyInfo['zip']) || !empty($companyInfo['phone_code'])): ?>
            <div class="hero-meta-sub">
                <?php if (!empty($companyInfo['zip'])): ?>ZIP <?= htmlspecialchars($companyInfo['zip']) ?><?php endif; ?>
                <?php if (!empty($companyInfo['zip']) && !empty($companyInfo['phone_code'])): ?> &nbsp;|&nbsp; <?php endif; ?>
                <?php if (!empty($companyInfo['phone_code'])): ?>+<?= htmlspecialchars($companyInfo['phone_code']) ?><?php endif; ?>
            </div>
            <?php endif; ?> -->
            <a href="<?= htmlspecialchars($shopUrl) ?>" class="hero-cta">Order Now</a>
        </div>
        <?php if (count($heroSlides) > 1): ?>
            <div class="hero-nav">
                <button class="hero-nav-btn hero-prev" aria-label="Previous">
                    <svg width="40" height="55" viewBox="0 0 54 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.6309 25.4001L25.1406 16.8902C25.4463 16.5333 25.9835 16.4917 26.3405 16.7974C26.6974 17.1031 26.739 17.6403 26.4333 17.9973C26.4048 18.0306 26.3738 18.0617 26.3405 18.0901L19.2859 25.1532H52.9762C53.4461 25.1532 53.8271 25.5343 53.8271 26.0043C53.8271 26.4743 53.4461 26.8552 52.9762 26.8552H19.2859L26.3405 33.9098C26.6974 34.2155 26.739 34.7527 26.4333 35.1097C26.1275 35.4666 25.5904 35.5082 25.2334 35.2025C25.2001 35.174 25.1691 35.1429 25.1406 35.1097L16.6308 26.5999C16.3009 26.2681 16.3009 25.732 16.6309 25.4001Z" fill="#FFD28D" />
                    </svg>
                </button>
                <button class="hero-nav-btn hero-next" aria-label="Next">
                    <svg width="40" height="55" viewBox="0 0 55 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M38.0234 25.4001L29.5137 16.8902C29.208 16.5333 28.6708 16.4917 28.3138 16.7974C27.9569 17.1031 27.9153 17.6403 28.221 17.9973C28.2495 18.0306 28.2805 18.0617 28.3138 18.0901L35.3684 25.1532H1.6781C1.20816 25.1532 0.827148 25.5343 0.827148 26.0043C0.827148 26.4743 1.20816 26.8552 1.6781 26.8552H35.3684L28.3138 33.9098C27.9569 34.2155 27.9153 34.7527 28.221 35.1097C28.5268 35.4666 29.0639 35.5082 29.4209 35.2025C29.4542 35.174 29.4852 35.1429 29.5137 35.1097L38.0235 26.5999C38.3534 26.2681 38.3534 25.732 38.0234 25.4001Z" fill="#FFD28D" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </header>

    <!-- Scrolling Marquee -->
    <div class="marquee-wrap">
        <div class="marquee-track">
            <span><?= htmlspecialchars($companyInfo['name'] ?? 'Shop') ?> &nbsp;&bull;&nbsp; Fresh &amp; Delicious &nbsp;&bull;&nbsp; Order Now &nbsp;&bull;&nbsp; <?= htmlspecialchars($companyInfo['name'] ?? 'Shop') ?> &nbsp;&bull;&nbsp; Fresh &amp; Delicious &nbsp;&bull;&nbsp; Order Now &nbsp;&bull;&nbsp;</span>
            <span><?= htmlspecialchars($companyInfo['name'] ?? 'Shop') ?> &nbsp;&bull;&nbsp; Fresh &amp; Delicious &nbsp;&bull;&nbsp; Order Now &nbsp;&bull;&nbsp; <?= htmlspecialchars($companyInfo['name'] ?? 'Shop') ?> &nbsp;&bull;&nbsp; Fresh &amp; Delicious &nbsp;&bull;&nbsp; Order Now &nbsp;&bull;&nbsp;</span>
        </div>
    </div>

    <!-- Food Showcase -->
    <!-- <?php if (!empty($showcaseProducts)): ?>
    <section class="section-showcase">
        <div class="container">
            <div class="section-heading">
                <p class="section-subtitle">Food Items</p>
                <h2 class="section-title">Food Showcase</h2>
            </div>
        </div>
        <div class="showcase-slider">
            <div class="showcase-track">
                <?php foreach ($showcaseProducts as $sp): ?>
                <div class="showcase-card">
                    <img src="<?= htmlspecialchars($sp['image_url']) ?>" alt="<?= htmlspecialchars($sp['name']) ?>">
                    <div class="showcase-card-info">
                        <h5 class="showcase-card-title"><?= htmlspecialchars($sp['name']) ?></h5>
                        <div class="showcase-card-subtitle"><?= htmlspecialchars($sp['product_category_id'][0]['name'] ?? '') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?> -->

    <!-- Category Filter -->
    <section class="section-menu" id="menu">
        <div class="container">
            <div class="section-heading">
                <p class="section-subtitle">Special Selection</p>
                <h2 class="section-title">Food Menu</h2>
            </div>

            <div class="product-categories">
                <button class="cat-pill active" data-id="all">All</button>
                <?php foreach ($productCategories as $c): ?>
                    <?php
                    $catId = (int)$c['id'];
                    $count = $categoryCounts[$catId] ?? 0;
                    if ($count === 0) continue;
                    ?>
                    <button class="cat-pill" data-id="<?= $catId ?>"><?= htmlspecialchars($c['name']) ?> <span class="pill-count">(<?= $count ?>)</span></button>
                <?php endforeach; ?>
            </div>

            <!-- Product Grid -->
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <?php
                    $image = !empty($p['image_url']) ? $p['image_url'] : 'images/no-image-white.svg';
                    $categoryName = $p['product_category_id'][0]['name'] ?? 'Uncategorized';
                    $categoryId = (int)($p['product_category_id'][0]['id'] ?? 0);
                    $productDesc = trim($p['description'] ?? $p['short_description'] ?? '');
                    if ($productDesc === '') {
                        $productDesc = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Handmade with fresh ingredients and served with house flavors.';
                    }
                    ?>

                    <div class="product-card" data-category-id="<?= $categoryId ?>">
                        <div class="card-img-wrap">
                            <div class="badge"><?= htmlspecialchars($categoryName) ?></div>
                            <?php if ($p['is_sold_out']): ?>
                                <div class="sold-out">Sold Out</div>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <h3 class="card-title"><?= htmlspecialchars($p['name']) ?></h3>
                                <div class="card-price">$<?= number_format($p['list_price'], 2) ?></div>
                            </div>
                            <p class="card-desc"><?= htmlspecialchars($productDesc) ?></p>
                            <div class="card-hr"></div>
                            <div class="card-bottom">
                                <div class="dish-meta">
                                    <span>
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                                        </svg>
                                        420 kcal
                                    </span>
                                    <span>
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 6v6l4 2" />
                                        </svg>
                                        15 min
                                    </span>
                                </div>
                                <?php if (!$p['is_sold_out']): ?>
                                    <a href="<?= htmlspecialchars($shopUrl) ?>" class="add-btn">Add to Cart</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- About Section (Flex) -->
    <?php
    $aboutBgImage = !empty($showcaseProducts[0]['image_url']) ? $showcaseProducts[0]['image_url'] : '';
    ?>
    <section class="section-about" id="about">
        <div class="about-flex">

            <div class="about-text-side">
                <h2 class="about-title"><?= htmlspecialchars($companyInfo['name'] ?? 'Our Restaurant') ?>
                    <br><span class="about-title-accent">Discover Our Menu</span>
                </h2>
                <p class="about-text">Explore our curated selection of <?= count($products) ?> dishes across <?= count($productCategories) ?> categories. Every item is crafted with care and delivered fresh to your table.<br>
                    Quality ingredients, authentic recipes, and a passion for great food — that's what we bring to every dish.
                </p>
                <p class="about-text"></p>
                <a href="<?= htmlspecialchars($shopUrl) ?>" class="about-btn">View Full Menu</a>
            </div>
            <div class="about-img-side">
                <?php if ($aboutBgImage): ?>
                    <img src="<?= htmlspecialchars($aboutBgImage) ?>" alt="About">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Opening Hours -->
    <?php
    $email = !empty($companyInfo['email']) ? $companyInfo['email'] : 'hello@example.com';
    $phone = !empty($companyInfo['phone']) ? $companyInfo['phone'] : '(000) 000-0000';
    $addrLine1 = trim($companyInfo['street'] ?? '');
    if (!empty($companyInfo['street2'])) {
        $addrLine1 .= ($addrLine1 !== '' ? ' ' : '') . $companyInfo['street2'];
    }
    if ($addrLine1 === '') $addrLine1 = '248 Mulberry Street';
    $addrLine2 = trim(($companyInfo['city'] ?? '') . (!empty($companyInfo['state_name']) ? ', ' . $companyInfo['state_name'] : '') . (!empty($companyInfo['zip']) ? ' ' . $companyInfo['zip'] : ''));
    if ($addrLine2 === '') $addrLine2 = 'New York, NY 10012';
    $todayKey = strtolower(date('l'));
    ?>
    <section class="section-opening" id="hours">
        <div class="container">
            <div class="opening-grid">
                <div class="opening-card hours-card">
                    <h2 class="opening-card-title">Opening Hours</h2>
                    <div class="hours-list">
                        <?php
                        $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        foreach ($daysOrder as $d):
                            $hours = formatOpeningHours($d, $firstOpeningByDay, $secondOpeningByDay, $isSecondOpening);
                            $isToday = ($d === $todayKey);
                            $isClosed = ($hours === 'Closed');
                        ?>
                            <div class="hours-row<?= $isToday ? ' is-today' : '' ?>">
                                <div class="hours-day-wrap">
                                    <span class="hours-day"><?= ucfirst($d) ?></span>
                                    <?php if ($isToday && !$isClosed): ?>
                                        <span class="open-now-pill"><span class="open-now-dot"></span>Open Now</span>
                                    <?php endif; ?>
                                </div>
                                <span class="hours-time<?= $isClosed ? ' is-closed' : '' ?>"><?= htmlspecialchars($hours) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title">Address</h3>
                            <p class="info-text"><?= htmlspecialchars($addrLine1) ?><br><?= htmlspecialchars($addrLine2) ?></p>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title">Phone</h3>
                            <p class="info-text"><a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a></p>
                            <span class="info-meta">Reservations &amp; takeout</span>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title">Email</h3>
                            <p class="info-text"><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></p>
                            <span class="info-meta">Private events &amp; press</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Map -->
    <!-- <section class="section-map">
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3222.7108225621614!2d-115.19547672419242!3d36.124902272447244!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c8c78f14989491%3A0x13655cf899255574!2sLullabar%20Thai%20Fusion%20%26%20Izakaya!5e0!3m2!1sen!2sth!4v1771483142031!5m2!1sen!2sth" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section> -->

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-main">
                <div class="footer-info">
                    <div class="footer-email-menu">
                        <div class="footer-email">
                            <?php if (!empty($companyInfo['name'] ?? 'Shop')): ?>
                                <a href="mailto:<?= htmlspecialchars($companyInfo['name'] ?? 'Shop') ?>"><?= htmlspecialchars($companyInfo['name'] ?? 'Shop') ?></a>
                            <?php else: ?>
                                <span>Thai Restaurant</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="footer-phone">
                        <?php if (!empty($companyInfo['phone'])): ?>
                            <a href="tel:<?= htmlspecialchars($companyInfo['phone']) ?>"><?= htmlspecialchars($companyInfo['phone']) ?></a>
                        <?php else: ?>
                            <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                            <span>
                            : +123456789</span>
                        <?php endif; ?>
                    </div>

                    <div class="footer-email">
                        <?php if (!empty($companyInfo['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($companyInfo['email']) ?>"><?= htmlspecialchars($companyInfo['email']) ?></a>
                        <?php else: ?>
                            <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                            <span>
                            : demo@localforyou.com</span>
                        <?php endif; ?>
                    </div>

                    <div class="footer-address">
                        <?php if (!empty($companyInfo['street']) || !empty($companyInfo['city'])): ?>
                            <p><?= htmlspecialchars(($companyInfo['street'] ?? '') . ', ' . ($companyInfo['city'] ?? '') . ' ' . ($companyInfo['state_name'] ?? '') . ' ' . ($companyInfo['zip'] ?? '')) ?></p>
                        <?php else: ?>
                            
                            <p>123 dome Street ,The Rocks, NSW 2000 ,AUSTRALIA</p>
                        <?php endif; ?>
                    </div>

                    

                    <!-- <div class="footer-btn">
                        <a href="<?= htmlspecialchars($shopUrl) ?>" class="footer-cta">Order Now</a>
                    </div> -->
                </div>
            </div>

            <div class="footer-hr-bottom"></div>
            <div class="footer-copy-section">
                <p class="footer-copy">&copy; <?= date('Y') ?> <?= htmlspecialchars($companyInfo['name'] ?? '') ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Hero Slider
        (() => {
            const slides = document.querySelectorAll('.hero-slide');
            if (slides.length <= 1) return;

            let current = 0;
            let autoTimer;

            function goTo(index) {
                slides[current].classList.remove('active');
                current = (index + slides.length) % slides.length;
                slides[current].classList.add('active');
            }

            function startAuto() {
                autoTimer = setInterval(() => goTo(current + 1), 5000);
            }

            function resetAuto() {
                clearInterval(autoTimer);
                startAuto();
            }

            const prevBtn = document.querySelector('.hero-prev');
            const nextBtn = document.querySelector('.hero-next');
            if (prevBtn) prevBtn.addEventListener('click', () => {
                goTo(current - 1);
                resetAuto();
            });
            if (nextBtn) nextBtn.addEventListener('click', () => {
                goTo(current + 1);
                resetAuto();
            });

            startAuto();
        })();

        // Category Filter
        const pills = document.querySelectorAll('.cat-pill');
        const cards = document.querySelectorAll('.product-card');

        pills.forEach(pill => {
            pill.addEventListener('click', () => {
                if (pill.classList.contains('disabled')) return;

                pills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                const id = pill.dataset.id;
                cards.forEach(card => {
                    card.style.display = (id === 'all' || card.dataset.categoryId === id) ? '' : 'none';
                });
            });
        });
    </script>
</body>

</html>