<?php
// Setting error reporting to display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Including necessary files
include('../essential/backbone.php');

// Debugging: Check if file inclusion is successful
if (!file_exists('../essential/backbone.php')) {
    die("Error: backbone.php not found");
}

// Retrieving username and session ID from cookies
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Checking if user is logged in
$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);



// If not admin, exit script
if (!$isAdmin) {
    die("Error: User not admin");
}

// Debugging: Check if confirmSessionKey function works correctly
if ($loggedIn === false) {
    die("Error: User not logged in");
}

// Retrieving user ID
$idx = getUserID();

// Debugging: Check if getUserID function works correctly
if ($idx === false) {
    die("Error: Unable to retrieve user ID");
}

// Processing POST data if present
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aes = new AES256();

    // Stripping HTML tags from POST data to prevent cross-site scripting
	$name = trim(strip_tags($_POST['name'] ?? ''));
    $man_details = trim(strip_tags($_POST['details'] ?? ''));
    $model = trim(strip_tags($_POST['model'] ?? ''));
    $serial = trim(strip_tags($_POST['serial'] ?? ''));
    $notes = trim(strip_tags($_POST['notes'] ?? ''));
    $capacity = trim(strip_tags($_POST['capacity'] ?? ''));
    $length = trim(strip_tags($_POST['length'] ?? ''));
    $width = trim(strip_tags($_POST['width'] ?? ''));
    $height = trim(strip_tags($_POST['height'] ?? ''));
    $weight_empty = trim(strip_tags($_POST['weight_empty'] ?? ''));
    $weight_full = trim(strip_tags($_POST['weight_full'] ?? ''));
    $supplier = trim(strip_tags($_POST['supplier'] ?? ''));
    $postcode = trim(strip_tags($_POST['postcode'] ?? ''));
    $date_received = trim(strip_tags($_POST['date_received'] ?? ''));
    $date_returned = trim(strip_tags($_POST['date_returned'] ?? ''));

    // Initialize eastings and northings
    $eastings = $northings = null;

    // Handle postcode to fetch eastings and northings
    if ($postcode) {
        $url = "https://api.postcodes.io/postcodes/$postcode";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['result'])) {
            $eastings = $data['result']['eastings'];
            $northings = $data['result']['northings'];
			$longitude = $data['result']['longitude'];
			$latitude = $data['result']['latitude'];
        }
    }

    // Checking if all required fields are not empty
    if (!empty($name) && !empty($latitude) &&!empty($longitude) && !empty($northings) && !empty($eastings) && !empty($postcode) && !empty($man_details) && !empty($model) && !empty($serial) && !empty($notes) && !empty($capacity) && !empty($length) && !empty($width) && !empty($height) && !empty($weight_empty) && !empty($weight_full) && !empty($supplier) && !empty($date_received) && !empty($date_returned)) {

        // Calling function to submit item details and exiting script after redirection
		
        createBowser($idx, $name, $man_details, $model, $serial, $notes, $capacity, $length, $width, $height, $weight_empty, $weight_full, $supplier, $date_received, $date_returned, $postcode, $northings, $eastings, $longitude, $latitude);
        echo 'created';
		header("Location: ../create-bowser/?err=" . urlencode($aes->encrypt("Bowser created successfully!", "secretkey")));
    } else {
        // If any required field is empty, return error response and redirect with error message
        echo 'Empty Fields';
        header("Location: ../create-bowser/?err=" . urlencode($aes->encrypt("Please fill out all fields, and make sure the postcode is correct", "secretkey")));
        exit(); // Exit script after redirection
    }
} else {
    echo 'responseCode=999'; // If not POST request, return error code
}
?>
