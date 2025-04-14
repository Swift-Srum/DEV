<?php
// Set error reporting level to report all errors except E_NOTICE
error_reporting(1);

// Include necessary files
include('../essential/backbone.php');

// Set HTTP headers for security measures
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Retrieve username and session ID from cookies
$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

// Check if user is logged in and if they are an admin
$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);

$userType = "";

// Retrieve bowser ID from URL parameter
$bowserId =  $_GET['id'];

// Validate bowserId
if (!is_numeric($bowserId)) {
    throw new Exception("Invalid bowserId.");
}

// Retrieve bowser details
$itemInfo = getBowserDetails($bowserId);

// Get bowser image name
$itemImageName = getItemImage($bowserId);

// Initialize AES encryption object and decrypt error message if any
$aes = new AES256;
$err = $_GET['err'] ?? '';
$err = $aes->decrypt($err, "secretkey");

// Assign bowser details to variables
foreach ($itemInfo as $item) {
    $id = $item['id'];
    $name = $item['name'];
    $postcode = $item['postcode'];
    $details = $item['manufacturer_details'];
    $active = $item['active'];
    
    $status = $active ? "Available" : "Unavailable";
}

// If item ID is null, redirect to main page
if ($id == null)
    header("Location: ../");
?>
<!DOCTYPE html>   
<html>   
<head>  
<link rel="stylesheet" href="/assets/css/style_details.css"> 
<style>
    img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">  
<title><?php echo htmlspecialchars($name); ?></title>  
</head>    
<body>    

<div class="container">   
    <?php 
    echo "<img src=\"../create-bowser/uploads/" . htmlspecialchars($itemImageName) . "\" onerror=\"this.onerror=null;this.src='/create-item/uploads/NOIMAGE.jpg';\" alt=\"Bowser Image\">";
    echo "<br>";
    echo "<label>Bowser Name: " . htmlspecialchars($name) . "</label><br>";
    echo "<label>Details: " . htmlspecialchars($details) . "</label><br>";            
    echo "<label>Postcode: " . htmlspecialchars($postcode) . "</label><br>";
    echo "<label>Status: " . htmlspecialchars($status) . "</label><br>";
    ?>
    <br>

    <button type="button" class="cancelbtn" onclick="window.location.href='../';">Back</button>

    <div class="report-form" style="margin-top: 20px;">
        <h3>Report this Bowser</h3>
        <form id="reportForm" action="../report/submit_bowser.php" method="POST">
            <input type="hidden" name="bowserId" value="<?php echo htmlspecialchars($id); ?>">
            
            <div class="form-group">
                <label for="report">Report Details:</label>
                <textarea id="report" name="report" rows="5" required style="width: 100%; margin-bottom: 10px;"></textarea>
            </div>
            
            <div class="form-group">
                <label for="typeOfReport">Urgency Level:</label>
                <select id="typeOfReport" name="typeOfReport" required style="width: 100%; margin-bottom: 10px;">
                    <option value="Urgent">Urgent</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            
            <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Submit Report
            </button>
        </form>

        <?php 
        if (isset($_GET['success'])) {
            if ($_GET['success'] == 1) {
                echo '<div style="color: green; margin-top: 10px;">Report submitted successfully!</div>';
            } else {
                echo '<div style="color: red; margin-top: 10px;">Failed to submit the report. Please try again.</div>';
            }
        }
        ?>
    </div>
</div>

<center><h1><?php echo htmlspecialchars($err); ?></h1></center>

</body>     
</html>

