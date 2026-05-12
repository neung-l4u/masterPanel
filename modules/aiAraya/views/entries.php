<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/entries.css?v=2.0.0" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/libs/DataTables/datatables-bs5.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <title>Customer Details - Entries</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><img src="../assets/img/L4U-Site-Icon.png" alt="logo" style="width: 50px;"/></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/customerDetailsForm.php">Form</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="../views/entries.php">Entries</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<body>

<div class="container-fluid mt-5 p-5" style="min-height: 500px;">
    <main>
        <section>
            <div class="form-div">
                <!-- <header>
                    <nav class="text-center mb-4" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                        <ol class="justify-content-center breadcrumb">
                            <i class="bi bi-house-fill"></i>&nbsp;
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Entries</li>
                        </ol>
                    </nav>
                </header> -->
                <h3 class="mb-4 text-center text-uppercase page-title">Customer Details Entries</h3>
                <p class="text-center">Here you can view all customer details entries submitted through the form.</p>
            </div>
            <div class="row">
                <div class="col-5 pt-3">

                    <div class="form-header unsubmit-header">
                        <h3 class="form-title text-uppercase">Unsubmitted Entries</h3>
                        <div class="submit-links">
                            <!-- <a href="customerDetailsForm.php" target="_blank">
                                <i class="bi bi-calendar3"></i> Go to Customer Details Form
                            </a> -->
                        </div>
                    </div>

                    <div class="border rounded py-2 px-3">
                        <table id="unsubmitTable" class="table table-striped table-hover">
                            <thead class="table-dark thead-dark">
                                <tr>
                                    <th class="col_id">#</th>
                                    <th class="col_business">Business</th>
                                    <th class="col_email">Email</th>
                                    <th class="col_status">Status</th>
                                    <th class="col_date">Date</th>
                                    <th class="col_toform"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-7 pt-3">

                    <div class="form-header submit-header">
                        <h3 class="form-title text-uppercase">Submitted Entries</h3>
                        <div class="submit-links">
                            <a href="https://local-for-you.monday.com/boards/2032016220" target="_blank">
                                <i class="bi bi-calendar3"></i> Monday - AI Receptionist
                            </a>
                        </div>
                    </div>
                    
                    <div class="border rounded py-2 px-3">
                        <table id="entriesTable" class="table table-striped table-hover">
                            <thead class="table-dark thead-dark">
                                <tr>
                                    <th class="col_id">#</th>
                                    <th class="col_business">Business</th>
                                    <th class="col_email">Email</th>
                                    <th class="col_phone">Phone</th>
                                    <th class="col_date">Date</th>
                                    <th class="col_detail"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="detailModalLabel"><i class="bi bi-file-earmark-text"></i> Customer Details</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="detailModalBody">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/DataTables/datatables.min.js"></script>
<script src="../controllers/entries.js?v=1.0.0"></script>
<script>
$(function() {
    $('#entriesTable').DataTable( {
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/entriesTable.php',
            dataSrc: 'data'
        },
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],
        columnDefs: [
            { targets: '_all', orderable: false },
            { targets: [0, 4], className: 'dt-center' },
            { targets: [5], render: function(data) {
                return `<button class="btn btn-sm btn-outline-primary btn-view-detail" title="View Details"><i class="bi bi-eye"></i></button>`;
            }, className: 'dt-center'},
        ],
        drawCallback: function () {
            initTooltips();
        }
    });

    // Detail modal handler
    $('#entriesTable').on('click', '.btn-view-detail', function () {
        const table = $('#entriesTable').DataTable();
        const row = table.row($(this).closest('tr'));
        const rawJson = row.data()[5]; // column 5 = raw JSON (rendered as button)

        try {
            const decoded = $('<textarea/>').html(rawJson).text();
            const json = JSON.parse(decoded);
            const fields = [
                { key: 'stripeID',             label: 'Stripe ID',           icon: 'bi-credit-card' },
                { key: 'businessName',         label: 'Business Name',       icon: 'bi-building' },
                { key: 'businessHours',        label: 'Business Hours',      icon: 'bi-clock' },
                { key: 'currentBookingSystem', label: 'Booking System',      icon: 'bi-calendar-check' },
                { key: 'bookingUser',          label: 'Booking User',        icon: 'bi-person-badge' },
                { key: 'bookingPassword',      label: 'Booking Password',    icon: 'bi-key' },
                { key: 'timezoneId',           label: 'Timezone',            icon: 'bi-globe' },
                { key: 'shopAddress',          label: 'Shop Address',        icon: 'bi-geo-alt' },
                { key: 'businessWebsite',      label: 'Website',             icon: 'bi-link-45deg' },
                { key: 'phoneNumberDecision',  label: 'Phone Decision',      icon: 'bi-telephone' },
                { key: 'backupPhoneNumber',    label: 'Backup Phone',        icon: 'bi-phone' },
                { key: 'phoneCarrier',         label: 'Phone Carrier',       icon: 'bi-sim' },
                { key: 'shopEmail',            label: 'Email',               icon: 'bi-envelope' },
                { key: 'servicesOffered',      label: 'Services Offered',    icon: 'bi-list-check' },
                { key: 'bufferMinutes',        label: 'Buffer Minutes',      icon: 'bi-hourglass-split' },
                { key: 'coupleMassage',        label: 'Couple Massage',      icon: 'bi-people' },
                { key: 'happyMassage',         label: 'Happy Massage',       icon: 'bi-emoji-smile' },
                { key: 'maleTherapists',       label: 'Male Therapists',     icon: 'bi-person' },
                { key: 'femaleTherapists',     label: 'Female Therapists',   icon: 'bi-person' },
                { key: 'numberOfTherapists',   label: 'Number of Therapists',icon: 'bi-people-fill' },
                { key: 'therapistNames',       label: 'Therapist Names',     icon: 'bi-person-lines-fill' },
                { key: 'wheelchair',           label: 'Wheelchair',          icon: 'bi-wheelchair' },
                { key: 'parking',              label: 'Parking',             icon: 'bi-p-circle' },
                { key: 'restroom',             label: 'Restroom',            icon: 'bi-door-open' },
                { key: 'promotions',           label: 'Promotions',          icon: 'bi-tag' },
                { key: 'voiceType',            label: 'Voice Type',          icon: 'bi-mic' },
                { key: 'googleCalendar',       label: 'Google Calendar',     icon: 'bi-calendar3' },
                { key: 'googleCalendarId',     label: 'Calendar ID',         icon: 'bi-calendar3-event' },
                { key: 'additionalComments',   label: 'Additional Comments', icon: 'bi-chat-dots' },
                { key: 'formVersion',          label: 'Form Version',        icon: 'bi-file-code' },
                { key: 'emailVersion',         label: 'Email Version',       icon: 'bi-file-code' },
                { key: 'date',                 label: 'Submitted',           icon: 'bi-calendar-event' },
            ];

            const booleanFields = ['coupleMassage','happyMassage','maleTherapists','femaleTherapists','wheelchair','parking','restroom','googleCalendar'];

            let html = '<div class="detail-grid">';
            for (const f of fields) {
                const val = json[f.key];
                if (val !== undefined && val !== '') {
                    let display;
                    if (f.key === 'voiceType') {
                        const gIcon = String(val).toLowerCase() === 'male'
                            ? '<i class="bi bi-gender-male"></i>'
                            : '<i class="bi bi-gender-female"></i>';
                        display = `<span class="badge-voice">${gIcon} ${val}</span>`;
                    } else if (booleanFields.includes(f.key)) {
                        const v = String(val).toLowerCase();
                        const isPositive = !v.includes('not') && (v.includes('available') || v.includes('accessible') || v === 'yes');
                        display = isPositive
                            ? `<span class="badge-yes"><i class="bi bi-check-circle-fill"></i> ${val}</span>`
                            : `<span class="badge-no"><i class="bi bi-x-circle-fill"></i> ${val}</span>`;
                    } else {
                        display = String(val).replace(/\n/g, '<br>').replace(/\r/g, '');
                    }
                    html += `<div class="detail-row">
                        <div class="detail-label"><i class="bi ${f.icon}"></i> ${f.label}</div>
                        <div class="detail-value">${display}</div>
                    </div>`;
                }
            }
            html += '</div>';
            $('#detailModalBody').html(html);
        } catch (e) {
            $('#detailModalBody').html('<div class="alert alert-danger">Unable to parse detail data.</div>');
        }

        new bootstrap.Modal('#detailModal').show();
    });

    $('#unsubmitTable').DataTable( {
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/unsubmitTable.php',
            dataSrc: 'data'
        },
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],
        columnDefs: [
            { targets: '_all', orderable: false },
            { targets: [0, 4], className: 'dt-center' },
            { targets: [5], render: function(data) {
                return `<a href="../views/customerDetailsForm.php?stripeID=${encodeURIComponent(data)}" class="btn btn-sm btn-outline-primary" title="Fill in the Information"><i class="bi bi-box-arrow-up-right"></i></a>`;
            }, className: 'dt-center'},
        ]
    });
});//ready
</script>
</body>
</html>