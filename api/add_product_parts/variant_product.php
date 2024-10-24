<?php

$variant_product = 0;
$attributes = array();
$choice_options = array();
$variant = '';

foreach($variations AS $variation){
    $pos = $variation['pos'];
    $attribute_name = $variation['name'];
    $attribute_id = $variation['id'];
    

    $attribute_value = addslashes($data[$pos]);
    if($attribute_value != '')
    {
        if($variant == '')
        {
            $variant = $attribute_value;
        }else{
            $variant .= '-'.$attribute_value;
        }

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

        // Construct the choice options array
        if (!isset($choice_options[$attribute_id])) {
            // Initialize the values array for the attribute if it doesn't exist
            $choice_options[$attribute_id] = [];
        }

        // Add attribute value only if it does not already exist
        if (!in_array($attribute_value, $choice_options[$attribute_id])) {
            $choice_options[$attribute_id][] = $attribute_value;
        }
    }
}

$sql_delete = "DELETE FROM `product_stocks` WHERE `product_id` = '$product_id'";
$query_delete = $db->query($sql_delete);

// Check if the product is a variant product
if ($variant_product == 1) {

    // $variant = str_replace('\"', '', $variant);
    // $variant = str_replace(' ', '', $variant);
    if($unit_price > 0)
    {
        $sql_insert = "INSERT INTO `product_stocks`(`product_id`, `variant`, `sku`, `price`, `qty`, `weight`, `image`) VALUES ('$product_id','$variant','$sku','$unit_price','$qty','$weight',NULL)";
        echo $sql_insert.'<br/>';
        $query_insert = $db->query($sql_insert);

    }

    $previousPosition = ftell($file);
    
    while (($next_data = fgetcsv($file)) !== FALSE) {


        $pos = $header_array['SKU'];
        $vr_sku = $next_data[$pos];

        $pos = $header_array['Product Name'];
        $next_name = addslashes($next_data[$pos]);

        $pos = $header_array['Sale Price'];
        $vr_unit_price = $next_data[$pos];

        if($vr_unit_price == '')
        {
            $vr_unit_price = '0';
            $published = 0;
        }

        $pos = $header_array['Stock'];
        $vr_qty = $next_data[$pos];

        $pos = $header_array['Weight (Kgs)'];
        $vr_weight = $next_data[$pos];
        
        if ($next_name != $name) {
            fseek($file, $previousPosition);
            break; // Exit the loop if the name is different
        }

        $variant = '';
        foreach($variations AS $variation){
            $pos = $variation['pos'];
            $attribute_name = $variation['name'];
            $attribute_id = $variation['id'];

            $attribute_value = addslashes($next_data[$pos]);
            if($attribute_value != '')
            {
                if($variant == '')
                {
                    $variant = $attribute_value;
                }else{
                    $variant .= '-'.$attribute_value;
                }
                
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

                // Construct the choice options array
                if (!isset($choice_options[$attribute_id])) {
                    // Initialize the values array for the attribute if it doesn't exist
                    $choice_options[$attribute_id] = [];
                }

                // Add attribute value only if it does not already exist
                if (!in_array($attribute_value, $choice_options[$attribute_id])) {
                    $choice_options[$attribute_id][] = $attribute_value;
                }
            }
        }

        // $variant = str_replace('\"', '', $variant);
        // $variant = str_replace(' ', '', $variant);
        if($vr_unit_price > 0)
        {
            $sql_insert = "INSERT INTO `product_stocks`(`product_id`, `variant`, `sku`, `price`, `qty`, `weight`, `image`) VALUES ('$product_id','$variant','$vr_sku','$vr_unit_price','$vr_qty','$vr_weight',NULL)";
            $query_insert = $db->query($sql_insert);
        }

        $previousPosition = ftell($file);


    }
}

$choice_options_save = [];
foreach ($choice_options as $attribute_id => $values) {
    $choice_options_save[] = [
        'attribute_id' => $attribute_id,
        'values' => $values
    ];
}

// Convert the output array to JSON format
$choice_options = json_encode($choice_options_save);
$attributes = json_encode($attributes);

?>