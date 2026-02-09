<?php
/**
 * Checkout script for processing orders from the shopping cart.
 *
 * Validates user input, ensures active price lists, and inserts
 * orders into the database. Uses UUID v4 for unique order IDs.
 */

session_start();
require __DIR__ . '/../config/db.php';

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    http_response_code(400);
    exit('Cart is empty');
}

// Retrieve and sanitize user input
$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');

// Validate required fields
if (!$first || !$last) {
    http_response_code(400);
    exit('Name required');
}

/**
 * Generate a random UUID version 4.
 *
 * This function creates a 128-bit (16-byte) random UUID compliant with RFC 4122.
 * Version 4 UUIDs are randomly generated, with the version and variant bits set appropriately.
 *
 * @return string A UUID v4 string in the format xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
 * @throws Exception If random_bytes() fails to generate secure random data
 */
function generate_uuid_v4() {
    $data = random_bytes(16);

    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Generate a unique order ID
$order_unique_id = generate_uuid_v4();

// Begin a transaction to ensure atomic inserts
$pdo->beginTransaction();

foreach ($_SESSION['cart'] as $scheduleId) {

    // Validate that the schedule still has an active price list
    $stmt = $pdo->prepare("
        SELECT 
            s.*, 
            p.valid_until, 
            p.api_id AS price_list_api_id
        FROM 
            schedules s
        JOIN 
            price_lists p 
            ON s.price_list_id = p.id
        WHERE 
            s.id = ? 
            AND p.valid_until > NOW();
    ");
    $stmt->execute([$scheduleId]);
    $row = $stmt->fetch();

    // Roll back if any price list has expired
    if (!$row) {
        $pdo->rollBack();
        http_response_code(409);
        exit('Price list expired. Please remove expired items from cart and try again.');
    }

    // Insert order into the database
    $pdo->prepare("
        INSERT INTO orders (
            order_id, 
            price_list_api_id, 
            first_name, 
            last_name, 
            from_city, 
            to_city, 
            start_time, 
            end_time, 
            price, 
            company_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $order_unique_id,
        $row['price_list_api_id'],
        $first,
        $last,
        $row['from_city'],
        $row['to_city'],
        $row['start_time'],
        $row['end_time'],
        $row['price'],
        $row['company_name']
    ]);
}

// Commit the transaction after all inserts succeed
$pdo->commit();

// Clear the cart
$_SESSION['cart'] = [];

// Return success response
echo json_encode([
    'status' => 'ok',
    'order_id' => $order_unique_id
]);
