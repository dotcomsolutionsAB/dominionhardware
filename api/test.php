<?php
// ini_set('display_errors',1);

// Establish a database connection
$conn = new mysqli('localhost','dominion','34Ey5*xt8','dominion');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Path to your CSV file
$csvFilePath = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTGaCbsSl6RuJe7ZP0R-OUEgxZAg4Ucq0_kkaNKK0kEJqiUPk_WxqO8aTUWO4Ui7yuqzArreMmapzHC/pub?gid=0&single=true&output=csv"; 

// Open the CSV file for reading
$file = fopen($csvFilePath, "r");

// Check if file opened successfully
if ($file !== FALSE) {
    // Read the header row
    $header = fgetcsv($file);
    $len = sizeof($header);

    $header_array = array();

    for($i=0;$i<$len;$i++)
    {
        // Value to search for
        $search_value = $header[$i];

        // Find the position of the value
        $position = array_search($search_value, $header);

        $header_array[$search_value] = $position;   
    }

    while (($data = fgetcsv($file)) !== FALSE) {

        $pos = $header_array['Product Name'];
        $name = $data[$pos];

        $pos = $header_array['Description'];
        $description = $data[$pos];

        $pos = $header_array['Images'];
        $image_link = $data[$pos];

        $pos = $header_array['Sale Price'];
        $unit_price = $data[$pos];

        $slug = str_replace(" ","-",$name);
        $slug = strtolower($slug);

        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO products (`name`,`image_link`,`description`,`user_id`,`category_id`,`unit_price`,`slug`) VALUES ('$name','$image_link','$description','9','72','$unit_price','$slug')");

        // Execute the query
        if ($stmt->execute() === TRUE) {
            echo "New record inserted successfully";
        } else {
            echo "Error: " . $conn->error;
        }

    }
    
    echo $validator;

    // Close the file
    fclose($file);
} else {
    echo "Error: Unable to open file.";
}

?>