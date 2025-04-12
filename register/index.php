<?php
error_reporting(1);
include('../essential/backbone.php');
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
<title>Register Page</title>  
<link rel="stylesheet" href="/assets/css/style_form.css">  
</head>    

<body>    
<div id = "main">
<div class="left-column">
  <center><h1>Registration Form</h1></center>   

  <form>  
    <div class="container">   
      <label>Username:</label>   
      <input type="text" id="username" placeholder="Enter Username" name="username" required>

      <label>Email:</label>   
      <input type="email" id="email" placeholder="Enter Email" name="email" required>

      <label>Password:</label>   
      <input type="password" id="password" placeholder="Enter Password" name="password" required>

      <label>Confirm Password:</label>   
      <input type="password" id="confirmPassword" placeholder="Re-enter Password" name="confirmPassword" required>

      <button type="button" onclick="register();">Register</button>
      <a href="/login"><button type="button" class="cancelbtn">Login</button></a>
      <a href="/"><button type="button" class="cancelbtn">Cancel</button></a> 
      <center><h1><?php echo $err ?></h1></center>
    </div>   
  </form>     
</div>
    <div class="right-column">
      <img src="/assets/back.jpg" style="width:256px;height:256px;">
</div>
</div>
  

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

    // Basic email validation
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    if (!emailValue.match(emailPattern)) {
      alert("Please enter a valid email address.");
      return;
    }

    if (passwordValue !== confirmPasswordValue) {
      alert("Passwords do not match!");
      return;
    }

    const urlEncodedData = new URLSearchParams();
    urlEncodedData.append("userID", usernameValue);
    urlEncodedData.append("email", emailValue);
    urlEncodedData.append("password", passwordValue);
    urlEncodedData.append("confirmPassword", confirmPasswordValue);

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
