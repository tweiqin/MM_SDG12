<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in as a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && $_POST['new_status'] == 'collected') {
    $order_id = intval($_POST['order_id']);
    $buyer_id = $_SESSION['user_id'];
    $new_status = 'collected';

    // SECURE: Only update if the current status is 'ready for pick-up' AND the user is the buyer
    $update_query = "UPDATE orders SET order_status = ? 
                     WHERE order_id = ? AND buyer_id = ? AND order_status = 'ready for pick-up'";

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('sii', $new_status, $order_id, $buyer_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Order #{$order_id} marked as collected.";
    } else {
        $_SESSION['message'] = "Error: Could not mark order as collected.";
    }
    $stmt->close();
}

header('Location: order-history.php');
exit;
?>