<?php
session_start();
require_once '../config/db.php';
include('../includes/header.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer' || !isset($_GET['order_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];
$message = $_SESSION['review_message'] ?? "";
unset($_SESSION['review_message']);

// 1. Fetch products linked to this collected order that are NOT YET reviewed for THIS order
$stmt = $conn->prepare("
    SELECT p.product_id, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    JOIN orders o ON oi.order_id = o.order_id 
    WHERE o.order_id = ? AND o.buyer_id = ? AND o.order_status = 'collected'
    AND p.product_id NOT IN (SELECT product_id FROM reviews WHERE order_id = ?)
");
// Bind parameters: order_id, user_id, order_id
$stmt->bind_param("iii", $order_id, $user_id, $order_id);
$stmt->execute();
$products_result = $stmt->get_result();
$stmt->close();

if ($products_result->num_rows == 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $message = "This order is not ready for review or has already been fully reviewed.";
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviews_submitted = 0;
    foreach ($_POST['review'] as $product_id => $review_data) {
        $product_id = intval($product_id);
        $rating = intval($review_data['rating']);
        $review_text = trim($review_data['text']);

        if ($rating >= 1 && !empty($review_text)) {
            
            // FIX: Check if review exists using order_id and product_id (prevents double form submission)
            $check_stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
            $check_stmt->bind_param("iii", $user_id, $product_id, $order_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows == 0) {
                
                // FIX: Secure INSERT query with the new order_id column
                $insert_stmt = $conn->prepare("INSERT INTO reviews (order_id, product_id, user_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("iiiis", $order_id, $product_id, $user_id, $rating, $review_text);
                $insert_stmt->execute();
                $insert_stmt->close();
                $reviews_submitted++;
            }
            $check_stmt->close();
        }
    }

    if ($reviews_submitted > 0) {
        $_SESSION['review_message'] = "Thank you, your review(s) have been recorded!";
        header('Location: order-history.php');
        exit;
    } else {
        $_SESSION['review_message'] = "Please provide ratings and text for at least one item.";
        header("Location: review.php?order_id=" . $order_id);
        exit;
    }
}
?>

<div class="container my-5">
    <h2 class="text-center mb-4">Review Order #<?= $order_id; ?></h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo strpos($message, 'Thank you') !== false ? 'success' : 'danger'; ?> text-center"><?= $message; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return confirm('Once you submit the review, you won\'t be able to edit again. Submit the review?');">
        <?php 
        $products_result->data_seek(0); 
        $products_to_review_count = 0;
        
        while ($product = $products_result->fetch_assoc()) {
            // Re-run check just for display purposes
            $check_stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
            $check_stmt->bind_param("iii", $user_id, $product['product_id'], $order_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows == 0) {
                $products_to_review_count++;
        ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5>Reviewing: <?= htmlspecialchars($product['name']); ?></h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Rating (1-5)</label>
                            <select class="form-select" name="review[<?= $product['product_id']; ?>][rating]" required>
                                <option value="">Select Rating</option>
                                <option value="5">5 Stars - Excellent</option>
                                <option value="4">4 Stars - Good</option>
                                <option value="3">3 Stars - Average</option>
                                <option value="2">2 Stars - Poor</option>
                                <option value="1">1 Star - Terrible</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your Comments</label>
                            <textarea class="form-control" name="review[<?= $product['product_id']; ?>][text]" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
            <?php } $check_stmt->close(); 
            }
            
        if ($products_to_review_count > 0): ?>
            <div class="d-flex justify-content-between mt-4">
                <a href="order-history.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Submit Review(s)</button>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No items found in this order that require a review.</div>
            <div class="text-center mt-4">
                <a href="order-history.php" class="btn btn-secondary">Back to Orders</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php include('../includes/footer.php'); ?>