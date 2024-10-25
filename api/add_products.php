<?php
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');

$db = new mysqli('localhost', 'dominion', '246XcBztd1rzIe&vh', 'dominion');
if ($db->connect_errno) {
    die('Sorry, We are having some errors');
}

// Establish a database connection
$conn = new mysqli('localhost', 'dominion', '246XcBztd1rzIe&vh', 'dominion');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function process_category($db, $category_name, $parent_id, $level) {
    if ($category_name != '') {
        // Check if category already exists
        $sql = "SELECT COUNT(*) AS `total` FROM `categories` WHERE `name` = '$category_name' AND `level` = '$level' AND `parent_id` = '$parent_id'";
        $query = $db->query($sql);
        $row = $query->fetch_assoc();

        if ($row['total'] > 0) {
            // Category exists, fetch its ID
            $sql_fetch = "SELECT * FROM `categories` WHERE `name` = '$category_name' AND `level` = '$level' AND `parent_id` = '$parent_id'";
            $query_fetch = $db->query($sql_fetch);
            $row_fetch = $query_fetch->fetch_assoc();
            return $row_fetch['id'];
        } else {
            // Category does not exist, insert it and fetch its ID
            $sql_1 = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dominion' AND TABLE_NAME = 'categories'";
            $query_1 = $db->query($sql_1);
            $row_1 = $query_1->fetch_assoc();

            $category_id = $row_1['AUTO_INCREMENT'];
            $category_slug = strtolower($category_name);
            $category_slug = str_replace(" ", "_", $category_slug) . '_' . $category_id;

            $sql_insert = "INSERT INTO `categories` (`parent_id`, `level`, `name`, `slug`) VALUES ('$parent_id', '$level', '$category_name', '$category_slug')";
            $db->query($sql_insert);

            return $category_id;
        }
    }
    return 0;
}

$csvFilePath = 'file.csv';

$sql_1 = "SELECT * FROM `google_sheet` WHERE `status` = '0' LIMIT 1";
$query_1 = $db->query($sql_1);
while ($row_1 = $query_1->fetch_assoc()) {
    $sheet_id = $row_1['id'];
    echo $sheet_id . '<br/>';

    // Path to your CSV file
    $csvFileUrl = $row_1['sheet_path'];

    // Download the CSV file from the URL and save it locally
    file_put_contents($csvFilePath, file_get_contents($csvFileUrl));

    // Open the locally saved CSV file for reading
    $file = fopen($csvFilePath, "r");

    // Check if file opened successfully
    if ($file !== FALSE) {
        // Read the header row
        $header = fgetcsv($file);
        $len = sizeof($header);

        $header_array = array();
        $variations = array();
        $specifications = array();

        for ($i = 0; $i < $len; $i++) {
            // Value to search for
            $search_value = $header[$i];

            // Find the position of the value
            $position = array_search($search_value, $header);

            if ($search_value[0] == '_') {
                $variation_attribute = array();
                $attribute_name = substr(trim($search_value), 1);

                $sql_2 = "SELECT COUNT(*) AS total FROM `attributes` WHERE `name` = '$attribute_name'";
                $query_2 = $db->query($sql_2);
                $row_2 = $query_2->fetch_assoc();

                if ($row_2['total'] == 0) {
                    $sql_1 = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dominion' AND TABLE_NAME = 'attributes'";
                    $query_1 = $db->query($sql_1);
                    $row_1 = $query_1->fetch_assoc();

                    $attribute_id = $row_1['AUTO_INCREMENT'];

                    $sql_2 = "INSERT INTO `attributes`(`name`) VALUES ('$attribute_name')";
                    $query_2 = $db->query($sql_2);
                } else {
                    $sql_2 = "SELECT * FROM `attributes` WHERE `name` = '$attribute_name'";
                    $query_2 = $db->query($sql_2);
                    $row_2 = $query_2->fetch_assoc();

                    $attribute_id = $row_2['id'];
                }

                $variation_attribute['id'] = $attribute_id;
                $variation_attribute['name'] = $attribute_name;
                $variation_attribute['pos'] = $position;

                $variations[] = $variation_attribute;
            } elseif ($search_value[0] == '-' && $search_value[1] != '') {
                $specifications[] = array(
                    'name' => substr($search_value, 1),
                    'pos' => $position
                );
            }

            $header_array[$search_value] = $position;
        }

        while (($data = fgetcsv($file)) !== FALSE) {

            $category_level_0_id = 0;
            $category_level_1_id = 0;
            $category_level_2_id = 0;

            $pos = $header_array['SKU'];
            $sku = $data[$pos];

            if ($sku != '') {

                $pos = $header_array['Merchant Item ID'];
                $item_id = $data[$pos];

                $pos = $header_array['Product Name'];
                $name = addslashes($data[$pos]);

                $slug = str_replace("/", "", str_replace(" ", "-", $name));
                $slug = strtolower($slug);

                $pos = $header_array['Description'];
                $description = addslashes($data[$pos]);

                $pos = $header_array['Images'];
                $image_link = $data[$pos];

                $pos = $header_array['Status'];
                $product_status = $data[$pos];

                if ($product_status == 'draft') {
                    $published = 0;
                } else {
                    $published = 1;
                }

                $pos = $header_array['Sale Price'];
                $sale_price = floatval($data[$pos]);
                 echo $sale_price ."Sale price : <br>";

                $pos = $header_array['Tax Rate'];
                $tax_value = floatval($data[$pos]);
                
                $tst_unit_price = $sale_price * (1 + $tax_value/100);
                 echo "before tst_unit_price : ".$tst_unit_price. " <br>" ;

                // Unit price (after calculating tax)
                // $unit_price = $unit_price - $tax;
                $tax = $tst_unit_price - $sale_price;
                 echo $tax ."tax : <br>";



                $unit_price =round($tst_unit_price) - $tax;
                echo "before unit price : ".$unit_price. " <br>" ;

                // $total = "unit_price:".$unit_price ." + "." tax ".$tax. " = ".$unit_price+$tax;
                //  die($total);
                
                $pos = $header_array['COD'];
                $cod = $data[$pos];

                $cod = 1;

                $pos = $header_array['Stock'];
                $qty = $data[$pos];

                if ($qty == '') {
                    $qty = 0;
                }

                if ($qty == 0) {
                    $published = 0;
                }

                $pos = $header_array['WA_KEYWORD'];
                $wa_keyword = $data[$pos];

                if ($cod == '') {
                    $cod = 0;
                }

                $unit = 'Nos';
                
               

                $pos = $header_array['PDF'];
                $pdf_url = isset($data[$pos]) ? $data[$pos] : '#';

                $pos = $header_array['MOQ'];
                $min_qty = $data[$pos];

                if ($min_qty < 1 || $min_qty == '') {
                    $min_qty = 1;
                }

                $pos = $header_array['Weight (Kgs)'];
                $weight = $data[$pos];

                if ($weight == '') {
                    $weight = '1';
                }

                $pos = $header_array['Brand'];
                $brand = $data[$pos];

                $video_provider = '';
                $video_link = '';
                $update_tax_type ='amount';
                // Assuming $header_array and $data are already defined

                for ($i = 1; $i <= 5; $i++) {
                    $pos = $header_array['Video ' . $i];
                    $temp = isset($data[$pos]) ? $data[$pos] : '';

                    if ($temp != '') {
                        // If $video_link is not empty, append a comma before adding the new video link
                        if ($video_link != '') {
                            $video_link .= ',';
                        }
                        $video_link .= $temp;

                        // Set $video_provider to 'youtube'
                        $video_provider = 'youtube';
                    }
                }

                include('add_product_parts/brand.php');

                include('add_product_parts/features.php');

                // Prepare the specifications HTML table
                $specification_html = '<table style="width:100%;"><thead></thead><tbody>';

                foreach ($specifications as $spec) {
                    $pos = $spec['pos'];
                    if($data[$pos] !='' && $data[$pos] != null ){
                    $specification_html .= '<tr style="border: 2px solid black; height:4vh; width:100%;">';
                    $specification_html .= '<th style="background: #eaeaea; padding-left: 2vw; width: 40%;">' . $spec['name'] . '</th>';
                    $specification_html .= '<td style="font-size: 13px; border-bottom: 1px solid #ccc; padding: 5px 10px; border-right: 1px solid #ccc; width: 60%;">' . $data[$pos] . '</td>';
                    $specification_html .= '</tr>';
                    }
                }
                
                $specification_html .= '</tbody></table>';

                $sql_check = "SELECT COUNT(*) AS `total` FROM `products` WHERE `sku` = '$sku'";
                $query_check = $db->query($sql_check);
                $row_check = $query_check->fetch_assoc();

                if ($row_check['total'] > 0) {
                    $sql_4 = "SELECT * FROM `products` WHERE `sku` = '$sku'";
                    $query_4 = $db->query($sql_4);
                    $row_4 = $query_4->fetch_assoc();

                    $product_id = $row_4['id'];

                    include('add_product_parts/category.php');

                    include('add_product_parts/variant_product.php');

                    if ($variant_product == 0) {
                        $sql_check = "SELECT COUNT(*) AS total FROM `product_stocks` WHERE  `product_id` = '$product_id'";
                        $query_check = $db->query($sql_check);
                        $row_check = $query_check->fetch_assoc();

                        if ($row_check["total"] > 0) {
                            $sql_insert = "UPDATE `product_stocks` SET `price`='$unit_price', `qty`='$qty',`min_qty`='$min_qty', `weight`='$weight', `item_id`='$item_id' WHERE `product_id` = '$product_id'";
                            $query_insert = $db->query($sql_insert);
                        } else {
                            $sql_insert = "INSERT INTO `product_stocks`(`product_id`, `variant`, `sku`, `item_id`, `price`, `qty`,`min_qty`, `weight`, `image`) VALUES ('$product_id','','$sku' ,'$item_id','$unit_price','$qty','$min_qty','$weight',NULL)";
                            $query_insert = $db->query($sql_insert);
                        }
                    }

                    // $sql = "UPDATE products SET `name`='$name',`image_link`='$image_link',`description`='$description',`cash_on_delivery`='$cod',`wa_keyword`='$wa_keyword',`features`='$features',`user_id`='9',`category_id`='$category_id',`brand_id`='$brand_id',`unit_price`='$unit_price',`unit`='$unit',`weight`='$weight',`tax`='$tax',`tax_type`='amount',`meta_title`='$name',`meta_description`='$description',`slug`='$slug',`variant_product`='$variant_product',`choice_options`='$choice_options',`attributes`='$attributes',`published` = '$published',`min_qty` = '$min_qty',`discount`=0, `discount_type`='amount', `product_specification`='$specification_html',`pdf`='$pdf_url' WHERE `sku` = '$sku'";
                    $sql = "UPDATE products SET `name`='$name',`image_link`='$image_link',`description`='$description',`cash_on_delivery`='$cod',`wa_keyword`='$wa_keyword',`features`='$features',`user_id`='9',`category_id`='$category_id',`brand_id`='$brand_id',`unit_price`='$unit_price',`unit`='$unit',`weight`='$weight',`tax`='$tax',`tax_type`='$update_tax_type',`meta_title`='$name',`meta_description`='$description',`slug`='$slug',`variant_product`='$variant_product',`choice_options`='$choice_options',`attributes`='$attributes',`published`='$published',`min_qty`='$min_qty',`discount`=0, `discount_type`='amount', `product_specification`='$specification_html',`pdf`='$pdf_url' WHERE `sku`='$sku'";

                    $query = $db->query($sql);

                    $sql_tax_check = "SELECT COUNT(*) AS total FROM `product_taxes` WHERE `product_id` = '$product_id'";
                    $query_tax_check = $db->query($sql_tax_check);
                    $row_tax_check = $query_tax_check->fetch_assoc();

                    if ($row_tax_check['total'] == 0) {
                        $sql_tax = "INSERT INTO `product_taxes`( `product_id`, `tax_id`, `tax`, `tax_type`) VALUES ('$product_id','3','$tax','$update_tax_type')";
                        $query_tax = $db->query($sql_tax);
                    } else {
                        $sql_tax = "UPDATE `product_taxes` SET `tax` = '$tax' WHERE `product_id` = '$product_id'";
                        $query_tax = $db->query($sql_tax);
                    }
                } else {
                    if ($unit_price != '') {
                        $sql_1 = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'dominion' AND TABLE_NAME = 'products'";
                        $query_1 = $db->query($sql_1);
                        $row_1 = $query_1->fetch_assoc();

                        $product_id = $row_1['AUTO_INCREMENT'];

                        include('add_product_parts/category.php');

                        include('add_product_parts/variant_product.php');

                        if ($variant_product == 0) {
                            $sql_insert = "INSERT INTO `product_stocks`(`product_id`, `variant`, `sku`,`item_id`, `price`, `qty`, `min_qty`,`weight`, `image`) VALUES ('$product_id','','$sku','$item_id','$unit_price','$qty','$min_qty','$weight',NULL)";
                            $query_insert = $db->query($sql_insert);
                        }

                        // Prepare SQL statement
                        $stmt = $conn->prepare("INSERT INTO products (`sku`,`name`,`image_link`,`description`,`features`,`user_id`,`category_id`,`brand_id`,`unit_price`,`slug`,`colors`,`choice_options`, `published`,`variant_product`,`attributes`,`unit`,`weight`,`tax`,`tax_type`,`meta_title`,`meta_description`,`min_qty`,`discount`,`discount_type`,`cash_on_delivery`,`wa_keyword`, `product_specification`,`pdf`) VALUES ('$sku','$name','$image_link','$description','$features','9','$category_id','$brand_id','$unit_price','$slug','[]','$choice_options','$published','$variant_product','$attributes','$unit','$weight','$tax','amount','$name','$description','$min_qty','0','amount','$cod','$wa_keyword','$specification_html','$pdf_url')");
                        // $sql_insert_product = "INSERT INTO products (`sku`,`name`,`image_link`,`description`,`features`,`user_id`,`category_id`,`brand_id`,`unit_price`,`slug`,`colors`,`choice_options`, `published`,`variant_product`,`attributes`,`unit`,`weight`,`tax`,`tax_type`,`meta_title`,`meta_description`,`min_qty`,`discount`,`discount_type`,`cash_on_delivery`,`wa_keyword`, `product_specification`,`pdf`) VALUES ('$sku','$name','$image_link','$description','$features','9','$category_id','$brand_id','$unit_price','$slug','[]','$choice_options','$published','$variant_product','$attributes','$unit','$weight','$tax','amount','$name','$description','$min_qty','0','amount','$cod','$wa_keyword','$specification_html','$pdf_url')";
                        // $db->query($sql_insert_product);

                        // Execute the query
                        if ($stmt->execute() === TRUE) {
                            $sql_tax = "INSERT INTO `product_taxes`( `product_id`, `tax_id`, `tax`, `tax_type`) VALUES ('$product_id','3','$tax','$update_tax_type')";
                            $query_tax = $db->query($sql_tax);
                        } else {
                            echo "Error: " . $conn->error;
                        }
                    }
                }
            }
        }

        $sql_3 = "UPDATE `google_sheet` SET `status`='1' WHERE `id` = '$sheet_id'";
        $query_3 = $db->query($sql_3);

        // Close the file
        fclose($file);
    } else {
        echo "Error: Unable to open file.";
    }
}

echo 'Completed';
?>
