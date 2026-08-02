<style>
    .modal-confirm {
        color: #434e65;
        width: 525px;
    }
    .modal-confirm .modal-content {
        padding: 20px;
        font-size: 16px;
        border-radius: 5px;
        border: none;
    }
    .modal-confirm .modal-header {
        background: #47c9a2;
        border-bottom: none;
        position: relative;
        text-align: center;
        margin: -20px -20px 0;
        border-radius: 5px 5px 0 0;
        padding: 35px;
    }
    .modal-confirm h4 {
        text-align: center;
        font-size: 36px;
        margin: 10px 0;
    }
    .modal-confirm .form-control, .modal-confirm .btn {
        min-height: 40px;
        border-radius: 3px;
    }
    .modal-confirm .close {
        position: absolute;
        top: 15px;
        right: 15px;
        color: #fff;
        text-shadow: none;
        opacity: 0.5;
    }
    .modal-confirm .close:hover {
        opacity: 0.8;
    }
    .modal-confirm .icon-box {
        color: #fff;
        width: 95px;
        height: 95px;
        display: inline-block;
        border-radius: 50%;
        z-index: 9;
        border: 5px solid #fff;
        padding: 15px;
        text-align: center;
    }
    .modal-confirm .icon-box i {
        font-size: 64px;
        margin: -4px 0 0 -4px;
    }
    .modal-confirm.modal-dialog {
        margin-top: 80px;
    }
    .modal-confirm .btn, .modal-confirm .btn:active {
        color: #fff;
        border-radius: 4px;
        background: #eeb711 !important;
        text-decoration: none;
        transition: all 0.4s;
        line-height: normal;
        border-radius: 30px;
        margin-top: 10px;
        padding: 6px 20px;
        border: none;
    }
    .modal-confirm .btn:hover, .modal-confirm .btn:focus {
        background: #eda645 !important;
        outline: none;
    }
    .modal-confirm .btn span {
        margin: 1px 3px 0;
        float: left;
    }
    .modal-confirm .btn i {
        margin-left: 1px;
        font-size: 20px;
        float: right;
    }
    .trigger-btn {
        display: inline-block;
        margin: 100px auto;
    }
</style>
<!-- Modal Response-->
<div class="modal fade" id="modalResponse" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalRespondLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="respondSuccess">
                <div class="modal-header bg-success d-flex flex-column">
                    <div class="w-100 d-flex flex-row justify-content-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="icon-box w-100 d-flex flex-row justify-content-center">
                        <img src="assets/img/icon-success.svg" alt="success" width="104">
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Great!</h4>
                    <p class="text-success fw-bold">The online application process has been completed.</p>
                    <p>The receipt file has been sent to <span class="receiveEmail fw-bold text-info"> your registered email address</span>. <br>
                        Please check your email inbox shortly.</p>

                    <div class="mb-3">
                        <input class="btn btn-warning" type="button" value="Contract" onclick="genPDF();">
                        <input class="btn btn-warning" id="docusignSendBtn" type="button"
                               value="Send for Signature" onclick="sendContractsForSignature();">
                        <div id="docusignStatus" class="small mt-2" style="display:none;"></div>
                    </div>

                    <!-- TH Payment Section -->
                    <div id="thPaymentSection" style="display:none; text-align:left;" class="mb-3">
                        <!-- Bank info card -->
                        <div style="border:1px solid #e0e0e0; border-radius:12px; overflow:hidden; margin-bottom:16px;">
                            <div class="d-flex" style="min-height:160px;">
                                <!-- Left: bank details -->
                                <div style="flex:1; padding:16px; border-right:1px solid #e0e0e0;">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <!-- <img src="assets/img/k-bank.png" alt="K PLUS" height="36" style="border-radius:8px;"> -->
                                        <span style="font-size:18px; font-weight:700;">K PLUS</span>
                                    </div>
                                    
                                    <div class="mb-1">
                                        <span style="font-size:11px; color:#888;">ชื่อบัญชี</span><br>
                                        <strong style="font-size:14px;">บจก. โลคอล อีทส์</strong>
                                    </div>
                                    <div class="mb-1">
                                        <span style="font-size:11px; color:#888;">สาขา</span><br>
                                        <strong style="font-size:14px;">สาขาสำนักแจ้งวัฒนะเมืองทองธานี</strong>
                                    </div>
                                    <div class="mb-3">
                                        <span style="font-size:11px; color:#888;">หมายเลขบัญชี</span><br>
                                        <strong style="font-size:15px;" id="thAccountNumber">056-1-85639-7</strong>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="navigator.clipboard.writeText($('#thAccountNumber').text())">คัดลอก</button>
                                </div>
                                <!-- Right: how-to -->
                                <div style="flex:1; padding:16px;">
                                    <strong style="font-size:14px;">วิธีโอนเงิน</strong>
                                    <ol style="list-style:none; font-size:13px; margin-top:10px; padding-left:0; line-height:1.8;" id="thHowToList">
                                        <li class="d-flex align-items-start gap-2 mb-2"><span>คัดลอกหมายเลขบัญชีโดยกดปุ่ม 'คัดลอก'</span></li>
                                        <li class="d-flex align-items-start gap-2 mb-2"><span>ไปที่ 'โอนเงิน'</span></li>
                                        <li class="d-flex align-items-start gap-2 mb-2"><span>เลือกรายการโอนเป็น 'บัญชีกสิกรไทย'</span></li>
                                        <li class="d-flex align-items-start gap-2 mb-2"><span>นำเลขบัญชีวางตรง 'ระบุเลขบัญชี' และกรอกตัวเลขในช่อง 'จำนวน'</span></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <!-- Payment expiry countdown -->
                        <div id="thCountdownBox" class="mb-3" style="display:none; border:1px solid #bee5eb; background:#e8f4f8; border-radius:8px; padding:10px 14px;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-size:13px; color:#0c5460;">
                                    <i class="bi bi-clock-history mr-1"></i>
                                    รายการชำระเงินจะหมดอายุในอีก
                                </span>
                                <span id="thCountdownTimer" style="font-family:'Courier New',monospace; font-size:16px; font-weight:700; color:#0c5460;">10:00</span>
                            </div>
                        </div>

                        <!-- Total amount -->
                        <div class="d-flex justify-content-between align-items-center mb-3" style="padding:0 4px;">
                            <span style="font-size:15px;">จำนวนที่ต้องชำระ</span>
                            <span id="thTotalAmount" style="font-size:20px; font-weight:700;">- บาท</span>
                        </div>
                        <!-- Upload slip -->
                        <div class="mb-3">
                            <label id="thSlipLabel" for="thSlipUpload"
                                style="display:block; border:2px dashed #d0d0d0; border-radius:12px; padding:28px 16px; cursor:pointer; text-align:center; color:#aaa; background:#fafafa; transition:border-color .2s, background .2s;"
                                onmouseover="this.style.borderColor='#888'" onmouseout="if(!document.getElementById('thSlipUpload').files[0])this.style.borderColor='#d0d0d0'">
                                <span id="thSlipIcon" class="d-block mb-2" style="line-height:1;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#aaa" viewBox="0 0 16 16">
                                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                        <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
                                    </svg>
                                </span>
                                <span id="thSlipFileName" style="font-size:14px;">อัปโหลดสลิปโอนเงิน</span>
                            </label>
                            <input type="file" class="d-none" id="thSlipUpload" accept="image/*"
                                onchange="
                                  const el=document.getElementById('thSlipFileName');
                                  const lbl=document.getElementById('thSlipLabel');
                                  const ico=document.getElementById('thSlipIcon');
                                  if(this.files[0]){
                                    el.textContent=this.files[0].name+' (พร้อมแล้ว)';
                                    el.style.color='#0d6efd';
                                    lbl.style.borderColor='#0d6efd';
                                    lbl.style.background='#e8f0fe';
                                    ico.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'30\' height=\'30\' fill=\'#0d6efd\' viewBox=\'0 0 16 16\'><path d=\'M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z\'/></svg>';
                                  }else{
                                    el.textContent='อัปโหลดสลิปโอนเงิน';
                                    el.style.color='';
                                    lbl.style.borderColor='#d0d0d0';
                                    lbl.style.background='#fafafa';
                                    ico.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'30\' height=\'30\' fill=\'#aaa\' viewBox=\'0 0 16 16\'><path d=\'M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5\'/><path d=\'M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z\'/></svg>';
                                  }
                                ">
                        </div>
                        <!-- Submit button -->
                        <button type="button"
                            style="width:100%; padding:14px; border:none; border-radius:10px; background:#1a73e8; color:#fff; font-size:16px; font-weight:600; cursor:pointer; margin-bottom:8px;"
                            onclick="sendThTransferProof()">ส่งหลักฐานการโอน</button>
                    </div>
                    <!-- end TH Payment Section -->

                    <div class="pt-2">
                        <div class="form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="enableCRM" onclick="enableCRMButton();">
                            <label class="form-check-label text-danger" for="enableCRM">Save to Monday</label>
                        </div>
                        <img src="assets/img/ballLoading.gif" alt="loading" id="ballLoading" width="30" height="30" style="display:none;">
                        <span id="countdownText" class="text-muted fw-bold" style="display:none; margin-left: 10px;"></span>
                        <button type="button" id="CRMButton" disabled class="btn btn-primary" style="display:none;" onclick="submitToCRM();">Save</button>
                    </div>
                </div>
            </div>

            <div class="respondFail">
                <div class="modal-header bg-danger d-flex flex-column">
                    <div class="w-100 d-flex flex-row justify-content-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="icon-box w-100 d-flex flex-row justify-content-center">
                        <img src="assets/img/icon-fail.svg" alt="fail" width="104">
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Oops!</h4>
                    <p class="text-danger fw-bold">The billing process was unsuccessful.</p>

                    <!-- กล่องแสดงเหตุผลจริงจาก Stripe / Server (ถูกเติมผ่าน JS: modalRespondAction) -->
                    <div id="failReasonBox"
                         class="alert alert-warning text-start small mx-auto"
                         style="display:none; max-height:180px; overflow:auto; white-space:pre-wrap; word-break:break-word; max-width:90%;">
                    </div>

                    <p>Please check your payment information <br> and wait 5 seconds and try submit again. <br><br>
                        If it still can't be done please contact <a href="mailto:admin@localforyou.com">admin@localforyou.com</a> </p>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/modalSubmit.js?v=2.0.0"></script>
<script>
function sendThTransferProof() {
    const slipInput = document.getElementById('thSlipUpload');
    const quotationID = $("#quotationID").val();
    const shopName = $("#shopName").val();
    const country = $("#formCountry").val();
    const sendBtn = $("button[onclick='sendThTransferProof()']");

    if (!slipInput.files || slipInput.files.length === 0) {
        alert('กรุณาเลือกไฟล์สลิปโอนเงินก่อน');
        return;
    }
    if (!quotationID) {
        alert('ไม่พบ Quotation ID กรุณาลองใหม่อีกครั้ง');
        return;
    }

    const fd = new FormData();
    fd.append('slip', slipInput.files[0]);
    fd.append('shopName', shopName);
    fd.append('country', country);
    fd.append('quotationID', quotationID);

    sendBtn.prop('disabled', true).text('กำลังส่ง...');

    $.ajax({
        url: settings.url_uploadSlip,
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function(res) {
            if (res.success) {
                sendBtn.text('ส่งสำเร็จ ✓').removeClass('btn-primary').css({'background':'#198754','color':'#fff'});
                $("#thSlipFileName").text(slipInput.files[0].name + ' (อัปโหลดพร้อมแล้ว)').css('color', '#0d6efd');
            } else {
                alert('เกิดข้อผิดพลาด: ' + res.message);
                sendBtn.prop('disabled', false).text('ส่งหลักฐานการโอน');
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการส่งไฟล์ กรุณาลองใหม่');
            sendBtn.prop('disabled', false).text('ส่งหลักฐานการโอน');
        }
    });
}
</script>