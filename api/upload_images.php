<?php
ini_set("display_errors", 1);
date_default_timezone_set('Asia/Kolkata');

$db = new mysqli('localhost', 'dominion', '246XcBztd1rzIe&vh', 'dominion');

// Check database connection
if ($db->connect_errno) {
    die('Sorry, We are having some errors');
}

// Define the SELECT query
$sql = "SELECT * FROM products WHERE `image_link` != '' AND (`photos` IS NULL OR `photos` = '') AND `published` = 1 ORDER BY `id` ASC LIMIT 5";
echo $sql . '<br/><br/>';

// Execute the query
$result = $db->query($sql);

if ($result === false) {
    echo "Error executing SELECT query: " . $db->error;
} else {
    if ($result->num_rows > 0) {
        echo "returned";
        while ($row = $result->fetch_assoc()) {
            $image_link = $row['image_link'];
            $id = $row['id'];

            $urls = explode(",", $image_link);
            $new_ids = [];

            foreach ($urls as $imageUrl) {
                $imageUrl = trim($imageUrl); // Remove any extra spaces
                echo $imageUrl . '<br/>';

                // Folder where you want to save the image
                $destinationFolder = '../public/uploads/all/';

                // Get the filename from the URL
                $filename = basename($imageUrl);
                echo $filename . '<br/>';

                // Check if the destination folder exists, if not create it
                if (!file_exists($destinationFolder)) {
                    if (!mkdir($destinationFolder, 0755, true)) {
                        echo "Error: Unable to create destination folder.";
                        continue; // Skip this image
                    }
                }

                // Path where the image will be saved
                $destinationPath = $destinationFolder . $filename;

                // Download the image using cURL
                $ch = curl_init($imageUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // For simplicity, ignoring SSL verification
                $imageContent = curl_exec($ch);

                if ($imageContent === false) {
                    echo 'Curl error: ' . curl_error($ch) . '<br/>';
                } else {
                    // Save the image to the destination folder
                    if (file_put_contents($destinationPath, $imageContent) !== false) {
                        // Prepare the file data
                        $file_original_name = pathinfo($filename, PATHINFO_FILENAME);
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);
                        $file_size = round(filesize($destinationPath) / 1024, 2); // Convert bytes to kilobytes
                        $user_id = 9;
                        $type = 'image';
                        $file_name = "uploads/all/" . $filename;

                        // Construct the INSERT SQL statement
                        $insert_sql = "INSERT INTO uploads (file_original_name, file_name, user_id, file_size, extension, type) 
                                       VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($insert_sql);
                        if ($stmt === false) {
                            echo "Error preparing INSERT statement: " . $db->error;
                            continue; // Skip this image
                        }
                        $stmt->bind_param("ssisii", $file_original_name, $file_name, $user_id, $file_size, $extension, $type);

                        if ($stmt->execute()) {
                            // Get the last inserted ID
                            $new_id = $db->insert_id;
                            $new_ids[] = $new_id;
                        } else {
                            echo "Error inserting upload: " . $stmt->error;
                        }
                    } else {
                        echo "Error: Unable to save the image.";
                    }
                }
                curl_close($ch);
            }

            // Update the products table with all new IDs
            if (!empty($new_ids)) {
                $existing_photos = $row['photos'];
                if (empty($existing_photos)) {
                    $photos = implode(',', $new_ids);
                } else {
                    $photos = $existing_photos . ',' . implode(',', $new_ids);
                }
                $thumbnail_img = $new_ids[0];

                $update_sql = "UPDATE products SET photos = ?, thumbnail_img = ? WHERE id = ?";
                $stmt = $db->prepare($update_sql);
                if ($stmt === false) {
                    echo "Error preparing UPDATE statement: " . $db->error;
                } else {
                    $stmt->bind_param("ssi", $photos, $thumbnail_img, $id);

                    if ($stmt->execute() !== TRUE) {
                        echo "Error updating product: " . $stmt->error;
                    }
                }
            }
        }
    } else {
        echo "No results found";
    }
}

// Close the connection
$db->close();
?>
