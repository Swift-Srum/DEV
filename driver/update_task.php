<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');
session_start();

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a driver
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'driver') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $taskId = isset($_POST['taskId']) ? (int)$_POST['taskId'] : 0;
    $bowserId = isset($_POST['bowserId']) ? (int)$_POST['bowserId'] : 0;
    $status = $_POST['status'] ?? '';
    
    if (!$taskId) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit();
    }
    
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Handle different actions
    switch ($action) {
        case 'assign':
            // Assign a bowser to the task
            if (!$bowserId) {
                echo json_encode(['success' => false, 'message' => 'Invalid bowser ID']);
                exit();
            }
            
            try {
                $db->begin_transaction();
                
                // Get destination postcode from assigned_area_reports associated with this task
                $stmt = $db->prepare("
                    SELECT ar.postcode 
                    FROM drivers_tasks dt 
                    LEFT JOIN assigned_area_reports ar ON dt.area_report_id = ar.id 
                    WHERE dt.id = ?
                ");
                $stmt->bind_param('i', $taskId);
                $stmt->execute();
                $result = $stmt->get_result();
                $taskData = $result->fetch_assoc();
                
                if (!$taskData || !$taskData['postcode']) {
                    throw new Exception('Task has no valid destination postcode');
                }
                
                $destinationPostcode = $taskData['postcode'];
                
                // Update the task with the bowser ID
                $stmt = $db->prepare("UPDATE drivers_tasks SET bowser_id = ? WHERE id = ?");
                $stmt->bind_param('ii', $bowserId, $taskId);
                $stmt->execute();
                
                // Update bowser status to 'In Transit' and update postcode
                $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'In Transit', postcode = ? WHERE id = ?");
                $stmt->bind_param('si', $destinationPostcode, $bowserId);
                $stmt->execute();
                
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'change':
            // Change the bowser assigned to a task
            if (!$bowserId) {
                echo json_encode(['success' => false, 'message' => 'Invalid bowser ID']);
                exit();
            }
            
            try {
                $db->begin_transaction();
                
                // Get the current bowser ID from the task and destination postcode
                $stmt = $db->prepare("
                    SELECT dt.bowser_id, ar.postcode 
                    FROM drivers_tasks dt 
                    LEFT JOIN assigned_area_reports ar ON dt.area_report_id = ar.id 
                    WHERE dt.id = ?
                ");
                $stmt->bind_param('i', $taskId);
                $stmt->execute();
                $result = $stmt->get_result();
                $task = $result->fetch_assoc();
                
                if (!$task) {
                    throw new Exception('Task not found');
                }
                
                $oldBowserId = $task['bowser_id'];
                $destinationPostcode = $task['postcode'];
                
                if (!$destinationPostcode) {
                    throw new Exception('Task has no valid destination postcode');
                }
                
                // Update the old bowser status to 'On Depot' if it exists
                if ($oldBowserId) {
                    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'On Depot' WHERE id = ?");
                    $stmt->bind_param('i', $oldBowserId);
                    $stmt->execute();
                }
                
                // Update the task with the new bowser ID
                $stmt = $db->prepare("UPDATE drivers_tasks SET bowser_id = ? WHERE id = ?");
                $stmt->bind_param('ii', $bowserId, $taskId);
                $stmt->execute();
                
                // Update new bowser status to 'In Transit' and update postcode
                $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'In Transit', postcode = ? WHERE id = ?");
                $stmt->bind_param('si', $destinationPostcode, $bowserId);
                $stmt->execute();
                
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'updateStatus':
            // Update the task status
            if (!in_array($status, ['On Depot', 'In Transit', 'Dispatched'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit();
            }
            
            if (!$bowserId) {
                echo json_encode(['success' => false, 'message' => 'No bowser assigned to this task']);
                exit();
            }
            
            try {
                $db->begin_transaction();

                // Update the bowser status
                $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = ? WHERE id = ?");
                $stmt->bind_param('si', $status, $bowserId);
                $stmt->execute();

                // If the status is "Dispatched," update the bowser's postcode and geolocation
                if ($status === 'Dispatched') {
                    $stmt = $db->prepare("
                        SELECT ar.postcode 
                        FROM drivers_tasks dt 
                        LEFT JOIN assigned_area_reports ar ON dt.area_report_id = ar.id 
                        WHERE dt.id = ?
                    ");
                    $stmt->bind_param('i', $taskId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $taskData = $result->fetch_assoc();

                    if (!$taskData || !$taskData['postcode']) {
                        throw new Exception('Task has no valid destination postcode');
                    }

                    $destinationPostcode = $taskData['postcode'];

                    // Fetch geolocation data using the postcode
                    $eastings = $northings = $longitude = $latitude = null;
                    $url = "https://api.postcodes.io/postcodes/$destinationPostcode";
                    $response = file_get_contents($url);
                    $data = json_decode($response, true);

                    if (isset($data['result'])) {
                        $eastings = $data['result']['eastings'];
                        $northings = $data['result']['northings'];
                        $longitude = $data['result']['longitude'];
                        $latitude = $data['result']['latitude'];
                    }

                    if (!empty($eastings) && !empty($northings) && !empty($longitude) && !empty($latitude)) {
                        // Update the bowser's postcode and geolocation data
                        $stmt = $db->prepare("UPDATE bowsers SET postcode = ?, eastings = ?, northings = ?, longitude = ?, latitude = ? WHERE id = ?");
                        $stmt->bind_param('sdddsi', $destinationPostcode, $eastings, $northings, $longitude, $latitude, $bowserId);
                        $stmt->execute();
                    } else {
                        throw new Exception('Failed to fetch geolocation data for the postcode');
                    }
                }

                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'resolve':
            // Resolve a task (remove it from drivers_tasks and reset bowser status)
            try {
                $db->begin_transaction();
                
                // Get the bowser ID associated with this task
                $stmt = $db->prepare("SELECT bowser_id FROM drivers_tasks WHERE id = ?");
                $stmt->bind_param('i', $taskId);
                $stmt->execute();
                $result = $stmt->get_result();
                $task = $result->fetch_assoc();
                
                if (!$task) {
                    throw new Exception('Task not found');
                }
                
                $bowserId = $task['bowser_id'];
                
                // Reset bowser status if one was assigned
                if ($bowserId) {
                    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'On Depot' WHERE id = ?");
                    $stmt->bind_param('i', $bowserId);
                    $stmt->execute();
                }
                
                // Delete the task
                $stmt = $db->prepare("DELETE FROM drivers_tasks WHERE id = ?");
                $stmt->bind_param('i', $taskId);
                $stmt->execute();
                
                $db->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
    $db->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>