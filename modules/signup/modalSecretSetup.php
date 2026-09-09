<!-- Modal -->
<div class="modal fade" id="modalSecretSetup" tabindex="-1" aria-labelledby="modalSecretLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSecretLabel">Form Setup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" value="1" type="checkbox" id="CheckedBoxMakeCharge" checked>
                    <label class="form-check-label" for="CheckedBoxMakeCharge">Customer charge</label>
                </div>
            </div>
            <div class="modal-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" value="1" type="checkbox" id="CheckedBoxTestmail">
                    <label class="form-check-label" for="CheckedBoxTestmail">Test mode</label>
                </div>
            </div>
            <!--
            ===== Skip email check (ปิดใช้งานชั่วคราว) =====
            ปิดไว้ตามที่ร้องขอ ถ้าจะเปิดใช้ใหม่ให้ uncomment บล็อกนี้
            และต้อง uncomment โค้ดฝั่ง JS ที่คู่กันด้วย มิฉะนั้นจะไม่ทำงาน:
              assets/js/bootstrap-multi-step-form.js
                - บล็อก $("#CheckedBoxSkipEmailCheck").on("change", ...)
                - เงื่อนไข skip ในฟังก์ชัน checkEmailUsed()
                - เงื่อนไข skip ในด่านตอน submit (cmdSubmit.on("click", ...))

            หมายเหตุ: value="1" ไม่มีผลต่อการทำงาน เพราะโค้ดอ่านด้วย
            .is(":checked") อย่างเดียว และ input นี้ไม่มี name จึงไม่ถูก submit

            <div class="modal-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" value="1" type="checkbox" id="CheckedBoxSkipEmailCheck">
                    <label class="form-check-label" for="CheckedBoxSkipEmailCheck">Skip email check</label>
                </div>
            </div>
            -->

            <div class="modal-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" value="1" type="checkbox" id="CheckedBoxShowEndModal" onchange="toggleShowEndModal(this)">
                        <label class="form-check-label" for="CheckedBoxShowEndModal">Show end modal</label>
                    </div>
                    <div id="endModalButtons" style="display:none;">
                        <button type="button" class="btn btn-sm btn-success me-1" onclick="modalRespondAction('open','success')">&#x2705; Success</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="modalRespondAction('open','fail','Test fail reason')">&#x274C; Fail</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
function toggleShowEndModal(checkbox) {
    if (checkbox.checked) { $("#endModalButtons").show(); }
    else { $("#endModalButtons").hide(); }
}
</script>