<?php

include('../includes/sellerheader.php');
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// FIX 1: MODIFIED QUERY to fetch full_name from the orders table
$seller_orders_query = "
    SELECT 
        o.order_id, o.order_status, o.created_at, o.full_name, 
        SUM(oi.quantity * oi.price) AS total_price,
        
        -- Aggregate all ordered items and quantities into a single string for display
        GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '<br>') AS product_list
        
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE p.seller_id = ?
    GROUP BY o.order_id, o.order_status, o.created_at, o.full_name
    ORDER BY o.created_at DESC";

$stmt = $conn->prepare($seller_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller_orders_result = $stmt->get_result();
$stmt->close();
?>

<div class="container my-5">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h1 class="text-center mb-4">Orders Received</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Name</th>
                <th>Products Ordered</th>
                <th>Status</th>
                <th>Date & Time</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $seller_orders_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $order['order_id']; ?></td>

                    <td><?= htmlspecialchars($order['full_name']); ?></td>

                    <td><?= $order['product_list']; ?></td>

                    <td><?= ucfirst($order['order_status']); ?></td>
                    <td><?= date('d-M-Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>RM<?= number_format($order['total_price'], 2); ?></td>
                    <td>
                        <?php if ($order['order_status'] != 'collected'): ?>
                            <form method="POST" action="update-order.php">
                                <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                <select name="order_status" class="form-select form-select-sm">
                                    <option value="received" <?= $order['order_status'] == 'received' ? 'selected' : ''; ?>>
                                        Received</option>
                                    <option value="preparing" <?= $order['order_status'] == 'preparing' ? 'selected' : ''; ?>>
                                        Preparing</option>
                                    <option value="ready for pick-up" <?= $order['order_status'] == 'ready for pick-up' ? 'selected' : ''; ?>>Ready for Pick-up</option>
                                </select>
                                <button type="submit" class="btn btn-warning btn-sm mt-1">Update</button>
                            </form>
                        <?php else: ?>
                            <span class="text-success fw-bold">Collected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include('../includes/footer.php'); ?>