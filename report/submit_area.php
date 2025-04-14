<?php
// Include config file first
require_once('../essential/config.php');
include('../essential/backbone.php');


// Get user ID and validate session
$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$userId = getUserID();
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    header("Location: /login");
    exit();
}

// Validate and sanitize form inputs
$postcode = filter_input(INPUT_POST, 'postcode', FILTER_SANITIZE_STRING);
$report = filter_input(INPUT_POST, 'report', FILTER_SANITIZE_STRING);
$reportType = filter_input(INPUT_POST, 'reportType', FILTER_SANITIZE_STRING);

if ($postcode) { //Checks if the postcode is valid, if not, return an error
        $url = "https://api.postcodes.io/postcodes/$postcode";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (!isset($data['result'])) {
			header("Location: /report/index.php?success=0"); //Probably change this to be more descriptive
			exit();
        }
    }

// Validate report type
$validTypes = ['Urgent', 'Medium', 'Low'];
if (!in_array($reportType, $validTypes)) {
    header("Location: /report/index.php?success=0");
    exit();
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    $stmt = $db->prepare("INSERT INTO area_reports (userId, postcode, report, reportType) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $postcode, $report, $reportType);
    
    if ($stmt->execute()) {
        header("Location: ../report/index.php?success=1");
    } else {
        header("Location: ../report/index.php?success=0");
    }
    
    $stmt->close();
    $db->close();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: ../report/index.php?success=0");
    exit();
}
?>