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
$driverId = $_POST['driverId'] ?? '';

if (empty($reportId) || empty($driverId)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Get available bowser
    $bowser = getAvailableBowser();
    if (!$bowser) {
        echo json_encode(['success' => false, 'message' => 'No available bowsers']);
        exit();
    }
    
    // Add to drivers tasks
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $db->prepare("INSERT INTO drivers_tasks (driver_id, area_report_id, bowser_id, status) VALUES (?, ?, ?, 'Driving')");
    $stmt->bind_param('iii', $driverId, $reportId, $bowser['id']);
    $stmt->execute();
    
    // Update bowser status
    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'Dispatched' WHERE id = ?");
    $stmt->bind_param('i', $bowser['id']);
    $stmt->execute();
    
    // Delete from area reports
    $stmt = $db->prepare("DELETE FROM area_reports WHERE id = ?");
    $stmt->bind_param('i', $reportId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}