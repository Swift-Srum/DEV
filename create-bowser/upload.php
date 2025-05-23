<?php

error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

$loggedIn = confirmSessionKey($username, $sessionID);

if ($loggedIn != true) {
    exit("Error: User not logged in.");
}

$idx = getUserID();
$itemId = getMostRecentItem($idx); // Get the id of the associated bowser

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
    // Check if file was uploaded without errors
    if ($_FILES["fileToUpload"]["error"] == 0) {
        // Allow only image files
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $fileType = $_FILES["fileToUpload"]["type"];
        $extension = $allowedTypes[$fileType] ?? null;

        if ($extension !== null) {
            $fileName = $username . "_" . rand(10000, 99999) . "." . $extension;
            $targetDir = __DIR__ . "/uploads/"; // Ensure the path is relative to the create-bowser folder
            $targetFile = $targetDir . $fileName;

            // Ensure the uploads directory exists
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
                // Insert the file information into the database
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

                if ($db->connect_error) {
                    die("Database connection failed: " . $db->connect_error);
                }

                $stmt = $db->prepare("INSERT INTO uploads (fileName, bowserId) VALUES (?, ?)");
                $stmt->bind_param("si", $fileName, $itemId);

                if ($stmt->execute()) {
                    echo "responseCode=1"; 
                } else {
                    echo "Error: Failed to insert file information into the database.";
                }

                $stmt->close();
                $db->close();
            } else {
                echo "Error: There was an error uploading your file.";
            }
        } else {
            echo "Error: Only image files (JPEG, PNG, GIF) are allowed.";
        }
    } else {
        echo "Error: " . $_FILES["fileToUpload"]["error"];
    }
} else {
    echo "Error: Invalid request.";
}
?>
