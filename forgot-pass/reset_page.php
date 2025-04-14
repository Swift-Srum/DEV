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
$email = $_GET['email'];
$userId = getUserIDByEmail($email);

if($userId == "res=999" && $email != null){
	header("Location: /forgot-pass/reset_page.php/?err=" . urlencode($aes->encrypt("The email you provided does not exist in our database.", "secretkey")));
}


$timestamp = time();


$codeExists = checkCodeExists($userId, $timestamp);

if(!$codeExists){
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
<html>   
<head>  
<meta name="viewport" content="width=device-width, initial-scale=1">  
<title>Verify Email</title>  
<link rel="stylesheet" href="/assets/css/style_form.css">  
</head>    

<body>    
<div id = "main">
<div class="left-column">
  <center><h1>Please verify your email</h1></center>   

  <form>  
    <div class="container">   
      <label>Verification Code:</label>   
      <input type="text" id="code" placeholder="Enter Your Code" name="code" required>
	  
	  <label>New Password:</label>   
      <input type="password" id="password" placeholder="Enter New Password" name="password" required>
	  
	  <label>Confirm New Password:</label>   
      <input type="password" id="confirm" placeholder="Confirm New Password" name="confirm" required>

      <button type="button" onclick="verify();">Verify</button>
      <a href="/"><button type="button" class="cancelbtn">Cancel</button></a> 
	  
    </div>   
  </form> 
<center><h1><?php echo $err ?></h1></center>  
</div>
    <div class="right-column">
      <img src="/assets/back1.png" style="width:256px;height:256px;">
</div>
</div>
  

  <script>
  function verify() {
    const codeInput = document.getElementById("code");
    const codeValue = codeInput.value.trim();
	
	const passwordInput = document.getElementById("password");
    const passwordValue = passwordInput.value.trim();
	
	const confirmInput = document.getElementById("confirm");
    const confirmValue = confirmInput.value.trim();


    const urlEncodedData = new URLSearchParams();
    urlEncodedData.append("code", codeValue);
	urlEncodedData.append("password", passwordValue);
	urlEncodedData.append("confirm", confirmValue);
	urlEncodedData.append("email", "<?php echo $email ?>");

    fetch("/forgot-pass/reset.php", {
      method: "POST",
      body: urlEncodedData,
      headers: {
        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
    })
    .then(response => {
      if (response.redirected) {
        window.location.href = response.url;
      } else {
        if (response.ok) {
          return response.text();
        } else {
          throw new Error(`Failed with status: ${response.status}`);
        }
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


</script>     
</html>  
