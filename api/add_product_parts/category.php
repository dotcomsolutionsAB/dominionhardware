<?php

$sql_3 = "DELETE FROM `product_categories` WHERE `product_id` = '$product_id'";
$query_3 = $db->query($sql_3);

$pos = $header_array['Category'];
$category_level_0 = $data[$pos];
$category_level_0_array = explode(",", $category_level_0);

foreach ($category_level_0_array as $cat_0) {
    $cat_0 = trim($cat_0);
    $category_level_0_id = process_category($db, $cat_0, 0, 0);

    $pos = $header_array['Sub Category'];
    $category_level_1 = $data[$pos];
    $category_level_1_array = explode(",", $category_level_1);

    foreach ($category_level_1_array as $cat_1) {
        $cat_1 = trim($cat_1);
        $category_level_1_id = process_category($db, $cat_1, $category_level_0_id, 1);

        $pos = $header_array['Category Level 3'];
        $category_level_2 = $data[$pos];
        $category_level_2_array = explode(",", $category_level_2);

        if (!empty($category_level_2_array)) {
            foreach ($category_level_2_array as $cat_2) {
                $cat_2 = trim($cat_2);
                $category_level_2_id = process_category($db, $cat_2, $category_level_1_id, 2);

                // Insert into product_categories table for each category level
                if ($category_level_0_id != '0') {
                    $sql_3 = "INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES ('$product_id', '$category_level_0_id')";
                    $db->query($sql_3);
                }
                if ($category_level_1_id != '0') {
                    $sql_3 = "INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES ('$product_id', '$category_level_1_id')";
                    $db->query($sql_3);
                }
                if ($category_level_2_id != '0') {
                    $sql_3 = "INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES ('$product_id', '$category_level_2_id')";
                    $db->query($sql_3);
                }
            }
        } else {
            // Handle case where there is no level 2 category
            if ($category_level_0_id != '0') {
                $sql_3 = "INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES ('$product_id', '$category_level_0_id')";
                $db->query($sql_3);
            }
            if ($category_level_1_id != '0') {
                $sql_3 = "INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES ('$product_id', '$category_level_1_id')";
                $db->query($sql_3);
            }
        }
    }
}

$category_id = 0;

if ($category_level_0_id != '0' && $category_level_1_id == '0' && $category_level_2_id == '0') {
    $category_id = $category_level_0_id;
} else if ($category_level_0_id != '0' && $category_level_1_id != '0' && $category_level_2_id == '0') {
    $category_id = $category_level_1_id;
} else if ($category_level_0_id != '0' && $category_level_1_id != '0' && $category_level_2_id != '0') {
    $category_id = $category_level_2_id;
}

?>
