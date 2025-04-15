<?php

session_start();
include('./essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);
$userType = $loggedIn ? getUserType($username) : '';

$isAdmin = $userType === 'admin';
$idx = getUserID();

// AES decryption
$aes = new AES256;
$err = $_GET['err'];
$err = $aes->decrypt($err, "secretkey");

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
    <nav class="navbar">
        <a href="/" class="navbar-brand">Swift Bowsers</a>
        <div class="navbar-nav">
            <?php if ($loggedIn): ?>
                <a href="/login/logout.php?session=<?= $sessionID ?>" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="/login" class="nav-link">Login</a>
            <?php endif; ?>
            <a href="/report/" class="nav-link nav-report">Report Here</a>
            <a href="#" class="nav-link">About Us</a>
            <a href="#" class="nav-link">FAQ</a>
            <?php if ($isAdmin): ?>
                <a href="admin/dashboard.php" class="nav-link">Admin Dashboard</a>
                <a href="/create-bowser" class="nav-link">Add Bowser</a>
            <?php endif; ?>
            <?php if ($loggedIn && $userType === 'dispatcher'): ?>
                <a href="/dispatcher/reported_areas.php" class="nav-link">Dispatcher Dashboard</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <section class="hero">
            <h1>Find the Closest<br>Water Bowser in Seconds</h1>
            
            <div class="search-container">
                <div class="search-input-group">
                    <span class="location-icon">📍</span>
                    <input type="text" id="postcode-input" placeholder="Enter location" 
                        value="<?= isset($_GET['postcode']) ? htmlspecialchars($_GET['postcode']) : '' ?>">
                </div>
                
                <div class="slider-container">
                    <label for="distance">
                        Distance: <span id="distanceValue"><?= isset($_GET['distance']) ? (int)$_GET['distance'] : 15 ?></span> km
                    </label>
                    <input type="range" id="distance" name="distance" min="1" max="30" 
                        value="<?= isset($_GET['distance']) ? (int)$_GET['distance'] : 15 ?>" 
                        oninput="updateDistanceValue(this.value)">
                </div>
                
                <button class="search-btn">Search</button>
            </div>
        </section>

        <section class="results-section">
            <div class="map-container">
                <div id="map"></div>
            </div>
            
            <div class="bowser-list">
                <?php foreach ($items as $item): 
                    $id = $item['id'];
                    $name = htmlspecialchars($item['name']);
                    $postcode = htmlspecialchars($item['postcode']);
                    $itemImageName = getItemImage($id);
                ?>
                <div class="summary-card">
                    <img src="/create-bowser/uploads/<?= $itemImageName ?>" alt="Bowser Image" class="responsive-img">
                    <div class="card-info">
                        <h3><?= $name ?></h3>
                        <p><?= $postcode ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        // Keep all your existing JavaScript for the map
        var map = L.map('map').setView([<?= $firstLat ?>, <?= $firstLong ?>], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var locations = [
            <?php foreach ($items as $item): 
                $id = $item['id'];
                $name = htmlspecialchars($item['name']);
                $postcode = htmlspecialchars($item['postcode']);
                $lat = floatval($item['latitude']);
                $long = floatval($item['longitude']);
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

        // Keep all your existing event listeners
        document.addEventListener("DOMContentLoaded", function () {
            const searchBtn = document.querySelector(".search-btn");
            const distanceSlider = document.getElementById("distance");
            const distanceValue = document.getElementById("distanceValue");

            function updateSearchURL() {
                let postcode = document.getElementById("postcode-input").value.trim();
                let distance = distanceSlider.value;

                let params = new URLSearchParams(window.location.search);
                if (postcode) {
                    params.set("postcode", postcode);
                }
                params.set("distance", distance);
                window.location.href = window.location.pathname + "?" + params.toString();
            }

            searchBtn.addEventListener("click", updateSearchURL);
            distanceSlider.addEventListener("change", updateSearchURL);
        });

        function updateDistanceValue(value) {
            document.getElementById("distanceValue").textContent = value;
        }
    </script>
</body>
</html>
