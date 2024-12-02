<?php
if (empty($_SESSION['id'])){ ?>
    <script>
        location.replace("https://report.localforyou.com/chkLogin.php?act=logout");
    </script>
<?php } ?>

