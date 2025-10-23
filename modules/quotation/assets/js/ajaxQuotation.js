cmdSubmit.on("click", async function () {
    function ajaxSaveQuotationToPDF() {
    const test = "test";

     const quotationToPDF = $.ajax({
        url: settings.url_saveQuestionToDB,
        method: 'GET',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "test": test
        }
    });

        quotationToPDF.done(function(res) {
        console.log(res);
        $("#quotationID").val(res.quotationID);
        return true;
    });

        quotationToPDF.fail(function(xhr, status, error) {
        console.log("Save to DB fail!!");
        console.log(status + ': ' + error);
        return false;
    });
    }

});