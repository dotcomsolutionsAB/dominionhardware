<?php
// ini_set("display_errors",1);
date_default_timezone_set('Asia/Kolkata');
$db = new mysqli('localhost','dominion','246XcBztd1rzIe&vh','dominion');
if($db->connect_errno){
    die('Sorry, We are having some errors');
}

$sql = "UPDATE `google_sheet` SET `status` = 0 WHERE 1";
$query = $db->query($sql);

?>