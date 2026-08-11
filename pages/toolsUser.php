<?php
/**
 * User Tools — static SaaS tools directory (read-only, visible to all users).
 * Add a new tool by appending an entry to the $tools array below.
 */

$tools = [
    [
        "name"  => "QR Generator",
        "desc"  => "สร้าง QR Code สำหรับร้านอาหาร (In-house) ขนาด 9.7 x 9.7 cm พร้อมพิมพ์ลง A4",
        "link"  => "main.php?p=qrGenerator",
        "icon"  => "bi-qr-code",
        "badge" => "New",
    ],
    [
        "name"  => "Image Resizer",
        "desc"  => "ครอปและปรับขนาดรูปตามพรีเซ็ต Logo, Header, Logo icon, Favicon พร้อมปรับตำแหน่งได้",
        "link"  => "main.php?p=imageResizer",
        "icon"  => "bi-crop",
        "badge" => "New",
    ],
];
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-tools mr-2"></i>
                    Tools
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">User Tools</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <?php foreach ($tools as $tool) { ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="<?php echo htmlspecialchars($tool["link"], ENT_QUOTES); ?>" class="text-decoration-none">
                        <div class="card h-100 shadow-sm tool-card">
                            <div class="card-body d-flex align-items-start">
                                <div class="tool-icon mr-3">
                                    <i class="bi <?php echo htmlspecialchars($tool["icon"], ENT_QUOTES); ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 text-dark font-weight-bold">
                                        <?php echo htmlspecialchars($tool["name"], ENT_QUOTES); ?>
                                        <?php if (!empty($tool["badge"])) { ?>
                                            <span class="badge badge-success ml-1"><?php echo htmlspecialchars($tool["badge"], ENT_QUOTES); ?></span>
                                        <?php } ?>
                                    </h5>
                                    <p class="mb-0 text-muted small"><?php echo htmlspecialchars($tool["desc"], ENT_QUOTES); ?></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<style>
    .tool-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .tool-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .12) !important;
        border-color: #0d6efd;
    }
    .tool-icon {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e7f1ff 0%, #d0e3ff 100%);
        color: #0d6efd;
        font-size: 24px;
    }
</style>
