<?php
error_reporting(1);
include('../essential/backbone.php');
header('Content-Type: application/json');

// Verify admin authentication
$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);

if (!$loggedIn || !$isAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

try {
    // Validate input
    $required = ['id', 'name', 'model', 'capacity', 'supplier', 'postcode', 'status', 'date_received', 'date_returned'];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
        // Allow empty dates
        if ($field !== 'date_received' && $field !== 'date_returned' && empty(trim($_POST[$field]))) {
            throw new Exception("Empty required field: $field");
        }
    }

    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $model = trim($_POST['model']);
    $capacity = (float)$_POST['capacity'];
    $supplier = trim($_POST['supplier']);
    $postcode = trim($_POST['postcode']);
    $status = trim($_POST['status']);
    $dateReceived = !empty($_POST['date_received']) ? trim($_POST['date_received']) : null;
    $dateReturned = !empty($_POST['date_returned']) ? trim($_POST['date_returned']) : null;

    // Validate status
    $validStatuses = ['On Depot', 'Dispatched', 'In Transit', 
        'Maintenance Requested', 'Under Maintenance', 'Ready', 'Out of Service'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status value');
    }

    // Validate dates if provided
    if ($dateReceived && !strtotime($dateReceived)) {
        throw new Exception('Invalid date received format');
    }
    if ($dateReturned && !strtotime($dateReturned)) {
        throw new Exception('Invalid date returned format');
    }

    // Connect to database
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }

    // Update bowser
    $stmt = $db->prepare("
        UPDATE bowsers 
        SET name = ?, 
            model = ?, 
            capacity_litres = ?, 
            supplier_company = ?, 
            postcode = ?,
            status_maintenance = ?,
            date_received = ?,
            date_returned = ?
        WHERE id = ?
    ");

    $stmt->bind_param('ssdsssssi', 
        $name, 
        $model, 
        $capacity, 
        $supplier, 
        $postcode, 
        $status,
        $dateReceived,
        $dateReturned,
        $id
    );

    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No bowser found with that ID']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($db)) $db->close();
}