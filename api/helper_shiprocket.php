<?php

ini_set('display_errors',1);
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'dominion');
define('DB_PASS', '246XcBztd1rzIe&vh');
define('DB_NAME', 'dominion');

// Shiprocket API credentials
define('SHIPROCKET_EMAIL', 'dotcomsolutions.apps@gmail.com');
define('SHIPROCKET_PASSWORD', 'Rh]DqqHR4/<=#');
// define('SHIPROCKET_EMAIL', 'globalmark52@gmail.com');
// define('SHIPROCKET_PASSWORD', 'yp$duLBeZjE7qAn');

// Function to get database connection
function getDatabaseConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to get authentication token from Shiprocket
function getShiprocketToken() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/auth/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(array(
            'email' => SHIPROCKET_EMAIL,
            'password' => SHIPROCKET_PASSWORD
        )),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        throw new Exception('Curl error: ' . curl_error($curl));
    }
    curl_close($curl);

    $responseArray = json_decode($response, true);

    if (isset($responseArray['token'])) {
        return $responseArray['token'];
    } else {
        throw new Exception("Failed to get token: " . json_encode($responseArray));
    }
}

// Function to get the latest order_id from orders where inv_no is not null or empty
function getLatestOrderWithInv($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE code = '20241025-11104855' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("No valid order found.");
    }

    return $order;
}

// Function to check if delivery_status in order_details is cancelled
function isOrderCancelled($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT delivery_status FROM order_details WHERE id = :order_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['order_id' => $order_id]);
    $order_detail = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_detail) {
        throw new Exception("Order details not found.");
    }

    // Check if the order has been cancelled
    return $order_detail['delivery_status'] === 'cancelled';
}

// Function to fetch shipping address from the database
function getShippingAddress($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE code = :order_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['order_id' => $order_id]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        throw new Exception("Shipping address not found.");
    }

    $shipping_address = json_decode($details['shipping_address'], true);

    return [$details, $shipping_address];
}

// Function to create an order in Shiprocket
function createShiprocketOrder($token, $order, $order_details, $order_items_arr, $shiprocket_payment_mode, $weight) {
    $details_arr = $order_details[0];
    $shipping_arr = $order_details[1];
    
    if($details_arr['shipping'] == 100){
        $details_arr['grand_total'] = $details_arr['grand_total'] - $details_arr['shipping'];
    }
    //  = $details_arr['grand_total']-100;
    // print_r($shipping_arr);
    // echo "<pre>";
    // print_r($details_arr);
    // echo "<pre>";
    // print_r($order_items_arr);
    // die;
    $post_fields = json_encode(array(
        'order_id' => $details_arr['code'],
        // 'order_id' => 126,
        'order_date' => date('Y-m-d', strtotime('-1 day')) ,
        // 'pickup_location' => 'Global M',
        // 'channel_id' => '825274',
        
        'pickup_location' => 'Dot Com Solutions',
        'channel_id' => '342406', // Replace with a valid channel ID
        
        'comment' => 'Test Order',
        'reseller_name' => 'Dot Com Solutions',
        'company_name' => $shipping_arr['name'],
        'billing_customer_name' => $shipping_arr['name'],
        'billing_last_name' => '',
        'billing_address' => $shipping_arr['address'],
        'billing_address_2' => '',
        'billing_isd_code' => '',
        'billing_city' => $shipping_arr['city'],
        'billing_pincode' => $shipping_arr['postal_code'],
        'billing_state' => $shipping_arr['state'],
        'billing_country' => $shipping_arr['country'],
        'billing_email' => $shipping_arr['email'],
        'billing_phone' => str_replace("+", "", substr($shipping_arr['phone'], -10)),
        'billing_alternate_phone' => '',
        'shipping_is_billing' => true,
        'shipping_customer_name' => '',
        'shipping_last_name' => '',
        'shipping_address' => '',
        'shipping_address_2' => '',
        'shipping_city' => '',
        'shipping_pincode' => '',
        'shipping_country' => '',
        'shipping_state' => '',
        'shipping_email' => '',
        'shipping_phone' => '',
        'order_items' => $order_items_arr,
        'payment_method' => $details_arr['payment_type'],
        // 'cod_charges'=> 50,
        'shipping_charges' => $details_arr['shipping'],
        'giftwrap_charges' => 0,
        'transaction_charges' => 0,
        'total_discount' => 0,
        'sub_total' => $details_arr['grand_total'],
        'length' => 15,
        'breadth' => 10,
        'height' => 10,
        'weight' => $weight,
        'ewaybill_no' => '',
        'customer_gstin' => '',
        'invoice_number' => $details_arr['inv_no'],
        'order_type' => ''
    ));

    // die(json_encode($post_fields));


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apiv2.shiprocket.in/v1/external/orders/create/adhoc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post_fields,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        throw new Exception('Curl error: ' . curl_error($curl));
    }
    curl_close($curl);

    return json_decode($response, true);
}

try {
    $pdo = getDatabaseConnection();

    // Fetch the latest order where inv_no is not null or empty
    $order = getLatestOrderWithInv($pdo);
    $od_id = $order['code'];
    $order_id = $order['id'];

    // Check if the order is cancelled in order_details
    // if (isOrderCancelled($pdo, $order['id'])) {
    //     throw new Exception("Order is cancelled, skipping Shiprocket punch.");
    // }

    // Fetch order details and shipping address
    $orderDetails = getShippingAddress($pdo, $od_id);



    $stmt2 = $pdo->prepare("SELECT p.name, od.product_id, od.price, od.tax, od.quantity, p.sku, p.weight
                        FROM products as p 
                        INNER JOIN order_details as od 
                        ON od.product_id = p.id 
                        WHERE od.order_id = :order_id");
    $stmt2->execute(['order_id' => $order_id]);
    $orderDetails2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $stmt3 = $pdo->prepare("SELECT p.discount, p.weight, p.sku 
                            FROM product_stocks as p 
                            INNER JOIN order_details as od 
                            ON od.product_id = p.product_id AND od.variation = p.variant 
                            WHERE od.order_id = :order_id");
    $stmt3->execute(['order_id' => $order_id]);
    $stockDetails = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    

    $order_items_arr = [];
    $total_weight = 0;

    foreach ($orderDetails2 as $index => $row2) {
        $row3 = $stockDetails[$index] ?? ['discount' => 0, 'weight' => 0, 'sku' => '']; // Safeguard in case index mismatch
        
        // Add the weight of each item to the total
        $total_weight += !empty($row3['weight']) ? $row3['weight'] : (!empty($row2['weight']) ? $row2['weight'] : 0);


        $order_items_arr[] = [
            'name' => $row2['name'],
            'sku' => !empty($row3['sku']) ? $row3['sku'] : $row2['sku'],
            'units' => $row2['quantity'],
            'selling_price' => round($row2['tax'] + $row2['price']),
            'discount' => 0, // Fetch discount if available
            'tax' => 18, // Assuming a flat tax rate, update this if dynamic
            'quantity' => $row2['quantity'],
            'weight' => !empty($row3['weight']) ? $row3['weight'] : (!empty($row2['weight']) ? $row2['weight'] : null), // Adding weight to the item array
        ];
    }


    $shiprocket_payment_mode = $order['payment_type'];
     // Example weight in kg

    // Authenticate and get token
    $token = getShiprocketToken();
    // die(json_encode($order_items_arr));

    // Create order in Shiprocket
    $response = createShiprocketOrder($token, $order, $orderDetails, $order_items_arr, $shiprocket_payment_mode, $total_weight);

    // Output the response
    echo '<pre>';
    print_r($response);
    echo '</pre>';

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

