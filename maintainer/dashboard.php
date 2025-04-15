<?php
error_reporting(1);
include('../essential/backbone.php');
session_start();

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a maintainer
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'maintainer') {
    header("Location: ../login/");
    exit();
}

// Get maintenance records
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// Update the query to include bowser name, postcode, and bowser ID
$query = "SELECT mb.*, b.status_maintenance, b.name AS bowser_name, b.postcode, b.id AS bowser_id 
          FROM maintain_bowser mb 
          JOIN bowsers b ON mb.bowserId = b.id 
          WHERE mb.userId = ?";
$stmt = $db->prepare($query);
$userId = getUserID();
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$maintenanceRecords = $result->fetch_all(MYSQLI_ASSOC);

// Define maintenance status options
$maintenanceStatuses = [
    'Maintenance Requested',
    'Under Maintenance',
    'Ready',
    'Out of Service'
];

include('../maintainer/header.php');
?>

<style>
.resolve-btn {
    margin-left: 5px;
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 3px;
}

.resolve-btn:hover {
    background-color: #45a049;
}
</style>

<div class="content-area">
    <div class="content-header">
        <h1>Maintenance Dashboard</h1>
    </div>

    <div class="content-body">
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>Bowser Name</th>
                    <th>Location</th>
                    <th>Description of Work</th>
                    <th>Date of Maintenance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($maintenanceRecords as $record): ?>
                <tr data-id="<?= $record['id'] ?>" data-bowser-id="<?= $record['bowser_id'] ?>">
                    <td><?= htmlspecialchars($record['bowser_name']) ?></td>
                    <td><?= htmlspecialchars($record['postcode']) ?></td>
                    <td>
                        <textarea class="description-edit"><?= htmlspecialchars($record['descriptionOfWork']) ?></textarea>
                    </td>
                    <td>
                        <input type="date" class="date-edit" value="<?= htmlspecialchars($record['dateOfMaintenance']) ?>">
                    </td>
                    <td>
                        <select class="status-edit">
                            <?php foreach ($maintenanceStatuses as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>" 
                                        <?= $record['status_maintenance'] === $status ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button onclick="updateMaintenance(<?= $record['id'] ?>)">Save Changes</button>
                        <button onclick="resolveMaintenance(<?= $record['id'] ?>)" class="resolve-btn">Resolve</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateMaintenance(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const bowserId = row.dataset.bowserId;
    const description = row.querySelector('.description-edit').value;
    const date = row.querySelector('.date-edit').value;
    const status = row.querySelector('.status-edit').value;

    fetch('update_maintenance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&bowserId=${bowserId}&description=${encodeURIComponent(description)}&date=${date}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Maintenance record updated successfully');
        } else {
            alert('Error updating maintenance record: ' + data.message);
        }
    });
}

function resolveMaintenance(id) {
    if (!confirm('Are you sure you want to resolve this maintenance record?')) {
        return;
    }

    fetch('resolve_maintenance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-id="${id}"]`);
            row.remove();
            alert('Maintenance record resolved successfully');
        } else {
            alert('Error resolving maintenance record: ' + data.message);
        }
    });
}
</script>