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

$reports = getReportedAreas($urgency, $postcode);
$drivers = getDrivers();

include('../essential/header.php');
?>

<div class="content-area">
    <div class="content-header">
        <h1>Reported Areas</h1>
    </div>

    <div class="content-body">
        <div class="filters">
            <form method="GET" class="filter-form">
                <select name="urgency">
                    <option value="">All Urgencies</option>
                    <option value="Urgent" <?= $urgency === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                    <option value="Medium" <?= $urgency === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="Low" <?= $urgency === 'Low' ? 'selected' : '' ?>>Low</option>
                </select>
                
                <input type="text" name="postcode" placeholder="Postcode" value="<?= htmlspecialchars($postcode) ?>">
                <button type="submit" class="btn-primary">Filter</button>
            </form>
        </div>

        <!-- Reports Count -->
        <div class="reports-count">
            Total Reports: <?= count($reports) ?>
        </div>

        <!-- Reports Table -->
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Report</th>
                    <th>Type</th>
                    <th>Postcode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                <tr>
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
                        <button onclick="markResolved(<?= $report['id'] ?>)">Resolve</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
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
            alert('No available drivers');
        }
    });
}

function markResolved(reportId) {
    if (!confirm('Are you sure you want to mark this report as resolved?')) {
        return;
    }

    console.log('Attempting to resolve report:', reportId);

    fetch('resolve_area.php', {  // Changed from resolve_report.php to resolve_area.php
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `reportId=${reportId}&type=area`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            location.reload();
        } else {
            alert('Error resolving report: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error resolving report');
    });
}
</script>
</body>
</html>