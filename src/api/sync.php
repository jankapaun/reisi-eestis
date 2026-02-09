<?php
/**
 * Price List Sync Script
 *
 * Fetches the latest schedule data from an external API, inserts it into
 * the database, and maintains only the most recent price lists.
 *
 * - Checks if the last price list is still valid before syncing.
 * - Prevents duplicate API imports.
 * - Stores new price list and schedules.
 * - Cleans up old schedules and keeps only the 15 most recent price lists.
 */

require __DIR__ . '/../config/db.php';

/**
 * API CONFIGURATION
 */
$apiUrl  = $_ENV['API_URL'] ?? '';
$user    = $_ENV['API_USER'] ?? '';
$pass    = $_ENV['API_PASS'] ?? '';

/**
 * Check last price list validity
 */
$stmt = $pdo->query(
    "SELECT valid_until
     FROM price_lists
     ORDER BY created_at DESC
     LIMIT 1"
);
$lastValidUntil = $stmt->fetchColumn();

// Use configured timezone or default to Tallinn
$timezone = new DateTimeZone($_ENV['TZ'] ?? 'Europe/Tallinn');
$lastValid = new DateTime($lastValidUntil, $timezone);
$now = new DateTime('now', $timezone);

// Exit if the last price list is still active
if ($lastValidUntil && $lastValid >= $now) {
    exit("Price list still active. Sync skipped.");
}

/**
 * FETCH DATA FROM API
 */
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => $user . ':' . $pass,
    CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
    CURLOPT_TIMEOUT        => 15
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    exit('API connection failed');
}

// Decode API response
$data = json_decode($response, true);
if (!$data || empty($data['id'])) {
    http_response_code(500);
    exit('Invalid API response');
}

/**
 * Prevent duplicate price list import
 */
$stmt = $pdo->prepare(
    "SELECT id 
     FROM price_lists 
     WHERE api_id = ?"
);
$stmt->execute([$data['id']]);

if ($stmt->fetch()) {
    exit('Price list already synced.');
}

/**
 * STORE DATA IN DATABASE
 */
$pdo->beginTransaction();

/**
 * Get last price list ID for cleanup
 */
$lastPriceListStmt = $pdo->query(
    "SELECT id 
     FROM price_lists 
     ORDER BY created_at DESC 
     LIMIT 1"
);
$lastPriceListId = $lastPriceListStmt->fetchColumn();

/**
 * Insert new price list
 */
$stmt = $pdo->prepare(
    "INSERT INTO price_lists (
        api_id, 
        schedule_data_json, 
        valid_until
    ) VALUES (?, ?, ?)"
);
$stmt->execute([
    $data['id'],
    json_encode($data),
    $data['expires']['date']
]);
$priceListId = $pdo->lastInsertId();

/**
 * Prepare schedule insert statement once
 */
$scheduleStmt = $pdo->prepare(
    "INSERT INTO schedules (
        id, 
        route_id, 
        from_city, 
        to_city, 
        distance, 
        price, 
        start_time, 
        end_time, 
        company_id, 
        company_name, 
        price_list_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

/**
 * Insert each route and schedule from API
 */
foreach ($data['routes'] as $route) {
    foreach ($route['schedule'] as $s) {
        $scheduleStmt->execute([
            $s['id'],
            $route['id'],
            $route['from']['name'],
            $route['to']['name'],
            $route['distance'],
            $s['price'],
            $s['start']['date'],
            $s['end']['date'],
            $s['company']['id'],
            $s['company']['state'],
            $priceListId
        ]);
    }
}

// Commit transaction after all inserts
$pdo->commit();

/**
 * Remove old schedules tied to the previous price list
 */
if ($lastPriceListId) {
    $deleteStmt = $pdo->prepare("DELETE FROM schedules WHERE price_list_id = ?");
    $deleteStmt->execute([$lastPriceListId]);
}

/**
 * Keep only the 15 most recent price lists
 */
$pdo->exec(
    "DELETE FROM price_lists
     WHERE id NOT IN (
         SELECT id FROM (
             SELECT id
             FROM price_lists
             ORDER BY created_at DESC
             LIMIT 15
         ) AS recent
     )"
);
