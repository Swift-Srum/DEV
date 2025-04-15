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

// Get reported bowsers based on filters
$reports = getReportedBowsers($urgency, $postcode);

// Get maintainers list
$maintainers = getMaintainers();

include('../essential/header.php');
?>

<div class="content-area">
    <div class="content-header">
        <h1>Reported Bowsers</h1>
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
                    <td><?= htmlspecialchars($report['typeOfReport']) ?></td>
                    <td><?= htmlspecialchars($report['postcode']) ?></td>
                    <td>
                        <select class="maintainer-select" data-report-id="<?= $report['id'] ?>">
                            <option value="">Select Maintainer</option>
                            <?php foreach ($maintainers as $maintainer): ?>
                            <option value="<?= $maintainer['id'] ?>"><?= htmlspecialchars($maintainer['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button onclick="assignToMaintainer(<?= $report['id'] ?>)">Assign</button>
                        <button onclick="markResolved(<?= $report['id'] ?>)">Resolve</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function assignToMaintainer(reportId) {
    const maintainerId = document.querySelector(`select[data-report-id="${reportId}"]`).value;
    if (!maintainerId) {
        alert('Please select a maintainer');
        return;
    }

    fetch('assign_maintainer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `reportId=${reportId}&maintainerId=${maintainerId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error assigning maintainer');
        }
    });
}

function markResolved(reportId) {
    if (!confirm('Are you sure you want to mark this report as resolved?')) {
        return;
    }

    fetch('resolve_report.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `reportId=${reportId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error resolving report');
        }
    });
}
</script>
</body>
</html>