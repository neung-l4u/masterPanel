const modalSettingsEl = $('#modalSettings');
const modalSettings = new bootstrap.Modal(document.getElementById('modalSettings'));
const inputRecipient = $("#recipient");
const inputChannel = $("#channel");
const inputEditID = $("#editID");
const inputAction = $("#frmAction");
const inputLoginID = $("#loginID");
const selectedTemplate = $("#selectedTemplate");

let payload = {};

$(function() {
    loadData();
});//ready

const loadData = () => {
    payload = {
        act: 'read',
        ownerID: inputLoginID.val(),
        token: Math.random()
    };

    const readSettings = $.ajax({
        url: "../models/settings.php",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: payload
    });

    readSettings.done(function(res) {
        $('#settingsData > tbody').empty();
        let allData = res.data.length;
        let row = res.data;
        let i = 0;
        const iconEdit = '<img src="../assets/img/edit.svg" alt="edit" title="Edit" class="action_icon">';
        const iconDelete = '<img src="../assets/img/del.svg" alt="delete" title="Delete" class="action_icon">';

        if (allData>0) {
            row.forEach(item => {
                let {id, email, channel, status} = item;
                let textStatus = (status===1) ? 'On' : 'Off';

                $('#settingsData > tbody:last-child').append(
                    `<tr>
                        <td>${++i}</td>
                        <td>${email}</td>
                        <td>${channel}</td>
                        <td>${textStatus}</td>
                        <td class="d-flex justify-content-end gap-2">
                            <a href="#" onclick="setEdit(${id});">${iconEdit}</a>
                            <a href="#" onclick="setDel(${id});">${iconDelete}</a>
                        </td>
                    </tr>`
                );
            });//foreach

        }else {
            $('#settingsData > tbody:last-child').append(
                `<tr>
                    <th colspan="6">No Data</th>
                </tr>`
            );
        }
        return true;
    });

    readSettings.fail(function(xhr, status, error) {
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
            url: "../models/settings.php",
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
                inputRecipient.val();
                inputChannel.val();
                $("#selectedTemplate").val(selectedTemplate);
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
            url: "../models/settings.php",
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
    payload = {
        act: inputAction.val(),
        recipient: inputRecipient.val(),
        channel: inputChannel.val(),
        selectedTemplate: selectedTemplate.val(),
        editID: inputEditID.val(),
        ownerID: inputLoginID.val(),
        token: Math.random()
    };

    const saveSettingsAPI =
        $.ajax({
            url: "../models/settings.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: payload,
        });//ajax

    saveSettingsAPI.done(function (res) {
        if(res.result === "success"){
            loadData();
            modalClose();
        }else if(res.result === "fail"){
            alert('fail');
        }
    });

    saveSettingsAPI.fail(function (xhr, status, error) {
        console.log("ajax call fail!!");
        console.log(status + ": " + error);
    });

}//saveForm

const modalShow = () => {
    modalSettings.show();
}
const modalClose = () => {
    modalSettings.hide();
    frmReset();
}