<?php
error_reporting(1);
include('../essential/backbone.php');
session_start();

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a dispatcher
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'dispatcher') {
    header("Location: ../login/");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatcher Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Dispatcher Dashboard</h1>
            <nav>
                <a href="../index.php" class="nav-link">Home</a>
                <a href="../login/logout.php?session=<?php echo $sessionID; ?>" class="nav-link">Logout</a>
            </nav>
        </header>

        <main>
            <div class="dashboard-buttons">
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/reported_bowsers.php" class="nav-link">Reported Bowsers</a>
                <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/reported_areas.php" class="nav-link">Reported Areas</a>
            </div>
        </main>
    </div>
</body>
</html>