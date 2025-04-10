<?php

error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

$loggedIn = confirmSessionKey($username, $sessionID);

if ($loggedIn != true)
    exit();

$idx = getUserID();
$itemId = getMostRecentItem($idx); //Get the id of the associated bike report so the image can be linked to that bike

$name = getItemImage($itemId);

if($name != "NOIMAGE.jpg") // Make sure that the bowser doesnt already have an associated image to prevent potential spam
	die();


//Checks user is actually logged in before uploading the image - terminates if not

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {

    // Check if file was uploaded without errors
    if ($_FILES["fileToUpload"]["error"] == 0) {

        // Allow only image files
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $fileType = $_FILES["fileToUpload"]["type"];
        $extension = $allowedTypes[$fileType];

        if ($extension !== null) {

            $fileName = $username . "_" . rand(10000, 99999) . "." . $extension;

            $targetDir = "uploads/";
            $targetFile = $targetDir . $fileName;

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
				
				echo "responseCode=1";
				logImageUpload($fileName, $itemId);

            } else {
                echo "Sorry, there was an error uploading your file.";
            }

        } else {
            echo "Error: Only image files (JPEG, PNG, GIF) are allowed.";
        }

    } else {
        echo "Error: " . $_FILES["fileToUpload"]["error"];
    }
}
?>
