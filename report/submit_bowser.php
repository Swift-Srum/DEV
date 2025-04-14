<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_COOKIE['user_name'] ?? '';
    $sessionID = $_COOKIE['sessionId'] ?? '';
    
    if (!confirmSessionKey($username, $sessionID)) {
        $_SESSION['feedback'] = [
            'type' => 'error',
            'message' => 'You must be logged in to submit a report.'
        ];
        header("Location: ../login/");
        exit();
    }

    $userId = getUserID();
    $bowserId = (int)$_POST['bowserId'];
    $report = trim($_POST['report']);
    $typeOfReport = $_POST['typeOfReport'];

    // Validate inputs
    if (empty($report) || empty($typeOfReport)) {
        $_SESSION['feedback'] = [
            'type' => 'error',
            'message' => 'Please fill in all required fields.'
        ];
        header("Location: ../view/?id=" . $bowserId);
        exit();
    }

    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $stmt = $db->prepare("INSERT INTO bowser_reports (userId, bowserId, report, typeOfReport) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $userId, $bowserId, $report, $typeOfReport);
        
        if ($stmt->execute()) {
            $_SESSION['feedback'] = [
                'type' => 'success',
                'message' => 'Report submitted successfully! Thank you for your feedback.'
            ];
            header("Location: ../view/?id=" . $bowserId);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['feedback'] = [
            'type' => 'error',
            'message' => 'An error occurred while submitting your report. Please try again.'
        ];
        error_log($e->getMessage());
        header("Location: ../view/?id=" . $bowserId);
    }
    exit();
}

$_SESSION['feedback'] = [
    'type' => 'error',
    'message' => 'Invalid request method.'
];
header("Location: ../view/");
exit();