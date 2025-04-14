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

$bowserId = $_POST['bowserId'] ?? '';
$report = $_POST['report'] ?? '';
$typeOfReport = $_POST['typeOfReport'] ?? '';
$userId = getUserID();

if (!empty($bowserId) && !empty($report) && !empty($typeOfReport)) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("INSERT INTO `bowser_reports` (`bowserId`, `report`, `typeOfReport`, `userId`) VALUES (?, ?, ?, ?)");
    $q->bind_param('issi', $bowserId, $report, $typeOfReport, $userId);
    $q->execute();
    header("Location: /report/?success=bowser");
} else {
    header("Location: /report/?error=bowser");
}