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

try {
    // Get filter parameters
    $model = trim($_GET['model'] ?? '');
    $capacity = trim($_GET['capacity'] ?? '');
    $supplier = trim($_GET['supplier'] ?? '');
    $dateReceived = trim($_GET['date_received'] ?? '');
    $dateReturned = trim($_GET['date_returned'] ?? '');
    $postcode = trim($_GET['postcode'] ?? '');
    $active = isset($_GET['active']) ? trim($_GET['active']) : '';
    $status = trim($_GET['status'] ?? '');

    // Build query
    $query = "SELECT * FROM bowsers WHERE 1=1";
    $params = array();
    $types = "";

    if (!empty($model)) {
        $query .= " AND LOWER(model) LIKE LOWER(?)";
        $params[] = "%{$model}%";
        $types .= "s";
    }
    if (!empty($capacity)) {
        $query .= " AND capacity_litres = ?";
        $params[] = $capacity;
        $types .= "s";
    }
    if (!empty($supplier)) {
        $query .= " AND LOWER(supplier_company) LIKE LOWER(?)";
        $params[] = "%{$supplier}%";
        $types .= "s";
    }
    if (!empty($dateReceived)) {
        $query .= " AND date_received = ?";
        $params[] = $dateReceived;
        $types .= "s";
    }
    if (!empty($dateReturned)) {
        $query .= " AND date_returned = ?";
        $params[] = $dateReturned;
        $types .= "s";
    }
    if (!empty($postcode)) {
        $query .= " AND LOWER(postcode) LIKE LOWER(?)";
        $params[] = "%{$postcode}%";
        $types .= "s";
    }
    if ($active !== '') {
        $query .= " AND active = ?";
        $params[] = $active;
        $types .= "i";
    }
    if (!empty($status)) {
        $query .= " AND status_maintenance = ?";
        $params[] = $status;
        $types .= "s";
    }

    // Add ORDER BY clause
    $query .= " ORDER BY name ASC";

    // Get bowsers
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }

    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $bowsers = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log($e->getMessage());
    $error_message = "An error occurred while retrieving the bowsers. Please try again later.";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($db)) {
        $db->close();
    }
}
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
                <a href="../admin/dashboard.php/" class="nav-link">Staff Control</a>
                <a href="../login/logout.php?session=<?php echo htmlspecialchars($sessionID); ?>" class="nav-link">Logout</a>
            </nav>
        </header>

        <main>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <section class="filter-section">
                <h2>Filter Bowsers</h2>
                <form id="filterForm" method="GET">
                    <div class="filter-grid">

                        <div class="filter-item">
                            <label for="capacity">Capacity (L):</label>
                            <input type="text" id="capacity" name="capacity" value="<?= htmlspecialchars($capacity) ?>">
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
                <?php if (empty($bowsers)): ?>
                    <p class="no-results">No bowsers found matching your criteria.</p>
                <?php else: ?>
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
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="../assets/js/manage-bowsers.js"></script>
</body>
</html>