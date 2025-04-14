<?php
include('../essential/backbone.php');

// Check if the user is logged in
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    // Redirect to login page if not logged in
    header("Location: /login.php");
    exit();
}

$postcode = $_POST['postcode'] ?? '';
$report = $_POST['report'] ?? '';
$reportType = $_POST['reportType'] ?? '';
$userId = getUserID();

if (!empty($postcode) && !empty($report) && !empty($reportType)) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("INSERT INTO `area_reports` (`postcode`, `report`, `reportType`, `userId`) VALUES (?, ?, ?, ?)");
    $q->bind_param('sssi', $postcode, $report, $reportType, $userId);
    $q->execute();
    header("Location: /report/?success=area");
} else {
    header("Location: /report/?error=area");
}