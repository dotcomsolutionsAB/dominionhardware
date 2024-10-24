<?php
// ini_set('display_errors',1);
date_default_timezone_set('Asia/Kolkata');

$db = new mysqli('localhost','dominion','34Ey5*xt8','dominion');
if($db->connect_errno){
    die('Sorry, We are having some errors');
}

// Establish a database connection
$conn = new mysqli('localhost','dominion','34Ey5*xt8','dominion');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$csvFilePath = 'file.csv';

$sql_1 = "SELECT * FROM `google_sheet` WHERE `id` = '6' LIMIT 1";
$query_1 = $db->query($sql_1);
while($row_1 = $query_1->fetch_assoc())
{
    $sheet_id = $row_1['id'];

    // Path to your CSV file
    $csvFileUrl = $row_1['sheet_path']; 

    // Open the CSV file for reading
    // $file = fopen($csvFilePath, "r");
    
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

        for($i=0;$i<$len;$i++)
        {
            // Value to search for
            $search_value = $header[$i];

            // Find the position of the value
            $position = array_search($search_value, $header);

            if($search_value[0] == '_'){
                $variation_attribute = array();
                $attribute_name = substr(trim($search_value), 1);
                // echo $attribute_name.'<br/>';

                $sql_2 = "SELECT COUNT(*) AS total FROM `attributes` WHERE `name` = '$attribute_name'";
                $query_2 = $db->query($sql_2);
                $row_2 = $query_2->fetch_assoc();

                if($row_2['total'] == 0){
                    $sql_1 = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'asmaindustrial_new' AND TABLE_NAME = 'attributes' ";
                    $query_1 = $db->query($sql_1);
                    $row_1 = $query_1->fetch_assoc();

                    $attribute_id = $row_1['AUTO_INCREMENT'];
                    
                    $sql_2 = "INSERT INTO `attributes`(`name`) VALUES ('$attribute_name')";
                    $query_2 = $db->query($sql_2);
                }else{
                    $sql_2 = "SELECT * FROM `attributes` WHERE `name` = '$attribute_name'";
                    $query_2 = $db->query($sql_2);
                    $row_2 = $query_2->fetch_assoc();

                    $attribute_id = $row_2['id'];
                }

                $variation_attribute['id'] = $attribute_id;
                $variation_attribute['name'] = $attribute_name;
                $variation_attribute['pos'] = $position;

                $variations[] = $variation_attribute;
            }

            $header_array[$search_value] = $position;   
        }

    }

    $prev_data = null;
    while (($data = fgetcsv($file)) !== FALSE) {

        $pos = $header_array['SKU'];
        $sku = $data[$pos];

        $pos = $header_array['Product Name'];
        $name = $data[$pos];
        
        $variant_product = 0;
        $attributes = array();
        $choice_options = array();

        foreach($variations AS $variation){
            $pos = $variation['pos'];
            $attribute_name = $variation['name'];
            $attribute_id = $variation['id'];

            $attribute_value = addslashes($data[$pos]);
            if($attribute_value != '')
            {
                $sql_2 = "SELECT COUNT(*) AS total FROM `attribute_values` WHERE `attribute_id` = '$attribute_id' AND `value` = '$attribute_value'";
                $query_2 = $db->query($sql_2);
                $row_2 = $query_2->fetch_assoc();

                if($row_2['total'] == 0)
                {
                    $sql_insert = "INSERT INTO `attribute_values`(`attribute_id`, `value`) VALUES ('$attribute_id','$attribute_value')";
                    $query_insert = $db->query($sql_insert);
                }

                $variant_product = 1;

                if (!in_array($attribute_id, $attributes)) {
                    $attributes[] = $attribute_id;
                }

                // Add attribute value only if it does not already exist
                if (!isset($choice_options[$attribute_id]) || !in_array($attribute_value, $choice_options[$attribute_id])) {
                    $choice_options[$attribute_id][] = $attribute_value;
                }
            }
        }

        // Check if the product is a variant product
        if ($variant_product == 1) {

            while (($next_data = fgetcsv($file)) !== FALSE) {
                $pos = $header_array['Product Name'];
                $next_name = $next_data[$pos];
                if ($next_name != $name) {
                    fseek($file, $previousPosition);
                    break; // Exit the loop if the name is different
                }

                foreach($variations AS $variation){
                    $pos = $variation['pos'];
                    $attribute_name = $variation['name'];
                    $attribute_id = $variation['id'];
        
                    $attribute_value = addslashes($next_data[$pos]);
                    if($attribute_value != '')
                    {
                        $sql_2 = "SELECT COUNT(*) AS total FROM `attribute_values` WHERE `attribute_id` = '$attribute_id' AND `value` = '$attribute_value'";
                        $query_2 = $db->query($sql_2);
                        $row_2 = $query_2->fetch_assoc();
        
                        if($row_2['total'] == 0)
                        {
                            $sql_insert = "INSERT INTO `attribute_values`(`attribute_id`, `value`) VALUES ('$attribute_id','$attribute_value')";
                            $query_insert = $db->query($sql_insert);
                        }
        
                        $variant_product = 1;
        
                        if (!in_array($attribute_id, $attributes)) {
                            $attributes[] = $attribute_id;
                        }
        
                        if (!isset($choice_options[$attribute_id]) || !in_array($attribute_value, $choice_options[$attribute_id])) {
                            $choice_options[$attribute_id][] = $attribute_value;
                        }
                    }
                }
                $previousPosition = ftell($file);
            }
        }

    }

}

?>