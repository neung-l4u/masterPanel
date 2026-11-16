<footer class="footer py-1 bg-light w-100" style="position: fixed; bottom: 0;">
    <div class="container">
        <div class="d-flex justify-content-between">
            <div style="font-size:0.8rem;">
                <img src="assets/img/newL4U-logo-100x100-2.png" alt="Monday Logo" style="width: 30px; height: 30px;">
                <span class="m-3" style="font-size:0.8rem; text-align: center;">
                    Quotation Form
                 </span>
            </div>

    </div>
</footer>

<script src="assets/js/jquery.3.6.0.min.js"></script>
<script src="assets/js/bootstrap5.0.2.bundle.min.js"></script>
<script src="assets/js/global_data.js?v=2.0.4"></script>
<!-- ใช้ settings ตัวเดียวกับฟอร์ม signup (ราคา/states/coupon ชุดเดียวกัน) -->
<script src="../signup/assets/js/settings.js?v=2.0.12"></script>
<script>
    // path ใน settings ของ signup เป็น relative กับโฟลเดอร์ signup จึงต้องชี้กลับไปที่ ../signup/
    settings.url_getProductList = "../signup/" + settings.url_getProductList;
    settings.url_getStates = "../signup/" + settings.url_getStates;
    settings.url_getCities = "../signup/" + settings.url_getCities;
    settings.url_couponCode = "../signup/assets/API/couponCode.json";
    // endpoint ของ quotation เอง
    settings.url_saveQuestionToDB = "assets/function/detailQuotation.php";
    settings.url_quotationPDF = "Monday/pdfQuotation.php";
</script>
<script src="assets/js/date_format.js"></script>
<script src="assets/js/getUserAgent.js"></script>
<script src="assets/js/util.js?v=2.0.4"></script>
<script src="assets/js/ajaxFunction.js?v=2.0.20"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script src="assets/js/popper.2.11.5.min.js"></script>

<script src="assets/js/bootstrap-multi-step-form.js?v=2.0.12"></script>

<input type="hidden" id="signupFormVersion" name="signupFormVersion" value="2.10.10" />