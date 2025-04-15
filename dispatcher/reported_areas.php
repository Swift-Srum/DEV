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

// Get filter values
$urgency = $_GET['urgency'] ?? '';
$postcode = $_GET['postcode'] ?? '';

// Get reported areas based on filters
$reports = getReportedAreas($urgency, $postcode);

// Get drivers list
$drivers = getDrivers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reported Areas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Reported Areas</h1>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
            <select name="urgency">
                <option value="">All Urgencies</option>
                <option value="Urgent" <?= $urgency === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                <option value="Medium" <?= $urgency === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Low" <?= $urgency === 'Low' ? 'selected' : '' ?>>Low</option>
            </select>
            
            <input type="text" name="postcode" placeholder="Postcode" value="<?= htmlspecialchars($postcode) ?>">
            <button type="submit">Filter</button>
        </form>

        <!-- Reports Count -->
        <div class="reports-count">
            Total Reports: <?= count($reports) ?>
        </div>

        <!-- Reports Table -->
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Report</th>
                    <th>Type</th>
                    <th>Postcode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?= htmlspecialchars($report['area']) ?></td>
                    <td><?= htmlspecialchars($report['report']) ?></td>
                    <td><?= htmlspecialchars($report['reportType']) ?></td>
                    <td><?= htmlspecialchars($report['postcode']) ?></td>
                    <td>
                        <select class="driver-select" data-report-id="<?= $report['id'] ?>">
                            <option value="">Select Driver</option>
                            <?php foreach ($drivers as $driver): ?>
                            <option value="<?= $driver['id'] ?>"><?= htmlspecialchars($driver['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button onclick="assignToDriver(<?= $report['id'] ?>)">Assign</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function assignToDriver(reportId) {
        const driverId = document.querySelector(`select[data-report-id="${reportId}"]`).value;
        if (!driverId) {
            alert('Please select a driver');
            return;
        }

        fetch('assign_driver.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `reportId=${reportId}&driverId=${driverId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error assigning driver');
            }
        });
    }
    </script>
</body>
</html>