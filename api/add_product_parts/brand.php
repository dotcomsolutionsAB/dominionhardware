<?php

$sql_2 = "SELECT COUNT(*) AS `total` FROM `brands` WHERE `name` = '$brand'";
$query_2 = $db->query($sql_2);
$row_2 = $query_2->fetch_assoc();

if($row_2['total']>0)
{
    $sql_fetch = "SELECT * FROM `brands` WHERE `name` = '$brand'";
    $query_fetch = $db->query($sql_fetch);
    $row_fetch = $query_fetch->fetch_assoc();

    $brand_id = $row_fetch['id'];
}
else
{
    $sql_1 = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'domnion' AND TABLE_NAME = 'brands' ";
    $query_1 = $db->query($sql_1);
    $row_1 = $query_1->fetch_assoc();

    $brand_id = $row_1['AUTO_INCREMENT'];
    $brand_slug = strtolower($brand);
    $brand_slug = str_replace(" ","_",$brand_slug).'_'.$brand_id;

    $sql_3 = "INSERT INTO `brands` (`name`,`slug`) VALUES ('$brand','$brand_slug')";
    $query_3 = $db->query($sql_3);
}

?>