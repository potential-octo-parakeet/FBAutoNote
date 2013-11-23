<?php
require 'fbsdk/facebook.php';

$baseURL = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

if($_SERVER['REMOTE_ADDR']!=='127.0.0.1'){
	$AppID  = '529590097137748';
	$secret = '56dc9586f4da8e83b5401ab298f2b0f5';
}
else{
	$AppID  = '529590097137748';
	$secret = '56dc9586f4da8e83b5401ab298f2b0f5';
}

$fb = new Facebook(
	array(
		'appId'  => $AppID,
		'secret' => $secret,
	)
);