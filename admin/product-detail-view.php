<?php
session_start();
require_once '../config/db.php';
// Use the Admin header since this is part of the Admin workflow
include('../includes/adminheader.php');

// SECURITY CHECK: Ensure user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "<div class='container my-5 text-center alert alert-danger'>Invalid Product ID.</div>";
    exit;
}

$product_id = intval($_GET['product_id']);

// Fetch Product Details and Seller Info (Securely)
$query = "SELECT p.*, p.product_status, u.name AS seller_name FROM products p 
          JOIN users u ON p.seller_id = u.user_id 
          WHERE p.product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "<div class='container my-5 text-center alert alert-warning'>Product not found!</div>";
    exit;
}

// Fetch Reviews (Securely)
$reviews_query = "SELECT r.*, u.name FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.product_id = ? ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param('i', $product_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews_stmt->close();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <img src="../assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'default.jpg'; ?>"
                class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6 mt-3">
            <h2 class="mb-4"><?= htmlspecialchars($product['name']); ?></h2>

            <p><strong>Status:</strong> <span
                    style="color: <?= $product['product_status'] === 'Unavailable' ? 'red' : 'green'; ?>; font-weight: bold;"><?= $product['product_status']; ?></span>
            </p>
            <?php
            $original_price = (float) ($product['original_price'] ?? $product['price']);
            $selling_price = (float) $product['price'];
            ?>
            <p>
                <strong>Price:</strong>
                <?php if ($original_price > $selling_price && $original_price > 0): ?>
                    <span style="text-decoration: line-through; color: #888; margin-right: 10px;">
                        $<?= number_format($original_price, 2); ?>
                    </span>
                    <span style="color: red; font-weight: bold; font-size: 1.2em;">
                        $<?= number_format($selling_price, 2); ?> (Sale!)
                    </span>
                <?php else: ?>
                    <span style="font-weight: bold; font-size: 1.2em;">
                        $<?= number_format($selling_price, 2); ?>
                    </span>
                <?php endif; ?>
            </p>
            <p><strong>Category:</strong> <?= htmlspecialchars($product['category']); ?></p>

            <p><strong>Seller:</strong>
                <a href="seller-profile-view.php?seller_id=<?= $product['seller_id']; ?>"
                    style="color: #bf8b2e; text-decoration: none; font-weight: bold;">
                    <?= htmlspecialchars($product['seller_name']); ?>
                </a>
            </p>

            <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>

            <a href="manage-products.php" class="btn btn-secondary mt-3">Back to Management</a>
        </div>
    </div>

    <hr class="my-5">

    <div class="reviews-section">
        <h3>Customer Reviews</h3>
        <?php if ($reviews_result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($review['name']); ?></strong>
                        <span class="badge bg-warning text-dark">Rating: <?= $review['rating']; ?>/5</span>
                        <p><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        <small class="text-muted">Reviewed on <?= date('F j, Y', strtotime($review['created_at'])); ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No reviews yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>