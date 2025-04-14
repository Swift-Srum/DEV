<?php
error_reporting(1);
include('../essential/backbone.php');

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

// Check if user is logged in and is admin
$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);

if (!$loggedIn || !$isAdmin) {
    http_response_code(403);
    exit('Unauthorized');
}

$action = $_POST['action'] ?? '';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

switch ($action) {
    case 'add':
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = hash('sha256', $_POST['password']);
        
        $stmt = $db->prepare("INSERT INTO users (username, email, password, active) VALUES (?, ?, ?, 1)");
        $stmt->bind_param('sss', $username, $email, $password);
        $stmt->execute();
        break;
        
    case 'edit':
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        $stmt = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param('ssi', $username, $email, $id);
        $stmt->execute();
        break;
        
    case 'toggle':
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $db->query("UPDATE users SET active = NOT active WHERE id = " . $id);
        break;
        
    case 'delete':
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $db->query("DELETE FROM users WHERE id = " . $id);
        break;
        
    default:
        http_response_code(400);
        exit('Invalid action');
}

$db->close();
http_response_code(200);