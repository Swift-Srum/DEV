<?php
error_reporting(1);
include('../essential/backbone.php');

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a dispatcher
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'dispatcher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$reportId = $_POST['reportId'] ?? '';

if (empty($reportId)) {
    echo json_encode(['success' => false, 'message' => 'Missing report ID']);
    exit();
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $db->prepare("DELETE FROM bowser_reports WHERE id = ?");
    $stmt->bind_param('i', $reportId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}