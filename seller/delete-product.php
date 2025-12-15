<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header("Location: ../pages/login.php"); 
    exit();
}

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']); 
    $seller_id = $_SESSION['user_id'];

    // Query ensures only the product owned by this seller is deactivated
    $sql = "UPDATE products SET product_status = 'Unavailable' WHERE product_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if the prepared statement was successful
    if ($stmt === false) {
        error_log("MySQLi Prepare Error: " . $conn->error);
        echo "<script>alert('Database error during preparation.'); window.location='manage-products.php';</script>";
        exit;
    }
    
    $stmt->bind_param('ii', $productId, $seller_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        echo "<script>alert('Product deactivated successfully.'); window.location='manage-products.php';</script>";
        exit();
    } else {
        $stmt->close();
        echo "<script>alert('Error deactivating product.'); window.location='manage-products.php';</script>";
    }
} else {
    echo "<script>alert('Invalid product ID.'); window.location='manage-products.php';</script>";
}
?>