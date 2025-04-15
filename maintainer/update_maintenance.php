<?php
error_reporting(1);
include('../essential/backbone.php');

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a maintainer
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'maintainer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'] ?? '';
$bowserId = $_POST['bowserId'] ?? '';
$description = $_POST['description'] ?? '';
$date = $_POST['date'] ?? '';
$status = $_POST['status'] ?? '';

if (empty($id) || empty($bowserId) || empty($description) || empty($date) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$allowedStatuses = [
    'Maintenance Requested',
    'Under Maintenance',
    'Ready',
    'Out of Service'
];

if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid maintenance status']);
    exit();
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Begin transaction
    $db->begin_transaction();

    // Update maintenance record
    $stmt = $db->prepare("UPDATE maintain_bowser SET descriptionOfWork = ?, dateOfMaintenance = ? WHERE id = ?");
    $stmt->bind_param('ssi', $description, $date, $id);
    $stmt->execute();

    // Update bowser status directly using bowser ID
    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $bowserId);
    $stmt->execute();

    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}