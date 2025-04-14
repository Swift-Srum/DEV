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
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <title><?php echo htmlspecialchars($name); ?></title>  
    <link rel="stylesheet" href="/assets/css/style_details.css"> 
</head>    
<body>    
    <div class="container">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="success-message">
                <strong>Bowser successfully reported!</strong>
            </div>
        <?php endif; ?>
        
        <div class="bowser-details">
            <div class="image-container">
                <?php echo "<img src=\"../create-bowser/uploads/" . htmlspecialchars($itemImageName) . "\" 
                    onerror=\"this.onerror=null;this.src='/create-item/uploads/NOIMAGE.jpg';\" 
                    alt=\"Bowser Image\">"; ?>
            </div>

            <div class="details-section">
                <h2><?php echo htmlspecialchars($name); ?></h2>
                <div class="detail-item">
                    <strong>Details:</strong> <?php echo htmlspecialchars($details); ?>
                </div>
                <div class="detail-item">
                    <strong>Postcode:</strong> <?php echo htmlspecialchars($postcode); ?>
                </div>
                <div class="detail-item">
                    <strong>Status:</strong> <span class="status-<?php echo strtolower($status); ?>">
                        <?php echo htmlspecialchars($status); ?>
                    </span>
                </div>
            </div>

            <?php if ($loggedIn): ?>
                <div class="report-section">
                    <h3>Report this Bowser</h3>
                    <form id="reportForm" action="../report/submit_bowser.php" method="POST">
                        <input type="hidden" name="bowserId" value="<?php echo htmlspecialchars($id); ?>">
                        <div class="form-group">
                            <label for="report">Report Details:</label>
                            <textarea name="report" id="report" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="typeOfReport">Urgency Level:</label>
                            <select name="typeOfReport" id="typeOfReport" required>
                                <option value="Urgent">Urgent</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">Submit Report</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <p>Please log in to report this bowser</p>
                    <a href="../login/" class="login-btn">Log In to Report</a>
                </div>
            <?php endif; ?>

            <div class="navigation">
                <button type="button" class="back-btn" onclick="window.location.href='../';">Back to List</button>
            </div>
        </div>
    </div>
</body>     
</html>

