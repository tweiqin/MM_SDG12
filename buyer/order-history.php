<?php
// Ensure session is started, and database is connected
include '../includes/buyerheader.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// --------------------------------------------------------------------------
// STEP 1: FETCH ALL ORDER ITEMS (Primary Query)
// --------------------------------------------------------------------------
$order_history_query = "
    SELECT 
        o.order_id, o.created_at, o.order_status, o.total_price,
        oi.quantity, p.name AS product_name, p.product_id,
        u.name AS seller_name, u.user_id AS seller_id
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id 
    JOIN products p ON oi.product_id = p.product_id 
    JOIN users u ON p.seller_id = u.user_id
    WHERE o.buyer_id = ?
    ORDER BY o.created_at DESC";

$stmt = $conn->prepare($order_history_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history_result = $stmt->get_result();
$stmt->close(); // Close statement after getting the result set

// Structure the results by Order ID for display grouping
$orders = [];
$all_order_ids = [];
while ($row = $history_result->fetch_assoc()) {
    $orders[$row['order_id']]['details'] = [
        'created_at' => $row['created_at'],
        'order_status' => $row['order_status'],
        'total_price' => $row['total_price']
    ];
    $orders[$row['order_id']]['items'][] = $row;
    $all_order_ids[] = $row['order_id'];
}


// --------------------------------------------------------------------------
// STEP 2: FETCH ALL REVIEWS UPFRONT (Resolves "Commands out of sync" error)
// --------------------------------------------------------------------------
$reviewed_items = [];
if (!empty($all_order_ids)) {
    // Generate placeholder string for IN clause (e.g., ?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($all_order_ids), '?'));
    $types = str_repeat('i', count($all_order_ids)); // 'iii...' for integers

    $reviewed_query = "SELECT order_id, product_id FROM reviews WHERE order_id IN ({$placeholders}) AND user_id = ?";
    $r_stmt = $conn->prepare($reviewed_query);

    // Bind all order IDs and the final user ID
    // We need to pass the array of IDs and then the user ID to bind_param
    $bind_params = array_merge([$types . 'i'], $all_order_ids, [$user_id]);
    
    // Use call_user_func_array to bind the dynamic parameters
    call_user_func_array([$r_stmt, 'bind_param'], ref_values($bind_params));
    
    $r_stmt->execute();
    $r_result = $r_stmt->get_result();
    
    while($row = $r_result->fetch_assoc()) {
        // Key format: order_id-product_id
        $reviewed_items[] = $row['order_id'] . '-' . $row['product_id']; 
    }
    $r_stmt->close();
}

// Helper function for call_user_func_array bug fix
function ref_values($arr){
    $refs = array();
    foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
    return $refs;
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Your Order History</h2>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Date & Time</th>
                    <th>Product / Seller / Quantity</th> <th>Total Price</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order_id => $order_data): ?>
                        
                        <?php 
                        $unreviewed_products_count = 0; 
                        $is_collected = ($order_data['details']['order_status'] == 'collected');
                        $total_items_in_order = count($order_data['items']);

                        // Check if any item in this order requires review
                        foreach ($order_data['items'] as $item) {
                            $key = $order_id . '-' . $item['product_id'];
                            if (!in_array($key, $reviewed_items)) {
                                $unreviewed_products_count++;
                            }
                        }
                        
                        $can_leave_review = ($is_collected && $unreviewed_products_count > 0);
                        $is_fully_reviewed = ($is_collected && $unreviewed_products_count === 0);
                        ?>

                        <tr>
                            <td><?= $order_id; ?></td>
                            <td><?= date('d-M-Y H:i', strtotime($order_data['details']['created_at'])); ?></td>
                            
                            <td>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($order_data['items'] as $item): ?>
                                        <li>
                                            <strong><?= htmlspecialchars($item['product_name']); ?></strong> (Qty: <?= $item['quantity']; ?>) 
                                            
                                            <small class="text-muted d-block">
                                                Sold by: 
                                                <a href="../pages/seller-profile.php?seller_id=<?= $item['seller_id']; ?>" 
                                                   style="color: #bf8b2e; text-decoration: none; font-weight: bold;">
                                                    <?= htmlspecialchars($item['seller_name']); ?>
                                                </a>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            
                            <td>RM<?= number_format($order_data['details']['total_price'], 2); ?></td>
                            <td><?= ucfirst($order_data['details']['order_status']); ?></td>
                            <td>
                                <?php if ($order_data['details']['order_status'] == 'ready for pick-up'): ?>
                                    <form method="POST" action="update-order-status.php"> 
                                        <input type="hidden" name="order_id" value="<?= $order_id; ?>">
                                        <input type="hidden" name="new_status" value="collected">
                                        <button type="submit" class="btn btn-success btn-sm">Collected</button>
                                    </form>
                                <?php elseif ($order_data['details']['order_status'] == 'collected'): ?>
                                    <span class="text-success fw-bold">Order Collected</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($can_leave_review): ?>
                                    <a href="review.php?order_id=<?= $order_id; ?>" class="btn btn-primary btn-sm">
                                        Review (<?= $unreviewed_products_count; ?>)
                                    </a>
                                <?php elseif ($is_fully_reviewed): ?>
                                    <a href="review.php?order_id=<?= $order_id; ?>" 
                                       style="color: #274081; font-weight: bold; text-decoration: none;">
                                        Reviewed
                                    </a>
                                <?php elseif (!$is_collected): ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan='7' class='text-center'>No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include('../includes/footer.php'); ?>