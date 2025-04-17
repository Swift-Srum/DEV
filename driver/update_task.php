<?php
error_reporting(1);
include('../essential/backbone.php');

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a driver
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'driver') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$taskId = $_POST['taskId'] ?? '';

if (empty($action) || empty($taskId)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Begin transaction
    $db->begin_transaction();

    if ($action === 'assign') {
        $bowserId = $_POST['bowserId'] ?? '';
        
        if (empty($bowserId)) {
            echo json_encode(['success' => false, 'message' => 'Missing bowser ID']);
            exit();
        }

        // Get task details to get the postcode
        $stmt = $db->prepare("SELECT ar.postcode FROM drivers_tasks dt 
                             JOIN area_reports ar ON dt.area_report_id = ar.id 
                             WHERE dt.id = ?");
        $stmt->bind_param('i', $taskId);
        $stmt->execute();
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();
        
        if (!$task) {
            throw new Exception('Task not found');
        }
        
        // Update the task with the bowser assignment
        $stmt = $db->prepare("UPDATE drivers_tasks SET bowser_id = ?, status = 'On Depot' WHERE id = ?");
        $stmt->bind_param('ii', $bowserId, $taskId);
        $stmt->execute();
        
        // Update bowser status and postcode
        $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'On Depot', postcode = ? WHERE id = ?");
        $stmt->bind_param('si', $task['postcode'], $bowserId);
        $stmt->execute();
    } 
    else if ($action === 'updateStatus') {
        $bowserId = $_POST['bowserId'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if (empty($bowserId) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }
        
        $allowedStatuses = ['On Depot', 'In Transit', 'Dispatched'];
        
        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }
        
        // Update the driver task status
        $stmt = $db->prepare("UPDATE drivers_tasks SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $taskId);
        $stmt->execute();
        
        // Update the bowser status
        $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $bowserId);
        $stmt->execute();
    }
    else {
        throw new Exception('Invalid action');
    }

    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}