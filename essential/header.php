<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Water Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="main-nav">
            <div class="nav-header">
                <h2>Dashboard</h2>
            </div>
            <ul class="nav-links">
                <li><a href="../dashboard/" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">Dashboard Home</a></li>
                <li><a href="../dispatcher/reported_areas.php" class="<?= $currentPage == 'reported_areas.php' ? 'active' : '' ?>">Reported Areas</a></li>
                <li><a href="../dispatcher/reported_bowsers.php" class="<?= $currentPage == 'reported_bowsers.php' ? 'active' : '' ?>">Reported Bowsers</a></li>
                <!-- Add other navigation items as needed -->
            </ul>
        </nav>