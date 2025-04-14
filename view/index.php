<?php
// Set error reporting level to report all errors except E_NOTICE
error_reporting(1);

// Include necessary files
include('../essential/backbone.php');

// Start session
session_start();

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

// Retrieve feedback from session
$feedback = null;
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']); // Clear the feedback after retrieving
}
?>
<!DOCTYPE html>   
<html>   
<head>  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <title><?php echo htmlspecialchars($name); ?></title>  
    <link rel="stylesheet" href="/assets/css/style_details.css"> 
</head>    
<body>    
    <?php if ($feedback): ?>
        <div class="feedback-message feedback-<?php echo $feedback['type']; ?>">
            <?php echo htmlspecialchars($feedback['message']); ?>
            <span class="feedback-close" onclick="this.parentElement.remove()">×</span>
        </div>
    <?php endif; ?>

    <div class="container">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="success-message">
                Report submitted successfully!
            </div>
        <?php endif; ?>

        <div class="bowser-image">
            <img src="../create-bowser/uploads/<?php echo htmlspecialchars($itemImageName); ?>" 
                alt="Bowser Image"
                onerror="this.src='/create-item/uploads/NOIMAGE.jpg'">
        </div>

        <div class="details">
            <h2><?php echo htmlspecialchars($name); ?></h2>
            <p><strong>Details:</strong> <?php echo htmlspecialchars($details); ?></p>
            <p><strong>Postcode:</strong> <?php echo htmlspecialchars($postcode); ?></p>
            <p><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($status); ?></span></p>
        </div>

        <?php if ($loggedIn): ?>
            <div class="report-form">
                <h3>Report this Bowser</h3>
                <form action="../report/submit_bowser.php" method="POST">
                    <input type="hidden" name="bowserId" value="<?php echo htmlspecialchars($bowserId); ?>">
                    <div class="form-group">
                        <label>Report Details:</label>
                        <textarea name="report" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Urgency Level:</label>
                        <select name="typeOfReport" required>
                            <option value="Urgent">Urgent</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit">Submit Report</button>
                </form>
            </div>
        <?php endif; ?>

        <a href="../" class="btn-back">Back to List</a>
    </div>
</body>     
</html>

