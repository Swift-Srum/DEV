<?php
include('../essential/internal.php');

// Validate and sanitize inputs
$userId = intval($_POST['userId']);
$postcode = htmlspecialchars($_POST['postcode']);
$report = htmlspecialchars($_POST['report']);
$reportType = htmlspecialchars($_POST['reportType']);

// Insert the report into the database
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$q = $db->prepare("INSERT INTO area_reports (userId, postcode, report, reportType) VALUES (?, ?, ?, ?)");
$q->bind_param('isss', $userId, $postcode, $report, $reportType);
$q->execute();

if ($q->affected_rows > 0) {
    echo "Report submitted successfully!";
} else {
    echo "Error submitting report.";
}
?>