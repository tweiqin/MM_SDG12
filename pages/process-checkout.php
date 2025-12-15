<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// FIX: Ensure db.php is included to define $conn
require_once '../config/db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- 0. START TRANSACTION ---
    $conn->autocommit(FALSE);
    $transaction_ok = true;
    
    // --- 1. Sanitize and Calculate ---
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $payment_method = trim($_POST['payment_method']);
    
    $grand_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $grand_total += (float)$item['product_price'] * (int)$item['quantity'];
    }

    $buyer_id = $_SESSION['user_id'] ?? 0;
    $order_status = 'received'; 
    
    
    // --- 2. TRANSACTION STEP 1: Insert Order ---
    $order_query = "INSERT INTO orders (buyer_id, total_price, order_status, full_name, email, phone, payment_method) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($order_query);
    
    if ($stmt_order === false) { $transaction_ok = false; } 

    if ($transaction_ok) {
        $stmt_order->bind_param("idsssss", $buyer_id, $grand_total, $order_status, $full_name, $email, $phone, $payment_method); 
        
        if (!$stmt_order->execute()) {
            $transaction_ok = false;
        } else {
            $order_id = $conn->insert_id;
        }
        $stmt_order->close();
    }


    // --- 3. TRANSACTION STEP 2: Insert Items & Deduct Stock ---
    
    if ($transaction_ok) {
        $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_item = $conn->prepare($item_query);

        // PREPARE 1: Stock Check Query (SELECT)
        $check_query = "SELECT quantity FROM products WHERE product_id = ?";
        $stmt_check = $conn->prepare($check_query);
        
        // PREPARE 2: STOCK DEDUCTION (Simple subtraction, relying on PHP check for safety)
        $deduct_query = "UPDATE products SET quantity = quantity - ? WHERE product_id = ?";
        $stmt_deduct = $conn->prepare($deduct_query);

        // PREPARE 3: STATUS UPDATE (Sets status ONLY if stock is exactly zero after deduction)
        $status_query = "UPDATE products SET product_status = 'Unavailable' WHERE product_id = ? AND quantity <= 0";
        $stmt_status_update = $conn->prepare($status_query);


        if ($stmt_item === false || $stmt_check === false || $stmt_deduct === false || $stmt_status_update === false) { 
            $transaction_ok = false; 
        }

        foreach ($_SESSION['cart'] as $item) {
            if (!$transaction_ok) break;

            $product_id = $item['product_id'];
            $quantity = (int)$item['quantity'];
            $product_price = (float)$item['product_price'];
            
            // -------------------------------------------------------------
            // STEP A: CHECK STOCK WITH PHP BEFORE DEDUCTING (Prevents negative assignment)
            // -------------------------------------------------------------
            $stmt_check->bind_param("i", $product_id);
            $stmt_check->execute();
            $stock_result = $stmt_check->get_result();
            $current_stock = $stock_result->fetch_assoc()['quantity'] ?? 0;
            $stock_result->free(); 
            
            if ($quantity > $current_stock) {
                // Insufficient stock found: FAIL transaction
                $transaction_ok = false;
                $_SESSION['message'] = "Stock error: Product ID {$product_id} requested ({$quantity}) exceeds available stock ({$current_stock}).";
                break; 
            }
            // -------------------------------------------------------------
            
            // 1. Insert into order_items
            $stmt_item->bind_param("iidd", $order_id, $product_id, $quantity, $product_price);
            $stmt_item->execute();
            
            // 2. Deduct Stock (Simple subtraction is safe because we checked first)
            $stmt_deduct->bind_param("ii", $quantity, $product_id); 
            
            if (!$stmt_deduct->execute()) {
                $transaction_ok = false;
                error_log("Deduction Execute Failed: " . $stmt_deduct->error);
                break;
            }
            
            // 3. Update Status (Only if stock hits zero)
            $stmt_status_update->bind_param("i", $product_id);
            $stmt_status_update->execute();
        }
        
        // Close all statements
        $stmt_item->close();
        $stmt_deduct->close(); 
        $stmt_status_update->close(); 
        $stmt_check->close(); 


        // --- 4. FINAL COMMIT/ROLLBACK ---
        if ($transaction_ok) {
            $conn->commit();
            $conn->autocommit(TRUE);
            unset($_SESSION['cart']);
            header('Location: thankyou.php');
            exit;
        } else {
            // Rollback all changes
            $conn->rollback();
            $conn->autocommit(TRUE);
            
            if (!isset($_SESSION['message'])) {
                 $_SESSION['message'] = "Transaction failed due to an unknown system or stock error. Changes rolled back.";
            }
            header('Location: cart.php');
            exit;
        }

    } else {
        // Error inserting the primary order record (Step 1)
        echo "Error: Unable to place your order (Step 1): " . $conn->error;
    }
    $stmt_order->close();
} else {
    echo "Invalid request.";
}
?>