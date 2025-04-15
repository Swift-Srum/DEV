<?php
    error_reporting(0);
    
    if(session_status() === PHP_SESSION_NONE)
    session_start();
    
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    //header_status(500);
    /*foreach($_POST as $data){
        echo $data;
    }*/
    include_once(dirname(__FILE__) . '/checksum.php');
    if(isset($_POST))
    $checksum = new Checksum($_POST);
    /*if($checksum != $_POST['checksum']){
        header_status(500);
    }*/


	include_once(dirname(__FILE__) . '/config.php');
	include_once(dirname(__FILE__) . '/internal.php');
	include_once(dirname(__FILE__) . '/protections.php');
    include_once(dirname(__FILE__) . '/aes256.php');
    //echo $checksum;


    /*ill leave this for now */
    function getReportedBowsers($urgency = '', $postcode = '') {
        global $conn;
        
        $sql = "SELECT br.*, b.postcode 
                FROM bowser_reports br
                JOIN bowsers b ON br.bowserId = b.id
                WHERE 1=1";
        
        if (!empty($urgency)) {
            $sql .= " AND br.typeOfReport = ?";
        }
        
        if (!empty($postcode)) {
            $sql .= " AND b.postcode LIKE ?";
        }
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($urgency) && !empty($postcode)) {
            $postcodeLike = "%$postcode%";
            $stmt->bind_param("ss", $urgency, $postcodeLike);
        } elseif (!empty($urgency)) {
            $stmt->bind_param("s", $urgency);
        } elseif (!empty($postcode)) {
            $postcodeLike = "%$postcode%";
            $stmt->bind_param("s", $postcodeLike);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
?>