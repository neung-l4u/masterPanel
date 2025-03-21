<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>validate</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col">
            <label for="name" id="labelName">Name</label>
            <span id="warnName" class="text-danger">No No No</span>
            <input type="text" name="name" id="name" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button onclick="sendIT();">Send</button>
        </div>
    </div>
</div>

<!-- Optional JavaScript; choose one of the two! -->

<!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#warnName').hide();
    });


    function sendIT() {
        let name = $('#name').val();
        let email = $('#email').val();
        console.log("name = " + name.length);
        console.log("email = " + email.length);

        if ( name.length < 1) {
            //alert("Name is required");
            //$('#labelName').addClass('text-danger');
            $('#warnName').show();
            $('#name').focus();
            return;
        }else if ( email.length < 1) {
            $('#labelName').removeClass('text-danger');
            alert("Email is required");
            return;
        }else {
            alert("All is OK");
        }

    }//function
</script>
</body>
</html>