<?php

?>
<link rel="stylesheet" href="../assets/css/home.css">
<script src="../controllers/home.js"></script>
<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">

<div class="row">
    <div class="col pt-5">
        <h5>How to use</h5>
        <ol>
            <li>go to <a href="main.php?m=project">Project</a> page to view the full list</li>
            <li>Select an existing project or add a new one as you wish.</li>
            <li>Click on the project to fill in the details.</li>
            <li>You can save to stop or resume at any time.</li>
            <li>Once you have filled in all the information, click back to the project list page.</li>
            <li>At the end of the project name line is a Send Email button. When you press it, all the project data will be sent to the IT team and the status will change from Draft to Sent.</li>
            <li>The system will email the relevant person and send a copy to your email.</li>
            <li>done</li>
        </ol>
    </div>
</div>
