<?php
error_reporting(1);
include('../essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
$aes = new AES256;
$err = $_GET['err'];
$err = $aes->decrypt($err, "secretkey");

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';

// Checking if user is logged in
$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);



// If not admin, exit script
if (!$isAdmin) {
    die("Error: User not admin");
}
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
                <a href="../admin/dashboard.php" class="nav-link">Staff Control</a>
                <a href="../admin/manage-bowsers.php" class="nav-link">Bowser Control</a>
                <a href="../login/logout.php?session=<?php echo $_COOKIE['sessionId']; ?>" class="nav-link">Logout</a>
            </nav>
        </header>
<body>    
  <center><h1>Add Bowser</h1></center>   
  <form>  
    <div class="container">   
  <label>Name (e.g. Cool Bowser):</label>   
  <input type="text" id="name" placeholder="Enter Item Name" name="name" required> 

  <label>Details:</label>   
		<br>
  <textarea id="details" name="details" placeholder="Enter Details" required rows="10" cols="45"></textarea>
		<br>

  <label>Model:</label>   
  <input type="text" id="model" placeholder="Enter Model" name="model">

  <label>Serial:</label>   
  <input type="text" id="serial" placeholder="Enter Serial Number" name="serial">

  <label>Notes:</label>   <br>
  <textarea id="notes" name="notes" placeholder="Enter Notes"></textarea><br>
  

  <label>Capacity:</label>   
  <input type="text" id="capacity" placeholder="Enter Capacity" name="capacity">

  <label>Length:</label>   
  <input type="text" id="length" placeholder="Enter Length" name="length">

  <label>Width:</label>   
  <input type="text" id="width" placeholder="Enter Width" name="width">

  <label>Height:</label>   
  <input type="text" id="height" placeholder="Enter Height" name="height">

  <label>Weight (Empty):</label>   
  <input type="text" id="weight_empty" placeholder="Enter Weight (Empty)" name="weight_empty">

  <label>Weight (Full):</label>   
  <input type="text" id="weight_full" placeholder="Enter Weight (Full)" name="weight_full">

  <label>Supplier:</label>   
  <input type="text" id="supplier" placeholder="Enter Supplier" name="supplier">
  
  <label>Postcode:</label>   
  <input type="text" id="postcode" placeholder="Enter Postcode" name="postcode">
  <br>
  <label>Date Received:</label>   
  <input type="date" id="date_received" name="date_received">
  <br><br>
  <label>Date Returned:</label>   
  <input type="date" id="date_returned" name="date_returned">

  <br><br>

  <label>Attach An Image:</label>   
  <input type="file" name="fileToUpload" id="fileToUpload">
  <progress id="uploadProgress" value="0" max="100"></progress>
  <div id="uploadStatus"></div>

  <button type="button" onclick="submitForm();">Create Bowser</button>
  <a href="../"><button type="button" class="cancelbtn">Cancel</button></a>
</div>
 
  </form>     
  <center><h1><?php echo $err ?></h1></center>

  <script>
    function handleFileSelect() {
        const fileInput = document.getElementById('fileToUpload');
        const file = fileInput.files[0];

        if (file) {
            uploadFile(file);
        }
    }

    function uploadFile(file) {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();
    formData.append('fileToUpload', file);

    xhr.upload.addEventListener('progress', function (event) {
        if (event.lengthComputable) {
            const percentComplete = (event.loaded / event.total) * 100;
            document.getElementById('uploadProgress').value = percentComplete;
        }
    });

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                document.getElementById('uploadStatus').innerHTML = xhr.responseText;
            } else {
                document.getElementById('uploadStatus').innerHTML = 'Error uploading file.';
            }
        }
    };

    xhr.open('POST', 'upload.php', true);
    xhr.send(formData);
}

function submitForm() {
    const nameInput = document.getElementById("name");
    const nameValue = nameInput.value;

    const detailsInput = document.getElementById("details");
    const detailsValue = detailsInput.value;

    const modelInput = document.getElementById("model");
    const modelValue = modelInput.value;

    const serialInput = document.getElementById("serial");
    const serialValue = serialInput.value;

    const notesInput = document.getElementById("notes");
    const notesValue = notesInput.value;

    const capacityInput = document.getElementById("capacity");
    const capacityValue = capacityInput.value;

    const lengthInput = document.getElementById("length");
    const lengthValue = lengthInput.value;

    const widthInput = document.getElementById("width");
    const widthValue = widthInput.value;

    const heightInput = document.getElementById("height");
    const heightValue = heightInput.value;

    const weightEmptyInput = document.getElementById("weight_empty");
    const weightEmptyValue = weightEmptyInput.value;

    const weightFullInput = document.getElementById("weight_full");
    const weightFullValue = weightFullInput.value;

    const supplierInput = document.getElementById("supplier");
    const supplierValue = supplierInput.value;
    
    const postcodeInput = document.getElementById("postcode");
    const postcodeValue = postcodeInput.value;

    const dateReceivedInput = document.getElementById("date_received");
    const dateReceivedValue = dateReceivedInput.value;

    const dateReturnedInput = document.getElementById("date_returned");
    const dateReturnedValue = dateReturnedInput.value;

    // Create a URLSearchParams object and append form data as parameters
    const params = new URLSearchParams();
    params.append("name", nameValue);
    params.append("details", detailsValue);
    params.append("model", modelValue);
    params.append("serial", serialValue);
    params.append("notes", notesValue);
    params.append("capacity", capacityValue);
    params.append("length", lengthValue);
    params.append("width", widthValue);
    params.append("height", heightValue);
    params.append("weight_empty", weightEmptyValue);
    params.append("weight_full", weightFullValue);
    params.append("supplier", supplierValue);
    params.append("postcode", postcodeValue);
    params.append("date_received", dateReceivedValue);
    params.append("date_returned", dateReturnedValue);

    // Send the form data using fetch with URL-encoded parameters
    fetch("./submit.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded", // This tells the server it's URL-encoded data
        },
        body: params.toString(), // Convert URLSearchParams to query string
    })
    .then(response => {
      // Check if the response is a redirect
      if (response.redirected) {
        // If redirected, get the new location
        const redirectLocation = response.url;

        // Redirect the user to the specified location
        window.location.href = redirectLocation;
      } else {
        // Handle other aspects of the response if needed
        if (response.ok) {
          return response.text(); // or response.json() if expecting JSON
        } else {
          throw new Error(`Failed with status: ${response.status}`);
        }
      }
    })
    .then(data => {
        console.log(data); // Log the response from the server

        // File upload part
        const fileInput = document.getElementById("fileToUpload");
        const file = fileInput.files[0];
        if (file) {
            // Trigger file upload if a file is selected
            uploadFile(file);
        }
    })
    .catch(error => {
        console.error("Error during fetch:", error);
        alert("An error occurred. Please try again.");
    });
}









	
</script>



</body>


</script>     
</html>