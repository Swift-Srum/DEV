<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../essential/backbone.php');
session_start();

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if user is logged in and is a driver
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = getUserType($username);

if (!$loggedIn || $userType !== 'driver') {
    header("Location: ../login/");
    exit();
}

// Get driver ID - modified to use the username directly
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userId = $user['id'];

// Replace the original task query with a join query to get destination and bowser name
$query = "SELECT dt.*, ar.postcode AS destination, b.name AS bowser_name 
          FROM drivers_tasks dt 
          LEFT JOIN area_reports ar ON dt.area_report_id = ar.id 
          LEFT JOIN bowsers b ON dt.bowser_id = b.id 
          WHERE dt.driver_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$driverTasks = $result->fetch_all(MYSQLI_ASSOC);

// Add debug output
echo "<!-- Debug: Found " . count($driverTasks) . " tasks for user ID: $userId -->";

// Get available bowsers (status = "On Depot")
$query = "SELECT id, name FROM bowsers WHERE status_maintenance = 'On Depot'";
$result = $db->query($query);
$availableBowsers = $result->fetch_all(MYSQLI_ASSOC);

// Define bowser status options
$bowserStatuses = [
    'On Depot',
    'In Transit',
    'Dispatched'
];

include('../driver/header.php');
?>

<div class="content-area">
    <div class="content-header">
        <h1>Driver Dashboard</h1>
    </div>

    <div class="content-body">
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>Bowser</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($driverTasks as $task): ?>
                <tr data-id="<?= $task['id'] ?>" data-bowser-id="<?= $task['bowser_id'] ?>">
                    <td>
                        <?php if ($task['bowser_id']): ?>
                            <!-- Make the bowser name clickable -->
                            <span class="bowser-name" onclick="showBowserSelection(this, <?= $task['id'] ?>)"><?= htmlspecialchars($task['bowser_name']) ?></span>
                            <!-- Hidden dropdown that will show when clicked -->
                            <div class="bowser-selection" style="display:none;">
                                <select class="bowser-select">
                                    <option value="<?= $task['bowser_id'] ?>"><?= htmlspecialchars($task['bowser_name']) ?></option>
                                    <?php foreach ($availableBowsers as $bowser): ?>
                                        <?php if ($bowser['id'] != $task['bowser_id']): ?>
                                            <option value="<?= $bowser['id'] ?>"><?= htmlspecialchars($bowser['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button onclick="changeBowser(<?= $task['id'] ?>, this)">Change</button>
                                <button onclick="cancelBowserChange(this)">Cancel</button>
                            </div>
                        <?php else: ?>
                            <select class="bowser-select">
                                <option value="">Select a Bowser</option>
                                <?php foreach ($availableBowsers as $bowser): ?>
                                <option value="<?= $bowser['id'] ?>"><?= htmlspecialchars($bowser['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="assignBowser(<?= $task['id'] ?>)">Assign</button>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($task['destination'] ?? 'Not assigned') ?></td>
                    <td>
                        <select class="status-edit">
                            <?php foreach ($bowserStatuses as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>" 
                                        <?= ($task['status'] === $status || ($task['bowser_id'] && $task['status_maintenance'] === $status)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button onclick="updateTaskStatus(<?= $task['id'] ?>)" class="btn-status">Update Status</button>
                        <button onclick="resolveTask(<?= $task['id'] ?>)" class="btn-resolve">Resolve Task</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.bowser-name {
    cursor: pointer;
    color: #0066cc;
    text-decoration: underline;
}
.bowser-name:hover {
    color: #004080;
}
.bowser-selection {
    margin-top: 5px;
}
.btn-resolve {
    margin-left: 5px;
    background-color: #d9534f;
    color: white;
    border: none;
    padding: 2px 8px;
    cursor: pointer;
    border-radius: 3px;
}
.btn-resolve:hover {
    background-color: #c9302c;
}
.btn-status {
    background-color: #5bc0de;
    color: white;
    border: none;
    padding: 2px 8px;
    cursor: pointer;
    border-radius: 3px;
}
.btn-status:hover {
    background-color: #46b8da;
}
</style>

<script>
function showBowserSelection(element, taskId) {
    // Hide the bowser name and show the selection dropdown
    element.style.display = 'none';
    element.nextElementSibling.style.display = 'block';
}

function cancelBowserChange(button) {
    // Find the parent div and hide it, then show the bowser name again
    const selectionDiv = button.closest('.bowser-selection');
    selectionDiv.style.display = 'none';
    selectionDiv.previousElementSibling.style.display = 'inline';
}

function changeBowser(taskId, button) {
    const selectionDiv = button.closest('.bowser-selection');
    const bowserId = selectionDiv.querySelector('.bowser-select').value;
    
    if (!bowserId) {
        alert('Please select a bowser');
        return;
    }

    fetch('update_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=change&taskId=${taskId}&bowserId=${bowserId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bowser changed successfully');
            location.reload();
        } else {
            alert('Error changing bowser: ' + data.message);
        }
    });
}

function assignBowser(taskId) {
    const row = document.querySelector(`tr[data-id="${taskId}"]`);
    const bowserId = row.querySelector('.bowser-select').value;
    
    if (!bowserId) {
        alert('Please select a bowser');
        return;
    }

    fetch('update_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=assign&taskId=${taskId}&bowserId=${bowserId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bowser assigned successfully');
            location.reload();
        } else {
            alert('Error assigning bowser: ' + data.message);
        }
    });
}

function updateTaskStatus(taskId) {
    const row = document.querySelector(`tr[data-id="${taskId}"]`);
    const bowserId = row.dataset.bowserId;
    const status = row.querySelector('.status-edit').value;

    if (!bowserId) {
        alert('You must assign a bowser first');
        return;
    }

    fetch('update_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=updateStatus&taskId=${taskId}&bowserId=${bowserId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully');
        } else {
            alert('Error updating status: ' + data.message);
        }
    });
}

function resolveTask(taskId) {
    if (!confirm('Are you sure you want to resolve this task? This will remove it from your task list.')) {
        return;
    }

    fetch('update_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=resolve&taskId=${taskId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Task resolved successfully');
            // Remove the row from the table
            document.querySelector(`tr[data-id="${taskId}"]`).remove();
        } else {
            alert('Error resolving task: ' + data.message);
        }
    });
}
</script>