<?php
error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$aes = new AES256;
$err = $_GET['err'];
$err = $aes->decrypt($err, "secretkey");

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirm'];

if($password != $confirmPassword){
	
	header("Location: ../forgot-pass/reset_page.php/?err=" . urlencode($aes->encrypt("Passwords do not match", "secretkey")));
    echo 'responseCode=1';
	exit();
}


$userId = getUserIDByEmail($email);
$timestamp = time();
$code = $_POST['code'];


$valid = checkCodeValid($userId, $code, $timestamp);

if($valid){
	$password = hash('sha256', $password); // Create a SHA256 hash of the password
	resetPassword($userId, $password);
	header("Location: ../forgot-pass/reset_page.php/?err=" . urlencode($aes->encrypt("Password reset", "secretkey")));
    echo 'responseCode=1';
}
else{
	header("Location: ../forgot-pass/reset_page.php?email=" . $email . "&err=" . urlencode($aes->encrypt("The code provided is invalid.", "secretkey")));
    echo 'responseCode=9991';
}


?>
