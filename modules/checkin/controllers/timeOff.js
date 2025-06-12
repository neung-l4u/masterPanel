function getStaffTeam(id) {
    const staff = id;
    const $staffName = $("#staffName");
    const $staffTeam = $("#staffTeam");
    const $manager = $("#manager");

    $.ajax({
        url: '../models/getStaff.php',
        type: 'POST',
        dataType: 'json',
        data: {
            staff: staff
        }
    }).done((res) => {
        console.log(res);

        $staffName.val(res.staffName);
        $staffTeam.val(res.team).attr("selected", true);
        $manager.val(res.manager);
    }).fail(() => {
        alert("Failed to select team");
    });

}

const handleFileUpload = (input) => {
    const $staffName = $("#staffName").val().trim();
    const $form = $(input).closest("form");
    const $filePath = $form.find(".filePath");
    const $fileName = $form.find(".fileName");
    const files = input.files;

    if (files.length === 0) {
        alert("Please select a file.");
        return;
    }

    const fd = new FormData();
    fd.append('file', files[0]);
    fd.append('staffName', $staffName);

    $.ajax({
        url: '../models/upload.php',
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response !== "0" && response !== "") {
                const filePath = response;
                const fileName = response.split("/").pop();
                $filePath.val(filePath);
            } else {
                alert("File not uploaded.");
            }
        },
        error: function() {
            alert("Upload failed. Try again.");
        }
    });
};
