const inputProjectName = $("#projectName");
const inputShopTypeID = $("#shopType");
const inputCountry = $("#country");
const inputEditID = $("#editID");
const inputAction = $("#frmAction");
const inputLoginID = $("#loginID");
const selectedTemplate = $("#selectedTemplate");

let payload = {};

$(function() {
    loadData();
    loadCountry();
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
                let {id, code, name, status} = item;
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
        emptyData();
        let allData = res.data.length;
        let row = res.data;
        let i = 0;
        const iconNext = '<img src="../assets/img/next.svg" alt="detail" title="Detail" class="action_icon">';
        const iconEdit = '<img src="../assets/img/edit.svg" alt="edit" title="Edit" class="action_icon">';
        const iconDelete = '<img src="../assets/img/del.svg" alt="delete" title="Delete" class="action_icon">';
        const iconOn = '<img src="../assets/img/on.svg" alt="Status On" title="Status On" class="status_icon">'
        const iconOff = '<img src="../assets/img/off.svg" alt="Status Off" title="Status Off" class="status_icon">';
        const iconTemplate = '<img src="../assets/img/template.svg" alt="Edit Template" title="Edit Template" class="action_icon">';

        if (allData>0) {
            row.forEach(item => {
                let {projectID : id, projectName : name, shopType, selectedTemplate, owner, countryName : country, projectTimeStamp, statusID : status} = item;
                let icon = (status===1) ? 'Draft' : 'Send';
                let url = `main.php?m=detail&id=${id}`;
                let temPage = (shopType==="Restaurant") ? 'res' : 'mas';
                temPage = temPage+selectedTemplate;
                let templateUrl = `main.php?m=${temPage}&id=${id}`;
                $('#projectData > tbody:last-child').append(
                    `<tr>
                        <td>${++i}</td>
                        <td>${shopType}</td>
                        <td style="text-align: center;">${selectedTemplate}</td>
                        <td>${name}</td>
                        <td>${icon}</td>
                        <td>${owner}</td>
                        <td>${country}</td>
                        <td class="d-flex justify-content-end gap-2">
                            <a href="${templateUrl}">${iconTemplate}</a>
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
                let {projectID : id, projectName : name, shopTypeID, selectedTemplate, projectOwner : owner, countryID : country, projectTimeStamp, statusID : status} = item;
                console.log("name = ",name);
                console.log("id = ",id);
                console.log("country = ",country);
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
    }// if not empty id
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

    //console.log('payload = ',payload);
    modalClose();

    const saveProjectAPI =
        $.ajax({
            //url: "https://report.localforyou.com/templates/assets/API/project.php",
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
    inputShopTypeID.val(0);
    inputCountry.val(0);
    inputProjectName.val('');
    inputAction.val('add');
}
const modalShow = () => {
    //$('.modal-backdrop').show();
    $('.modal-backdrop').show();
    $('#modalForm').modal('show');
}
const modalClose = () => {
    //$('#modalForm').modal('hide');
    $('#modalForm').hide();
    $('.modal-backdrop').hide();
    frmReset();
    //$('body').removeClass('modal-open');
    //$('.modal-backdrop').hide();
}

$('#modalForm').on('hidden.bs.modal', function (event) {
    frmReset();
})
const emptyData = () => {
    $('#projectData > tbody').empty();
}