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
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
            
            </div>
        </nav>
        <nav class="main-nav">
            <div class="nav-header">
                <h2>Dashboard</h2>
            </div>
            <ul class="nav-links">
                <li><a href="../" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">Home</a></li>
                <!-- Add other navigation items as needed -->
            </ul>
        </nav>