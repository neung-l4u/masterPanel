<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = 'LOC' . $_POST['employee_id'];
    $nickname_th = $_POST['nickname_th'];
    $fullName_th = $_POST['fullName_th'];
    $nickname_en = $_POST['nickname_en'];
    $fullName_en = $_POST['fullName_en'];
    $dob = convertDateFormat($_POST['dob']);
    $mobile = $_POST['mobile'];
    $personal_email = $_POST['personal_email'];
    $address = $_POST['address'];
    $team = $_POST['team'];
    $position = $_POST['position'];
    $start_date = convertDateFormat($_POST['start_date']);

    $company_email = strtolower($nickname_en) . "." . getFirstTwoLower($fullName_en) ."@localforyou.com";
    $password = "Localeats#" . date("Y");
}
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Employee Account Result</title>
<!--        <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">-->
        <style>
            h6 {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            caption {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 5px;
                color: #006cfa;
            }

            table, td, th {
                border: 1px solid #cccccc;
            }

            tr{
                min-height: 30px;
            }

            table {
                width: 45%;
                border-collapse: collapse;
                margin-bottom: 2rem;
            }

            div, td, th{
                max-width:100%;
                white-space:nowrap;
            }

            th{
                text-align: left;
                width:50px;
                background-color: #0361D1;
                color: white;
                padding: 5px;
                padding-top: 10px;
                padding-bottom: 10px;
            }

            caption{
                text-align: left;
                margin-bottom: 5px;
            }

            th,td{
                padding-left: 10px;
            }
        </style>
    </head>
    <body style="padding: 2rem; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #000000; background-color: #ffffff;">
    <div>
        <h3>Employee Account Creation Details</h3><br>
        <h6>Subject:</h6>
        <p>Local For You | Employee Account Creation Details For <?php echo $nickname_en; ?></p>
        <h6>Message:</h6>
        <p>Hi <?php echo $nickname_en; ?>, <br><br>
            &nbsp;&nbsp;&nbsp;&nbsp; Welcome to Local For You! Your employee account has been successfully created. <br>Below, you’ll find the
            details
            you need to access our systems:</p>
    </div>
    <br><br>
    <div>
    <h4>Employee Details</h4>
    <table>
        <tr style="height: 36px;">
            <td>
                <b>Employee ID: </b>
                <?php echo $employee_id; ?>
            </td>
            <td>
                <b>Start Date: </b>
                <?php echo $start_date; ?>
            </td>
        </tr>
        <tr style="height: 36px;">
            <td>
                <b>Team: </b>
                <?php echo $team; ?>
            </td>
            <td>
                <b>Position: </b>
                <?php echo $position; ?>
            </td>
        </tr>
    </table> <br>
    <table>
        <tr>
            <th>Name TH</th>
            <td><?php echo $nickname_th . ' ' . $fullName_th; ?></td>
        </tr>
        <tr>
            <th>Name EN</th>
            <td><?php echo $nickname_en . ' ' . $fullName_en; ?></td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td><?php echo $dob; ?></td>
        </tr>
        <tr>
            <th>Mobile</th>
            <td><?php echo $mobile; ?></td>
        </tr>
        <tr>
            <th>Personal Email</th>
            <td><?php echo $personal_email; ?></td>
        </tr>
        <!--<tr>
            <th>Address</th>
            <td><?php /*echo $address; */?></td>
        </tr>-->
    </table>
    <br><br>

    <h4>Account Details</h4>
    <?php
    createTable("Master accounts for all platforms below", [
        ["Email", "$company_email"],
        ["Password", "$password"]
    ]);

    createTable("Company Email", [
        ["URL", "https://www.google.com"],
        ["Drive", "https://support.google.com/a/users/answer/13022292?hl=en"]
    ]);

    createTable("Zoom", [
        ["Desktop App", "https://zoom.us/download"],
        ["Extension Number", "--"],
        ["PinCode", "--"],
        ["Web portal", "https://zoom.us/signin#/login"]
    ]);

    createTable("Trainual", [
        ["URL", "https://app.trainual.com/local-for-you"]
    ]);

    createTable("Respond.io", [
        ["URL", "https://respond.io/"]
    ]);

    createTable("Monday", [
        ["URL", "https://local-for-you.monday.com/auth/login_monday/email_password"],
        ["Desktop app", "https://support.monday.com/hc/en-us/articles/115005316885-monday-com-s-desktop-app"]
    ]);

    createTable("Coin System", [
        ["URL", "https://report.localforyou.com/"]
    ]);
?>
</div>
    <br><br>
    <!--<script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>-->
</body>
</html>
<?php
function createTable($caption, $rows): void
{
    echo "<div>";

    echo "<table>";
    echo "<caption>$caption</caption>";
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<th>{$row[0]}</th>";
        echo "<td>{$row[1]}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

function convertDateFormat($date): string
{
    return date('d/m/Y', strtotime($date));
}

function getFirstTwoLower($name) {
    return strtolower(substr($name, 0, 2));
}
?>