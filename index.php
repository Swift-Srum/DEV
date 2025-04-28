<?php
session_start();

// Include necessary files (e.g., backbone with essential functions)
include('./essential/backbone.php');

// Set headers to enhance security (XSS Protection, Content-Type options)
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Check user login status using session and cookies
$username = $_COOKIE['user_name'] ?? '';
$sessionID = $_COOKIE['sessionId'] ?? '';
$loggedIn = confirmSessionKey($username, $sessionID);  // Validate session
$userType = $loggedIn ? getUserType($username) : '';    // Get user type if logged in
$isAdmin = $userType === 'admin';                       // Check if user is admin
$idx = getUserID();                                     // Get user ID

// Decrypt error message if any
$aes = new AES256;
$err = $_GET['err'] ?? '';
$err = $aes->decrypt($err, "secretkey");

// Retrieve postcode from query parameters and fetch location details
$postcode = $_GET['postcode'] ?? null;
if ($postcode) {
    $url      = "https://api.postcodes.io/postcodes/$postcode";  // API to fetch postcode data
    $response = file_get_contents($url);                          // Send request to API
    $data     = json_decode($response, true);                     // Decode JSON response
    if (isset($data['result'])) {
        $eastings  = $data['result']['eastings'];   // Get eastings (x-coordinate)
        $northings = $data['result']['northings'];  // Get northings (y-coordinate)
    } else {
        $eastings  = 0;  // Default to 0 if no valid data found
        $northings = 0;
    }
}

// Set search radius (distance) and ensure it doesn't exceed 30 km
$distance = ($_GET['distance'] ?? 15) * 1000;  // Convert km to meters
if ($distance > 30000) $distance = 30000;     // Cap distance at 30 km

// Calculate boundary coordinates for searching (within the distance range)
$n1 = $northings - $distance;
$n2 = $northings + $distance;
$e1 = $eastings  - $distance;
$e2 = $eastings  + $distance;

// If coordinates are available, perform search
if ($northings != 0 && $eastings != 0) {
    try {
        $items = searchBowsers($e1, $e2, $n1, $n2);  // Search for nearby bowser locations
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();           // Handle any errors during search
    }
}

// Set default map location based on the first found item, or fallback
$firstLat  = $items[0]['latitude']  ?? '51.5007';
$firstLong = $items[0]['longitude'] ?? '0.1246';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadata and external resources (CSS, JS) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bowser Report</title>
    <link rel="stylesheet" href="/assets/css/style_landing.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

</head>
<body>

<!-- Navigation menu with login/logout based on session -->
<nav>
    <div class="logo"><h1>Swift Bowsers</h1></div>
    <ul id="menuList">
        <li><a href="/">Home</a></li>
        <li><a href="/report/">Report Here</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="faq.php">FAQ</a></li>
        <?php if ($loggedIn): ?>
            <li><a href="/login/logout.php?session=<?= $sessionID ?>">Logout</a></li>
        <?php else: ?>
            <li><a href="/login">Login</a></li>
        <?php endif; ?>
    </ul>
    <div class="menu-icon" onclick="toggleMenu()">
        <img src="/assets/icons/menu-icon.svg" alt="Menu">
    </div>
</nav>

<script>
  // Toggle navigation menu for mobile view
  window.toggleMenu = function () {
    let menuList = document.getElementById("menuList");
    if (menuList.style.maxHeight === "0px" || menuList.style.maxHeight === "") {
      menuList.style.maxHeight = menuList.scrollHeight + "px";
    } else {
      menuList.style.maxHeight = "0px";
    }
  }
</script>

<main>
  <!-- Hero section with a search interface for finding bowser locations -->
  <section class="hero">
    <h1>Search For Nearby<br>Water Bowser in Seconds</h1>
    <div class="search-container">
      <div class="search-input-group">
        <span class="location-icon">📍</span>
        <input
          type="text"
          id="postcode-input"
          placeholder="Enter location"
          value="<?= isset($_GET['postcode']) ? htmlspecialchars($_GET['postcode']) : '' ?>"
        >
        <button onclick="getPostcode()">&target;</button>
      </div>
      <div class="slider-container">
        <label for="distance">
          Distance:
          <span id="distanceValue"><?= isset($_GET['distance']) ? (int)$_GET['distance'] : 15 ?></span> km
        </label>
        <input
          type="range"
          id="distance"
          name="distance"
          min="1"
          max="30"
          value="<?= isset($_GET['distance']) ? (int)$_GET['distance'] : 15 ?>"
          oninput="updateDistanceValue(this.value)"
        >
      </div>
      <button class="search-btn">Search</button>
    </div>
  </section>

  <!-- Results section displaying map and bowser list -->
  <section class="results-section">
    <div class="map-container">
      <div id="map"></div>  <!-- Leaflet map showing bowser locations -->
    </div>
    <div class="bowser-list">
      <?php foreach ($items as $item):
        $id      = $item['id'];
        $name    = htmlspecialchars($item['name']);
        $pc      = htmlspecialchars($item['postcode']);
        $status  = htmlspecialchars($item['status_maintenance']);
        $imgName = getItemImage($id);
        // Determine status class for styling
        if (stripos($status,'active') !== false)       $cls = 'active';
        elseif (stripos($status,'maintenance') !== false) $cls = 'maintenance';
        else                                            $cls = 'outofservice';
      ?>
      <div class="summary-card">
        <div class="summary-card-img">
          <img src="/create-bowser/uploads/<?= $imgName ?>" alt="Bowser Image">
        </div>
        <div class="summary-card-content">
          <h3 class="summary-title"><?= $name ?></h3>
          <div class="summary-meta">📍 <?= $pc ?></div>
          <div class="summary-meta">
            🔧 <span class="status-label <?= $cls ?>"><?= $status ?></span>
          </div>
          <a href="view?id=<?= $id ?>" class="summary-view-btn">View</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<footer>
  <div class="footer-content">
    <p>&copy; 2025 Swift Bowsers. All rights reserved.</p>
    <div class="social-icons">
      <a href="#"><img src="/assets/icons/facebook.png" alt="Facebook"></a>
      <a href="#"><img src="/assets/icons/twitter.png" alt="Twitter"></a>
      <a href="#"><img src="/assets/icons/instagram.png" alt="Instagram"></a>
    </div>
  </div>
</footer>

<script>
  // Initialize map and markers
  var map = L.map('map').setView([<?= $firstLat ?>, <?= $firstLong ?>], 10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Build and add markers
  var locations = [
    <?php foreach ($items as $item):
      $lat   = floatval($item['latitude']);
      $lon   = floatval($item['longitude']);
      $label = addslashes("
        <div class='popup-box $cls'>
          <div class='popup-row'><span class='popup-label'>👤 Name:</span> $name</div>
          <div class='popup-row'><span class='popup-label'>📍 Postcode:</span> $pc</div>
          <div class='popup-row'><span class='popup-label'>📦 Status:</span> <span class='status-label'>$status</span></div>
          <div class='popup-actions'>
            <a href='view?id=$id' class='popup-btn'>View</a>
            <a href='https://www.google.com/maps/dir/?api=1&destination=$lat,$lon' target='_blank' class='popup-btn directions-btn'>Get Directions</a>
          </div>
        </div>
      ");
    ?>
    ,{ lat: <?= $lat ?>, lon: <?= $lon ?>, label: `<?= $label ?>` }
    <?php endforeach; ?>
  ];
  locations.forEach(loc => {
    L.marker([loc.lat, loc.lon]).addTo(map).bindPopup(loc.label);
  });

  // Search form behavior
  document.addEventListener("DOMContentLoaded", function () {
    const searchBtn      = document.querySelector(".search-btn");
    const distanceSlider = document.getElementById("distance");
    function updateSearchURL() {
      let pc   = document.getElementById("postcode-input").value.trim();
      let dist = distanceSlider.value;
      let params = new URLSearchParams(window.location.search);
      if (pc) params.set("postcode", pc);
      params.set("distance", dist);
      window.location.href = window.location.pathname + "?" + params.toString();
    }
    searchBtn.addEventListener("click", updateSearchURL);
    distanceSlider.addEventListener("change", updateSearchURL);
  });

  // Update distance display
  function updateDistanceValue(val) {
    document.getElementById("distanceValue").textContent = val;
  }

  // Geolocation → postcode
  function getPostcode() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition);
    } else {
      alert("Geolocation is not supported by this browser.");
    }
  }
  function showPosition(position) {
    const lat  = position.coords.latitude;
    const lon  = position.coords.longitude;
    const apiKey = "188416c11e824232a324ca08015e9f9f";
    const url    = `https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lon}&key=${apiKey}`;
    fetch(url)
      .then(res => res.json())
      .then(data => {
        document.getElementById("postcode-input").value = data.results[0].components.postcode;
      })
      .catch(console.error);
  }
</script>

</body>
</html>
