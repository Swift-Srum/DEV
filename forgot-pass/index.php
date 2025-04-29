<?php
session_start();
error_reporting(1);
include('../essential/backbone.php');
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
<title>Forgot Password</title>
<link rel="stylesheet" href="/assets/css/style_form.css">
</head>
<body>

<div id="main">
    <!-- Left Section -->
    <div class="left-column">
        <h1>Forgot Your Password?</h1>
        <p>Enter your registered email address and we'll send you a reset link to get back into your account.</p>
        <a href="/login">Remembered? Login</a>
    </div>

    <!-- Right Section -->
    <div class="right-column">
        <h1>Reset Password</h1>
        <form onsubmit="redirectToReset(event)">
            <label for="email">Email Address</label>
            <input type="email" id="email" placeholder="Enter Your Email" name="email" required>

            <button type="submit">Send Code</button>

            <div class="action-links">
                <a href="/">Cancel</a>
            </div>
        </form>

        <?php if (!empty($err)) : ?>
            <p style="color:red; text-align:center; margin-top:1rem;"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
function redirectToReset(event) {
    event.preventDefault(); // Prevent form from submitting normally
    const email = document.getElementById("email").value.trim();
    if (email) {
        const encodedEmail = encodeURIComponent(email);
        window.location.href = `/forgot-pass/reset_page.php?email=${encodedEmail}&sent=1`;
    }
}
</script>

</body>
</html>
