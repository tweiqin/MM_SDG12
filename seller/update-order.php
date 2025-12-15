<?php
require_once '../config/db.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $order_status = $_POST['order_status'];

    // Update order status query
    $update_query = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $order_status, $order_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Order status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update order status.";
    }

    mysqli_stmt_close($stmt);
}

header('Location: orders.php');
exit;
?>