<?php
/**
 * Fetch all schedules with active price lists and return as JSON.
 *
 * Only schedules whose associated price list is still valid (`valid_until > NOW()`) are returned.
 */

require '../config/db.php';

// SQL query to fetch schedules with active price lists
$sql = "
SELECT 
    s.*
FROM 
    schedules s
JOIN 
    price_lists p 
    ON p.id = s.price_list_id
WHERE 
    p.valid_until > NOW();
";

// Parameters array (empty because no user input)
$params = [];

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Return results as JSON
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
