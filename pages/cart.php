<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Pull and clear the specific cart error message here
$cart_error_message = $_SESSION['cart_error'] ?? null;
unset($_SESSION['cart_error']);
// The general $_SESSION['message'] (for success) is still checked and cleared below if needed


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: ../pages/login.php');
    exit;
}

// Display success message from other pages (like remove-from-cart.php)
$general_message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);


// Check if cart is empty and redirect/display message
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $is_empty = true;
    $db_stock = [];
} else {
    $is_empty = false;

    // Stock fetch logic remains the same (secure dynamic query)
    $db_stock = [];
    $product_ids = array_column($_SESSION['cart'], 'product_id');
    if (!empty($product_ids)) {
        $stock_query = "SELECT product_id, quantity FROM products WHERE product_id IN (" . implode(',', array_fill(0, count($product_ids), '?')) . ")";
        $stmt_stock = $conn->prepare($stock_query);

        $types = str_repeat('i', count($product_ids));
        $ref_values = function ($arr) {
            $refs = [];
            foreach ($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs; };
        $bind_params = array_merge([$types], $product_ids);
        call_user_func_array([$stmt_stock, 'bind_param'], $ref_values($bind_params));

        $stmt_stock->execute();
        $stock_result = $stmt_stock->get_result();
        while ($row = $stock_result->fetch_assoc()) {
            $db_stock[$row['product_id']] = $row['quantity'];
        }
        $stmt_stock->close();
    }

    // Calculate total (only if cart is NOT empty)
    $grand_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $grand_total += $item['product_price'] * $item['quantity'];
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="container my-5">
    <h2 class="text-center mb-4">Your Shopping Cart</h2>

    <?php if ($cart_error_message): ?>
        <div class="alert alert-danger text-center"><?= $cart_error_message; ?></div>
    <?php endif; ?>
    <?php if ($general_message): ?>
        <div class="alert alert-info text-center"><?= $general_message; ?></div>
    <?php endif; ?>


    <?php if ($is_empty): ?>
        <p class="text-center">Your cart is empty.</p>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-success" style="font-size:18px">Continue Shopping</a>
        </div>

    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item):
                        $current_stock = $db_stock[$item['product_id']] ?? 0;
                        $can_increase = ($item['quantity'] < $current_stock);
                        ?>
                        <tr>
                            <td>
                                <img src="../assets/images/<?= htmlspecialchars($item['product_image']); ?>"
                                    alt="<?= htmlspecialchars($item['product_name']); ?>" width="80" height="auto">
                            </td>
                            <td><?= htmlspecialchars($item['product_name']); ?></td>
                            <td>RM<?= number_format($item['product_price'], 2); ?></td>

                            <td>
                                <form action="update-cart.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="submit" name="action" value="decrease" class="btn btn-sm btn-warning"
                                        <?= $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                                </form>
                                <?= htmlspecialchars($item['quantity']); ?>
                                <form action="update-cart.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="submit" name="action" value="increase" class="btn btn-sm btn-success"
                                        <?= !$can_increase ? 'disabled' : ''; ?>>+</button>
                                </form>
                            </td>
                            <td>RM<?= number_format($item['product_price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form action="remove-from-cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                        <td colspan="2"><strong>RM<?= number_format($grand_total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="text-end mt-4">
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-success">Checkout</a>
        </div>

    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>