<?php
// Include config file first
require_once('../essential/config.php');
include('../essential/backbone.php');

// Check if user is logged in
if (!isset($_COOKIE['user_name']) || !isset($_COOKIE['sessionId'])) {
    header("Location: /login");
    exit();
}

// Get user ID and validate session
$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$userId = getUserID();
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    header("Location: /login");
    exit();
}

// Validate and sanitize form inputs
$postcode = filter_input(INPUT_POST, 'postcode', FILTER_SANITIZE_STRING);
$report = filter_input(INPUT_POST, 'report', FILTER_SANITIZE_STRING);
$reportType = filter_input(INPUT_POST, 'reportType', FILTER_SANITIZE_STRING);

// Validate report type
$validTypes = ['Urgent', 'Medium', 'Low'];
if (!in_array($reportType, $validTypes)) {
    header("Location: /index.php?error=1");
    exit();
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    $stmt = $db->prepare("INSERT INTO area_reports (userId, postcode, report, reportType) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $postcode, $report, $reportType);
    
    if ($stmt->execute()) {
        header("Location: /index.php?success=1");
    } else {
        header("Location: /index.php?error=1");
    }
    
    $stmt->close();
    $db->close();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: /index.php?error=1");
    exit();
}
?>