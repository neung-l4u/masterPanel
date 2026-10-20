<?php
/**
 * Image Resizer — crop & resize an uploaded image to one of four fixed presets.
 * Rendered inside the master panel layout (sidebar/navbar provided by main.php).
 * Uses Cropper.js (scoped to #imgResizerApp) for aspect-locked cropping with
 * drag/zoom position control. Exports a pixel-exact PNG per preset.
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-crop mr-2"></i>Image Resizer</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=userTools">User Tools</a></li>
                    <li class="breadcrumb-item active">Image Resizer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Tailwind + libs (scoped usage inside #imgResizerApp) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/lucide@latest"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

        <style>
            #imgResizerApp { font-family: 'Sarabun', sans-serif; }
            #imgResizerApp .cropper-container { margin: 0 auto; }
            /* keep the cropped image preview inside its box */
            #imgResizerApp .crop-stage { max-height: 62vh; }
            #imgResizerApp .crop-stage img { max-width: 100%; display: block; }
        </style>

        <div id="imgResizerApp" class="text-slate-800">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Controls -->
                <div class="lg:col-span-4 flex flex-col gap-5">

                    <!-- 1. Upload -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">1</span>
                            <h2 class="font-bold text-slate-800">อัปโหลดรูป</h2>
                        </div>
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-xl p-5 bg-slate-50 hover:bg-slate-100 cursor-pointer transition">
                            <input type="file" id="imgUpload" accept="image/*" class="hidden">
                            <i data-lucide="image-plus" class="w-8 h-8 text-slate-400 mb-1"></i>
                            <span class="text-xs font-bold text-slate-700">คลิกเพื่ออัปโหลดรูป</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">รองรับไฟล์รูปภาพทั้งหมด</span>
                        </label>
                    </div>

                    <!-- 2. Preset -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">2</span>
                            <h2 class="font-bold text-slate-800">เลือกขนาด (พรีเซ็ต)</h2>
                        </div>
                        <div id="presetGrid" class="grid grid-cols-2 gap-2"></div>

                        <!-- Custom size -->
                        <div id="customBox" class="hidden pt-3 border-t border-slate-100 space-y-2">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">ขนาดกำหนดเอง (px)</span>
                            <div class="flex items-center gap-2">
                                <input type="number" id="customW" min="1" max="10000" placeholder="กว้าง"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                                <span class="text-slate-400 font-bold">×</span>
                                <input type="number" id="customH" min="1" max="10000" placeholder="สูง"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Adjust -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">3</span>
                            <h2 class="font-bold text-slate-800">ปรับตำแหน่ง</h2>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" data-act="zoomIn"  class="ir-tool"><i data-lucide="zoom-in" class="w-4 h-4"></i></button>
                            <button type="button" data-act="zoomOut" class="ir-tool"><i data-lucide="zoom-out" class="w-4 h-4"></i></button>
                            <button type="button" data-act="rotateL" class="ir-tool"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></button>
                            <button type="button" data-act="rotateR" class="ir-tool"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>
                            <button type="button" data-act="flipH"   class="ir-tool"><i data-lucide="flip-horizontal" class="w-4 h-4"></i></button>
                            <button type="button" data-act="flipV"   class="ir-tool"><i data-lucide="flip-vertical" class="w-4 h-4"></i></button>
                            <button type="button" data-act="reset"   class="ir-tool col-span-2 text-xs font-bold">รีเซ็ต</button>
                        </div>
                        <p class="text-[10px] text-slate-400">ลากรูปในกรอบเพื่อเลื่อนตำแหน่ง • สกอลล์เพื่อซูม</p>
                    </div>

                    <!-- 4. Export -->
                    <button id="btnExport" disabled
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl py-3 flex items-center justify-center gap-2 transition">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        <span>ดาวน์โหลด PNG</span>
                    </button>
                </div>

                <!-- Right: Crop stage -->
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="font-bold text-slate-800">ตัวอย่าง / ครอป</h2>
                            <span id="presetLabel" class="text-xs font-bold text-blue-600 bg-blue-50 rounded-full px-3 py-1"></span>
                        </div>
                        <div class="crop-stage flex-1 flex items-center justify-center bg-slate-50 rounded-xl overflow-hidden">
                            <div id="cropEmpty" class="text-center text-slate-400 py-16">
                                <i data-lucide="image" class="w-10 h-10 mx-auto mb-2"></i>
                                <p class="text-sm font-bold">อัปโหลดรูปเพื่อเริ่มครอป</p>
                            </div>
                            <img id="cropImg" class="hidden" style="display:none" alt="">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <style>
            #imgResizerApp .ir-tool {
                display: flex; align-items: center; justify-content: center;
                gap: .25rem; padding: .5rem; border-radius: .625rem;
                background: #f1f5f9; color: #334155;
                border: 1px solid #e2e8f0; transition: background .12s;
            }
            #imgResizerApp .ir-tool:hover { background: #e2e8f0; }
            #imgResizerApp .ir-preset {
                text-align: left; padding: .625rem .75rem; border-radius: .75rem;
                background: #f8fafc; border: 2px solid #e2e8f0; transition: all .12s;
            }
            #imgResizerApp .ir-preset:hover { border-color: #93c5fd; }
            #imgResizerApp .ir-preset.active { border-color: #2563eb; background: #eff6ff; }
            #imgResizerApp .ir-preset .t { font-weight: 700; font-size: .8125rem; color: #1e293b; }
            #imgResizerApp .ir-preset .d { font-size: .6875rem; color: #64748b; }
        </style>

        <script>
        (function () {
            const PRESETS = [
                { key: 'logo',     name: 'Logo',              w: 300,  h: 100 },
                { key: 'header',   name: 'Header',            w: 1920, h: 500 },
                { key: 'icon',     name: 'Logo icon / Favicon', w: 350,  h: 350 },
                { key: 'custom',   name: 'กำหนดเอง',           w: 0,    h: 0, custom: true },
            ];

            const root       = document.getElementById('imgResizerApp');
            const grid       = document.getElementById('presetGrid');
            const upload     = document.getElementById('imgUpload');
            const img        = document.getElementById('cropImg');
            const empty      = document.getElementById('cropEmpty');
            const btnExport  = document.getElementById('btnExport');
            const label      = document.getElementById('presetLabel');
            const customBox  = document.getElementById('customBox');
            const customW    = document.getElementById('customW');
            const customH    = document.getElementById('customH');

            let cropper  = null;
            let current  = PRESETS[0];
            let hasImage = false;

            // Effective output size (resolves custom W×H when the custom preset is active)
            function outSize() {
                if (current.custom) {
                    return {
                        w: Math.max(1, parseInt(customW.value, 10) || 0),
                        h: Math.max(1, parseInt(customH.value, 10) || 0),
                    };
                }
                return { w: current.w, h: current.h };
            }
            function customReady() {
                return (parseInt(customW.value, 10) > 0) && (parseInt(customH.value, 10) > 0);
            }

            function refreshIcons() {
                if (window.lucide) window.lucide.createIcons();
            }

            // Build preset buttons
            PRESETS.forEach(function (p, i) {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'ir-preset' + (i === 0 ? ' active' : '');
                b.dataset.key = p.key;
                const detail = p.custom ? 'W × H เอง' : (p.w + ' × ' + p.h + ' px');
                b.innerHTML = '<div class="t">' + p.name + '</div><div class="d">' + detail + '</div>';
                b.addEventListener('click', function () { selectPreset(p.key); });
                grid.appendChild(b);
            });

            function applyAspect() {
                if (!cropper) return;
                const s = outSize();
                if (s.w > 0 && s.h > 0) cropper.setAspectRatio(s.w / s.h);
            }

            function selectPreset(key) {
                current = PRESETS.find(function (p) { return p.key === key; }) || PRESETS[0];
                grid.querySelectorAll('.ir-preset').forEach(function (el) {
                    el.classList.toggle('active', el.dataset.key === key);
                });
                customBox.classList.toggle('hidden', !current.custom);
                const s = outSize();
                label.textContent = current.custom && !customReady()
                    ? current.name
                    : current.name + ' · ' + s.w + '×' + s.h;
                applyAspect();
            }

            function initCropper() {
                if (cropper) { cropper.destroy(); cropper = null; }
                const s = outSize();
                cropper = new Cropper(img, {
                    aspectRatio: (s.w > 0 && s.h > 0) ? (s.w / s.h) : NaN,
                    viewMode: 1,
                    autoCropArea: 1,
                    dragMode: 'move',
                    background: true,
                    responsive: true,
                });
            }

            // Custom size inputs → live aspect + label update
            [customW, customH].forEach(function (inp) {
                inp.addEventListener('input', function () {
                    if (!current.custom) return;
                    const s = outSize();
                    label.textContent = customReady()
                        ? current.name + ' · ' + s.w + '×' + s.h
                        : current.name;
                    applyAspect();
                });
            });

            upload.addEventListener('change', function (e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (ev) {
                    img.src = ev.target.result;
                    img.classList.remove('hidden');
                    img.style.display = '';
                    empty.classList.add('hidden');
                    hasImage = true;
                    btnExport.disabled = false;
                    initCropper();
                };
                reader.readAsDataURL(file);
            });

            // Adjust tools
            root.querySelectorAll('[data-act]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!cropper) return;
                    switch (btn.dataset.act) {
                        case 'zoomIn':  cropper.zoom(0.1); break;
                        case 'zoomOut': cropper.zoom(-0.1); break;
                        case 'rotateL': cropper.rotate(-90); break;
                        case 'rotateR': cropper.rotate(90); break;
                        case 'flipH':   cropper.scaleX(-(cropper.getData().scaleX || 1)); break;
                        case 'flipV':   cropper.scaleY(-(cropper.getData().scaleY || 1)); break;
                        case 'reset':   cropper.reset(); break;
                    }
                });
            });

            btnExport.addEventListener('click', function () {
                if (!cropper) return;
                if (current.custom && !customReady()) {
                    alert('กรุณากรอกขนาดกว้างและสูง');
                    return;
                }
                const s = outSize();
                const canvas = cropper.getCroppedCanvas({
                    width: s.w,
                    height: s.h,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
                if (!canvas) return;
                canvas.toBlob(function (blob) {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = current.key + '-' + s.w + 'x' + s.h + '.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }, 'image/png');
            });

            // init
            selectPreset(PRESETS[0].key);
            refreshIcons();
        })();
        </script>

    </div>
</div>
