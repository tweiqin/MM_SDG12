<?php
include '../includes/buyerheader.php';
require_once '../config/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: ../pages/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($query);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$stmt_user->close();

$total_meals_query = "SELECT SUM(oi.quantity) as total_meals 
                      FROM orders o 
                      JOIN order_items oi ON o.order_id = oi.order_id 
                      WHERE o.buyer_id = ?";
$stmt_meals = $conn->prepare($total_meals_query);
$stmt_meals->bind_param('i', $user_id);
$stmt_meals->execute();
$total_meals = $stmt_meals->get_result()->fetch_assoc()['total_meals'] ?? 0;
$stmt_meals->close();

$money_saved_query = "SELECT SUM(oi.quantity * (p.original_price - oi.price)) as total_saved 
                      FROM orders o 
                      JOIN order_items oi ON o.order_id = oi.order_id
                      JOIN products p ON oi.product_id = p.product_id
                      WHERE o.buyer_id = ?";
$stmt_money = $conn->prepare($money_saved_query);
$stmt_money->bind_param('i', $user_id);
$stmt_money->execute();
$total_saved = $stmt_money->get_result()->fetch_assoc()['total_saved'] ?? 0;
$stmt_money->close();

$ready_orders_query = "SELECT COUNT(*) as ready_count FROM orders WHERE buyer_id = ? AND order_status = 'ready for pick-up'";
$stmt_ready = $conn->prepare($ready_orders_query);
$stmt_ready->bind_param('i', $user_id);
$stmt_ready->execute();
$ready_orders = $stmt_ready->get_result()->fetch_assoc()['ready_count'] ?? 0;
$stmt_ready->close();
?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Welcome, <?= htmlspecialchars($user['name']); ?>!</h2>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card text-white shadow-sm"
                style="background-image: radial-gradient(circle at 0% 0%, #7ed957, #00a650);">
                <div class="card-body">
                    <h5 class="card-title">Meals Saved
                        <i class="fas fa-utensils fa-2x float-end text-white-50"></i>
                    </h5>
                    <h2 class="card-text"><?= $total_meals; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white shadow-sm"
                style="background-image: radial-gradient(circle at 0% 0%, #5de0e6, #274081);">
                <div class="card-body">
                    <h5 class="card-title">Money Saved
                        <i class="fas fa-wallet fa-2x float-end text-white-50"></i>
                    </h5>
                    <h2 class="card-text">RM<?= number_format($total_saved, 2); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-dark shadow-sm"
                style="background-image: radial-gradient(circle at 0% 0%, #ffde59, #ff914d);">
                <div class="card-body">
                    <h5 class="card-title">Ready for Pick-up
                        <i class="fas fa-clock fa-2x float-end text-white-50"></i>
                    </h5>
                    <h2 class="card-text"><?= $ready_orders; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4 class="mb-3">Recent Orders</h4>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_orders_query = "SELECT order_id, created_at, order_status, total_price 
                                            FROM orders 
                                            WHERE buyer_id = ? 
                                            ORDER BY created_at DESC LIMIT 5";
                    $stmt_recent = $conn->prepare($recent_orders_query);
                    $stmt_recent->bind_param('i', $user_id);
                    $stmt_recent->execute();
                    $recent_orders_result = $stmt_recent->get_result();

                    if ($recent_orders_result->num_rows > 0) {
                        while ($order = $recent_orders_result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$order['order_id']}</td>
                                    <td>" . date("d M Y H:i", strtotime($order['created_at'])) . "</td>
                                    <td>" . ucfirst($order['order_status']) . "</td>
                                    <td>RM" . number_format($order['total_price'], 2) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No recent orders</td></tr>";
                    }
                    $stmt_recent->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>