<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure you include/configure your database connection here, e.g., require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log POST data for debugging
    error_log("POST Data: " . print_r($_POST, true));

    // Assuming $db is your mysqli connection
    $task_id = $_POST['task_id'] ?? null;
    $status = $_POST['status'] ?? '';

    // Validate required parameters
    if (!$task_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
        exit;
    }

    // Validate allowed statuses
    $allowed_statuses = ['on depot', 'in transit', 'dispatched'];
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status.']);
        exit;
    }

    // Get the bowser_id from drivers_tasks table since the status is stored in the bowsers table
    $stmt = $db->prepare("SELECT bowser_id FROM drivers_tasks WHERE id = ?");
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    error_log("Task Data: " . print_r($task, true));

    if (!$task || !$task['bowser_id']) {
        echo json_encode(['success' => false, 'message' => 'No associated bowser found for this task.']);
        exit;
    }
    $bowser_id = $task['bowser_id'];

    // Update the bowsers table with the new status for the given bowser
    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $bowser_id);
    $stmt->execute();

    if ($stmt->error) {
        error_log("SQL Error: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed or no changes made.']);
    }
}
?>