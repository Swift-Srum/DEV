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
$maintainerId = $_POST['maintainerId'] ?? '';

if (empty($reportId) || empty($maintainerId)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Get report details
    $report = getBowserReport($reportId);
    
    // Add to maintenance table
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $db->prepare("INSERT INTO maintain_bowser (bowserId, userId, descriptionOfWork, maintenanceType, dateOfMaintenance) VALUES (?, ?, ?, 'Maintenance', CURRENT_DATE)");
    $stmt->bind_param('iis', $report['bowserId'], $maintainerId, $report['report']);
    $stmt->execute();
    
    // Update bowser status to 'Maintenance Requested'
    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'Maintenance Requested' WHERE id = ?");
    $stmt->bind_param('i', $report['bowserId']);
    $stmt->execute();
    
    // Delete from reports
    $stmt = $db->prepare("DELETE FROM bowser_reports WHERE id = ?");
    $stmt->bind_param('i', $reportId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}