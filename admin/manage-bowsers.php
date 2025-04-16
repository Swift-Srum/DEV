<?php
error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];

// Check if user is logged in and is admin
$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);

if (!$loggedIn || !$isAdmin) {
    header("Location: /login");
    exit();
}

// Get filter parameters
$model = $_GET['model'] ?? '';
$capacity = $_GET['capacity'] ?? '';
$supplier = $_GET['supplier'] ?? '';
$dateReceived = $_GET['date_received'] ?? '';
$dateReturned = $_GET['date_returned'] ?? '';
$postcode = $_GET['postcode'] ?? '';
$active = $_GET['active'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$query = "SELECT * FROM bowsers WHERE 1=1";
$params = array();

if ($model) {
    $query .= " AND model LIKE ?";
    $params[] = "%$model%";
}
if ($capacity) {
    $query .= " AND capacity_litres = ?";
    $params[] = $capacity;
}
if ($supplier) {
    $query .= " AND supplier_company LIKE ?";
    $params[] = "%$supplier%";
}
if ($dateReceived) {
    $query .= " AND date_received = ?";
    $params[] = $dateReceived;
}
if ($dateReturned) {
    $query .= " AND date_returned = ?";
    $params[] = $dateReturned;
}
if ($postcode) {
    $query .= " AND postcode LIKE ?";
    $params[] = "%$postcode%";
}
if ($active !== '') {
    $query .= " AND active = ?";
    $params[] = $active;
}
if ($status) {
    $query .= " AND status_maintenance = ?";
    $params[] = $status;
}

// Get bowsers
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$bowsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bowsers - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Admin Dashboard</h1>
            <nav>
                <a href="../" class="nav-link">Home</a>
                <a href="../create-bowser/" class="nav-link">Add Bowser</a>
                <a href="../login/logout.php?session=<?php echo $_COOKIE['sessionId']; ?>" class="nav-link">Logout</a>
            </nav>
        </header>

        <main>
            <section class="filter-section">
                <h2>Filter Bowsers</h2>
                <form id="filterForm" method="GET">
                    <div class="filter-grid">
                        <div class="filter-item">
                            <label for="model">Model:</label>
                            <input type="text" id="model" name="model" value="<?= htmlspecialchars($model) ?>">
                        </div>
                        <div class="filter-item">
                            <label for="capacity">Capacity (L):</label>
                            <input type="text" id="capacity" name="capacity" value="<?= htmlspecialchars($capacity) ?>">
                        </div>
                        <div class="filter-item">
                            <label for="supplier">Supplier:</label>
                            <input type="text" id="supplier" name="supplier" value="<?= htmlspecialchars($supplier) ?>">
                        </div>
                        <div class="filter-item">
                            <label for="date_received">Date Received:</label>
                            <input type="date" id="date_received" name="date_received" value="<?= htmlspecialchars($dateReceived) ?>">
                        </div>
                        <div class="filter-item">
                            <label for="date_returned">Date Returned:</label>
                            <input type="date" id="date_returned" name="date_returned" value="<?= htmlspecialchars($dateReturned) ?>">
                        </div>
                        <div class="filter-item">
                            <label for="postcode">Postcode:</label>
                            <input type="text" id="postcode" name="postcode" value="<?= htmlspecialchars($postcode) ?>">
                        </div>
                        <div class="filter-item">
                            <label for="active">Active:</label>
                            <select id="active" name="active">
                                <option value="">All</option>
                                <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="status">Status:</label>
                            <select id="status" name="status">
                                <option value="">All</option>
                                <option value="On Depot" <?= $status === 'On Depot' ? 'selected' : '' ?>>On Depot</option>
                                <option value="Dispatched" <?= $status === 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                <option value="In Transit" <?= $status === 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                <option value="Maintenance Requested" <?= $status === 'Maintenance Requested' ? 'selected' : '' ?>>Maintenance Requested</option>
                                <option value="Under Maintenance" <?= $status === 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                                <option value="Ready" <?= $status === 'Ready' ? 'selected' : '' ?>>Ready</option>
                                <option value="Out of Service" <?= $status === 'Out of Service' ? 'selected' : '' ?>>Out of Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit">Apply Filters</button>
                        <button type="reset" onclick="window.location.href='?'">Clear Filters</button>
                    </div>
                </form>
            </section>

            <section class="bowsers-list">
                <h2>Bowsers List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Capacity (L)</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Date Returned</th>
                            <th>Postcode</th>
                            <th>Status</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bowsers as $bowser): ?>
                        <tr data-id="<?= htmlspecialchars($bowser['id']) ?>">
                            <td><?= htmlspecialchars($bowser['name']) ?></td>
                            <td><?= htmlspecialchars($bowser['model']) ?></td>
                            <td><?= htmlspecialchars($bowser['capacity_litres']) ?></td>
                            <td><?= htmlspecialchars($bowser['supplier_company']) ?></td>
                            <td><?= htmlspecialchars($bowser['date_received']) ?></td>
                            <td><?= htmlspecialchars($bowser['date_returned']) ?></td>
                            <td><?= htmlspecialchars($bowser['postcode']) ?></td>
                            <td><?= htmlspecialchars($bowser['status_maintenance']) ?></td>
                            <td><?= $bowser['active'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <button onclick="editBowser(<?= $bowser['id'] ?>)">Edit</button>
                                <button onclick="deleteBowser(<?= $bowser['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="../assets/js/manage-bowsers.js"></script>
</body>
</html>