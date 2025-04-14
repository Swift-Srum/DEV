<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');

// Check if user is logged in
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    header("Location: ../login/");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/');
    exit();
}

// Get user ID from the logged-in user
$userId = getUserID();

$bowserId = filter_input(INPUT_POST, 'bowserId', FILTER_SANITIZE_NUMBER_INT);
$report = trim($_POST['report']);
$typeOfReport = $_POST['typeOfReport'];

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$stmt = $db->prepare("INSERT INTO bowser_reports (userId, bowserId, report, typeOfReport) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $userId, $bowserId, $report, $typeOfReport);
$stmt->execute();

header("Location: ../view/?bowserId=" . $bowserId . "&status=success");
exit();