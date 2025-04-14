<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/internal.php');

// Ensure the user is logged in
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    header("Location: ../login/");
    exit();
}

$userId = getUserID(); // Get the logged-in user's ID

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $bowserId = $_POST['bowserId'] ?? '';
        $report = trim(strip_tags($_POST['report'] ?? ''));
        $typeOfReport = $_POST['typeOfReport'] ?? 'Medium';

        // Validate bowserId
        if (!is_numeric($bowserId)) {
            throw new Exception("Invalid bowserId.");
        }

        // Check if bowserId exists and is active
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $stmt = $db->prepare("SELECT id FROM bowsers WHERE id = ? AND active = 1 LIMIT 1;");
        $stmt->bind_param('i', $bowserId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Invalid or inactive bowserId.");
        }

        // Validate report and typeOfReport
        if (empty($report)) {
            throw new Exception("Report details are required.");
        }
        $allowedTypes = ['Urgent', 'Medium', 'Low'];
        if (!in_array($typeOfReport, $allowedTypes)) {
            throw new Exception("Invalid report type.");
        }

        // Insert into bowser_reports table
        $stmt = $db->prepare("INSERT INTO bowser_reports (userId, bowserId, report, typeOfReport) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iiss', $userId, $bowserId, $report, $typeOfReport);
        if (!$stmt->execute()) {
            throw new Exception("Failed to submit report: " . $stmt->error);
        }

        // Redirect on success
        header("Location: ../view/index.php?id=" . $bowserId . "&success=1");
        exit();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        header("Location: ../view/index.php?id=" . $bowserId . "&error=1");
        exit();
    }
} else {
    header("Location: ../");
    exit();
}
?>