<?php
	function arrayToString($array)
	{
		$string = "";
		
		for($i=0; $i < count($array); $i++)
		{
			//$string = ($string + $array[$i);
			$string .= $array[$i];
		}
		return $string;
	}

	function removeEmptyValuesFromArray($array) {
		$newArray = [];

		foreach($array as $values) {
			if($values != "" && $values != null)
			array_push($newArray, $values);
		}

		return $newArray;
	}

	function GetIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                  $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $remote  = $_SERVER['REMOTE_ADDR'];
    
        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        else
        {
            $ip = $remote;
        }
    
        return $ip;
    }

	function IsProxy()
	{
		
		$ip_address = GetIP();
		
		$url = 'https://awebanalysis.com/includes/tools/ip-proxy-checker.php';
		$data = array('ip_address' => $ip_address, 'lang' => 'en');

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-Type: application/x-www-form-urlencoded\r\nReferer: https://awebanalysis.com/en/ip-proxy-checker/\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.128 Safari/537.36 OPR/75.0.3969.267\r\nX-Requested-With: XMLHttpRequest\r\nHost: awebanalysis.com\r\nOrigin: https://awebanalysis.com\r\nSec-Fetch-Mode: cors\r\nSec-Fetch-Site: same-origin",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { echo 'Error'; }
		else if(strpos($result, 'Proxy Detected') !== false)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function IsProxy2()
	{
		
		$ip_address = GetIP();
		
		$url = 'https://ip.teoh.io/vpn-detection';
		$data = array('ip' => $ip_address, 'vpnsubmit' => 'Submit');

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-Type: application/x-www-form-urlencoded\r\nReferer: https://ip.teoh.io/vpn-detection\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36 OPR/64.0.3417.167\r\nHost: ip.teoh.io\r\nOrigin: https://ip.teoh.io\r\nSec-Fetch-Mode: cors\r\nSec-Fetch-Site: same-origin",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { echo 'Error'; }
		else if(strpos($result, 'No VPN/Proxy Detected') !== false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function scramblePassword($username, $password) {
		$key = "?F5-#b$8M*e!5eR4";
		$hash1 = sha1($password, true);
		$hash2 = $hash1 ^ sha1($username . $password . $key . sha1($hash1, true), true);
		return base64_encode($hash2);
	}

	function scrambleEmail($email) {
		$key = "?F5-#b$1B*e!1eR0";
		$hash1 = sha1($email, true);
		$hash2 = $hash1 ^ sha1($email . $key . sha1($hash1, true), true);
		return base64_encode($hash2);
	}
?>