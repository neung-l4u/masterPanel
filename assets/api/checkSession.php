<?php
if (empty($_SESSION['id'])){ ?>
    <script>
        //location.replace("https://report.localforyou.com/chkLogin.php?act=logout");
        location.replace("index.php");
    </script>
<?php } ?>

