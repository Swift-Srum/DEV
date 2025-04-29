<?php
session_start();
error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$aes = new AES256;
$err = $_GET['err'] ?? '';
$err = $aes->decrypt($err, "secretkey");

// Setup Math Captcha
$firstNumber = rand(1, 10);
$secondNumber = rand(1, 10);
$_SESSION['captcha_answer'] = $firstNumber + $secondNumber;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link rel="stylesheet" href="/assets/css/style_form.css">
</head>
<body>

<div id="main">
    <!-- Left Section -->
    <div class="left-column">
        <h1>Join Us!</h1>
        <p>Create your account to view and manage your account and view bowsers.</p>
        <a href="/login">Already have an account? Login</a>
    </div>

    <!-- Right Section -->
    <div class="right-column">
        <h1>Register</h1>
        <form>
            <label for="username">Username</label>
            <input type="text" id="username" placeholder="Enter Username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" placeholder="Enter Email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" placeholder="Enter Password" name="password" required>

            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" placeholder="Re-enter Password" name="confirmPassword" required>

            <!-- Math Captcha Field -->
            <label for="captcha">What is <?php echo $firstNumber . " + " . $secondNumber; ?>?</label>
            <input type="text" id="captcha" name="captcha" placeholder="Enter Answer" required>

            <button type="button" onclick="register();">Register</button>

            <div class="action-links">
                <a href="/login">Login</a>
                <a href="/">Cancel</a>
            </div>
        </form>

        <?php if (!empty($err)) : ?>
            <p style="color:red; text-align:center; margin-top:1rem;"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for sending register data -->
<script>
function register() {
    const usernameInput = document.getElementById("username");
    const usernameValue = usernameInput.value.trim();

    const emailInput = document.getElementById("email");
    const emailValue = emailInput.value.trim();

    const passwordInput = document.getElementById("password");
    const passwordValue = passwordInput.value;

    const confirmPasswordInput = document.getElementById("confirmPassword");
    const confirmPasswordValue = confirmPasswordInput.value;

    const captchaInput = document.getElementById("captcha");
    const captchaValue = captchaInput.value.trim();

    // Basic email validation
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,6}$/;
    if (!emailValue.match(emailPattern)) {
        alert("Please enter a valid email address.");
        return;
    }

    if (passwordValue !== confirmPasswordValue) {
        alert("Passwords do not match!");
        return;
    }

    if (!captchaValue) {
        alert("Please solve the captcha!");
        return;
    }

    const urlEncodedData = new URLSearchParams();
    urlEncodedData.append("userID", usernameValue);
    urlEncodedData.append("email", emailValue);
    urlEncodedData.append("password", passwordValue);
    urlEncodedData.append("confirmPassword", confirmPasswordValue);
    urlEncodedData.append("captcha", captchaValue);

    fetch("./register.php", {
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
            throw new Error(`Failed with status: ${response.status}`);
        }
    })
    .then(data => {
        console.log(data);
    })
    .catch(error => {
        console.error("Error during fetch:", error);
    });
}
</script>

</body>
</html>
