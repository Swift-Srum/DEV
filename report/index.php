<!-- filepath: c:\Users\adanh\Documents\GitHub\DEV\report\index.php -->
<?php
include('../essential/backbone.php');

// Check if the user is logged in
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);

if (!$loggedIn) {
    // Redirect to login page if not logged in
    header("Location: /login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Page</title>
    <link rel="stylesheet" href="/assets/css/style_report.css">
</head>
<body>
    <header>
        <h1>Report Page</h1>
    </header>
    <div class="report-options">
        <button id="reportAreaBtn" class="report-btn">Report Area</button>
        <button id="reportBowserBtn" class="report-btn">Report Bowser</button>
    </div>
    <div id="formContainer">
        <!-- Forms will be dynamically inserted here -->
    </div>

    <script src="/assets/js/report.js"></script>
</body>
</html>