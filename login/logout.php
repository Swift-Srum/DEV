<?php
error_reporting(0);
include('../essential/backbone.php');

// Check if session ID matches before continuing
if ($_GET['session'] != $_COOKIE['sessionId']) {
    die();
}

function logout() {
    if (isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
        $sessionId = $_COOKIE['sessionId'];

        // Destroy cookies by setting them to expire
        setcookie("sessionId", $_COOKIE['sessionId'], time() - 86400, '/');
        setcookie("user_name", $_COOKIE['user_name'], time() - 86400, '/');
        session_destroy();

        // Update the session details in the database
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM users WHERE sessionKey = ? LIMIT 1;");
        $q->bind_param('s', $sessionId);
        $q->execute();

        $res = $q->get_result();

        if ($res = $res->fetch_array()) {
            $db->query("UPDATE users SET sessionKey = '', loginKey = '' WHERE id = " . $res['id'] . ";");
			
			
        }
    }

    // Redirect with a 301 status code after logout

    exit(); // Make sure the script stops after the redirect
}

// Call the logout function to handle the logout process
header("HTTP/1.1 301 Moved Permanently");
header("Location: ../"); // Target URL after logout
logout();
?>
