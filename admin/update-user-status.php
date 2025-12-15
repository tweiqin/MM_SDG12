<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['action'])) {
    $userId = intval($_GET['id']);
    $action = $_GET['action'];

    // Determine new status: 0 for Deactivate/Block, 1 for Activate
    $new_status = ($action === 'deactivate') ? 0 : 1;

    $updateQuery = "UPDATE users SET is_active = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateQuery);

    $updateStmt->bind_param("ii", $new_status, $userId);
    $updateStmt->execute();
    $updateStmt->close();

    // Update products if user is deactivated
    if ($new_status === 0) {
        $prodQuery = "UPDATE products SET product_status = 'Unavailable' WHERE seller_id = ?";
        $prodStmt = $conn->prepare($prodQuery);
        $prodStmt->bind_param("i", $userId);
        $prodStmt->execute();
        $prodStmt->close();
    } elseif ($new_status === 1) {
        // Reactivate products if user is activated
        $prodQuery = "UPDATE products SET product_status = 'Available' WHERE seller_id = ?";
        $prodStmt = $conn->prepare($prodQuery);
        $prodStmt->bind_param("i", $userId);
        $prodStmt->execute();
        $prodStmt->close();
    }

    header("Location: manage-users.php");
    exit;
} else {
    echo "Invalid request parameters.";
    exit;
}
?>