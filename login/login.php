<?php
error_reporting(0);
include('../essential/backbone.php');

function verifyUser($username, $password) {
    // Initialize AES256 encryption class
    $aes = new AES256();
    // Get the IP address of the user trying to log in
    $loginIP = GetIP();

    // Check if both username and password are provided
    if (!empty($username) && !empty($password)) {
        echo "gi"; // Debugging output

        // Create a new database connection
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        // Prepare the SQL statement to prevent SQL injection
        $q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
        // Bind the username parameter to the SQL query
        $q->bind_param('s', $username);
        // Execute the query
        $q->execute();

        // Get the result of the query
        $res = $q->get_result();

        // Fetch the user data from the result
        if ($res = $res->fetch_array()) {
            // Check if the provided password matches the stored password
            if ($password == $res['password']) {
                // Check if the user account is active
                if ($res["active"] == 1) {
                    // Generate a new session key for the user
                    $sessKey = generateSessionKey();
                    // Create a new database connection
                    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                    // Prepare the SQL statement to update the session key
                    $q = $db->prepare("UPDATE users SET sessionKey = ? WHERE username = ? LIMIT 1;");
                    // Bind the session key and username parameters to the SQL query
                    $q->bind_param('ss', $sessKey, $username);
                    // Execute the query
                    $q->execute();

                    // Redirect the user to the main page
                    header('Location: ../');
                    // Set a cookie for the session ID, valid for 24 hours
                    setcookie("sessionId", $sessKey, time() + 86400, '/'); // Eh ... I'll leave it for now ...
                    // Set a cookie for the username, valid for 24 hours
                    setcookie("user_name", $username, time() + 86400, '/');
                } else {
                    // Redirect to login page with error message if account is banned
                    header("Location: ../login/?err=" . urlencode($aes->encrypt("This account has been banned", "secretkey")));
                }
            } else {
                // Redirect to login page with error message if password is incorrect
                header("Location: ../login/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", "secretkey")));
            }
        } else {
            // Redirect to login page with error message if username is not found
            header("Location: ../login/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", "secretkey")));
        }
    } else {
        // Redirect to login page with error message if username or password is empty
        header("Location: ../login/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", "secretkey")));
    }
}


function logout() {
    if (isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
        $user_name = $_COOKIE['user_name'];
        // session_destroy();
        setcookie("sessionId", $_COOKIE['sessionId'], time() - 86400, '/');
        setcookie("user_name", $_COOKIE['user_name'], time() - 86400, '/');
        session_destroy();

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
        $q->bind_param('s', $user_name);
        $q->execute();

        $res = $q->get_result();

        if ($res = $res->fetch_array()) {
            $db->query("UPDATE users SET sessionKey = '', loginKey = '' WHERE id = " . $res['id'] . ";");
        }
    }

    header("Location: ../");
}

if (isset($_POST['userID']) && isset($_POST['password'])) {
    $username = $_POST['userID'];
    $password = $_POST['password'];
	
	$password = hash('sha256', $password); //Create a SHA256 hash of the password

    verifyUser($username, $password);
} else {
    logout();
}
?>
