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
$err = $_GET['err'];
$err = $aes->decrypt($err, "secretkey");
?>

<!DOCTYPE html>   
<html>   
<head>  
<meta name="viewport" content="width=device-width, initial-scale=1">  
<title> Login Page </title>  
<link rel="stylesheet" href="/assets/css/style_form.css">  
</head>    
<body>    
  <div id = "main">
    <div class="left-column">
    <center> <h1> Login Form </h1> </center>   
    <form>  
        <div class="container">   
            <label>Username : </label>   
            <input type="text" id="username" placeholder="Enter Username" name="username" required>  
            <label>Password : </label>   
            <input type="password" id="password" placeholder="Enter Password" name="password" required>  
            <button type="button" onclick="login();">Login</button>   
			<a href="/register"><button type="button" class="cancelbtn"> Register</button></a> 
			<a href="/forgot-pass"><button type="button" class="cancelbtn"> Forgot Password</button></a> 
			<a href="/"><button type="button" class="cancelbtn"> Cancel</button></a> 
        </div>  	
    </form>   
	<center> <h1> <?php echo $err ?> </h1> </center> 
	</div>
    <div class="right-column">
      <img src="/assets/back1.png" style="width:256px;height:256px;">
</div>
</div>


  <script>
  function login() {
  const usernameInput = document.getElementById("username");
  const usernameValue = usernameInput.value;

  const passwordInput = document.getElementById("password");
  const passwordValue = passwordInput.value;

  // Create URL-encoded string
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
      // Optionally display error to user
      alert("Login failed. Please try again.");
  });
  }
</script>
	
</body>     
</html>
