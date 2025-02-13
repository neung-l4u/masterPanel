<?php
$teams = array("None", "Sales", "Customer Support", "Account Manager", "Marketing", "IT", "HR")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Account Form</title>
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">Create Employee Account</h3>
    <form method="POST" enctype="multipart/form-data" action="result.php">
        <h5 class="mt-3">Employee Details</h5>
        <div class="border rounded p-2 mb-4">
        <table class="table table-borderless table-hover">
            <tr>
                <td>
                    <label for="employee_id" class="form-label">รหัสพนักงาน</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">LOC</span>
                        <input id="employee_id" type="text" name="employee_id" class="form-control">
                    </div>
                </td>
                <td>
                    <label for="start_date" class="form-label">วันที่เริ่มงาน</label>
                    <input id="start_date" type="date" name="start_date" class="form-control" value="<?php echo getNextMonday(); ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="team" class="form-label">ทีม</label>
                    <select id="team" name="team" class="form-select">
                        <?php
                        foreach ($teams as $team) {
                            echo '<option value="' . $team . '">' . $team . '</option>';
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <label for="position" class="form-label">ตำแหน่ง</label>
                    <input id="position" type="text" name="position" class="form-control">
                </td>
            </tr>

            </table>
        </div>

        <h5 class="mt-3">Personal Information</h5>
        <div class="border rounded p-2 mb-4">
        <table class="table table-borderless table-hover">
            <tr>
                <td>
                    <label for="nickname_th" class="form-label">ชื่อเล่น (ไทย)</label>
                    <input id="nickname_th" type="text" name="nickname_th" class="form-control">
                </td>
                <td>
                    <label for="fullName_th" class="form-label">ชื่อจริงและนามสกุล (ไทย)</label>
                    <input id="fullName_th" type="text" name="fullName_th" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="nickname_en" class="form-label">ชื่อเล่น (อังกฤษ)</label>
                    <input id="nickname_en" type="text" name="nickname_en" class="form-control">
                </td>
                <td>
                    <label for="fullName_en" class="form-label">ชื่อจริงและนามสกุล (อังกฤษ)</label>
                    <input id="fullName_en" type="text" name="fullName_en" class="form-control">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="dob" class="form-label">วันเกิด</label>
                    <input id="dob" type="date" name="dob" class="form-control" value="">
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <label for="mobile" class="form-label">เบอร์มือถือ</label>
                    <input id="mobile" type="tel" name="mobile" class="form-control">
                </td>
                <td>
                    <label for="personal_email" class="form-label">อีเมลส่วนตัว</label>
                    <input id="personal_email" type="email" name="personal_email" class="form-control">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="address" class="form-label">ที่อยู่ปัจจุบัน</label>
                    <textarea id="address" name="address" class="form-control"></textarea>
                </td>
            </tr>
        </table>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
</body>
</html>
<?php
function getNextMonday(): string
{
    return date('Y-m-d', strtotime('next monday'));
}
?>