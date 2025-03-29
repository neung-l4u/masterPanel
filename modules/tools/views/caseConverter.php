<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="../assets/css/index.css" rel="stylesheet">
    <script src="../assets/js/index.js"></script>
    <title>Case Converter</title>
</head>
<body>

    <main class="container py-5">
        <section class="container" style="min-height: 60vh;">

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3"><i class="bi bi-arrow-left-right"></i> Case Converter</h4>
                    <textarea aria-label="type here" id="inputText" class="form-control mb-3" rows="5" placeholder="พิมพ์ข้อความที่นี่..."></textarea>
                    <div id="statusMsg" class="text-success text-center fw-bold" style="display:none; float: right;">Copied to clipboard!</div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="autoClear">
                        <label class="form-check-label" for="autoClear">Auto Clear</label>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted fw-bold"><i class="bi bi-chat-right-dots"></i> Text Cases</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-primary btn-sm" data-case="sentence">Sentence case</button>
                            <button class="btn btn-outline-primary btn-sm" data-case="lower">lowercase</button>
                            <button class="btn btn-outline-primary btn-sm" data-case="upper">UPPER CASE</button>
                            <button class="btn btn-outline-primary btn-sm" data-case="capitalized">Capitalized</button>
                            <button class="btn btn-outline-primary btn-sm" data-case="title">Title Case</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted fw-bold"><i class="bi bi-wallet-fill"></i> Fun/Fancy</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-success btn-sm" data-case="alternating">aLtErNaTiNg</button>
                            <button class="btn btn-outline-success btn-sm" data-case="inverse">InVeRsE</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted fw-bold"><i class="bi bi-code-square"></i> Naming Conventions</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-secondary btn-sm" data-case="snake">snake_case</button>
                            <button class="btn btn-outline-secondary btn-sm" data-case="camel">camelCase</button>
                            <button class="btn btn-outline-secondary btn-sm" data-case="pascal">PascalCase</button>
                            <button class="btn btn-outline-secondary btn-sm" data-case="kebab">kebab-case</button>
                        </div>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-sm btn-danger" id="clearBtn">
                            <i class="bi bi-x-circle"></i> เคลียร์ข้อความ
                        </button>
                    </div>


                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> History <small>max 20</small></h5>
                        <button class="btn btn-sm btn-outline-danger" id="clearHistoryBtn">
                            <i class="bi bi-trash3"></i> ลบทั้งหมด
                        </button>
                    </div>

                    <ul id="historyList" class="list-group small" style="max-height: 250px; overflow-y: auto;"></ul>
                </div>
            </div>

        </section>
    </main>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/index.js"></script>
<script>
    const chkAutoClear = $("#autoClear");
    const inputText = $("#inputText");

    $(()=>{
        renderHistory(); // โหลดรายการย้อนหลังเมื่อเปิดหน้า

        // โหลดค่าจาก localStorage ตอนเปิดหน้า
        if (localStorage.getItem("autoClear") === "true") {
            chkAutoClear.prop("checked", true);
        }

        // เมื่อ checkbox ถูกเปลี่ยน ให้บันทึกค่าไว้ใน localStorage
        chkAutoClear.on("change", function () {
            localStorage.setItem("autoClear", $(this).is(":checked"));
        });

        $("button[data-case]").click(function () {
            const caseType = $(this).data("case");
            const text = inputText.val();

            $.post("../controllers/convert.php", { text: text, case: caseType }, function (result) {
                navigator.clipboard.writeText(result); // copy ก่อน
                addToHistory(result);

                if (chkAutoClear.is(":checked")) {
                    inputText.val(''); // เคลียร์ถ้าติ๊ก
                } else {
                    inputText.val(result); // แสดงผลลัพธ์ถ้าไม่เคลียร์
                }

                $("#statusMsg").fadeIn().delay(1000).fadeOut();
            });
        });//.click

        $("#clearBtn").click(function () {
            inputText.val('');
        });//.click

        $("#clearHistoryBtn").click(function () {
            if (confirm("Are you sure you want to empty your history?")) {
                localStorage.removeItem("caseHistory");
                renderHistory();
            }
        });//.click
    });//ready

    function addToHistory(text) {
        if (!text.trim()) return;

        const raw = localStorage.getItem("caseHistory") || "[]";
        const history = JSON.parse(raw);

        history.unshift({ text: text, timestamp: Date.now() });
        if (history.length > 20) history.length = 20; // จำกัด 20 รายการล่าสุด

        localStorage.setItem("caseHistory", JSON.stringify(history));
        renderHistory();
    }//addToHistory

    function renderHistory() {
        const raw = localStorage.getItem("caseHistory") || "[]";
        const history = JSON.parse(raw);
        const list = $("#historyList");
        list.empty();

        if (history.length === 0) {
            list.append(`<li class="list-group-item text-muted">You don't have any history messages yet.</li>`);
            return;
        }

        history.forEach((item, i) => {
            const safeText = $('<div>').text(item.text).html(); // escape XSS
            const date = new Date(item.timestamp).toLocaleString();
            list.append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-2" style="max-width: 65%;">
                                    <div class="text-truncate fw-bold">${safeText}</div>
                                    <small class="text-muted">${date}</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                  <button class="btn btn-outline-primary" onclick="reuseHistory(${i})">
                                    <i class="bi bi-plus-circle"></i>
                                  </button>
                                  <button class="btn btn-outline-danger" onclick="deleteHistory(${i})">
                                    <i class="bi bi-x-lg"></i>
                                  </button>
                                </div>
                            </li>
                        `);
        });
    }//renderHistory

    function reuseHistory(index) {
        const raw = localStorage.getItem("caseHistory") || "[]";
        const history = JSON.parse(raw);
        if (history[index]) {
            inputText.val(history[index].text);
        }
    }//reuseHistory

    function deleteHistory(index) {
        const raw = localStorage.getItem("caseHistory") || "[]";
        const history = JSON.parse(raw);

        if (index >= 0 && index < history.length) {
            history.splice(index, 1); // ลบ 1 รายการ
            localStorage.setItem("caseHistory", JSON.stringify(history));
            renderHistory();
        }
    }//deleteHistory
</script>
</body>
</html>