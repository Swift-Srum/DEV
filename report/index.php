<!-- filepath: c:\Users\adanh\Documents\GitHub\DEV\report\index.php -->
<?php
// Include necessary files
include('../essential/internal.php');

// Check if the user is logged in
if (isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
    $username = $_COOKIE['user_name'];
    $sessionID = $_COOKIE['sessionId'];

    // Confirm the session key
    $loggedIn = confirmSessionKey($username, $sessionID);

    if ($loggedIn) {
        // Retrieve the user ID
        $userId = getUserID();
    } else {
        // Redirect to login page if not logged in
        header("Location: /login");
        exit();
    }
} else {
    // Redirect to login page if no session exists
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
    <div id="formContainer" class="active">
        <form action="/report/submit_area.php" method="POST">
            <input type="hidden" id="userId" name="userId" value="<?php echo htmlspecialchars($userId); ?>">

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