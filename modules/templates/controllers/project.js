const modalFormEl = $('#modalForm');
const modalForm = new bootstrap.Modal(document.getElementById('modalForm'));
const inputProjectName = $("#projectName");
const inputShopTypeID = $("#shopType");
const inputCountry = $("#country");
const inputEditID = $("#editID");
const inputAction = $("#frmAction");
const inputLoginID = $("#loginID");
const selectedTemplate = $("#selectedTemplate");

$callback = 'callback';

let payload = {};

$(function() {
    loadData();
    loadCountry();
    loadShopType();
});//ready

const loadCountry = () => {
    payload = {
        act: 'readForSelectInput',
        token: Math.random()
    };

    const readCountry = $.ajax({
        url: "../models/country.php",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: payload
    });

    readCountry.done(function(res) {
        let row = res.data;
            row.forEach(item => {
                let {id, code, name} = item;
                let optionHTML = `<option value="${id}">${code} : ${name}</option>`;
                inputCountry.append(optionHTML);
            });//foreach
        return true;
    });

    readCountry.fail(function(xhr, status, error) {
        console.log("ajax call fail!!");
        console.log(status + ": " + error);
        return false;
    });
}//load Country

const loadShopType = () => {
    payload = {
        act: 'readForSelectInput',
        token: Math.random()
    };

    const readShopType = $.ajax({
        url: "../models/shopType.php",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: payload
    });

    readShopType.done(function(res) {
        let row = res.data;
        row.forEach(item => {
            let {id, name} = item;
            let optionHTML = `<option value="${id}">${id} : ${name}</option>`;
            inputShopTypeID.append(optionHTML);
        });//foreach
        return true;
    });

    readShopType.fail(function(xhr, status, error) {
        console.log("ajax call shopType fail!!");
        console.log(status + ": " + error);
        return false;
    });
}//load Country

const loadData = () => {
    payload = {
        act: 'read',
        ownerID: inputLoginID.val(),
        token: Math.random()
    };

    const readProject = $.ajax({
        url: "../models/project.php",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: payload
    });

    readProject.done(function(res) {
        $('#projectData > tbody').empty();
        let allData = res.data.length;
        let row = res.data;
        let i = 0;
        const iconSendMailDraft = '<img src="../assets/img/sendMailGray.svg" alt="Send Mail" title="Send Mail" class="action_icon">';
        const iconSendMailReady = '<img src="../assets/img/sendMail.svg" alt="Send Mail" title="Send Mail" class="action_icon">';
        const iconSendMailSend = '<img src="../assets/img/sendMailGreen.svg" alt="Send Mail" title="Send Mail" class="action_icon">';
        const iconPageGray = '<img src="../assets/img/page_gray.svg" alt="Unsubmit page" class="action_icon page_icon">';
        const iconPageGreen = '<img src="../assets/img/page_green.svg" alt="Submit page" class="action_icon page_icon">';
        const iconNext = '<img src="../assets/img/next.svg" alt="detail" title="Detail" class="action_icon">';
        const iconEdit = '<img src="../assets/img/edit.svg" alt="edit" title="Edit" class="action_icon">';
        const iconDelete = '<img src="../assets/img/del.svg" alt="delete" title="Delete" class="action_icon">';
        const iconTemplate = '<img src="../assets/img/template.svg" alt="Edit Template" title="Edit Template" class="action_icon">';
        const iconTemplateGray = '<img src="../assets/img/template_gray.svg" alt="Edit Template" title="Edit Template" class="action_icon">';

        if (allData > 0) {
            row.forEach(item => {
                let { saveFlag, projectID: id, projectName: name, shopType, selectedTemplate, owner, countryName: country, statusID: status, homePage, aboutPage, servicesPage, contactPage } = item;
                let statusText = (status === 1) ? 'Draft' : 'Send';
                let url = `main.php?m=detail&id=${id}`;
                let temPage = (shopType === "Restaurant") ? 'res' : 'mas';
                temPage = temPage + selectedTemplate;
                let templateUrl = `main.php?m=${temPage}&id=${id}`;

                let iconTemplateUse = (saveFlag === 1) ? iconTemplate : iconTemplateGray;
                let linkTemplate = (saveFlag === 1) ? `<a href="${templateUrl}">${iconTemplateUse}</a>` : `${iconTemplateUse}`;

                let iconPage;
                if (shopType === 'Restaurant') {
                    let iconPageHome = (homePage === null) ? `<span title="Home">${iconPageGray}</span>` : `<span title="Home">${iconPageGreen}</span>`;
                    let iconPageAbout = (aboutPage === null) ? `<span title="About">${iconPageGray}</span>` : `<span title="About">${iconPageGreen}</span>`;
                    let iconPageContact = (contactPage === null) ? `<span title="Contact">${iconPageGray}</span>` : `<span title="Contact">${iconPageGreen}</span>`;
                    iconPage = `${iconPageHome} ${iconPageAbout} ${iconPageContact}`;
                } else if (shopType === 'Massage') {
                    let iconPageHome = (homePage === null) ? `<span title="Home">${iconPageGray}</span>` : `<span title="Home">${iconPageGreen}</span>`;
                    let iconPageAbout = (aboutPage === null) ? `<span title="About">${iconPageGray}</span>` : `<span title="About">${iconPageGreen}</span>`;
                    let iconPageServices = (servicesPage === null) ? `<span title="Services">${iconPageGray}</span>` : `<span title="Services">${iconPageGreen}</span>`;
                    let iconPageContact = (contactPage === null) ? `<span title="Contact">${iconPageGray}</span>` : `<span title="Contact">${iconPageGreen}</span>`;
                    iconPage = `${iconPageHome} ${iconPageAbout} ${iconPageServices} ${iconPageContact}`;
                }

                let iconSendMail;
                if (shopType === 'Restaurant') {
                    if (homePage !== null && aboutPage !== null && contactPage !== null) 
                        {
                        iconSendMail = `<a href="#" onclick="sendProject(${id});">${iconSendMailReady}</a>`;
                        if (status === 1) {
                            statusText = "Ready";
                        }
                    } else {
                        iconSendMail = `<a>${iconSendMailDraft}</a>`;
                    }
                } else if (shopType === 'Massage') {
                    if (homePage !== null && aboutPage !== null && contactPage !== null && servicesPage !== null) {
                        iconSendMail = `<a href="#" onclick="sendProject(${id});">${iconSendMailReady}</a>`;
                        if (status === 1) {
                            statusText = "Ready";
                        }
                    } else {
                        iconSendMail = `<a>${iconSendMailDraft}</a>`;
                    }
                }

                if (status === 2) {
                    iconSendMail = `<a>${iconSendMailSend}</a>`;
                }

                $('#projectData > tbody:last-child').append(
                    `<tr>
                        <td>${++i}</td>
                        <td>${shopType} ${selectedTemplate}</td>
                        <td>${country} : ${name}</td>
                        <td>${iconPage}</td>
                        <td>${statusText}</td>
                        <!--<td>${owner}</td>-->
                        <td class="d-flex justify-content-end gap-2">
                            ${iconSendMail}
                            ${linkTemplate}
                            <a href="${url}">${iconNext}</a>
                            <a href="#" onclick="setEdit(${id});">${iconEdit}</a>
                            <a href="#" onclick="setDel(${id});">${iconDelete}</a>
                        </td>
                    </tr>`
                );
            });//foreach

        }else {
            $('#projectData > tbody:last-child').append(
                `<tr>
                    <th colspan="6">No Data</th>
                </tr>`
            );
        }
        return true;
    });

    readProject.fail(function(xhr, status, error) {
        console.log("ajax call fail!!");
        console.log(status + ": " + error);
        return false;
    });

}//load data



const setEdit = (id) => {
    console.log('Edit ID = ',id);

    if (id){
        payload = {
            act: 'setEdit',
            editID: id,
            ownerID: inputLoginID.val(),
            token: Math.random()
        };

        const readProject = $.ajax({
            url: "../models/project.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

        readProject.done(function(res) {
            console.log(res);
            let row = res.data;

            row.forEach( item => {
                let {projectID : id, projectName : name, shopTypeID, selectedTemplate, countryID : country} = item;
                inputProjectName.val(name);
                inputShopTypeID.val(shopTypeID);
                $("#selectedTemplate").val(selectedTemplate);
                inputCountry.val(country);
                inputEditID.val(id);
                inputAction.val('update');
            });
            modalShow();
            return true;
        });// ajax done

        readProject.fail(function(xhr, status, error) {
            console.log("ajax call fail!!");
            console.log(status + ": " + error);
            return false;
        }); //ajax fail
    }// if not empty, id
}//setEdit

const setDel = (id) => {
    let answer = confirm("Are you sure to delete this item?");
    console.log('Del ID = ',id);
    if (answer){
        payload = {
            act: 'del',
            delID: id,
            ownerID: inputLoginID.val(),
            token: Math.random()
        };

        const readProject = $.ajax({
            url: "../models/project.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

        readProject.done(function(res) {
            console.log(res);
            loadData();
            return true;
        });

        readProject.fail(function(xhr, status, error) {
            console.log("ajax call fail!!");
            console.log(status + ": " + error);
            return false;
        });
    } // if true
}//setDel

const saveForm = () => {
    const pname = inputProjectName.val();
    const countPname = pname.trim().length;

    if (countPname < 1) {
        $('label[for="projectName"]').css('color', 'red');
        inputProjectName.css('border-color', 'red');
        inputProjectName.css('box-shadow', '0 0 0 .2rem rgb(255 0 0 / 25%)');
        inputProjectName.focus();
        return;
    }

    payload = {
        act: inputAction.val(),
        name: inputProjectName.val(),
        shopTypeID: inputShopTypeID.val(),
        selectedTemplate: selectedTemplate.val(),
        country: inputCountry.val(),
        editID: inputEditID.val(),
        ownerID: inputLoginID.val(),
        token: Math.random()
    };

    const saveProjectAPI =
        $.ajax({
            url: "../models/project.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: payload,
        });//ajax

    saveProjectAPI.done(function (res) {
        if(res.result === "success"){
            loadData();
            modalClose();
        }else if(res.result === "fail"){
            alert('fail');
        }
    });

    saveProjectAPI.fail(function (xhr, status, error) {
        console.log("ajax call fail!!");
        console.log(status + ": " + error);
    });

}//saveForm

const frmReset = () => {
  $("#frmProject").trigger("reset");
    inputEditID.val('');
    inputShopTypeID.val(1);
    inputCountry.val(1);
    inputProjectName.val('');
    inputProjectName.css('border-color', '');
    inputProjectName.css('box-shadow', '');
    $('label[for="projectName"]').css('color', '');
    inputAction.val('add');
}
const modalShow = () => {
    modalForm.show();
}
const modalClose = () => {
    modalForm.hide();
    frmReset();
}

modalFormEl.on('hidden.bs.modal', function () {
    //alert('Modal is about to be Close');
    frmReset();
})

modalFormEl.on('show.bs.modal', function () {
    //alert('Modal is about to be shown');
});

function updateTemplates() {
    const inputShopTypeID = $("#shopType").val();

    selectedTemplate.empty();

    if (inputShopTypeID === "2") {
        $("<option>", { value: "1", text: "Template no. 1" }).appendTo(selectedTemplate);
    } else if (inputShopTypeID === "1") {
        const templates = {
            1: "Template no. 1",
            2: "Template no. 2",
            3: "Template no. 3"
        };

        $.each(templates, function (value, label) {
            $("<option>", { value: value, text: label }).appendTo(selectedTemplate);
        });
    }
}

function sendProject(id) {
    let answer = confirm("Are you sure you want to submit this project?");

    if (answer) {
        const callAjax = $.ajax({
            type: "GET",
            crossDomain: true,
            dataType: 'jsonp',
            jsonpCallback: "l4uCallback",
            headers: {  'Access-Control-Allow-Origin': 'https://report.localforyou.com' },
            url: "https://report.localforyou.com/modules/templates/assets/php/sendProject.php",
            data: {
                "loginID": inputLoginID.val(),
                "projectID": id
            }
        });
        // callAjax.done(function (res) {
        //     console.log(res);
            
        //     if(res.result === 1){
        //         alert('Reload page');
        //         //location.reload();
        //     }
        // });
        // callAjax.fail(function (xhr, status, error) {
        //     console.log("ajax request fail!!");
        //     console.log(status + ": " + error);
        // });
    }//if
}//sendEmail

function l4uCallback(response) {
    console.log("Template Submittion Response:", response);
}