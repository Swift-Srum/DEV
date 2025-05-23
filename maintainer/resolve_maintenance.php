<?php
error_reporting(1);
include('../essential/backbone.php');
session_start();

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a maintainer
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'maintainer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing maintenance ID']);
    exit();
}

$maintenanceId = (int)$_POST['id'];

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Start transaction
    $db->begin_transaction();

    // Update bowser status to 'Dispatched'
    $updateBowserQuery = "UPDATE bowsers b 
                         JOIN maintain_bowser mb ON b.id = mb.bowserId 
                         SET b.status_maintenance = 'Dispatched' 
                         WHERE mb.id = ?";
    $stmt = $db->prepare($updateBowserQuery);
    $stmt->bind_param('i', $maintenanceId);
    $stmt->execute();

    // Delete the maintenance record
    $deleteQuery = "DELETE FROM maintain_bowser WHERE id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->bind_param('i', $maintenanceId);
    $stmt->execute();

    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$db->close();
?>