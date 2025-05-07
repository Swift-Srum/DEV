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

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Add to assigned_area_reports
    $stmt = $db->prepare("INSERT INTO assigned_area_reports (report, postcode, reportType, userId, assigned_date) 
                           SELECT report, postcode, reportType, userId, NOW() 
                           FROM area_reports WHERE id = ?");
    $stmt->bind_param('i', $reportId);
    $stmt->execute();

    // Get the inserted report's ID from assigned_area_reports
    $assignedReportId = $db->insert_id;

    // Add to drivers_tasks using the assigned_area_reports ID
    $stmt = $db->prepare("INSERT INTO drivers_tasks (driver_id, area_report_id, bowser_id, status) VALUES (?, ?, ?, 'Dispatch Requested')");
    $stmt->bind_param('iii', $driverId, $assignedReportId, $bowser['id']);
    $stmt->execute();

    // Update bowser status to 'Dispatch Requested'
    $stmt = $db->prepare("UPDATE bowsers SET status_maintenance = 'Dispatch Requested' WHERE id = ?");
    $stmt->bind_param('i', $bowser['id']);
    $stmt->execute();

    // Delete from area_reports
    $stmt = $db->prepare("DELETE FROM area_reports WHERE id = ?");
    $stmt->bind_param('i', $reportId);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Error assigning driver: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>