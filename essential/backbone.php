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


    