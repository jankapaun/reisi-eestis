<?php
/**
 * Shopping cart management script.
 *
 * Handles adding, removing, and listing schedule items in the user's cart.
 * Uses PHP sessions to store cart data and returns JSON responses.
 */

session_start();
require __DIR__ . '/../config/db.php';

// Initialize cart if it does not exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Determine the action: 'add', 'remove', or 'list' (default)
$action = $_GET['action'] ?? 'list';

switch ($action) {

    case 'add':
        /**
         * Add a schedule item to the cart.
         *
         * Expects POST parameter 'schedule_id'. If the item is not already in
         * the cart, it is added and a success response is returned. Otherwise,
         * an error message is returned.
         */
        $id = $_POST['schedule_id'] ?? null;
        if ($id) {
            if (!in_array($id, $_SESSION['cart'])) {
                $_SESSION['cart'][] = $id;
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Selline reis on juba ostukorvis' // "This trip is already in the cart"
                ]);
            }
        }
        break;

    case 'remove':
        /**
         * Remove a booked trip from the cart.
         *
         * Expects POST parameter 'schedule_id'. Filters out the item from
         * the session cart array and returns a success response.
         */
        $id = $_POST['schedule_id'] ?? null;
        $_SESSION['cart'] = array_values(
            array_filter($_SESSION['cart'], fn($v) => $v !== $id)
        );
        echo json_encode(['status' => 'ok']);
        break;

    default:
        /**
         * List all booked trips in the cart.
         *
         * If the cart is empty, returns an empty array. Otherwise, fetches
         * schedule details from the database and returns them as JSON.
         */
        if (empty($_SESSION['cart'])) {
            echo json_encode([]);
            exit;
        }

        // Prepare placeholders for a secure IN clause
        $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));

        // Fetch schedule details for all items in the cart
        $stmt = $pdo->prepare("
            SELECT *
            FROM schedules
            WHERE id IN ($placeholders)
        ");
        $stmt->execute($_SESSION['cart']);

        // Return cart items as JSON
        echo json_encode($stmt->fetchAll());
        break;
}
