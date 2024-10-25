<?php
	date_default_timezone_set('Asia/Kolkata');
	$db = new mysqli('localhost','dominion','34Ey5*xt8','dominion');
	if($db->connect_errno){
		die('Sorry, We are having some errors');
	}
?>