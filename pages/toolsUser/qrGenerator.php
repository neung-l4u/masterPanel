<?php
/**
 * QR Generator — In-house restaurant QR code tool (9.7 x 9.7 cm, A4 print).
 * Rendered inside the master panel layout (sidebar/navbar provided by main.php).
 * Tailwind is loaded scoped for this page's markup only.
 */

// Handle AJAX request for template images
if (isset($_GET['action']) && $_GET['action'] === 'getTemplateImages') {
    header('Content-Type: application/json');
    $imagesDir = __DIR__ . '/assets/images/';
    $images = [];
    
    if (is_dir($imagesDir)) {
        $files = scandir($imagesDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $imagesDir . $file;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']) && is_file($filePath)) {
                    $images[] = 'pages/toolsUser/assets/images/' . $file;
                }
            }
        }
    }
    
    sort($images);
    echo json_encode(['success' => true, 'images' => $images]);
    exit;
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-qr-code mr-2"></i>QR Generator</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=userTools">User Tools</a></li>
                    <li class="breadcrumb-item active">QR Generator</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Tailwind + libs (scoped usage inside #qrgenApp) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/lucide@latest"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
        <script>
            if (typeof QRCode === 'undefined') {
                const s = document.createElement('script');
                s.src = "https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js";
                document.head.appendChild(s);
            }
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        <style>
            #qrgenApp { font-family: 'Sarabun', sans-serif; }
            #qrgenApp .custom-scrollbar::-webkit-scrollbar { width: 5px; }
            #qrgenApp .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
            #qrgenApp .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
            #templateBgSelect {
                display: block !important;
                width: 100% !important;
                padding: 0.625rem 0.75rem !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 0.5rem !important;
                font-size: 0.75rem !important;
                font-weight: 600 !important;
                background-color: #ffffff !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                font-family: 'Sarabun', sans-serif !important;
                color: #1e293b !important;
                box-sizing: border-box !important;
                min-height: 38px !important;
            }
            #templateBgSelect:hover {
                border-color: #94a3b8 !important;
            }
            #templateBgSelect:focus {
                outline: none !important;
                box-shadow: 0 0 0 2px #3b82f6 !important;
                border-color: #3b82f6 !important;
            }
            #templateBgSelect option {
                padding: 0.5rem !important;
                background-color: #ffffff !important;
                color: #1e293b !important;
                font-family: 'Sarabun', sans-serif !important;
            }
            @media print {
                body > :not(#printSection) { display: none !important; }
                #printSection { display: block !important; }
            }
        </style>

        <div id="qrgenApp" class="text-slate-800">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Controls -->
                <div class="lg:col-span-5 flex flex-col gap-5">

                    <!-- 1. Background -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">1</span>
                            <h2 class="font-bold text-slate-800">ภาพพื้นหลังเทมเพลต</h2>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">เลือกพื้นหลังจากเทมเพลต</label>
                            <select id="templateBgSelect">
                                <option value="">-- เลือกพื้นหลังเทมเพลต --</option>
                            </select>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">หรืออัปโหลดรูปพื้นหลังเพิ่มเติม (เลือกทีละหลายไฟล์ได้)</span>
                            <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-xl p-4 bg-slate-50 hover:bg-slate-100 cursor-pointer transition">
                                <input type="file" id="userBgUpload" accept="image/*" multiple class="hidden">
                                <i data-lucide="image-plus" class="w-7 h-7 text-slate-400 mb-1"></i>
                                <span class="text-xs font-bold text-slate-700">คลิกเพื่ออัปโหลดไฟล์ภาพพื้นหลังเพิ่ม</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">รองรับไฟล์รูปภาพสัดส่วนจัตุรัสเท่านั้นเพื่อให้พิมพ์ตรงตำแหน่ง</span>
                            </label>
                        </div>
                        <div class="pt-3 border-t border-slate-100">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">รูปพื้นหลังที่อัปโหลด (คลิกเพื่อเลือก / ลบออกเพื่อเปลี่ยน)</span>
                            <div id="bgGalleryGrid" class="grid grid-cols-5 gap-2 max-h-48 overflow-y-auto custom-scrollbar p-1"></div>
                        </div>
                    </div>

                    <!-- 2. Content Inputs -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">2</span>
                                <h2 class="font-bold text-slate-800">ข้อมูลการ์ดแต่ละใบ</h2>
                            </div>
                            <div class="flex bg-slate-100 rounded-lg p-0.5 text-xs font-semibold">
                                <button id="tabSingle" class="px-3 py-1.5 rounded-md bg-white text-slate-800 shadow-sm transition font-bold">Manual</button>
                                <button id="tabBulk" class="px-3 py-1.5 rounded-md text-slate-500 hover:text-slate-800 transition">กลุ่ม (CSV)</button>
                            </div>
                        </div>

                        <div class="p-2.5 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-2">
                            <input type="checkbox" id="checkboxAutoUnique" checked class="mt-0.5 accent-blue-600 cursor-pointer">
                            <div class="text-[10px] text-blue-700">
                                <label for="checkboxAutoUnique" class="font-bold cursor-pointer select-none">เปิดระบบป้องกัน QR Code ซ้ำและป้องกันการแคช</label>
                                <p class="text-blue-500 mt-0.5">สุ่มต่อท้ายพารามิเตอร์ที่ URL อัตโนมัติ เพื่อให้แต่ละ QR มีลวดลายแตกต่างกันทุกครั้งและไม่มีปัญหาแคชเบราว์เซอร์</p>
                            </div>
                        </div>

                        <!-- Manual -->
                        <div id="panelSingle" class="space-y-3">
                            <div class="grid grid-cols-1 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">กรอกและเพิ่มรายการทีละใบ</span>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">ข้อความบนการ์ด (เลขโต๊ะ หรือประโยค)</label>
                                    <input type="text" id="inputTableNo" value="Table No. 1" placeholder="เช่น 01, โต๊ะ 15, VIP Room" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">หมายเหตุ (ไม่บังคับ — แสดงใต้เลขโต๊ะ)</label>
                                    <input type="text" id="inputTableNote" value="" placeholder="เช่น สแกนเพื่อสั่งอาหาร" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">URL / ข้อมูลลิงก์สำหรับสร้าง QR</label>
                                    <input type="text" id="inputQrData" value="https://demo4.localforyoucarts.com?table_id=e7gTS5f2r" placeholder="เช่น https://domain.com/menu" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <button type="button" onclick="addManualItem()" class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-1">
                                    <i data-lucide="plus" class="w-4 h-4"></i> เพิ่มลงรายการพิมพ์
                                </button>
                            </div>
                            <div id="manualStatusArea" class="hidden bg-slate-50 p-3 rounded-lg border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-700" id="manualRowReadyText">รายการ Manual 0 รายการ</span>
                                    <button onclick="clearManualList()" class="text-xs text-red-500 hover:underline font-bold">ล้างทั้งหมด</button>
                                </div>
                                <div class="max-h-36 overflow-y-auto custom-scrollbar text-[10px] text-slate-600 bg-white p-2 rounded border">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-slate-50 sticky top-0 font-bold text-[9px] text-slate-400">
                                            <tr>
                                                <th class="p-1 border-b">เลขโต๊ะ</th>
                                                <th class="p-1 border-b">ลิงก์ URL</th>
                                                <th class="p-1 border-b text-right">เครื่องมือ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="manualPreviewTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk -->
                        <div id="panelBulk" class="space-y-3 hidden">
                            <div class="p-4 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition relative flex flex-col items-center justify-center text-center cursor-pointer">
                                <input type="file" id="csvFileInput" accept=".csv" class="absolute inset-0 opacity-0 cursor-pointer">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-slate-400 mb-1"></i>
                                <span class="text-xs font-bold text-slate-700">อัปโหลดไฟล์ .CSV ของคุณ</span>
                                <span class="text-[10px] text-slate-400 mt-1">คอลัมน์แรก: เลขโต๊ะ | คอลัมน์สอง: URL</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1">
                                <span class="text-slate-500 font-medium" id="csvFileName">ยังไม่ได้เลือกไฟล์</span>
                                <button onclick="downloadSampleCSV()" class="text-blue-600 hover:underline flex items-center gap-1 font-bold">
                                    <i data-lucide="download" class="w-3 h-3"></i> โหลดตัวอย่าง CSV
                                </button>
                            </div>
                            <div id="csvStatusArea" class="hidden bg-slate-50 p-3 rounded-lg border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-700" id="csvRowReadyText">พร้อมพิมพ์ 0 รายการ</span>
                                    <button onclick="clearCSV()" class="text-xs text-red-500 hover:underline font-bold">ล้างข้อมูล</button>
                                </div>
                                <div id="csvDuplicateAlert" class="hidden bg-red-100 border border-red-200 text-red-700 p-2.5 rounded-lg text-xs font-bold mb-2 flex items-start gap-1.5">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                    <div>พบข้อมูลซ้ำกันในการาง! (เลขโต๊ะหรือลิงก์ URL) กรุณาแก้ไขไฟล์หรือตรวจสอบแถวที่ไฮไลต์สีแดง</div>
                                </div>
                                <div class="max-h-36 overflow-y-auto custom-scrollbar text-[10px] text-slate-600 bg-white p-2 rounded border">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-slate-50 sticky top-0 font-bold">
                                            <tr>
                                                <th class="p-1.5 border-b">เลขโต๊ะ</th>
                                                <th class="p-1.5 border-b">ลิงก์ URL</th>
                                                <th class="p-1.5 border-b">หมายเหตุ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="csvPreviewTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Customization -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">3</span>
                            <h2 class="font-bold text-slate-800">แต่งสีเฉพาะ QR และใส่โลโก้กลาง</h2>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">เปลี่ยนสีของตัวคิวอาร์ให้เข้าธีม</label>
                            <div class="flex gap-2">
                                <input type="color" id="inputQrColor" value="#000000" class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer">
                                <input type="text" id="inputQrColorText" value="#000000" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg font-mono uppercase focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>เพิ่มโลโก้ตรงกลาง QR Code</span>
                                <button id="btnRemoveLogo" class="text-[10px] text-red-500 hover:underline hidden">ลบโลโก้</button>
                            </label>
                            <input type="file" id="logoFileInput" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>

                <!-- Right: Preview -->
                <div class="lg:col-span-7 flex flex-col gap-5">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col items-center">
                        <div class="w-full flex items-center justify-between mb-4 border-b pb-3">
                            <div class="flex items-center gap-2">
                                <i data-lucide="eye" class="w-5 h-5 text-slate-500"></i>
                                <h2 class="font-bold text-slate-800">ภาพตัวอย่างจริง (ความละเอียดพิมพ์จับสูง 300 DPI)</h2>
                            </div>
                        </div>
                        <div class="w-full max-w-[450px] bg-slate-100 rounded-2xl p-3 border border-slate-200 relative overflow-hidden shadow-inner flex items-center justify-center">
                            <div id="canvasLoader" class="absolute inset-0 bg-white/90 flex flex-col items-center justify-center z-10 transition-opacity duration-300">
                                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs text-slate-600 mt-2 font-bold">กำลังประมวลผลกราฟิก...</span>
                            </div>
                            <canvas id="previewCanvas" class="max-w-full h-auto rounded-lg shadow-md border bg-white" width="1146" height="1146"></canvas>
                        </div>
                        <div class="w-full text-center mt-2">
                            <span class="text-[11px] text-slate-400 font-medium">สัดส่วนกรอบเสมือนจริงจะถูกล็อกตามมาตรฐาน 9.7 x 9.7 cm เสมอเพื่อไม่ให้กำหนดเพี้ยน</span>
                        </div>

                        <!-- Single actions -->
                        <div id="singleActionPanel" class="w-full mt-4 flex flex-col sm:flex-row gap-3">
                            <button id="btnDownloadSingle" class="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <i data-lucide="download" class="w-5 h-5"></i> ดาวน์โหลดภาพเดี่ยว (PNG)
                            </button>
                            <button id="btnPdfSingleA4" onclick="prepareA4PrintLayout()" class="flex-1 py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5"></i> ดาวน์โหลด PDF ขนาด A4
                            </button>
                        </div>

                        <!-- Bulk actions -->
                        <div id="bulkActionPanel" class="w-full mt-4 space-y-3 hidden border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-sm text-slate-800">โหมดดาวน์โหลดกลุ่มใหญ่ (Batch Mode)</h3>
                                    <p class="text-xs text-slate-500" id="bulkTotalText">อัปโหลดไฟล์ CSV เพื่อประมวลผลกลุ่มทั้งหมด</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button id="btnDownloadZip" class="py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                    <i data-lucide="folder-archive" class="w-5 h-5"></i> ดาวน์โหลดทั้งหมด .ZIP
                                </button>
                                <button id="btnPdfGroupA4" onclick="prepareA4PrintLayout()" class="py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                    <i data-lucide="file-text" class="w-5 h-5"></i> ดาวน์โหลด PDF ขนาด A4 (กลุ่ม)
                                </button>
                            </div>
                            <div id="zipProgressWrapper" class="hidden space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <div class="flex justify-between text-xs font-semibold text-slate-700">
                                    <span>กำลังบีบอัดภาพลงกระบวน ZIP...</span>
                                    <span id="zipProgressPercent">0%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div id="zipProgressBar" class="bg-emerald-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /#qrgenApp -->

        <!-- Shop Name Modal -->
        <div id="shopNameModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4" style="font-family:'Sarabun',sans-serif;">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-2xl"><i data-lucide="store" class="w-6 h-6"></i></div>
                    <div>
                        <h3 class="font-extrabold text-lg text-slate-900">กรอกชื่อร้านค้าของคุณ</h3>
                        <p class="text-xs text-slate-500">ระบบจะใช้ชื่อนี้เป็นชื่อเริ่มต้นของไฟล์ภาพพิมพ์</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">ชื่อร้านค้า (อังกฤษ/ไทย/ตัวเลข)</label>
                    <input type="text" id="modalShopNameInput" value="Local_For_You" placeholder="ตัวอย่าง: Local_For_You, My_Restaurant" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button id="btnCancelShopName" class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition">ยกเลิก</button>
                    <button id="btnConfirmShopName" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">ดำเนินการต่อ <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
                </div>
            </div>
        </div>

        <div id="printSection" class="hidden"></div>
    </div>
</div>

<script>
(function () {
    const FIXED_COORDS = { qrSize: 424, qrX: 573, qrY: 515, textSize: 83, textX: 573, textY: 940 };

    let logoImageObj = null;
    let csvDataList = [];
    let csvHasDuplicates = false;
    let manualDataList = [];
    let currentMode = 'single';
    let defaultPlaceholderBg = null;
    let pendingActionCallback = null;
    let bgGallery = [];
    let activeBgIndex = -1;

    const previewCanvas = document.getElementById('previewCanvas');
    const canvasLoader = document.getElementById('canvasLoader');
    const inputTableNo = document.getElementById('inputTableNo');
    const inputTableNote = document.getElementById('inputTableNote');
    const inputQrData = document.getElementById('inputQrData');
    const inputQrColor = document.getElementById('inputQrColor');
    const inputQrColorText = document.getElementById('inputQrColorText');
    const logoFileInput = document.getElementById('logoFileInput');
    const btnRemoveLogo = document.getElementById('btnRemoveLogo');
    const userBgUpload = document.getElementById('userBgUpload');
    const bgGalleryGrid = document.getElementById('bgGalleryGrid');
    const templateBgSelect = document.getElementById('templateBgSelect');
    const tabSingle = document.getElementById('tabSingle');
    const tabBulk = document.getElementById('tabBulk');
    const panelSingle = document.getElementById('panelSingle');
    const panelBulk = document.getElementById('panelBulk');
    const singleActionPanel = document.getElementById('singleActionPanel');
    const bulkActionPanel = document.getElementById('bulkActionPanel');
    const csvFileInput = document.getElementById('csvFileInput');
    const csvFileName = document.getElementById('csvFileName');
    const csvStatusArea = document.getElementById('csvStatusArea');
    const csvRowReadyText = document.getElementById('csvRowReadyText');
    const csvPreviewTableBody = document.getElementById('csvPreviewTableBody');
    const csvDuplicateAlert = document.getElementById('csvDuplicateAlert');
    const bulkTotalText = document.getElementById('bulkTotalText');
    const manualStatusArea = document.getElementById('manualStatusArea');
    const manualRowReadyText = document.getElementById('manualRowReadyText');
    const manualPreviewTableBody = document.getElementById('manualPreviewTableBody');
    const btnDownloadSingle = document.getElementById('btnDownloadSingle');
    const btnDownloadZip = document.getElementById('btnDownloadZip');
    const btnPdfGroupA4 = document.getElementById('btnPdfGroupA4');
    const zipProgressWrapper = document.getElementById('zipProgressWrapper');
    const zipProgressBar = document.getElementById('zipProgressBar');
    const zipProgressPercent = document.getElementById('zipProgressPercent');
    const shopNameModal = document.getElementById('shopNameModal');
    const modalShopNameInput = document.getElementById('modalShopNameInput');
    const btnCancelShopName = document.getElementById('btnCancelShopName');
    const btnConfirmShopName = document.getElementById('btnConfirmShopName');
    const checkboxAutoUnique = document.getElementById('checkboxAutoUnique');

    function drawRoundedRect(ctx, x, y, width, height, radius, fill, stroke) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
        if (fill) ctx.fill();
        if (stroke) ctx.stroke();
    }

    function drawHexagon(ctx, x, y, size, rotation) {
        ctx.beginPath();
        for (let i = 0; i < 6; i++) {
            const angle = rotation + (i * Math.PI / 3);
            const hx = x + size * Math.cos(angle);
            const hy = y + size * Math.sin(angle);
            if (i === 0) ctx.moveTo(hx, hy); else ctx.lineTo(hx, hy);
        }
        ctx.closePath();
        ctx.stroke();
    }

    function drawLocalForYouLogo(ctx, x, y, scale = 1) {
        ctx.save();
        ctx.translate(x, y);
        ctx.scale(scale, scale);
        ctx.fillStyle = '#00a3e0';
        ctx.beginPath();
        ctx.moveTo(0, -18);
        ctx.bezierCurveTo(10, -18, 18, -10, 18, 0);
        ctx.bezierCurveTo(18, 9, 10, 18, 0, 28);
        ctx.bezierCurveTo(-10, 18, -18, 9, -18, 0);
        ctx.bezierCurveTo(-18, -10, -10, -18, 0, -18);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2.5;
        ctx.beginPath(); ctx.arc(0, 0, 8, 0, Math.PI * 2); ctx.stroke();
        ctx.fillStyle = '#ffffff';
        ctx.beginPath(); ctx.arc(0, 0, 3.5, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = '#00a3e0';
        ctx.font = "800 21px 'Sarabun', sans-serif";
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText('LOCAL', 26, -5);
        ctx.font = "600 13px 'Sarabun', sans-serif";
        ctx.fillText('FOR YOU', 26, 12);
        ctx.restore();
    }

    function showLoader(show) {
        if (!canvasLoader) return;
        if (show) canvasLoader.classList.remove('opacity-0', 'pointer-events-none');
        else canvasLoader.classList.add('opacity-0', 'pointer-events-none');
    }

    function promptShopNameInput(onSuccess) {
        pendingActionCallback = onSuccess;
        shopNameModal.classList.remove('hidden');
        modalShopNameInput.focus();
        modalShopNameInput.select();
    }

    btnCancelShopName.addEventListener('click', () => {
        shopNameModal.classList.add('hidden');
        pendingActionCallback = null;
    });

    btnConfirmShopName.addEventListener('click', () => {
        let shopName = modalShopNameInput.value.trim().replace(/[^a-zA-Z0-9฀-๿_\-\s]/g, '_');
        if (!shopName) shopName = 'Local_For_You';
        shopName = shopName.replace(/\s+/g, '_');
        shopNameModal.classList.add('hidden');
        if (pendingActionCallback) { pendingActionCallback(shopName); pendingActionCallback = null; }
    });

    modalShopNameInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') btnConfirmShopName.click(); });

    window.addManualItem = function () {
        const tableNo = inputTableNo.value.trim();
        const qrData = inputQrData.value.trim();
        if (!tableNo) { showBannerNotification('กรุณากรอกเลขโต๊ะก่อนกดเพิ่มรายการ'); return; }
        if (!qrData) { showBannerNotification('กรุณากรอก URL/ข้อมูลลิงก์ก่อนกดเพิ่มรายการ'); return; }
        if (manualDataList.some(item => item.tableNo === tableNo)) {
            showBannerNotification(`เลขโต๊ะ "${tableNo}" มีอยู่ในรายการ Manual แล้ว! กรุณาใช้เลขโต๊ะที่ไม่ซ้ำ`); return;
        }
        if (manualDataList.some(item => item.url === qrData)) {
            showBannerNotification(`ลิงก์ URL นี้มีอยู่ในรายการ Manual แล้ว! กรุณาใช้ข้อมูลลิงก์ที่ไม่ซ้ำเพื่อความปลอดภัย`); return;
        }
        manualDataList.push({ tableNo, url: qrData, note: inputTableNote.value.trim() });
        updateManualListUI();
        const nextTableNum = parseInt(tableNo);
        inputTableNo.value = isNaN(nextTableNum) ? '' : (nextTableNum + 1).toString();
        inputQrData.value = '';
        renderPreview();
    };

    window.deleteManualItem = function (index) {
        manualDataList.splice(index, 1);
        updateManualListUI();
        renderPreview();
    };

    window.clearManualList = function () {
        manualDataList = [];
        updateManualListUI();
        renderPreview();
    };

    function updateManualListUI() {
        manualPreviewTableBody.innerHTML = '';
        if (manualDataList.length === 0) { manualStatusArea.classList.add('hidden'); return; }
        manualRowReadyText.textContent = `รายการพิมพ์ในคิวรอออก ${manualDataList.length} รายการ`;
        manualDataList.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = "border-b hover:bg-slate-100 transition duration-150 cursor-pointer";
            tr.onclick = () => { inputTableNo.value = item.tableNo; inputQrData.value = item.url; renderPreview(); };
            tr.innerHTML = `
                <td class="p-1.5 font-bold text-slate-800 select-all">${escapeHtml(item.tableNo)}</td>
                <td class="p-1.5 text-slate-500 font-mono text-[9px] truncate max-w-[180px]">${escapeHtml(item.url)}</td>
                <td class="p-1.5 text-right">
                    <button onclick="event.stopPropagation(); deleteManualItem(${index})" class="text-red-500 hover:text-red-700 font-bold transition p-1 rounded hover:bg-red-50" title="ลบรายการนี้">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 inline"></i>
                    </button>
                </td>`;
            manualPreviewTableBody.appendChild(tr);
        });
        manualStatusArea.classList.remove('hidden');
        lucide.createIcons();
    }

    function createDefaultPlaceholderBgDataUrl() {
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = 1146; tempCanvas.height = 1146;
        const tCtx = tempCanvas.getContext('2d');
        tCtx.fillStyle = '#ffffff'; tCtx.fillRect(0, 0, 1146, 1146);
        tCtx.strokeStyle = '#e2e8f0'; tCtx.lineWidth = 10; tCtx.strokeRect(5, 5, 1136, 1136);
        tCtx.strokeStyle = 'rgba(226, 232, 240, 0.4)'; tCtx.lineWidth = 3;
        tCtx.beginPath(); tCtx.arc(573, 573, 540, 0, Math.PI*2); tCtx.stroke();
        tCtx.beginPath(); tCtx.arc(573, 573, 490, 0, Math.PI*2); tCtx.stroke();
        tCtx.fillStyle = '#94a3b8'; tCtx.font = "bold 38px 'Sarabun', sans-serif"; tCtx.textAlign = 'center';
        tCtx.fillText('กรุณาอัปโหลดรูปพื้นหลังของคุณ', 573, 160);
        tCtx.font = "24px 'Sarabun', sans-serif";
        tCtx.fillText('(ขนาดพิมพ์แนะนำ 9.7 x 9.7 cm สัดส่วนจัตุรัส)', 573, 210);
        tCtx.strokeStyle = '#cbd5e1'; tCtx.fillStyle = '#f8fafc'; tCtx.lineWidth = 4; tCtx.setLineDash([15, 10]);
        const qrSize = FIXED_COORDS.qrSize;
        drawRoundedRect(tCtx, FIXED_COORDS.qrX - qrSize/2, FIXED_COORDS.qrY - qrSize/2, qrSize, qrSize, 24, true, true);
        tCtx.setLineDash([]);
        tCtx.fillStyle = '#64748b'; tCtx.font = "bold 24px 'Sarabun', sans-serif";
        tCtx.fillText('ตำแหน่งวาง QR โค้ดคิวอาร์ที่นี่', 573, FIXED_COORDS.qrY + 7);
        tCtx.fillStyle = '#cbd5e1'; tCtx.font = "bold 30px 'Sarabun', sans-serif"; tCtx.textAlign = 'center';
        tCtx.fillText('(ข้อความบนการ์ด)', FIXED_COORDS.textX, FIXED_COORDS.textY - 55);
        tCtx.strokeStyle = '#94a3b8'; tCtx.lineWidth = 8;
        drawHexagon(tCtx, 170, 210, 40, Math.PI/6);
        drawHexagon(tCtx, 950, 890, 45, 0);
        tCtx.fillStyle = '#475569'; tCtx.font = "bold 26px 'Sarabun', sans-serif"; tCtx.textAlign = 'right';
        tCtx.fillText('Powered by', 578, 1037);
        drawLocalForYouLogo(tCtx, 598, 1037, 1.2);
        return tempCanvas.toDataURL();
    }

    async function preloadGalleryTemplates() {
        try {
            const response = await fetch('pages/toolsUser/qrGenerator.php?action=getTemplateImages');
            const data = await response.json();
            console.log('Template images loaded:', data);
            if (data.success && data.images && data.images.length > 0) {
                for (const imgPath of data.images) {
                    await new Promise((resolve) => {
                        const img = new Image();
                        img.onload = () => {
                            const fileName = imgPath.split('/').pop();
                            bgGallery.push({ id: Date.now() + Math.random(), name: fileName, imgObj: img, path: imgPath });
                            // Add option to dropdown
                            const option = document.createElement('option');
                            option.value = bgGallery.length - 1;
                            option.textContent = fileName;
                            templateBgSelect.appendChild(option);
                            resolve();
                        };
                        img.onerror = () => {
                            console.warn('Failed to load image:', imgPath);
                            resolve();
                        };
                        img.src = imgPath;
                    });
                }
                if (bgGallery.length > 0) {
                    activeBgIndex = 0;
                    templateBgSelect.value = '0';
                    console.log('Gallery loaded with', bgGallery.length, 'images');
                }
            }
        } catch (err) {
            console.warn('Could not load template images:', err);
        }
        updateGalleryUI(); renderPreview();
    }

    templateBgSelect.addEventListener('change', (e) => {
        const index = parseInt(e.target.value);
        if (!isNaN(index) && index >= 0 && index < bgGallery.length) {
            activeBgIndex = index;
            updateGalleryUI();
            renderPreview();
        }
    });

    function updateGalleryUI() {
        bgGalleryGrid.innerHTML = '';
        if (bgGallery.length === 0) {
            bgGalleryGrid.innerHTML = `<div class="col-span-full py-6 text-center text-xs text-slate-400 font-medium">ยังไม่มีภาพเทมเพลต กรุณาคลิกอัปโหลดภาพพื้นหลังของคุณด้านบน</div>`;
            return;
        }
        bgGallery.forEach((item, index) => {
            const isActive = index === activeBgIndex;
            const container = document.createElement('div');
            container.className = `relative aspect-square rounded-xl overflow-hidden border-2 cursor-pointer group transition-all ${isActive ? 'border-blue-600 ring-2 ring-blue-100 shadow-md scale-95' : 'border-slate-200 hover:border-slate-400 bg-white'}`;
            const thumb = document.createElement('img');
            thumb.src = item.imgObj.src; thumb.className = "w-full h-full object-cover"; thumb.title = item.name;
            thumb.onclick = () => { activeBgIndex = index; updateGalleryUI(); renderPreview(); };
            container.appendChild(thumb);
            if (isActive) {
                const badge = document.createElement('div');
                badge.className = "absolute top-1 right-1 bg-blue-600 text-white p-0.5 rounded-full z-10 shadow-sm";
                badge.innerHTML = `<i data-lucide="check" class="w-2.5 h-2.5"></i>`;
                container.appendChild(badge);
            }
            const deleteBtn = document.createElement('button');
            deleteBtn.className = "absolute bottom-1 right-1 bg-red-600 text-white p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity z-10 shadow hover:bg-red-700";
            deleteBtn.innerHTML = `<i data-lucide="trash-2" class="w-3 h-3"></i>`;
            deleteBtn.onclick = (e) => {
                e.stopPropagation();
                bgGallery.splice(index, 1);
                if (activeBgIndex === index) activeBgIndex = bgGallery.length > 0 ? 0 : -1;
                else if (activeBgIndex > index) activeBgIndex--;
                updateGalleryUI(); renderPreview();
            };
            container.appendChild(deleteBtn);
            bgGalleryGrid.appendChild(container);
        });
        lucide.createIcons();
    }

    userBgUpload.addEventListener('change', async (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;
        showLoader(true);
        const initialCount = bgGallery.length;
        for (const file of files) {
            await new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = new Image();
                    img.onload = () => { bgGallery.push({ id: Date.now() + Math.random(), name: file.name, imgObj: img }); resolve(); };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            });
        }
        activeBgIndex = initialCount;
        updateGalleryUI(); renderPreview(); showLoader(false);
        userBgUpload.value = '';
    });

    inputQrColor.addEventListener('input', (e) => { inputQrColorText.value = e.target.value.toUpperCase(); renderPreview(); });
    inputQrColorText.addEventListener('input', (e) => {
        if (e.target.value.match(/^#[0-9A-F]{6}$/i)) { inputQrColor.value = e.target.value; renderPreview(); }
    });
    [inputTableNo, inputTableNote, inputQrData].forEach(elem => elem.addEventListener('input', renderPreview));

    tabSingle.addEventListener('click', () => {
        currentMode = 'single';
        tabSingle.className = "px-3 py-1.5 rounded-md bg-white text-slate-800 shadow-sm transition font-bold";
        tabBulk.className = "px-3 py-1.5 rounded-md text-slate-500 hover:text-slate-800 transition";
        panelSingle.classList.remove('hidden'); panelBulk.classList.add('hidden');
        singleActionPanel.classList.remove('hidden'); bulkActionPanel.classList.add('hidden');
        renderPreview();
    });

    tabBulk.addEventListener('click', () => {
        currentMode = 'bulk';
        tabBulk.className = "px-3 py-1.5 rounded-md bg-white text-slate-800 shadow-sm transition font-bold";
        tabSingle.className = "px-3 py-1.5 rounded-md text-slate-500 hover:text-slate-800 transition";
        panelBulk.classList.remove('hidden'); panelSingle.classList.add('hidden');
        bulkActionPanel.classList.remove('hidden'); singleActionPanel.classList.add('hidden');
        inputTableNote.value = '';
        renderPreview();
    });

    logoFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        showLoader(true);
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => { logoImageObj = img; btnRemoveLogo.classList.remove('hidden'); renderPreview(); showLoader(false); };
            img.onerror = () => { showLoader(false); showBannerNotification('เกิดข้อผิดพลาดในการประมวลผลไฟล์ภาพโลโก้'); };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    btnRemoveLogo.addEventListener('click', () => {
        logoImageObj = null; logoFileInput.value = ''; btnRemoveLogo.classList.add('hidden'); renderPreview();
    });

    function drawCardOnCanvas(targetCanvas, tableNo, qrData, note) {
        return new Promise((resolve, reject) => {
            const tCtx = targetCanvas.getContext('2d');
            const bgImg = (activeBgIndex !== -1 && bgGallery[activeBgIndex]) ? bgGallery[activeBgIndex].imgObj : defaultPlaceholderBg;
            if (!bgImg) { reject('ยังไม่ได้กำหนดเทมเพลตรูปภาพเทมเพลต'); return; }
            if (typeof QRCode === 'undefined') { reject('เมนูสร้างคิวอาร์โค้ดหายไป กรุณาเชื่อมต่ออินเทอร์เน็ตใหม่'); return; }
            tCtx.clearRect(0, 0, 1146, 1146);
            tCtx.imageSmoothingEnabled = true; tCtx.imageSmoothingQuality = 'high';
            tCtx.drawImage(bgImg, 0, 0, 1146, 1146);
            const qrSize = FIXED_COORDS.qrSize, qrX = FIXED_COORDS.qrX, qrY = FIXED_COORDS.qrY, qrColorVal = inputQrColor.value;
            let finalQrPayload = qrData;
            if (checkboxAutoUnique.checked) {
                const uniqueSalt = Math.random().toString(36).substring(2, 7) + Date.now().toString().slice(-4);
                const separator = finalQrPayload.includes('?') ? '&' : '?';
                finalQrPayload = finalQrPayload + separator + '_uq=' + uniqueSalt;
            }
            const tempQrCanvas = document.createElement('canvas');
            QRCode.toCanvas(tempQrCanvas, finalQrPayload, {
                width: qrSize, margin: 1, color: { dark: qrColorVal, light: '#ffffff' }, errorCorrectionLevel: 'H'
            }, function (error) {
                if (error) { console.error(error); reject(error); return; }
                const qrCtx = tempQrCanvas.getContext('2d');
                const imgData = qrCtx.getImageData(0, 0, tempQrCanvas.width, tempQrCanvas.height);
                const data = imgData.data;
                for (let i = 0; i < data.length; i += 4) {
                    if (data[i] > 200 && data[i+1] > 200 && data[i+2] > 200) data[i+3] = 0;
                }
                qrCtx.putImageData(imgData, 0, 0);
                tCtx.drawImage(tempQrCanvas, qrX - qrSize / 2, qrY - qrSize / 2, qrSize, qrSize);
                if (logoImageObj && logoImageObj.complete && logoImageObj.naturalWidth !== 0) {
                    const logoSize = qrSize * 0.22;
                    const logoX = qrX - logoSize / 2, logoY = qrY - logoSize / 2;
                    tCtx.fillStyle = '#ffffff';
                    const shieldPadding = logoSize * 0.15;
                    drawRoundedRect(tCtx, logoX - shieldPadding, logoY - shieldPadding, logoSize + shieldPadding * 2, logoSize + shieldPadding * 2, 8, true, false);
                    tCtx.drawImage(logoImageObj, logoX, logoY, logoSize, logoSize);
                }
                const noteStr = (note !== undefined && note !== null) ? String(note).trim() : '';
                if (tableNo !== undefined && tableNo !== null && tableNo !== '') {
                    tCtx.textAlign = 'center';
                    if (noteStr === '') {
                        // No note: table text stays balanced on the baseline.
                        tCtx.fillStyle = '#000000';
                        tCtx.font = `800 ${FIXED_COORDS.textSize}px 'Sarabun', sans-serif`;
                        tCtx.fillText(tableNo, FIXED_COORDS.textX, FIXED_COORDS.textY);
                    } else {
                        // With note: shift table text up, insert note below.
                        tCtx.fillStyle = '#000000';
                        tCtx.font = `800 ${FIXED_COORDS.textSize}px 'Sarabun', sans-serif`;
                        tCtx.fillText(tableNo, FIXED_COORDS.textX, FIXED_COORDS.textY - 35);
                        tCtx.fillStyle = '#475569';
                        tCtx.font = "bold 34px 'Sarabun', sans-serif";
                        tCtx.fillText(noteStr, FIXED_COORDS.textX, FIXED_COORDS.textY + 30);
                    }
                }
                resolve(targetCanvas);
            });
        });
    }

    async function renderPreview() {
        try {
            const noteVal = inputTableNote.value.trim();
            if (currentMode === 'single') {
                await drawCardOnCanvas(previewCanvas, inputTableNo.value.trim(), inputQrData.value.trim() || 'https://localforyou.co', noteVal);
            } else if (csvDataList.length > 0) {
                await drawCardOnCanvas(previewCanvas, csvDataList[0].tableNo, csvDataList[0].url, csvDataList[0].note || noteVal);
            } else {
                await drawCardOnCanvas(previewCanvas, 'XX', 'https://localforyou.co/table-sample', noteVal);
            }
        } catch (err) {
            console.error("Renderer runtime error:", err);
        } finally {
            showLoader(false);
        }
    }

    btnDownloadSingle.addEventListener('click', () => {
        promptShopNameInput((shopName) => {
            const link = document.createElement('a');
            const tableNoStr = inputTableNo.value.trim() || 'unknown';
            link.download = `${shopName}_Table_9.7x9.7cm_No_${tableNoStr}.png`;
            link.href = previewCanvas.toDataURL('image/png');
            link.click();
        });
    });

    csvFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        csvFileName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = (event) => {
            const lines = event.target.result.split(/\r?\n/);
            const tempData = [];
            if (lines.length < 2) { showBannerNotification('ไฟล์ CSV โครงสร้างไม่ถูกต้องหรือไม่มีข้อมูล'); return; }
            const parseCSVLine = (line) => {
                let clean = line.trim();
                if (clean.startsWith('"') && clean.endsWith('"')) clean = clean.slice(1, -1);
                const result = []; let current = ''; let inQuotes = false;
                for (let i = 0; i < clean.length; i++) {
                    const char = clean[i];
                    if (char === '"') inQuotes = !inQuotes;
                    else if (char === ',' && !inQuotes) { result.push(current.trim().replace(/^["']|["']$/g, '')); current = ''; }
                    else current += char;
                }
                result.push(current.trim().replace(/^["']|["']$/g, ''));
                return result;
            };
            const headers = parseCSVLine(lines[0]);
            let tableIdx = headers.findIndex(h => { const low = h.toLowerCase(); return low.includes('table') || low.includes('โต๊ะ') || low.includes('name') || low.includes('no'); });
            let urlIdx = headers.findIndex(h => { const low = h.toLowerCase(); return low.includes('url') || low.includes('link') || low.includes('ลิงก์') || low.includes('qr') || low.includes('order'); });
            let noteIdx = headers.findIndex(h => { const low = h.toLowerCase(); return low.includes('note') || low.includes('หมายเหตุ') || low.includes('remark') || low.includes('desc'); });
            if (tableIdx === -1) tableIdx = 1;
            if (urlIdx === -1) urlIdx = 2;
            for (let i = 1; i < lines.length; i++) {
                const line = lines[i].trim();
                if (!line) continue;
                const row = parseCSVLine(line);
                if (row.length > Math.max(tableIdx, urlIdx)) {
                    const note = (noteIdx !== -1 && row.length > noteIdx) ? (row[noteIdx] || '') : '';
                    tempData.push({ tableNo: row[tableIdx], url: row[urlIdx], note: note });
                }
            }
            if (tempData.length === 0) { showBannerNotification('ไม่สามารถอ่านคอลัมน์ข้อมูลในไฟล์ CSV ได้'); return; }
            csvDataList = tempData;
            csvHasDuplicates = false;
            const uniqueTables = new Set(), uniqueUrls = new Set(), duplicateRowIndices = new Set();
            csvDataList.forEach((item, index) => {
                if (uniqueTables.has(item.tableNo) || uniqueUrls.has(item.url)) { duplicateRowIndices.add(index); csvHasDuplicates = true; }
                uniqueTables.add(item.tableNo); uniqueUrls.add(item.url);
            });
            csvPreviewTableBody.innerHTML = '';
            csvDataList.forEach((item, index) => {
                const isDup = duplicateRowIndices.has(index);
                const tr = document.createElement('tr');
                tr.className = isDup ? "border-b bg-red-50 text-red-800 font-semibold border-red-100 hover:bg-red-100/70" : "border-b hover:bg-slate-50";
                tr.innerHTML = `
                    <td class="p-1.5 font-bold">${escapeHtml(item.tableNo)} ${isDup ? '(ซ้ำ)' : ''}</td>
                    <td class="p-1.5 font-mono text-[10px] truncate max-w-[200px]">${escapeHtml(item.url)}</td>
                    <td class="p-1.5">
                        <input type="text" data-note-index="${index}" value="${escapeHtml(item.note || '')}" placeholder="เพิ่มหมายเหตุ..."
                            class="w-full max-w-[120px] px-1.5 py-0.5 text-[10px] bg-transparent border border-transparent rounded hover:border-slate-200 focus:border-blue-400 focus:bg-white focus:outline-none">
                    </td>`;
                const noteInput = tr.querySelector('input[data-note-index]');
                noteInput.addEventListener('input', (e) => {
                    csvDataList[index].note = e.target.value;
                    if (index === 0) renderPreview();
                });
                csvPreviewTableBody.appendChild(tr);
            });
            if (csvHasDuplicates) {
                csvDuplicateAlert.classList.remove('hidden');
                btnDownloadZip.disabled = true; btnPdfGroupA4.disabled = true;
                btnDownloadZip.classList.add('opacity-50', 'cursor-not-allowed');
                btnPdfGroupA4.classList.add('opacity-50', 'cursor-not-allowed');
                showBannerNotification('พบข้อมูลเลขโต๊ะหรือลิงก์ URL ซ้ำกันในตาราง! ดาวน์โหลดถูกระงับชั่วคราว');
            } else {
                csvDuplicateAlert.classList.add('hidden');
                btnDownloadZip.disabled = false; btnPdfGroupA4.disabled = false;
                btnDownloadZip.classList.remove('opacity-50', 'cursor-not-allowed');
                btnPdfGroupA4.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            csvRowReadyText.textContent = `พร้อมพิมพ์ทั้งหมด ${csvDataList.length} รายการ`;
            bulkTotalText.textContent = `ตรวจสอบไฟล์และข้อมูลการพิมพ์ทั้งสิ้น ${csvDataList.length} รายการ`;
            csvStatusArea.classList.remove('hidden');
            renderPreview();
        };
        reader.readAsText(file);
    });

    window.clearCSV = function () {
        csvFileInput.value = '';
        csvFileName.textContent = 'ยังไม่ได้เลือกไฟล์';
        csvStatusArea.classList.add('hidden');
        csvDuplicateAlert.classList.add('hidden');
        csvDataList = []; csvHasDuplicates = false;
        btnDownloadZip.disabled = false; btnPdfGroupA4.disabled = false;
        btnDownloadZip.classList.remove('opacity-50', 'cursor-not-allowed');
        btnPdfGroupA4.classList.remove('opacity-50', 'cursor-not-allowed');
        bulkTotalText.textContent = 'อัปโหลด CSV เพื่อสร้างไฟล์แบบกลุ่ม';
        renderPreview();
    };

    window.downloadSampleCSV = function () {
        const csvContent = "TableNo,URL\n01,https://localforyou.co/order?table=01\n02,https://localforyou.co/order?table=02\n03,https://localforyou.co/order?table=03\n04,https://localforyou.co/order?table=04";
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.setAttribute('download', 'template_table_qr.csv');
        document.body.appendChild(link); link.click(); document.body.removeChild(link);
    };

    function escapeHtml(str) {
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function showBannerNotification(msg) {
        const notifyDiv = document.createElement('div');
        notifyDiv.className = "fixed bottom-5 right-5 bg-red-600 text-white font-semibold text-xs px-4 py-3 rounded-xl shadow-xl z-50 flex items-center gap-2";
        notifyDiv.style.fontFamily = "'Sarabun',sans-serif";
        notifyDiv.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4"></i> <span>${msg}</span>`;
        document.body.appendChild(notifyDiv);
        lucide.createIcons();
        setTimeout(() => { notifyDiv.classList.add('opacity-0', 'transition-all'); setTimeout(() => notifyDiv.remove(), 400); }, 4000);
    }

    window.prepareA4PrintLayout = function () {
        let cardsToProcess = [];
        if (currentMode === 'single') {
            if (manualDataList.length > 0) cardsToProcess = [...manualDataList];
            else cardsToProcess = [{ tableNo: inputTableNo.value.trim(), url: inputQrData.value.trim() || 'https://localforyou.co' }];
        } else {
            if (csvDataList.length === 0) { showBannerNotification('กรุณานำเข้าข้อมูลไฟล์ CSV ก่อนทำชุดพิมพ์!'); return; }
            if (csvHasDuplicates) { showBannerNotification('ไม่สามารถจัดหน้าดาวน์โหลดได้เพราะพบข้อมูลซ้ำกันในตาราง!'); return; }
            cardsToProcess = [...csvDataList];
        }
        if (typeof window.jspdf === 'undefined') { showBannerNotification('ไลบรารีสำหรับสร้าง PDF กำลังดาวน์โหลดหรือขัดข้อง กรุณาลองใหม่อีกครั้ง'); return; }

        promptShopNameInput(async (shopName) => {
            showLoader(true);
            const processCanvas = document.createElement('canvas');
            processCanvas.width = 1146; processCanvas.height = 1146;
            const renderedCardImages = [];
            for (let i = 0; i < cardsToProcess.length; i++) {
                try {
                    await drawCardOnCanvas(processCanvas, cardsToProcess[i].tableNo, cardsToProcess[i].url, cardsToProcess[i].note || inputTableNote.value.trim());
                    renderedCardImages.push(processCanvas.toDataURL('image/png'));
                } catch (e) { console.error("Card drawing failed at index " + i, e); }
            }
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
            const cardsPerPage = 4, pagesCount = Math.ceil(renderedCardImages.length / cardsPerPage);
            const cardWidth = 97, cardHeight = 97, gap = 2, leftMargin = 7, topMargin = 50.5;
            for (let pageIdx = 0; pageIdx < pagesCount; pageIdx++) {
                if (pageIdx > 0) doc.addPage();
                for (let cardOffset = 0; cardOffset < cardsPerPage; cardOffset++) {
                    const imgIndex = pageIdx * cardsPerPage + cardOffset;
                    if (imgIndex >= renderedCardImages.length) continue;
                    const col = cardOffset % 2, row = Math.floor(cardOffset / 2);
                    const x = leftMargin + col * (cardWidth + gap), y = topMargin + row * (cardHeight + gap);
                    doc.setDrawColor(148, 163, 184);
                    doc.setLineDashPattern([2, 1], 0);
                    doc.setLineWidth(0.35);
                    doc.rect(x, y, cardWidth, cardHeight);
                    doc.addImage(renderedCardImages[imgIndex], 'PNG', x, y, cardWidth, cardHeight);
                }
            }
            doc.save(`${shopName}_Table_QR_Cards_A4.pdf`);
            showLoader(false);
            showBannerNotification('ระบบทำการดาวน์โหลดไฟล์ PDF ขนาด A4 เรียบร้อยแล้ว!');
        });
    };

    btnDownloadZip.addEventListener('click', async () => {
        if (csvDataList.length === 0) { showBannerNotification('กรุณานำเข้าข้อมูลไฟล์ CSV ก่อนดาวน์โหลดชุดอัด!'); return; }
        if (typeof QRCode === 'undefined') { showBannerNotification('ตัวสร้างคิวอาร์โค้ดไม่พร้อมทำงานขณะนี้'); return; }
        if (csvHasDuplicates) { showBannerNotification('ไม่สามารถดาวน์โหลด .ZIP ได้เพราะพบข้อมูลซ้ำกันในตาราง!'); return; }

        promptShopNameInput(async (shopName) => {
            btnDownloadZip.disabled = true;
            btnDownloadZip.textContent = "กำลังรวมรวมไฟล์...";
            zipProgressWrapper.classList.remove('hidden');
            const zip = new JSZip();
            const total = csvDataList.length;
            const tempProcessCanvas = document.createElement('canvas');
            tempProcessCanvas.width = 1146; tempProcessCanvas.height = 1146;
            for (let i = 0; i < total; i++) {
                const item = csvDataList[i];
                const percent = Math.round((i / total) * 100);
                zipProgressBar.style.width = percent + '%';
                zipProgressPercent.textContent = percent + '%';
                try {
                    await drawCardOnCanvas(tempProcessCanvas, item.tableNo, item.url, item.note || inputTableNote.value.trim());
                    const blob = await new Promise(resolve => tempProcessCanvas.toBlob(resolve, 'image/png'));
                    const safeTableNo = String(item.tableNo).replace(/[^a-zA-Z0-9_\-]/g, '_');
                    zip.file(`${shopName}_Table_${safeTableNo}_9.7cm.png`, blob);
                } catch (e) { console.error("Batch render error index " + i, e); }
            }
            zipProgressBar.style.width = '100%'; zipProgressPercent.textContent = '100%';
            btnDownloadZip.textContent = "กำลังบีบอัดเป็นไฟล์ ZIP...";
            zip.generateAsync({ type: "blob" }).then(function (content) {
                const link = document.createElement('a');
                link.download = `${shopName}_Table_QR_Cards_9.7x9.7cm.zip`;
                link.href = URL.createObjectURL(content);
                link.click();
                btnDownloadZip.disabled = false;
                btnDownloadZip.innerHTML = `<i data-lucide="folder-archive" class="w-5 h-5"></i> ดาวน์โหลดทั้งหมด .ZIP`;
                zipProgressWrapper.classList.add('hidden');
                lucide.createIcons();
            });
        });
    });

    function initQrGen() {
        showLoader(true);
        defaultPlaceholderBg = new Image();
        defaultPlaceholderBg.onload = () => { preloadGalleryTemplates(); };
        defaultPlaceholderBg.src = createDefaultPlaceholderBgDataUrl();
        if (window.lucide) lucide.createIcons();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initQrGen);
    else initQrGen();
})();
</script>
