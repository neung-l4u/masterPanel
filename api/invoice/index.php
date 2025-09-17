<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

$invoiceID = $_GET['invoiceID'];

$data = $db->query('SELECT * FROM quotation WHERE invoiceID = ?', $invoiceID)->fetchArray();

?>