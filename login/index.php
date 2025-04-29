<?php
error_reporting(1);
include('../essential/backbone.php');
session_start();

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Check if already logged in
$loggedIn = confirmSessionKey($username, $sessionID);

if ($loggedIn) {
    $userType = getUserType($username);

    if ($userType === 'dispatcher') {
        header("Location: ../dispatcher/dashboard.php");
        exit();
    } elseif ($userType === 'admin') {
        header("Location: ../admin/dashboard.php");
        exit();
    } elseif ($userType === 'maintainer') {
        header("Location: ../maintainer/dashboard.php");
        exit();
    } elseif ($userType === 'driver') {
        header("Location: ../driver/dashboard.php");
        exit();
    } else {
        header("Location: ../");
        exit();
    }
}

header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$aes = new AES256;
$err = $_GET['err'] ?? '';
$err = $aes->decrypt($err, "secretkey");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="/assets/css/style_form.css">
</head>
<body>

<div id="main">
    <!-- Left Section -->
    <div class="left-column">
        <h1>Welcome Back!</h1>
        <p>Please login to to view and manage your account.</p>
        <a href="/register">Don't have an account? Register</a>
    </div>

    <!-- Right Section -->
    <div class="right-column">
        <h1>Login</h1>
        <form>
            <label for="username">Username</label>
            <input type="text" id="username" placeholder="Enter Username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" placeholder="Enter Password" name="password" required>

            <button type="button" onclick="login();">Login</button>

            <div class="action-links">
                <a href="/forgot-pass">Forgot Password?</a>
                <a href="/">Cancel</a>
            </div>
        </form>

        <?php if (!empty($err)) : ?>
            <p style="color:red; text-align:center; margin-top:1rem;"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for login functionality -->
<script>
function login() {
  const usernameInput = document.getElementById("username");
  const usernameValue = usernameInput.value.trim();

  const passwordInput = document.getElementById("password");
  const passwordValue = passwordInput.value.trim();

  if (!usernameValue || !passwordValue) {
      alert("Please fill in both fields.");
      return;
  }

  const urlEncodedData = new URLSearchParams();
  urlEncodedData.append("userID", usernameValue);
  urlEncodedData.append("password", passwordValue);

  fetch("./login.php", {
    method: "POST",
    body: urlEncodedData,
    headers: {
      "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
    },
  })
  .then(response => {
      if (response.redirected) {
          window.location.href = response.url;
      } else if (response.ok) {
          return response.text();
      } else {
          throw new Error(`Login failed: ${response.status}`);
      }
  })
  .catch(error => {
      console.error("Error during login:", error);
      alert("Login failed. Please try again.");
  });
}
</script>

</body>
</html>
