<?php
// Set error reporting level to report all errors except E_NOTICE
error_reporting(1);

// Include necessary files
include('../essential/backbone.php');
include('../essential/internal.php');

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

// Fetch bowser details
$bowserId = $_GET['id'] ?? '';
if (!is_numeric($bowserId)) {
    die("Invalid bowser ID.");
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = $db->prepare("SELECT * FROM bowsers WHERE id = ? AND active = 1 LIMIT 1;");
$stmt->bind_param('i', $bowserId);
$stmt->execute();
$result = $stmt->get_result();
$bowser = $result->fetch_assoc();

if (!$bowser) {
    die("Bowser not found or inactive.");
}
?>
<!DOCTYPE html>   
<html>   
<head>  
<link rel="stylesheet" href="/assets/css/style_details.css"> 
<style>
    img {
        max-width: 100%; /* Ensure the image does not exceed the container width */
        height: auto; /* Maintain the aspect ratio */
        display: block; /* Ensure proper layout */
        margin: 0 auto; /* Center the image horizontally */
    }
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">  
<title> <?php echo htmlspecialchars($bowser['name']); ?></title>  
</head>    
<body>    
    <form>  
        <div class="container">   
            <?php 
			echo "<img src=\"../create-bowser/uploads/" . htmlspecialchars($bowser['image']) . "\" onerror=\"this.onerror=null;this.src='/create-item/uploads/NOIMAGE.jpg';\"";
			echo "<br>";
            echo "<label>Bowser Name: " . htmlspecialchars($bowser['name']) . "</label><br>";
            echo "<label>Details: " . htmlspecialchars($bowser['manufacturer_details']) . "</label><br>";            
			echo "<label>Postcode: " . htmlspecialchars($bowser['postcode']) . "</label><br>";
			echo "<label>Status: Available </label><br>";
            ?>
			<br>
			
			<a href="../"><button type="button" class="cancelbtn">Back</button></a>

            <?php if ($loggedIn): ?>
                <div class="report-form" style="margin-top: 20px;">
                    <h3>Report this Bowser</h3>
                    <form id="reportForm" action="../report/submit_bowser.php" method="POST">
                        <input type="hidden" name="bowserId" value="<?php echo htmlspecialchars($bowserId); ?>">
                        
                        <div class="form-group">
                            <label for="report">Report Details:</label>
                            <textarea id="report" name="report" rows="5" required 
                                style="width: 100%; margin-bottom: 10px;"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="typeOfReport">Urgency Level:</label>
                            <select id="typeOfReport" name="typeOfReport" required 
                                style="width: 100%; margin-bottom: 10px;">
                                <option value="Urgent">Urgent</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                        
                        <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                            Submit Report
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p>You must be logged in to report this bowser.</p>
            <?php endif; ?>

        </div>

    </form>   

</body>     
</html>
