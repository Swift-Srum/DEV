<?php
include('../essential/backbone.php');

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$userId = getUserID();

// Validate session



$loggedIn = confirmSessionKey($username, $sessionID);
if (!$loggedIn) {
    header("Location: /login");
    exit();
}

$success = $_GET['success'];

if($success == 1)
	echo '<script>alert("Report made successfully")</script>';
else if(!$success && $success != null)
	echo '<script>alert("Report failed for an unspecified reason")</script>'; //This should be updated in future to be more descriptive. Currently checks basic things like postcode being valid, empty fields etc
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Area</title>
    <link rel="stylesheet" href="/assets/css/style_report.css">
</head>
<body>
    <header>
        <h1>Report an Area</h1>
    </header>
    
    <div id="formContainer" class="active">  <!-- Added 'active' class -->
        <form action="/report/submit_area.php" method="POST">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($userId); ?>">
            
            <label for="postcode">Postcode:</label>
            <input type="text" id="postcode" name="postcode" placeholder="Enter the postcode" required>
            
            <label for="report">Report Details:</label>
            <textarea id="report" name="report" placeholder="Describe the issue" rows="5" required></textarea>
            
            <label for="reportType">Urgency:</label>
            <select id="reportType" name="reportType" required>
                <option value="Urgent">Urgent</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
            
            <button type="submit" class="report-btn">Submit Report</button>
        </form>
    </div>
</body>
</html>