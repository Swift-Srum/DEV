<?php
include('../essential/backbone.php');


// Check if user is logged in
if (!isset($_COOKIE['user_name']) || !isset($_COOKIE['sessionId'])) {
    header("Location: /login");
    exit();
}

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$userId = getUserID();

// Validate session
$loggedIn = confirmSessionKey($username, $sessionID);
if (!$loggedIn) {
    header("Location: /login");
    exit();
}
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
    
    <div id="formContainer">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">Report submitted successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">Error submitting report. Please try again.</div>
        <?php endif; ?>

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