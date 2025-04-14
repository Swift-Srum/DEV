<?php
error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$aes = new AES256;
$err = $_GET['err'];
$err = $aes->decrypt($err, "secretkey");

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$userId = getUserID();
$email = getUserEmail($userId);
$unverified = checkIsUnverified($username, $sessionID);
$timestamp = time();



?>

<!DOCTYPE html>   
<html>   
<head>  
<meta name="viewport" content="width=device-width, initial-scale=1">  
<title>Reset Password</title>  
<link rel="stylesheet" href="/assets/css/style_form.css">  
</head>    

<body>    
<div id = "main">
<div class="left-column">
  <center><h1>Please enter your email</h1></center>   

<form onsubmit="redirectToReset(event)">  
  <div class="container">   
    <label>Email:</label>   
    <input type="email" id="email" placeholder="Enter Your Email" name="email" required>

    <button type="submit" class="cancelbtn">Send Code</button> 
    <a href="/"><button type="button" class="cancelbtn">Cancel</button></a> 
  </div>   
</form> 

<script>
function redirectToReset(event) {
  event.preventDefault(); // Prevent form from submitting normally
  const email = document.getElementById("email").value.trim();
  if (email) {
    const encodedEmail = encodeURIComponent(email);
    window.location.href = `/forgot-pass/reset_page.php?email=${encodedEmail}`;
  }
}
</script>

<center><h1><?php echo $err ?></h1></center>  
</div>
    <div class="right-column">
      <img src="/assets/back1.png" style="width:256px;height:256px;">
</div>
</div>
 

</body>
</html>


</script>     
</html>  
