<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_COOKIE['user_name'] ?? '';
    $sessionID = $_COOKIE['sessionId'] ?? '';
    
    if (!confirmSessionKey($username, $sessionID)) {
        header("Location: ../login/");
        exit();
    }

    $userId = getUserID();
    $bowserId = (int)$_POST['bowserId'];
    $report = $_POST['report'];
    $typeOfReport = $_POST['typeOfReport'];

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $db->prepare("INSERT INTO bowser_reports (userId, bowserId, report, typeOfReport) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $userId, $bowserId, $report, $typeOfReport);
    $stmt->execute();
    
    header("Location: ../view/?id=" . $bowserId);
    exit();
}

header("Location: ../view/");
exit();