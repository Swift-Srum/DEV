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
        $userType = filter_input(INPUT_POST, 'userType', FILTER_SANITIZE_STRING);
        
        // Prevent adding public users through admin panel
        if ($userType !== 'public') {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, active, userType) VALUES (?, ?, ?, 1, ?)");
            $stmt->bind_param('ssss', $username, $email, $password, $userType);
            $stmt->execute();
        } else {
            http_response_code(400);
            exit('Invalid user type');
        }
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
        
    case 'updateStaffType':
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $staff_type = filter_input(INPUT_POST, 'staff_type', FILTER_SANITIZE_STRING);
        
        $stmt = $db->prepare("UPDATE users SET staff_type = ? WHERE id = ?");
        $stmt->bind_param('si', $staff_type, $id);
        $stmt->execute();
        break;
        
    case 'updateUserType':
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $userType = filter_input(INPUT_POST, 'userType', FILTER_SANITIZE_STRING);
        
        // Prevent setting user type to public
        if ($userType !== 'public') {
            $stmt = $db->prepare("UPDATE users SET userType = ? WHERE id = ?");
            $stmt->bind_param('si', $userType, $id);
            $stmt->execute();
        } else {
            http_response_code(400);
            exit('Invalid user type');
        }
        break;
        
    default:
        http_response_code(400);
        exit('Invalid action');
}

$db->close();
http_response_code(200);