<?php
    function fetchApi($url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode(new stdClass()),
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    $base = 'https://' . ($_GET['domain'] ?? 'staging.core.wooshfood.com');

    $dataCompanyInfo       = fetchApi($base . '/api/get_company_info');
    $dataProductCategories = fetchApi($base . '/api/get_website_products_category');
    $dataProduct           = fetchApi($base . '/api/get_website_products');

    $companyInfo       = $dataCompanyInfo['result'][0] ?? [];
    $productCategories = $dataProductCategories['result']['categories'] ?? [];
    $products          = $dataProduct['result']['products'] ?? [];

    // กรอง products ที่มี category เท่านั้น
    $products = array_filter($products, function($p) {
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

?>
<html>
<head>
    <link rel="stylesheet" href="shopDetails.css">
</head>
<body>

    <div class="shop-info">
    <h1><?= htmlspecialchars($companyInfo['name'] ?? 'Shop Name') ?></h1>
    <p><?= htmlspecialchars($companyInfo['country_name'] ?? '') ?><?php if (!empty($companyInfo['state_name'])): ?> • <?= htmlspecialchars($companyInfo['state_name']) ?><?php endif; ?></p>
    <?php if (!empty($companyInfo['zip']) || !empty($companyInfo['phone_code'])): ?>
    <p><?php if (!empty($companyInfo['zip'])): ?>ZIP <?= htmlspecialchars($companyInfo['zip']) ?><?php endif; ?><?php if (!empty($companyInfo['zip']) && !empty($companyInfo['phone_code'])): ?> | <?php endif; ?><?php if (!empty($companyInfo['phone_code'])): ?>+<?= htmlspecialchars($companyInfo['phone_code']) ?><?php endif; ?></p>
    <?php endif; ?>
    </div>

    <div class="product-categories">
        <h4 class="cat-pill active" data-id="all">All</h4>
        <?php foreach ($productCategories as $c): ?>
            <?php
                $catId = (int)$c['id'];
                $count = $categoryCounts[$catId] ?? 0;
                $disabledClass = $count === 0 ? ' disabled' : '';
            ?>
            <h4 class="cat-pill<?= $disabledClass ?>" data-id="<?= $catId ?>"><?= htmlspecialchars($c['name']) ?> (<?= $count ?>)</h4>
        <?php endforeach; ?>
    </div>

    <div class="product-grid">
    <?php foreach ($products as $p): ?>

    <?php
    $image = !empty($p['image_url']) ? $p['image_url'] : '/assets/no-image.png';
    $categoryName = $p['product_category_id'][0]['name'] ?? 'Uncategorized';
    $categoryId = (int)($p['product_category_id'][0]['id'] ?? 0);
    ?>

    <div class="product-card" data-category-id="<?= $categoryId ?>">

        <?php if ($p['is_sold_out']): ?>
            <div class="sold-out">Sold Out</div>
        <?php endif; ?>

        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($p['name']) ?>">

        <h3><?= htmlspecialchars($p['name']) ?></h3>

        <div class="price">$<?= number_format($p['list_price'],2) ?></div>

        <div class="card-footer">
            <div class="badge"><?= htmlspecialchars($categoryName) ?></div>
            <?php if (!$p['is_sold_out']): ?>
                <button class="add-btn">Add to Cart</button>
            <?php endif; ?>
        </div>

    </div>

    <?php endforeach; ?>
    </div>

<script>
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