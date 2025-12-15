<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$order_id = $_GET['order_id'];

require_once '../config/db.php';
$query = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
?>

<section class="order-confirmation py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">Order Confirmation</h2>
        <p>Thank you for your order!</p>
        <p>Order ID: <?= $order['order_id']; ?></p>
        <p>Total Price: $<?= number_format($order['total_price'], 2); ?></p>
        <p>Status: <?= ucfirst($order['order_status']); ?></p>
    </div>
</section>