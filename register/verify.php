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
$userId = getUserID();
$unverified = checkIsUnverified($username, $sessionID);
$timestamp = time();
$code = $_POST['code'];


if (!$unverified) {
    header("Location: /login");
    exit();
}

$valid = checkCodeValid($userId, $code, $timestamp);

if($valid){
	setVerified($userId);
	header("Location: ../register/verify_page.php/?err=" . urlencode($aes->encrypt("Verified successfully", "secretkey")));
    echo 'responseCode=1';
}
else{
	header("Location: ../register/verify_page.php?err=" . urlencode($aes->encrypt("The code provided is invalid", "secretkey")));
    echo 'responseCode=9991';
}


?>
