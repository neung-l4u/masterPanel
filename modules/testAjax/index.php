
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script> <!-- v5.3.2 -->
    <script>
        $("#cmdSubmit").click(function () {

            //console.log("Form Submitted");

            let payload = {
                mode : "save",
                shop_name : $("#shopName").val(),
                shop_type : $("#shopType").val(),
                template : $("#template").val(),
                po_name : $("#po").val(),
                address : $("#address").val(),
                token: Math.random()
            };

            //console.log("Payload", payload);

            const callAjax = $.ajax({
                url: "assets/php/actionAjax.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload
            });

            callAjax.done(function(res) {
                console.log("return = ",res);
                resetForm();
                return true;
            });

            callAjax.fail(function(xhr, status, error) {
                console.log("ajax fail!!");
                console.log(status + ': ' + error);
                return false;
            });

        });//cmdSubmit.click

        $("#loadHTML").click(function () {
            $("#allData").empty();

            let payload = {
                mode : "loadHTML",
                token: Math.random()
            };

            const callAjax = $.ajax({
                url: "assets/php/loadHTML.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload
            });

            callAjax.done(function(res) {
                console.log("return = ",res.result);
                $("#allData").html(res.table);
                return true;
            });

            callAjax.fail(function(xhr, status, error) {
                console.log("ajax fail!!");
                console.log(status + ': ' + error);
                return false;
            });

        });//loadHTML.click

        $("#loadArray").click(function () {
            
            let payload = {
                mode : "loadArray",
                token: Math.random(),
                id : 44
            };

            const callAjax = $.ajax({
                url: "assets/php/loadArray.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload
            });

            callAjax.done(function(res) {
                console.log("return = ",res);

                let allData = res.data.length;
                let row = res.data;
                let i = 0;

                if (allData>0) {
                    row.forEach(item => {
                        $('#name').html(item.projectName);
                        $('#shopType2').html(item.projectID);

                    });//foreach
                }

                return true;
            });

            callAjax.fail(function(xhr, status, error) {
                console.log("ajax fail!!");
                console.log(status + ': ' + error);
                return false;
            });

        });//loadArray.click


        function resetForm() {
            $("#shopName").val('');
            $("#shopType").val(0);
            $("#template").val(0);
            $("#po").val('Steve');
            $("#address").val('');
        }//resetForm
    </script>
</body>
</html>