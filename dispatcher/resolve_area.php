<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'dispatcher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$reportId = $_POST['reportId'] ?? '';

// Validate inputs
if (empty($reportId)) {
    echo json_encode(['success' => false, 'message' => 'Missing report ID']);
    exit();
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }

    // Delete from drivers_tasks first to avoid foreign key constraint issues
    $stmt = $db->prepare("DELETE FROM drivers_tasks WHERE area_report_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }

    $stmt->bind_param('i', $reportId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    // Delete from area_reports
    $stmt = $db->prepare("DELETE FROM area_reports WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }

    $stmt->bind_param('i', $reportId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No report found with that ID']);
    }

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}