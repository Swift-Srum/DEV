<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
	
	function createBowser($idx, $name, $manufacturer_details, $model, $serial_number, $specific_notes, $capacity_litres, $length_mm, $width_mm, $height_mm, $weight_empty_kg, $weight_full_kg, $supplier_company, $date_received, $date_returned, $postcode, $northings, $eastings, $longitude, $latitude) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check for connection errors
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }

    // Prepare the SQL statement
    $q = $db->prepare("
        INSERT INTO `bowsers` 
        (`ownerId`, `name`, `manufacturer_details`, `model`, `serial_number`, `specific_notes`, `capacity_litres`, `length_mm`, `width_mm`, `height_mm`, `weight_empty_kg`, `weight_full_kg`, `supplier_company`, `date_received`, `date_returned`, `eastings`, `northings`, `longitude`, `latitude`, `postcode`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$q) {
        die("Prepare statement failed: " . $db->error);
    }

    // Bind parameters with correct types
    $q->bind_param(
        'issssssssssssssiisss', 
        $idx, $name, $manufacturer_details, $model, $serial_number, $specific_notes, 
        $capacity_litres, $length_mm, $width_mm, $height_mm, 
        $weight_empty_kg, $weight_full_kg, $supplier_company, 
        $date_received, $date_returned, $eastings, $northings, $longitude, $latitude, $postcode
    );

    // Execute query and check for errors
    if (!$q->execute()) {
        die("Error executing query: " . $q->error);
    }

    // Store result
    $success = $q->affected_rows > 0;

    // Cleanup
    $q->close();
    $db->close();

    return $success;
}

	function send_mailjet_email($apiKey, $apiSecret, $fromEmail, $fromName, $toEmail, $toName, $subject, $text, $html = "") { //Function for sending emails
		$url = 'https://api.mailjet.com/v3.1/send';
	
		$data = [
			'Messages' => [[
				'From' => [
					'Email' => $fromEmail,
					'Name'  => $fromName
				],
				'To' => [[
					'Email' => $toEmail,
					'Name'  => $toName
				]],
				'Subject' => $subject,
				'TextPart' => $text,
				'HTMLPart' => $html ?: nl2br($text)
			]]
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:$apiSecret");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json'
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode === 200) {
			return ["success" => true, "message" => "Email sent via Mailjet."];
		} else {
			return ["success" => false, "error" => "Failed with status code $httpCode", "response" => $response];
		}
	}



	
	function logImageUpload($fileName, $itemId) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("INSERT INTO `uploads` (`fileName`, `bowserId`) VALUES (?, ?)");
        $q->bind_param('si', $fileName, $itemId);
        $q->execute();
	}
	
	function createCode($userId, $code, $timestamp) { //Used for email verification and password resets
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("INSERT INTO `verification_codes` (`userId`, `code`, `expires`) VALUES (?, ?, ?)");
        $q->bind_param('isi', $userId, $code, $timestamp);
        $q->execute();
	}
	
	function checkCodeValid($userId, $code, $timestamp) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		if ($db->connect_error) {
			return false; // Optionally handle DB errors
		}

		$q = $db->prepare("SELECT * FROM `verification_codes` WHERE `userId` = ? AND `code` = ? AND `expires` > ?");
		$q->bind_param('iss', $userId, $code, $timestamp);
		$q->execute();
		$result = $q->get_result();

		$row = $result->fetch_array(MYSQLI_ASSOC);

		if (!$row) {
			return false;
		}

		return true;
	}
	
	function setVerified()
	{
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId']))
		{
			$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $q = $db->prepare("UPDATE users SET `verified` = 1 WHERE `sessionKey` = ? AND username = ?");
            $q->bind_param('ss', $_COOKIE['sessionId'], $_COOKIE['user_name']);
            $q->execute();
			
	}
		
		return "res=123";
	}
	
	function resetPassword($userId, $password)
	{
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if ($db->connect_error) {
			return false;
		}

		$q = $db->prepare("UPDATE users SET `password` = ? WHERE `id` = ?");
		if (!$q) {
			$db->close();
			return false;
		}

		$q->bind_param('si', $password, $userId);
		$success = $q->execute();

		$q->close();
		$db->close();

		return $success;
	}

	
	function checkCodeExists($userId, $timestamp) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		if ($db->connect_error) {
			return false; // Optionally handle DB errors
		}

		$q = $db->prepare("SELECT * FROM `verification_codes` WHERE `userId` = ? AND `expires` > ?");
		$q->bind_param('is', $userId, $timestamp);
		$q->execute();
		$result = $q->get_result();

		$row = $result->fetch_array(MYSQLI_ASSOC);

		if (!$row) {
			return false;
		}

		return true;
	}

	
	function deleteItem($itemId) { //NOT CURRENTLY IN USE
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("DELETE FROM `products` WHERE `bowsers`.`id` = ?");
        $q->bind_param('i', $itemId);
        $q->execute();
	}
	
	function checkIsUserAdmin($adminName, $key) {
		$key = str_replace(" ","",$key);
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT admin FROM `users` WHERE `username` = ? AND `sessionKey` = ? AND `verified` = 1");
        $q->bind_param('ss', $adminName, $key);
        $q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array()) {
			if($res['admin'] == 1 && $key != "")
			return true;
		}

		return false;
	}
    /* Admin panel functions */

	function generateSessionKey($len = 25)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_*";
		$ret = "";
		
		for($i = 0; $i < $len; $i++)
		{
			$ret .= $chars[rand(0, strlen($chars)-1)];
		}
		
		return $ret;
	}
	
	

	function generateLogKey($len = 5)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		$ret = "";
		
		for($i = 0; $i < $len; $i++)
		{
			$ret .= $chars[rand(0, strlen($chars)-1)];
		}
		
		return $ret;
	}
	
	
	function confirmSessionKey($username, $key)
	{
		$key = str_replace(" ","",$key);
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT active FROM users WHERE username = ? AND sessionKey = ? AND verified = 1 LIMIT 1;");
		$q->bind_param('ss', $username, $key);
		$q->execute();
		
		$res = $q->get_result();
		
		if($res = $res->fetch_array())
		{
			if((int)$res['active'] == 1 && $key != "") {
				return true;
			} // Checks if banned
		}

		return false;
	}
	
	function checkIsUnverified($username, $key)
	{
		$key = str_replace(" ","",$key);
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT active FROM users WHERE username = ? AND sessionKey = ? LIMIT 1;");
		$q->bind_param('ss', $username, $key);
		$q->execute();
		
		$res = $q->get_result();
		
		if($res = $res->fetch_array())
		{
			if((int)$res['verified'] == 0 && $key != "") {
				return true;
			} // Checks if unverified
		}

		return false;
	}

	
	function getUserID()
	{
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId']))
		{
			
			$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			$q = $db->prepare("SELECT * FROM users WHERE username = ? AND sessionKey = ? LIMIT 1;");
			$q->bind_param('ss', $_COOKIE['user_name'], $_COOKIE['sessionId']);
			$q->execute();
				
			$res = $q->get_result();
				
			if($res = $res->fetch_array())
			{
				$st = rand();
				return $res['id'];
			}
			
		}
		
		return "res=999";
	}
	
	function getUserIDByEmail($email)
	{
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if ($db->connect_error) {
			return false;
		}

		$q = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
		if (!$q) {
			return false;
		}

		$q->bind_param('s', $email);
		$q->execute();
		$res = $q->get_result();

		if ($row = $res->fetch_array()) {
			$q->close();
			$db->close();
			return $row['id'];
		}

		$q->close();
		$db->close();
		return false;
	}


	function updateItemStatusUnavaliable($itemId)  //NOT CURRENTLY IN USE
	{
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);
			
			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE bowsers SET `avaliable` = '0' WHERE `id` = ?");
                $q->bind_param('s', $itemId);
                $q->execute();

				
				$res = $q->get_result();
			}
		}
		
		return "res=999";
	}
	
	function logoutClient()
	{
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);
			
			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE users SET `sessionKey` = '' WHERE `sessionKey` = ?");
                $q->bind_param('s', $_COOKIE['sessionId']);
                $q->execute();

				
				$res = $q->get_result();
			}
		}
		
		return "res=999";
	}
	
	function updateItemStatusAvaliable($itemId)  //NOT CURRENTLY IN USE
	{
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);
			
			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE bowsers SET `avaliable` = '1' WHERE `id` = ?");
                $q->bind_param('s', $itemId);
                $q->execute();

				
				$res = $q->get_result();
			}
		}
		
		return "res=999";
	}
	
	function getItemImage($id)
{

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $q = $db->prepare("SELECT * FROM uploads WHERE bowserId = ? LIMIT 1;");
            $q->bind_param('i', $id);
            $q->execute();

            $res = $q->get_result();

            // Check if there is a result
            if ($row = $res->fetch_array()) {
                $st = rand();
                return $row['fileName'];
            }

    return "NOIMAGE.jpg";
}

	
	function getMostRecentItem($ownerId) // This function will return the id that has been given to the item that the user has just registered. The reason this is needed is so that the logImageUpload function knows which item to associate the image to.
{
    if (isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
        $loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);

        if ($loggedIn == true) {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $q = $db->prepare("SELECT * FROM bowsers WHERE ownerId = ? ORDER BY id DESC LIMIT 1;"); //This query will sort the list of items by descending order and select the one which was most recently inserted by the user
            $q->bind_param('s', $ownerId);
            $q->execute();

            $res = $q->get_result();

            if ($row = $res->fetch_array()) {
                $st = rand();
                return $row['id'];
            }
        }
    }

    return "res=999";
}
    function getAllBowsers()  //NOT CURRENTLY IN USE
{

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $q = $db->prepare("SELECT * FROM bowsers WHERE active = 1;");
            //$q->bind_param('b', $active);
            $q->execute();

            $res = $q->get_result();

            $items = array(); // Initialize an array to store item data

            while ($row = $res->fetch_array()) {
                // Add each item to the array
                $items[] = $row;
            }

            // Return the array of items
            return $items;
        
    }
	
	function searchBowsers($e1, $e2, $n1, $n2)  //Searches backward and forward east and north within the specified distance (see /index.php to see where this is called from currently)
{

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $q = $db->prepare("SELECT * FROM bowsers WHERE active = 1 AND ( (eastings BETWEEN ? AND ?) AND (northings BETWEEN ? AND ?) );");
            $q->bind_param('iiii', $e1, $e2, $n1, $n2);
            $q->execute();

            $res = $q->get_result();

            $items = array(); // Initialize an array to store item data

            while ($row = $res->fetch_array()) {
                // Add each item to the array
                $items[] = $row;
            }

            // Return the array of items
            return $items;
        
    }




	function getAllBowsersOwned($ownerId) //NOT CURRENTLY IN USE
{
    if (isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
        $loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);

        if ($loggedIn == true) {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $q = $db->prepare("SELECT * FROM bowsers WHERE active = 1 AND ownerId = ?;");
            $q->bind_param('s', $ownerId);
            $q->execute();

            $res = $q->get_result();

            $items = array(); // Initialize an array to store item data

            while ($row = $res->fetch_array()) {
                // Add each item to the array
                $items[] = $row;
            }

            // Return the array of items
            return $items;
        }
    }

    return "res=999";
}

   function getBowserDetails($id)
{
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM bowsers WHERE id = ?;");
        $q->bind_param('s', $id);
        $q->execute();

        $res = $q->get_result();

        $items = array(); // Initialize an array to store item data

        while ($row = $res->fetch_array()) {
                // Add each item to the array
            $items[] = $row;
        }

            // Return the array of items
         return $items;
    }


function getItemOwner($itemId) //NOT CURRENTLY IN USE
{
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $q = $db->prepare("SELECT ownerId FROM `bowsers` WHERE `id` = ?");
    $q->bind_param('i', $itemId);
    $q->execute();

    $res = $q->get_result();

    if ($res = $res->fetch_array()) {
        return $res['ownerId'];
    }

    return false;
}

function getUserEmail($userId)
{
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $q = $db->prepare("SELECT email FROM `users` WHERE `id` = ?");
    $q->bind_param('i', $userId);
    $q->execute();

    $res = $q->get_result();

    if ($res = $res->fetch_array()) {
        return $res['email'];
    }

    return false;
}

function getUsernameById($idx) //NOT CURRENTLY IN USE
{
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $q = $db->prepare("SELECT username FROM `users` WHERE `id` = ?");
    $q->bind_param('i', $idx);
    $q->execute();

    $res = $q->get_result();

    if ($res = $res->fetch_array()) {
        return $res['username'];
    }

    return false;
}




	function getAllUserInfo($userIDX) { //NOT CURRENTLY IN USE
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1;");
				$q->bind_param('s', $userIDX);
				$q->execute();
				
				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
				else return json_encode(["responseCode" => 999, "message" => "user data not found"]);
			}
		}

		return json_encode(["responseCode" => 999, "message" => "err"]);
	}

	function getAllUserInfoByName($userName) { //NOT CURRENTLY IN USE
		if(isset($_COOKIE['user_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['user_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
				$q->bind_param('s', $userName);
				$q->execute();
				
				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
				else return json_encode(["responseCode" => 999, "message" => "user data not found"]);
			}
		}

		return json_encode(["responseCode" => 999, "message" => "err"]);
	}
?>