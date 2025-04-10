<?php

error_reporting(1);
include('./essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$username = $_COOKIE['user_name'];
$sessionID = $_COOKIE['sessionId'];
$idx = getUserID();


// AES decryption
$aes = new AES256;
$err = $_GET['err'];
$err = $aes->decrypt($err, "secretkey");

// Validate session and check if the user is an admin
$loggedIn = confirmSessionKey($username, $sessionID);
$isAdmin = checkIsUserAdmin($username, $sessionID);



$postcode = $_GET['postcode'] ?? null;
if ($postcode) {
    $url = "https://api.postcodes.io/postcodes/$postcode";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['result'])) {
        $eastings = $data['result']['eastings'];
        $northings = $data['result']['northings'];
    } else {
        $eastings = 0;
		$northings = 0;
    }
}

$userType = $isAdmin ? "Admin" : "Standard";
$isAdmin = $isAdmin ? 1 : 0;

$distance = $_GET['distance'];

$distance = $distance * 1000;

if ($distance > 30000)
	$distance = 30000;

$n1 = $northings - $distance;
$n2 = $northings + $distance;
$e1 = $eastings - $distance;
$e2 = $eastings + $distance;


if ($northings != 0 && $eastings != 0)
	try {
   	 
		$items = searchBowsers($e1, $e2, $n1, $n2);
	} catch (Exception $e) {
  	  echo "Error: " . $e->getMessage();
	

	}


$firstLat = $items[0]['latitude'] ?? '51.5007'; // Set a default value if latitude is null or not set
$firstLong = $items[0]['longitude'] ?? '0.1246'; // Set a default value if longitude is null or not set


//var_dump($items);

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bowser Report</title>
    <link rel="stylesheet" href="/assets/css/style_landing.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

</head>
<body>


	<?php 
	if ($loggedIn)
		echo '<a href="/login/logout.php?session=' . $sessionID . '" class="top-right-btn">Logout</a>';
	else
		echo '<a href="/login" class="top-right-btn">Login</a>';
	?>


    <header>
        <h1>Report Bowser</h1>
        <p>Lorem ipsum dolor sit amet et delectus accommodare his consul copiosae legendos at vix ad putent delectus delicata usu.</p>
        <button class="report-btn">Report Here</button>
		<?php 
		if ($isAdmin)
			echo '<a href="/create-bowser" class="report-btn">Add Bowser</a>';
		?>
		
    </header>
    
    <section class="search-section">
    <h2>Find bowsers near by</h2>
    <div class="search-bar">
        <input type="text" id="postcode-input" placeholder="Type Here" value="<?= isset($_GET['postcode']) ? htmlspecialchars($_GET['postcode']) : '' ?>">
        <button class="search-btn">Search</button>
		</div>
		<div class="slider-container">
        <label for="distance" style="color: black;">
            Distance: <span id="distanceValue"><?= isset($_GET['distance']) ? (int)$_GET['distance'] : 15 ?></span> km
        </label>
			<br>
        <input type="range" id="distance" name="distance" min="1" max="30" 
            value="<?= isset($_GET['distance']) ? (int)$_GET['distance'] : 15 ?>" 
            oninput="updateDistanceValue(this.value)">
    </div>
</section>

    
    <section class="main-image">
<div style="display: flex; justify-content: center; align-items: center;">
    <div id="map" style="height: 500px; width: 500px;"></div>
</div>
        <script>
    var map = L.map('map').setView([<?= $firstLat ?>, <?= $firstLong ?>], 10); // London

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var locations = [

        <?php foreach ($items as $item): 
            $id = $item['id'];
            $name = htmlspecialchars($item['name']);
            $postcode = htmlspecialchars($item['postcode']);
            $lat = floatval($item['latitude']); // Ensure it's a valid float
            $long = floatval($item['longitude']); // Ensure it's a valid float
        ?>
			,{ 
        postcode: "<?= $postcode ?>", 
        lat: <?= $lat ?>, 
        lon: <?= $long ?>, 
        label: "<?= addslashes($name . ', ' . $postcode . '<br><br><a href=view?id=' . $id . ' class=\"report-btn\">View</a>') ?>" 
    }
        <?php endforeach; ?>
    ];

    locations.forEach(loc => {
        L.marker([loc.lat, loc.lon]).addTo(map)
            .bindPopup(loc.label)
            .openPopup();
    });
</script>

		<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchBtn = document.querySelector(".search-btn");
        const distanceSlider = document.getElementById("distance");
        const distanceValue = document.getElementById("distanceValue");

        function updateSearchURL() {
            let postcode = document.querySelector(".search-bar input").value.trim();
            let distance = distanceSlider.value;

            let params = new URLSearchParams(window.location.search);
            if (postcode) {
                params.set("postcode", postcode);
            }
            params.set("distance", distance);
            window.location.href = window.location.pathname + "?" + params.toString();
        }

        searchBtn.addEventListener("click", updateSearchURL);

        distanceSlider.addEventListener("input", function () {
            distanceValue.textContent = this.value;
        });

        distanceSlider.addEventListener("change", updateSearchURL);
    });
</script>

		<script>
    function updateDistanceValue(value) {
        document.getElementById("distanceValue").textContent = value;
    }
</script>

    </section>
    
    <section class="bowser-summary">
       
		
		<?php foreach ($items as $item): 
    $id = $item['id'];
    $name = htmlspecialchars($item['name']); // Sanitize output
    $ownerId = $item['ownerId'];
    $model = htmlspecialchars($item['model']);
    $manufacturer_details = htmlspecialchars($item['manufacturer_details']);
    $itemImageName = getItemImage($id);
    $ownerName = getUsernameById($ownerId);
    $available = $item['active'] ?? 0;
    $postcode = htmlspecialchars($item['postcode']);
?>
    <div class="summary-card">
        <img src="/create-bowser/uploads/<?= $itemImageName ?>" alt="Bowser Image" class="responsive-img">
        <p><?= $name ?></p>
		<p><?= $postcode ?></p>
    </div>
<?php endforeach; ?>


		 
    </section>
</body>
</html>