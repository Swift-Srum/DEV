<?php
error_reporting(1);
include('../essential/backbone.php');
header('Content-Type: application/json');

// Check admin authentication
$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);

if (!$loggedIn || !$isAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Validate input
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid bowser ID']);
    exit();
}

$bowserId = (int)$_POST['id'];

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Start transaction
    $db->begin_transaction();
    
    // First delete related records from child tables
    $tables = ['active_bowser', 'bowser_reports', 'maintain_bowser', 'uploads'];
    
    foreach ($tables as $table) {
        $stmt = $db->prepare("DELETE FROM $table WHERE bowserId = ?");
        $stmt->bind_param('i', $bowserId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Then delete the bowser
    $stmt = $db->prepare("DELETE FROM bowsers WHERE id = ?");
    $stmt->bind_param('i', $bowserId);
    $result = $stmt->execute();
    
    if ($result && $stmt->affected_rows > 0) {
        $db->commit();
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete bowser');
    }
    
} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($db)) $db->close();
}