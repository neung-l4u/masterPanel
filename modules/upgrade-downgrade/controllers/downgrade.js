let projects = [];
let loadingProjects = false;

// ================= FETCH WITH FALLBACK =================
async function fetchWithFallback(country) {
    loadingProjects = true;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000);

    try {
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
        return normalizeProjects(data);

    } catch (err) {
        console.warn('⚠️ API failed, fallback to file:', err.message);

        const fileRes = await fetch(
            `http://localhost/masterPanel/api/monday/selectProjectCountry/file/${country}`
        );

        if (!fileRes.ok) {
            console.error('❌ File fallback failed');
            return [];
        }

        const fileData = await fileRes.json();
        console.log('📁 Loaded from file');
        return normalizeProjects(fileData);

    } finally {
        clearTimeout(timeoutId);
        loadingProjects = false;
    }
}

// ================= NORMALIZE DATA =================
function normalizeProjects(data) {
    return data.map(p => ({
        shop_id: p.shop_id ?? '',
        shop_name: p.shop_name ?? '',
        shop_type: p.shop_type ?? '',
        owner_name: p.owner_name ?? '',
        phone: p.phone ?? '',
        country: p.country ?? ''
    }));
}

// ================= COUNTRY CHANGE =================
document.getElementById('projectCountry')
    .addEventListener('change', async function () {

    const country = this.value;
    if (!country) return;

    projects = [];
    document.getElementById('shopSuggest').innerHTML = '';

    projects = await fetchWithFallback(country);
});

// ================= AUTOCOMPLETE =================
const shopInput = document.getElementById('shop_id');
const suggestBox = document.getElementById('shopSuggest');

shopInput.addEventListener('input', function () {
    const keyword = this.value.trim().toLowerCase();
    suggestBox.innerHTML = '';

    if (!keyword || loadingProjects || projects.length === 0) return;

    const matches = projects
        .filter(p =>
            p.shop_name.toLowerCase().includes(keyword) ||
            p.shop_id.toLowerCase().includes(keyword)
        )
        .slice(0, 5);

    matches.forEach(p => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.textContent = `${p.shop_name} (${p.shop_id})`;

        item.addEventListener('mousedown', () => selectShop(p));
        suggestBox.appendChild(item);
    });
});

// ================= SELECT SHOP =================
function selectShop(p) {
    shopInput.value = p.shop_id;
    suggestBox.innerHTML = '';

    setValue('shop_name', p.shop_name);
    setValue('shop_type', p.shop_type);
    setValue('owner_name', p.owner_name);
    setValue('phone', p.phone);
    setValue('country', p.country);
}

function setValue(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.value = value;
}

// ================= CLICK OUTSIDE =================
document.addEventListener('click', e => {
    if (!shopInput.contains(e.target) && !suggestBox.contains(e.target)) {
        suggestBox.innerHTML = '';
    }
});
