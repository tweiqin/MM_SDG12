<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = intval($_GET['id']);

    // Change DELETE to UPDATE the status to 'Unavailable' (Deactivate)
    $query = "UPDATE products SET product_status = 'Unavailable' WHERE product_id = ?";
    $stmt = $conn->prepare($query);

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

} else {
    header("Location: manage-products.php");
    exit;
}
?>