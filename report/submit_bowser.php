<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');

// Get the logged-in user's ID
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    header("Location: ../login/");
    exit();
}

$userId = getUserID();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $bowserId = $_POST['bowserId'] ?? '';
        $report = trim(strip_tags($_POST['report'] ?? ''));
        $typeOfReport = $_POST['typeOfReport'] ?? '';

        // Debugging output
        error_log("Debug: userId=$userId, bowserId=$bowserId, report=$report, typeOfReport=$typeOfReport");

        // Validate inputs
        if (empty($bowserId) || empty($report) || empty($typeOfReport)) {
            throw new Exception("All fields are required");
        }

        if (!is_numeric($bowserId) || !is_numeric($userId)) {
            throw new Exception("Invalid bowserId or userId.");
        }

        // Validate typeOfReport
        $allowedTypes = ['Urgent', 'Medium', 'Low'];
        if (!in_array($typeOfReport, $allowedTypes)) {
            throw new Exception("Invalid report type");
        }

        // Connect to database
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($db->connect_error) {
            error_log("Connection failed: " . $db->connect_error);
            throw new Exception("Database connection failed");
        }

        // Prepare statement
        $stmt = $db->prepare("INSERT INTO bowser_reports (userId, bowserId, report, typeOfReport) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $db->error);
            throw new Exception("Failed to prepare statement");
        }

        // Bind parameters and execute
        $stmt->bind_param('iiss', $userId, $bowserId, $report, $typeOfReport);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Failed to submit report");
        }

        // Check if rows were affected
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows were inserted. Please check your data.");
        }

        error_log("Report submitted successfully");
        
        $stmt->close();
        $db->close();
        
        header("Location: ../view/index.php?id=" . $bowserId . "&success=1");
        exit();
        
    } catch (Exception $e) {
        error_log("Report submission error: " . $e->getMessage());
        header("Location: ../view/index.php?id=" . $bowserId . "&error=1");
        exit();
    }
} else {
    header("Location: ../");
    exit();
}