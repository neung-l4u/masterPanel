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

const viewDetail = (id) => {
    const reqAjax = $.ajax({
        url: "../models/actionWebsiteList.php",
        method: "POST",
        async: false,
        cache: false,
        dataType: "json",
        data: {
            act: "viewDetail",
            id: id,
        },
    });

    reqAjax.done(function (res) {
        console.log(res);
        ProjectName.text(res.wProject);
        Location.text(res.wLocation);
        Owner.text(res.wOwner);
        OwnerEmail.text(res.wOwnerEmail);
        Industry.text(res.wIndustry);
        TemplateUsed.text(res.wTemplateUsed);
        if(res.wSystemGloriaFood === 1) {
            System.text("Gloria Food");
        }else if(res.wSystemAmelia === 1) {
            System.text("Amelia");
        }else if(res.wSystemVoucher === 1) {
            System.text("Voucher");
        }else if(res.wSystemAmelia === 1 && res.wSystemVoucher === 1) {
            System.text("Amelia, Voucher");
        }
        DomainName.text(res.wDomain);
        DomainProviderID.text(res.wDomainProviderID);
        PublishDate.text(res.wPublishDate);
        LiveStatus.text(res.wLiveStatus);
        CPanelUser.text(res.wCPanelUser);
        CPanelPass.text(res.wCPanelPass);
        WordpressURL.text(res.wWordpressURL);
        WordpressUser.text(res.wWordpressUser);
        WordpressPass.text(res.wWordpressPass);
        SMTPEmailUser.text(res.wSMTPEmailUser);
        SMTPEmailPass.text(res.wSMTPEmailPass);
        SMTPRemark.text(res.wSMTPRemark);
        ContactEmailUser.text(res.wContactEmailUser);
        ContactEmailPass.text(res.wContactEmailPass);
        ContactEmailRemark.text(res.wContactEmailRemark);
        $("#detailModal").modal("show");
    });

    reqAjax.fail(function (xhr, status, error) {
        console.log("ajax request fail!!");
        console.log(status + ": " + error);
    });
}// viewDetail

    // const setStatus = (id, status) => {
    //     const flagStatus = !status ? 1 : 0;
    //     const reqAjax = $.ajax({
    //         url: "assets/php/actionStaffs.php",
    //         method: "POST",
    //         async: false,
    //         cache: false,
    //         dataType: "json",
    //         data: {
    //             act: "setStatus",
    //             id: id,
    //             status: flagStatus
    //         },
    //     });

    //     reqAjax.done(function (res) {
    //         reloadTable();
    //     });

    //     reqAjax.fail(function (xhr, status, error) {
    //         console.log("ajax request fail!!");
    //         console.log(status + ": " + error);
    //     });
    // }// const
    

    // const setEdit = (id) => {
    //     const inputName = $("#inputName");
    //     const inputTname = $("#inputTname");
    //     const inputNickName = $("#inputNickName");
    //     const inputStartDate = $("#inputStartDate");
    //     const inputEmployeeNumber = $("#inputEmployeeNumber");
    //     const inputAddress = $("#inputAddress");
    //     const inputBirthday = $("#inputBirthday");
    //     const inputEmail = $("#inputEmail");
    //     const inputPhone = $("#inputPhone");
    //     const inputPassword = $("#inputPassword");
    //     const passwordNotAllow = $("#passwordNotAllow");
    //     const inputLevel = $("#inputLevel");
    //     const inputReligion = $("#inputReligion");
    //     const inputTeam = $("#inputTeam");
    //     const statusOn = $("#statusOn");
    //     const statusOff = $("#statusOff");
    //     const editID = $("#editID");
    //     const formAction = $("#formAction");
    //     const reqAjax = $.ajax({
    //         url: "assets/php/actionStaffs.php",
    //         method: "POST",
    //         async: false,
    //         cache: false,
    //         dataType: "json",
    //         data: {
    //             act: "loadUpdate",
    //             id: id,
    //         },
    //     });

        
    //     reqAjax.done(function (res) {
    //         console.log(res);
    //         inputName.val(res.name);
    //         inputTname.val(res.tname);
    //         inputNickName.val(res.nickname);
    //         inputBirthday.val(res.birthday);
    //         inputStartDate.val(res.startdate);
    //         inputEmployeeNumber.val(res.employeenumber);
    //         inputAddress.val(res.address);
    //         inputEmail.val(res.email);
    //         inputPhone.val(res.phone);
    //         inputPassword.val("Encrypted : " + res.password).attr('disabled', 'disabled');
    //         passwordNotAllow.show();
    //         inputLevel.val(res.level);
    //         inputReligion.val(res.religion)
    //         inputTeam.val(res.team)
    //         if(res.status === 1) {
    //             statusOff.prop('checked', false);
    //             statusOn.prop('checked', true);
    //         }else{
    //             statusOn.prop('checked', false);
    //             statusOff.prop('checked', true);
    //         }
    //         editID.val(res.id);
    //         formAction.val("edit");
    //         modalFormAction("open");
    //     });

    //     reqAjax.fail(function (xhr, status, error) {
    //         console.log("ajax request fail!!");
    //         console.log(status + ": " + error);
    //     })
    // }// const

    // const formSave = () => {
    //     const inputName = $("#inputName");
    //     const inputTname = $("#inputTname");
    //     const inputNickName = $("#inputNickName");
    //     const inputBirthday = $("#inputBirthday");
    //     const inputStartDate = $("#inputStartDate");
    //     const inputEmployeeNumber = $("#inputEmployeeNumber");
    //     const inputAddress = $("#inputAddress");
    //     const inputEmail = $("#inputEmail");
    //     const inputPhone = $("#inputPhone");
    //     const inputPassword = $("#inputPassword");
    //     const inputReligion = $("#inputReligion");
    //     const inputTeam = $("#inputTeam");
    //     const inputLevel = $("#inputLevel");
    //     const editID = $("#editID");
    //     const formAction = $("#formAction");

    //     let statusValue = $("input[name='inputStatus']:checked").val();

    //     let payload = {
    //             act: "save",
    //             inputName : inputName.val(),
    //             inputTname : inputTname.val(),
    //             inputNickName : inputNickName.val(),
    //             inputBirthday : inputBirthday.val(),
    //             inputStartDate : inputStartDate.val(),
    //             inputEmployeeNumber : inputEmployeeNumber.val(),
    //             inputAddress : inputAddress.val(),
    //             inputEmail : inputEmail.val(),
    //             inputPhone : inputPhone.val(),
    //             inputPassword : inputPassword.val(),
    //             inputReligion : inputReligion.val(),
    //             inputTeam : inputTeam.val(),
    //             inputLevel : inputLevel.val(),
    //             inputStatus : statusValue,
    //             editID : editID.val(),
    //             formAction : formAction.val()
    //         };

    //         console.log("payload=",payload);
            
    //     const reqAjax = $.ajax({
    //         url: "assets/php/actionStaffs.php",
    //         method: "POST",
    //         async: false,
    //         cache: false,
    //         dataType: "json",
    //         data: payload
    //     });
            
    //     reqAjax.done(function (res) {
    //         modalFormAction("close");
    //         console.log(res);
    //         reloadTable();
    //         resetForm();
    //         $("#formModal").modal('hide');
    //     });

    //     reqAjax.fail(function (xhr, status, error) {
    //         console.log("ajax request fail!!");
    //         console.log(status + ": " + error);
    //     });
        
    // }// const


    // const resetForm = () => {
    //     const inputName = $("#inputName");
    //     const inputTname = $("#inputTname");
    //     const inputNickName = $("#inputNickName");
    //     const inputBirthday = $("#inputBirthday");
    //     const inputStartDate = $("#inputStartDate");
    //     const inputEmployeeNumber = $("#inputEmployeeNumber");
    //     const inputAddress = $("#inputAddress");
    //     const inputEmail = $("#inputEmail");
    //     const inputPhone = $("#inputPhone");
    //     const inputPassword = $("#inputPassword");
    //     const inputReligion = $("#inputReligion");
    //     const inputTeam = $("#inputTeam");
    //     const inputLevel = $("#inputLevel");
    //     const statusOn = $("#statusOn");
    //     const statusOff = $("#statusOff");
    //     const editID = $("#editID");
    //     const formAction = $("#formAction");
    //     const passwordNotAllow = $("#passwordNotAllow");

    //     const date = new Date();

    //     let day = date.getDate();
    //     let month = date.getMonth() + 1;
    //     let year = date.getFullYear();
    //     let currentDate = `${year}-${month}-${day}`;

    //     inputName.val('');
    //     inputTname.val('');
    //     inputNickName.val('');
    //     inputBirthday.val('');
    //     inputStartDate.val(currentDate);
    //     inputEmployeeNumber.val('');
    //     inputAddress.val('');
    //     inputEmail.val('');
    //     inputPhone.val('');
    //     inputPassword.val('Localeats#2025').removeAttr('disabled');
    //     passwordNotAllow.hide();
    //     inputLevel.val('4');
    //     inputReligion.val('1');
    //     inputTeam.val('0');
    //     statusOn.prop('checked', true);
    //     statusOff.prop('checked', false);
    //     editID.val('');
    //     formAction.val('add');
    // }// const

    // const setDel = (delID) => {
    //     //alert ("Delete"+delID);

    //     let answer = confirm ("Are you sure to delete this Staff?");

    //     console.log (answer);
    //     if (answer === true){
    //         let payload = {
    //             act: "setDelete",
    //             id : delID
    //         };

    //         console.log("payload=",payload);

    //         const reqAjax = $.ajax({
    //             url: "assets/php/actionStaffs.php",
    //             method: "POST",
    //             async: false,
    //             cache: false,
    //             dataType: "json",
    //             data: payload
    //         });
                
    //         reqAjax.done(function (res) {
    //             modalFormAction("close");
    //             console.log(res);
    //             reloadTable();
    //             resetForm();
    //             $("#formModal").modal('hide');
    //         });

    //         reqAjax.fail(function (xhr, status, error) {
    //             console.log("ajax request fail!!");
    //             console.log(status + ": " + error);
    //         });

    //     }//if


    // }//setDel

function showCopy() {
    $("#alert").fadeIn(500);
    setTimeout(function () {
        $("#alert").fadeOut();
    }, 1000);
}
function copyText(text) {
    navigator.clipboard.writeText(text).then(function() {
        showCopy();
    }).catch(function(err) {
        console.error("Error copying text: ", error);
    });
}

$(() => {
    $("#alert").hide();

    $('#datatable').DataTable( {
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/dataWebsiteList.php',
            dataSrc: 'data'
        },
        "pageLength": 8,
        lengthMenu: [
            [8, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],columnDefs: [
            {
                targets: 5,
                className: 'dt-body-center'
            }
        ]
    } );
}); //ready
