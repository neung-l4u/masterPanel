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
const WordpressUser = $("#wWordpressUser");
const WordpressPass = $("#wWordpressPass");
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

            if (res.wSystemGloriaFood === 1) {
                System.text("Gloria Food");
            } else if (res.wSystemAmelia === 1 && res.wSystemVoucher === 1) {
                System.text("Amelia, Voucher");
            } else if (res.wSystemAmelia === 1) {
                System.text("Amelia");
            } else if (res.wSystemVoucher === 1) {
                System.text("Voucher");
            }

            DomainName.text(res.wDomain);
            DomainProviderID.text(res.wDomainProviderID);
            PublishDate.text(res.wPublishDate);
            LiveStatus.text(res.wLiveStatus);
            CPanelUser.text(res.wCPanelUser);
            CPanelPass.text(res.wCPanelPass);

            urlTxt = `<a href="${res.wWordpressURL}" target="_blank">${iconLink}</a> ${res.wWordpressURL}`;
            WordpressURL.html(urlTxt);

            txt = `<a onclick="copyText('admin@localforyou.com')" href="#">${iconCopy}</a> admin@localforyou.com`;
            txt2 = `<a onclick="copyText('L4U=New@min')" href="#">${iconCopy}</a> L4U=New@min`;

            WordpressUser.html(txt);
            WordpressPass.html(txt2);

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

function filterChange() {
    shopType = filterShopType.val();
    system = filterSystem.val();
    fstatus = filterStatus.val();
    $('#datatable').DataTable().ajax.reload();
}

$(() => {
    $("#alert").hide();

    $('#datatable').DataTable({
        pagingType: 'full_numbers',
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
                d.fstatus = filterStatus.val()
            }
        },
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [2, 4, 5] },
            { "bSearchable": false, "aTargets": [0, 5] }
        ]
    });
});
