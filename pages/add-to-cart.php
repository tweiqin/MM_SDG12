<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    
    $product_id = intval($_POST['product_id']);
    
    // Fetch necessary data and stock securely
    $stmt = $conn->prepare("SELECT seller_id, name, price, image, quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$product_data || $product_data['quantity'] <= 0) {
        // FIX: Use specific session key
        $_SESSION['cart_error'] = "This product is currently out of stock.";
        header('Location: cart.php');
        exit;
    }

    $new_seller_id = $product_data['seller_id'];
    $product_name = $product_data['name'];
    $product_price = $product_data['price'];
    $product_image = $product_data['image'];
    $max_stock = $product_data['quantity']; // Max quantity available
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    } else {
        // Core Single-Seller Check (Remains)
        $existing_seller_id = $_SESSION['cart'][0]['seller_id']; 
        if ($new_seller_id != $existing_seller_id) {
            // FIX: Use specific session key for single-seller conflict
            $_SESSION['cart_error'] = "You can only order from one vendor at a time. Please clear your cart first.";
            header('Location: cart.php');
            exit; 
        }
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            
            // FIX: Check stock limit before incrementing
            if ($item['quantity'] < $max_stock) {
                $item['quantity'] += 1;
            } else {
                // FIX: Use specific session key for stock limit
                $_SESSION['cart_error'] = "Maximum available stock for " . htmlspecialchars($product_name) . " has been reached.";
            }
            $found = true;
            break;
        }
    }

    if (!$found) {
        // Only add if initial stock is > 0
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'product_price' => $product_price,
            'product_image' => $product_image,
            'quantity' => 1,
            'seller_id' => $new_seller_id // Store seller ID
        ];
    }

    header('Location: cart.php');
    exit;
} else {
    echo "Invalid product data.";
    exit;
}
?>