<?php
global $db;
include_once 'db/db.php';
include_once 'db/initDB.php';

include 'function.php';

// กำหนดค่าเริ่มต้น
$generatedTicket = null;

// ตรวจสอบเมื่อส่งข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'];
    $generatedTicket = generateTicketCode($projectId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Ticket Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Generate Ticket Code</h1>
    <div class="card mt-4">
        <div class="card-body">
            <div><?php echo 'raaaa'; ?></div>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="project_id" class="form-label">Select Project</label>
                    <select class="form-select" id="project_id" name="project_id" required>
                        <option value="" disabled selected>-- Select Project --</option>
                            <?php
                            $projects = $db->query('SELECT * FROM projects')->fetchAll();
                            foreach ($projects as $val){
                                ?>
                                <option value="<?php echo $val['project_id']; ?>">
                                    <?php echo $val['project_name']; ?>
                                </option>
                            <?php }//foreach ?>
                    </select>
                </div>
                <div><?php echo 'row = '.count($projects); ?></div>
                <button type="submit" class="btn btn-primary w-100">Generate Ticket</button>
            </form>
        </div>
    </div>

    <?php if ($generatedTicket): ?>
        <div class="alert alert-success mt-4 text-center">
            <strong>Generated Ticket Code:</strong> <?= $generatedTicket ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>