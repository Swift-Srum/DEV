<?php
error_reporting(0);
include('../essential/backbone.php');

function verifyUser($username, $password) {
    $aes = new AES256();
    $loginIP = GetIP();

    if (!empty($username) && !empty($password)) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
        $q->bind_param('s', $username);
        $q->execute();
        $res = $q->get_result();

        if ($res = $res->fetch_array()) {
            if ($password == $res['password']) {
                if ($res["active"] == 1) {
                    $sessKey = generateSessionKey();
                    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                    $q = $db->prepare("UPDATE users SET sessionKey = ? WHERE username = ? LIMIT 1;");
                    $q->bind_param('ss', $sessKey, $username);
                    $q->execute();

                    // Set cookies first
                    setcookie("sessionId", $sessKey, time() + 86400, '/');
                    setcookie("user_name", $username, time() + 86400, '/');

                    // Check verification status
                    if ($res["verified"] == 0) {
                        header('Location: ../register/verify_page.php');
                        exit();
                    }

                    // Redirect based on user type
                    switch($res["userType"]) {
                        case 'admin':
                            header('Location: ../admin/dashboard.php');
                            break;
                        case 'dispatcher':
                            header('Location: ../dispatcher/dashboard.php');
                            break;
                        case 'maintainer':
                            header('Location: ../maintainer/dashboard.php');
                            break;
                        case 'driver':
                            header('Location: ../driver/dashboard.php');
                            break;
                        default:
                            header('Location: ../');
                    }
                    exit();
                } else {
                    header("Location: ../login/?err=" . urlencode($aes->encrypt("This account has been banned", "secretkey")));
                }
            } else {
                header("Location: ../login/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", "secretkey")));
            }
        } else {
            header("Location: ../login/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", "secretkey")));
        }
    } else {
        header("Location: ../login/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", "secretkey")));
    }
}

function logout() {
    if (isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
        $user_name = $_COOKIE['user_name'];
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
    exit();
}

if (isset($_POST['userID']) && isset($_POST['password'])) {
    $username = $_POST['userID'];
    $password = $_POST['password'];
    $password = hash('sha256', $password);
    verifyUser($username, $password);
} else {
    logout();
}
?>
