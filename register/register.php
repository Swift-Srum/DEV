<?php
// Setting error reporting to suppress errors
error_reporting(0);

// Including necessary files
include('../essential/backbone.php');
include('../essential/BanBuilder/CensorWords.php');
include('../essential/ProfanityFilter/Check.php');

// Initializing profanity and censorship objects
$bbcensor = new CensorWords();
$pfcensor = new Check();

// Setting up dictionary for censorship
$bbcensor->setDictionary(array(
    'cs',
    'de',
    'en-base',
    'en-uk',
    'en-us',
    'es',
    'fi',
    'fr',
    'it',
    'jp',
    'kr',
    'nl',
    'no'
));

// Function to check if a user already exists in the database
function checkUserExists($username) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("SELECT * FROM `users` WHERE `username` = ? LIMIT 1;");
    $q->bind_param('s', $username);
    $q->execute();
    $res = $q->get_result();

    // Returning true if user exists, false otherwise
    if($res = $res->fetch_array())
        return true;
    else
        return false;
}

// Function to create a new user account (✅ updated to insert email)
function createAccount($username, $password, $email) {
    $aes = new AES256();
    $sessKey = generateSessionKey();

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("INSERT INTO `users` (`username`, `password`, `email`, `sessionKey`) VALUES (?, ?, ?, ?)");
    $q->bind_param('ssss', $username, $password, $email, $sessKey);
    $q->execute();

    // If insertion was successful, set cookies and redirect
    if($q->affected_rows == 1) {
        setcookie("sessionId", $sessKey, time() + 86400, '/'); 
        setcookie("user_name", $username, time() + 86400, '/');
        header('Location: ../');
        return "responseCode=1";
    }

    // Return error response if insertion failed
    return "responseCode=2";
    header("Location: ../register/?err=" . urlencode($aes->encrypt("An unknown error occurred.", "secretkey")));
}

// Processing POST data if present
if(isset($_POST['userID']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['email'])) {
    $aes = new AES256();
    $username = $_POST['userID'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $email = $_POST['email'];
    
    // Checking if passwords match
    if($password != $confirmPassword) {
        header("Location: ../register/?err=" . urlencode($aes->encrypt("Passwords do not match", "secretkey")));
        echo 'responseCode=999';
        exit();
    }

    // Validating username against profanity and other criteria
    if(!empty($username) && !empty($password) && !empty($email)) {
		if(checkUserExists($username))
			{
			   header("Location: ../register/?err=" . urlencode($aes->encrypt("Username is already taken", "secretkey")));
               echo 'responseCode=999';
               exit();
			}
        if(!$pfcensor->hasProfanity($username) && strpos($bbcensor->censorString($username, true)['clean'], '*') === false && preg_match('/^(?=[a-zA-Z]{2})(?=.{3,16})[\w -]+$/iD', $username) && !preg_match('/([a-z A-Z]+\w)\1+$/', $username) && strlen($username) <= 16 && preg_match_all('/[0-9]/', $username) <= 4 && preg_match_all('/-/', $username) <= 2 && preg_match_all('/_/', $username) <= 2 && preg_match('/^\S.*\S$/', $username) && substr_count($username, ' ') <= 2) {
            $password = hash('sha256', $password); // Create a SHA256 hash of the password
            echo createAccount($username, $password, $email);
        } else {
            header("Location: ../register/?err=" . urlencode($aes->encrypt("The username is not allowed.", "secretkey")));
            echo 'responseCode=3';
        }
    } else {
        echo 'responseCode=991';
        header("Location: ../register/?err=" . urlencode($aes->encrypt("The username, password, or email cannot be blank", "secretkey")));
    }
} else {
    echo 'responseCode=990';
}
?>
