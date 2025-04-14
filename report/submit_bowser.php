<?php
error_reporting(1);
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

        // Validate inputs
        if (empty($bowserId) || empty($report) || empty($typeOfReport)) {
            throw new Exception("All fields are required");
        }

        // Validate typeOfReport is one of the allowed values
        $allowedTypes = ['Urgent', 'Medium', 'Low'];
        if (!in_array($typeOfReport, $allowedTypes)) {
            throw new Exception("Invalid report type");
        }

        // Connect to database
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Prepare and execute the insert statement
        $stmt = $db->prepare("INSERT INTO bowser_reports (userId, bowserId, report, typeOfReport) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiss', $userId, $bowserId, $report, $typeOfReport);
        
        if ($stmt->execute()) {
            header("Location: ../view/index.php?id=" . $bowserId . "&success=1");
            exit();
        } else {
            throw new Exception("Database error");
        }
        
        $stmt->close();
        $db->close();
        
    } catch (Exception $e) {
        error_log("Report submission error: " . $e->getMessage());
        header("Location: ../view/index.php?id=" . $bowserId . "&error=1");
        exit();
    }
} else {
    header("Location: ../");
    exit();
}