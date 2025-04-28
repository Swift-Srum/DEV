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
    <script src="https://kit.fontawesome.com/76082def73.js" crossorigin="anonymous"></script>
</head>
<body>
<nav>
    <div class="logo">
            <h1>Swift Bowsers</h1>
        </div>
        <ul id="menuList">
            <li><a href="/">Home</a></li>
            <li><a href="/report/">Report Here</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="faq.php ">FAQ</a></li>
            <?php if ($loggedIn): ?>
                <li><a href="/login/logout.php?session=<?= $sessionID ?>">Logout</a></li>
            <?php else: ?>
                <li><a href="/login">Login</a></li>
            <?php endif; ?>
        </ul>
        <div class="menu-icon">
            <i class="fa-solid fa-bars" onclick="toggleMenu()"></i>
        </div>
   </nav>

   <script>
        let menuList = document.getElementById("menuList");
        menuList.style.maxHeight = "0px";
        function toggleMenu() {
            if (menuList.style.maxHeight == "0px") {
                menuList.style.maxHeight = "300px";
            } else {
                menuList.style.maxHeight = "0px";
            }
        }
    </script>
        <section class="about-section" style="padding: 4rem 2rem; background-color: #f9f9f9;">
                <div class="container" style="max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">
                    <h2 style="font-size: 2.5rem; color: #1a75ff; text-align: center;">About Swift</h2>

                    <!-- First Content Block -->
                    <div style="display: flex; flex-wrap: wrap; gap: 2rem; align-items: center;">
                    <img src="https://media.istockphoto.com/id/184600781/photo/a-water-truck-spraying-the-street.jpg?s=2048x2048&w=is&k=20&c=WJHBobodDYGQURcEfTA9rbSZnwX_akUZS9Vn0DNjMuk=" alt="Water Truck" style="flex: 1; max-width: 400px; border-radius: 8px;">
                    <p style="flex: 2; font-size: 1.1rem; color: #333; line-height: 1.8;">
                        At <strong>Swift</strong>, our mission is simple — to make clean water accessible for everyone. 
                        We connect individuals, families, and businesses with reliable <strong>water bowser services</strong> 
                        in their area, fast. Whether you're facing a water outage, planning an event, or managing a construction site, 
                        Swift helps you find a trusted bowser nearby within minutes.
                    </p>
                    </div>

                    <!-- Second Content Block -->
                    <div style="display: flex; flex-wrap: wrap-reverse; gap: 2rem; align-items: center;">
                    <p style="flex: 2; font-size: 1.1rem; color: #333; line-height: 1.8;">
                        Born out of the need for quick and dependable water solutions, Swift was created to serve local communities 
                        with efficiency and care. Our platform provides real-time availability, verified service providers, 
                        and user-friendly booking tools to make your experience seamless and stress-free.
                    </p>
                    <img src="https://c8.alamy.com/comp/P0JC5F/lady-collecting-drinking-water-from-tanker-supply-for-camping-at-festival-P0JC5F.jpg" alt="Local community getting water" style="flex: 1; max-width: 400px; border-radius: 8px;">
                    </div>

                    <!-- Closing Statement -->
                    <p style="font-size: 1.1rem; color: #333; line-height: 1.8; text-align: center;">
                    We're proud to support neighborhoods by ensuring that water is never out of reach. 
                    With Swift, you're not just finding water — you're finding peace of mind.
                    </p>

                    <div style="text-align: center; margin-top: 2rem;">
                    <a href="/contact.html" class="view-btn" style="background-color: #1a75ff; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: bold;">
                        Contact Us
                    </a>
                    </div>
                </div>
            </section>
    </body>
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

</html>