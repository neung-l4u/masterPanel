const ProjectName = $("#wProject");
const Location = $("#wLocation");
const Owner = $("#wOwner");
const OwnerEmail = $("#wOwnerEmail");
const Industry = $("#wIndustry");
const TemplateUsed = $("#wTemplateUsed");
const System = $("#wSystem");
const DomainName = $("#wDomain");
const DomainProviderID = $("#wDomainProviderID");
const PublishDate = $("#wPublishDate");
const LiveStatus = $("#wLiveStatus");
const CPanelUser = $("#wCPanelUser");
const CPanelPass = $("#wCPanelPass");
const WordpressURL = $("#wWordpressURL");
const AdminSupportPass = $("#wAdminSupportPass");
const MASK = "••••••••••";
const iconEye = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>';
const iconEyeSlash = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>';
let adminPass = "";

// แสดง/ซ่อนรหัส Admin (ซ่อนเป็นค่าเริ่มต้นทุกครั้งที่เปิด popup)
function showAdminPass(show) {
    AdminSupportPass.text(show ? adminPass : MASK);
    $("#btnShowAdminPass").html(show ? iconEyeSlash : iconEye).attr("title", show ? "ซ่อนรหัส" : "ดูรหัส");
}
const SMTPEmailUser = $("#wSMTPEmailUser");
const SMTPEmailPass = $("#wSMTPEmailPass");
const SMTPRemark = $("#wSMTPRemark");
const ContactEmailUser = $("#wContactEmailUser");
const ContactEmailPass = $("#wContactEmailPass");
const ContactEmailRemark = $("#wContactEmailRemark");

const filterShopType = $("#filterShopType");
const filterSystem = $("#filterSystem");
const filterStatus = $("#filterStatus");

let shopType = filterShopType.val();
let system = filterSystem.val();
let fstatus = filterStatus.val();

let txt = '';
let txt2 = '';
let urlTxt = '';

let iconCopy = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg>';
let iconLink = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg>';

const viewDetail = (id) => {
    $.ajax({
        url: "../models/actionWebsiteList.php",
        method: "POST",
        dataType: "json",
        data: {
            act: "viewDetail",
            id: id
        },
        success: function (res) {
            txt = `<a onclick="copyText('${res.wProject}')" href="#">${iconCopy}</a> ${res.wProject}`;
            ProjectName.html(txt);
            Location.text(res.wLocation);
            Owner.text(res.wOwner);
            OwnerEmail.text(res.wOwnerEmail);
            Industry.text(res.wIndustry);
            TemplateUsed.text(res.wTemplateUsed);

            const systems = [];
            if (res.wSystemGloriaFood == 1) systems.push("Gloria Food");
            if (res.wSystemAmelia == 1) systems.push("Amelia");
            if (res.wSystemVoucher == 1) systems.push("Voucher");
            if (res.wSystemCloudwaitress == 1) systems.push("Cloudwaitress");
            System.text(systems.length ? systems.join(", ") : "-");

            DomainName.text(res.wDomain);
            DomainProviderID.text(res.wDomainProviderID);
            PublishDate.text(res.wPublishDate);
            LiveStatus.text(res.wLiveStatus);
            CPanelUser.text(res.wCPanelUser);
            CPanelPass.text(res.wCPanelPass);

            urlTxt = `<a href="${res.wWordpressURL}" target="_blank">${iconLink}</a> ${res.wWordpressURL}`;
            WordpressURL.html(urlTxt);


            adminPass = res.wAdminSupportPass === "-" ? "" : res.wAdminSupportPass;
            $("#btnShowAdminPass, #btnCopyAdminPass").prop("disabled", !adminPass);
            if (adminPass) showAdminPass(false);
            else AdminSupportPass.text("-");

            SMTPEmailUser.text(res.wSMTPEmailUser);
            SMTPEmailPass.text(res.wSMTPEmailPass);
            SMTPRemark.text(res.wSMTPRemark);
            ContactEmailUser.text(res.wContactEmailUser);
            ContactEmailPass.text(res.wContactEmailPass);
            ContactEmailRemark.text(res.wContactEmailRemark);

            $("#detailModal").modal("show");
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
};

function showCopy() {
    $("#alert").fadeIn(500).delay(1000).fadeOut();
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(showCopy).catch(err => {
        console.error("Error copying text:", err);
    });
}

function resetFilters() {
    filterShopType.val('');
    filterSystem.val('');
    filterStatus.val('');
    $("#filterCountry").val('');
    filterChange();
}

function filterChange() {
    shopType = filterShopType.val();
    system = filterSystem.val();
    fstatus = filterStatus.val();
    $('#datatable').DataTable().ajax.reload();
}

$(() => {
    $("#alert").hide();

    // คลิกค่าที่มี class copyable ใน popup เพื่อ copy (อ่านจาก text ไม่ต้องใส่ค่าใน onclick เพราะรหัสบางตัวมี ')
    $(document).on("click", ".copyable", function () {
        const text = $(this).text().trim();
        if (text && text !== "-") copyText(text);
    });
    $(".copyable").attr("title", "คลิกเพื่อ copy");

    $("#btnShowAdminPass").on("click", () => showAdminPass(AdminSupportPass.text() === MASK));
    $("#btnCopyAdminPass").on("click", () => copyText(adminPass));

    // รหัส Admin Support กลางที่หัวหน้า (ซ่อนไว้ กดดวงตาเพื่อดู)
    const headerPass = $("#headerAdminPass");
    $("#btnShowHeaderPass").html(iconEye).on("click", function (e) {
        e.preventDefault();
        const show = headerPass.text() === MASK;
        headerPass.text(show ? headerPass.data("pass") : MASK);
        $(this).html(show ? iconEyeSlash : iconEye).attr("title", show ? "ซ่อนรหัส" : "ดูรหัส");
    });

    $('#datatable').DataTable({
        pagingType: 'simple_numbers',
        language: {
            search: '',
            searchPlaceholder: 'ค้นหาชื่อร้าน, โดเมน, อีเมล...'
        },
        pageLength: 14,
        lengthMenu: [
            [14, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],
        ajax: {
            url: '../models/dataWebsiteList.php',
            type: 'POST',
            dataSrc: 'data',
            data: function (d) {
                d.shopType = filterShopType.val(),
                d.system = filterSystem.val(),
                d.fstatus = filterStatus.val(),
                d.country = $("#filterCountry").val()
            }
        },
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [7] },
            { "bSearchable": false, "aTargets": [0, 7] }
        ]
    });
});
