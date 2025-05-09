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

// Update the staff types array to remove public user
$staffTypes = [
    'admin' => 'Administrator',
    'maintainer' => 'Maintainer',
    'dispatcher' => 'Dispatcher',
    'driver' => 'Driver'
];

// Get all staff members
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$result = $db->query("SELECT id, username, email, active, userType FROM users WHERE id != " . getUserID());
$staff = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Admin Dashboard</h1>
            <nav>
                <a href="../" class="nav-link">Home</a>
                <a href="../create-bowser/" class="nav-link">Add Bowser</a>
                <a href="../admin/manage-bowsers.php" class="nav-link">Bowser Control</a>
                <a href="../login/logout.php?session=<?php echo $_COOKIE['sessionId']; ?>" class="nav-link">Logout</a>
            </nav>
        </header>

        <main>
            <section class="add-staff">
                <h2>Add New Staff Member</h2>
                <form id="addStaffForm">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="userType" required>
                        <option value="">Select User Type</option>
                        <?php foreach($staffTypes as $value => $label): ?>
                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Add Staff Member</button>
                </form>
            </section>

            <section class="staff-list">
                <h2>Manage Staff</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $member): ?>
                        <tr data-id="<?php echo $member['id']; ?>">
                            <td><?php echo htmlspecialchars($member['username']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td>
                                <select class="user-type-select" onchange="updateUserType(<?php echo $member['id']; ?>, this.value)">
                                    <?php foreach($staffTypes as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $member['userType'] == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?php echo $member['active'] ? 'Active' : 'Inactive'; ?></td>
                            <td>
                                <button onclick="editStaff(<?php echo $member['id']; ?>)">Edit</button>
                                <button onclick="toggleStatus(<?php echo $member['id']; ?>)">
                                    <?php echo $member['active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                                <button onclick="deleteStaff(<?php echo $member['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>