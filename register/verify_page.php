<?php
error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$aes = new AES256;
$err = $_GET['err'] ?? '';
$err = $aes->decrypt($err, "secretkey");

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$userId = getUserID();
$email = getUserEmail($userId);
$unverified = checkIsUnverified($username, $sessionID);
$timestamp = time();

if (!$unverified) {
    header("Location: /login");
    exit();
}

$codeExists = checkCodeExists($userId, $timestamp);

if (!$codeExists) {
    $code = rand(100000, 999999);
    createCode($userId, $code, $timestamp + 120);

    $result = send_mailjet_email(
        "386d53b649d4366e63d4bab2f85b7335",
        "dbeb11d70f72929864677584ddbf8f3c",
        "swiftbowsers@yopmail.com",
        "Swift Bowsers",
        $email,
        "Verification Code",
        "Swift Bowsers Verification Code",
        "This is a plain text message.",
        "<h1>Your code is: " . $code . "</h1><p>Thank you for using Swift Bowsers. Before you can start using our service, please enter the verification code above into the verification box. The code will be valid for 2 minutes.</p>"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Email</title>
<link rel="stylesheet" href="/assets/css/style_form.css">
</head>
<body>

<div id="main">
    <!-- Left Section -->
    <div class="left-column">
        <h1>Verify Your Email</h1>
        <p>We've sent a verification code to your email address.<br>
           Please enter it below to verify your account.</p>
        <a href="/">Go back to Home</a>
    </div>

    <!-- Right Section -->
    <div class="right-column">
        <h1>Email Verification</h1>
        <form>
            <label for="code">Verification Code</label>
            <input type="text" id="code" placeholder="Enter Your Code" name="code" required>

            <button type="button" onclick="verify();">Verify</button>

            <div id="timer" style="margin-top: 1rem; font-size: 0.95rem; color: #333; text-align: center;"></div>

            <button id="resend-btn" type="button" onclick="resendCode();" style="display: none; margin-top: 1rem; width: 100%; padding: 0.75rem; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer;">
                Resend Verification Code
            </button>

            <div class="action-links">
                <a href="/">Cancel</a>
            </div>
        </form>

        <?php if (!empty($err)) : ?>
            <p style="color:red; text-align:center; margin-top:1rem;"><?php echo htmlspecialchars($err); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for countdown, verify and resend -->
<script>
let countdownInterval;
let countdownTime = 120; // 2 minutes (120 seconds)

function startCountdown() {
    const timerElement = document.getElementById('timer');
    countdownInterval = setInterval(() => {
        if (countdownTime <= 0) {
            clearInterval(countdownInterval);
            timerElement.textContent = "Code expired.";
            const resendBtn = document.getElementById('resend-btn');
            resendBtn.style.display = "inline-block";
            resendBtn.disabled = false;
        } else {
            const minutes = Math.floor(countdownTime / 60);
            const seconds = countdownTime % 60;
            timerElement.textContent = `Code expires in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (countdownTime <= 30) {
                timerElement.style.color = "red";
            }

            countdownTime--;
        }
    }, 1000);
}

function verify() {
    const codeInput = document.getElementById("code");
    const codeValue = codeInput.value.trim();

    if (!codeValue) {
        alert("Please enter the verification code.");
        return;
    }

    const urlEncodedData = new URLSearchParams();
    urlEncodedData.append("code", codeValue);

    fetch("/register/verify.php", {
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
        console.error("Error during verification:", error);
    });
}

function resendCode() {
    const resendBtn = document.getElementById('resend-btn');
    resendBtn.disabled = true; // Disable immediately
    const originalText = resendBtn.textContent;
    resendBtn.textContent = "Sending...";

    const urlEncodedData = new URLSearchParams();
    urlEncodedData.append("resend", "true");

    fetch("/register/verify.php?resend=true", {
      method: "POST",
      headers: {
        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
    })
    .then(response => {
        if (response.ok) {
            alert("A new verification code has been sent to your email.");
            countdownTime = 120;
            document.getElementById('timer').style.color = "#333";
            resendBtn.style.display = "none"; // Hide after resend
            startCountdown();
        } else {
            throw new Error(`Failed to resend code: ${response.status}`);
        }
    })
    .catch(error => {
        console.error("Error during resend:", error);
        alert("Failed to resend code. Please try again.");
    });

    // Re-enable button and reset text after 5 seconds
    setTimeout(() => {
        resendBtn.disabled = false;
        resendBtn.textContent = originalText;
    }, 5000);
}

// Start countdown when page loads
document.addEventListener('DOMContentLoaded', startCountdown);
</script>

</body>
</html>
