<?php
require_once '../config/db.php';
session_start(); // Ensure session is started for security

// Check if user is admin (security is critical here)
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = intval($_GET['id']); 

    // ----------------------------------------------------
    // START MODIFICATION: REMOVE DESTRUCTIVE DELETION LOGIC
    // ----------------------------------------------------
    
    // REMOVED: The code block that deleted from order_items.
    // REMOVED: The SQL query for DELETE FROM products.
    
    // FIX: Change DELETE to UPDATE the status to 'Unavailable' (Deactivate)
    $query = "UPDATE products SET product_status = 'Unavailable' WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    
    // Check if the prepared statement was successful
    if ($stmt === false) {
        // Handle preparation error (e.g., if column product_status doesn't exist)
        error_log("MySQLi Prepare Error: " . $conn->error);
        header("Location: manage-products.php?status=error");
        exit;
    }
    
    // Bind the product ID
    $stmt->bind_param("s", $productId);

    // Execute the deactivation
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: manage-products.php?status=deactivated");
        exit;
    } else {
        // Handle execution error
        $stmt->close();
        header("Location: manage-products.php?status=error");
        exit;
    }

    // ----------------------------------------------------
    // END MODIFICATION
    // ----------------------------------------------------
} else {
    header("Location: manage-products.php");
    exit;
}
?>