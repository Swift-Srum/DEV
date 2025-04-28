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
        <div class="menu-icon" onclick="toggleMenu()">
            <img src="/assets/icons/menu-icon.svg" alt="Menu">
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
    
             <!-- FAQ Section content -->
            <section class="faq-modern">
            <h2 class="faq-title">Frequently Asked Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                <button class="faq-toggle">
                    <span>What is Swift Bowsers?</span>
                    <i class="icon">+</i>
                </button>
                <div class="faq-content">
                    <p>Swift Bowsers helps you find and book water delivery trucks near your location quickly and reliably, especially during water shortages.</p>
                </div>
                </div>

                <div class="faq-item">
                <button class="faq-toggle">
                    <span>How do I book a water bowser?</span>
                    <i class="icon">+</i>
                </button>
                <div class="faq-content">
                    <p>Simply enter your location or postcode, view available bowsers, and click "Book Now" for a fast and secure booking.</p>
                </div>
                </div>

                <div class="faq-item">
                <button class="faq-toggle">
                    <span>Is there a delivery fee?</span>
                    <i class="icon">+</i>
                </button>
                <div class="faq-content">
                    <p>Yes, but the fee depends on your area and the provider. Fees are clearly displayed before confirmation.</p>
                </div>
                </div>

                <div class="faq-item">
                <button class="faq-toggle">
                    <span>Can I cancel my booking?</span>
                    <i class="icon">+</i>
                </button>
                <div class="faq-content">
                    <p>Yes. You can cancel up to an hour before delivery from your dashboard. No penalties apply within this window.</p>
                </div>
                </div>
            </div>
            </section>

            <!-- Javascript code to handle accordion functionality -->
            <script>
                const faqToggles = document.querySelectorAll('.faq-toggle');

                faqToggles.forEach(toggle => {
                    toggle.addEventListener('click', () => {
                    const faqItem = toggle.parentElement;
                    faqItem.classList.toggle('active');

                    // Collapse all other items
                    document.querySelectorAll('.faq-item').forEach(item => {
                        if (item !== faqItem) item.classList.remove('active');
                    });
                    });
                });
            </script>
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