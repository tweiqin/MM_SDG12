<?php
session_start();
require_once '../config/db.php'; // Needed for stock check

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        
        // Fetch current max stock securely
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product_stock = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $max_stock = $product_stock['quantity'] ?? 0;

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                if ($action === 'increase') {
                    // FIX: Check against max stock before increasing
                    if ($item['quantity'] < $max_stock) {
                        $item['quantity'] += 1; 
                    } else {
                        // Store message for display on cart.php
                        $_SESSION['message'] = "Maximum available stock for " . htmlspecialchars($item['product_name']) . " has been reached.";
                    }
                } elseif ($action === 'decrease') {
                    $item['quantity'] -= 1; 
                    if ($item['quantity'] <= 0) {
                        $item = null;
                    }
                }
                break;
            }
        }
        
        // Remove null entries and re-index
        $_SESSION['cart'] = array_filter($_SESSION['cart']);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

header('Location: cart.php');
exit;
?>