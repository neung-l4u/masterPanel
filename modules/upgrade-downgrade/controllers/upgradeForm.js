let projects = []; // เก็บร้านทั้งหมดของ country ที่เลือก

async function fetchWithFallback(country) {
    try {
        const controller = new AbortController();
        setTimeout(() => controller.abort(), 5000); // timeout 5 วิ

        const res = await fetch(
            `http://localhost/masterPanel/api/monday/selectProjectCountry/index.php?country=${country}`,
            { signal: controller.signal }
        );

        if (!res.ok) throw new Error('API not ok');

        const data = await res.json();
        if (!Array.isArray(data) || data.length === 0) {
            throw new Error('Empty API data');
        }

        console.log('✅ Loaded from API');
        return data;

    } catch (err) {
        console.warn('⚠️ API failed, fallback to file', err.message);

        try {
            const fileRes = await fetch(
                `http://localhost/masterPanel/api/monday/selectProjectCountry/index.php?country=${country}`
            );

            if (!fileRes.ok) throw new Error('File fallback not ok');

            const fileData = await fileRes.json();
            console.log('📁 Loaded from file');
            return fileData;
        } catch (fileErr) {
            console.error('❌ Both API and file fallback failed', fileErr.message);
            return [];
        }
    }
}

// ===== 1. เมื่อเลือกประเทศ =====
document.getElementById('projectCountry').addEventListener('change', async function () {
    const country = this.value;
    if (!country) return;

    projects = await fetchWithFallback(country);
});

// ===== 2. autocomplete ตอนพิมพ์ shop_id =====
const shopInput = document.getElementById('shop_id');
const suggestBox = document.getElementById('shopSuggest');

shopInput.addEventListener('input', function () {
    const keyword = this.value.toLowerCase();
    suggestBox.innerHTML = '';

    if (!keyword || projects.length === 0) return;

    const matches = projects.filter(p =>
        p.shop_name.toLowerCase().includes(keyword) ||
        p.shop_id.toLowerCase().includes(keyword)
    ).slice(0, 5); // จำกัด 5 รายการ

    matches.forEach(p => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.textContent = `${p.shop_name} (${p.shop_id})`;

        item.onclick = () => selectShop(p);
        suggestBox.appendChild(item);
    });
});

// ===== 3. เลือกร้าน → fill ข้อมูล =====
function selectShop(p) {
    shopInput.value = p.shop_id;
    suggestBox.innerHTML = '';

    document.querySelector('[name="shop_name"]').value = p.shop_name;
    document.querySelector('[name="shop_type"]').value = p.shop_type;
    document.querySelector('[name="owner_name"]').value = p.owner_name;
    document.querySelector('[name="phone"]').value = p.phone;
    document.querySelector('[name="country"]').value = p.country;
}

// ===== 4. คลิกนอก → ปิด dropdown =====
document.addEventListener('click', e => {
    if (!shopInput.contains(e.target)) {
        suggestBox.innerHTML = '';
    }
});
