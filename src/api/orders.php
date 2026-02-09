<?php
/**
 * Fetch all orders from the database and return as JSON.
 *
 * Orders are returned in descending order by `order_item_id` (latest first).
 */

require '../config/db.php';

// SQL query to fetch all orders
$sql = "
SELECT *
FROM orders
ORDER BY order_item_id DESC
";

// Parameters array (empty because no user input)
$params = [];

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Return results as JSON
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
