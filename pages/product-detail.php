<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "<div class='container my-5 text-center'>";
    echo "<div class='alert alert-warning'>Invalid Product!</div>";
    echo "<a href='../index.php' class='btn btn-primary'>Go Back</a>";
    echo "</div>";
    exit;
}

$product_id = intval($_GET['product_id']);

//  Ensure p.quantity is selected here (for stock display)
$query = "SELECT p.*, p.quantity, u.name AS seller_name, u.user_id AS seller_id FROM products p 
          JOIN users u ON p.seller_id = u.user_id 
          WHERE p.product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='container my-5 text-center'>";
    echo "<div class='alert alert-warning'>Product not found!</div>";
    echo "<a href='../index.php' class='btn btn-primary'>Go Back</a>";
    echo "</div>";
    exit;
}

// Fetch product reviews (Query remains the same)
$reviews_query = "SELECT r.*, u.name FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.product_id = ? ORDER BY r.created_at DESC";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param('i', $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();

// Define quantity variables
$quantity_left = (int) $product['quantity'];
?>

<?php include('../includes/header.php'); ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <img src="../assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'default.jpg'; ?>"
                class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']); ?>">
        </div>

        <div class="col-md-6 mt-5">

            <h2 style="display: inline-block; margin-right: 15px;"><?= htmlspecialchars($product['name']); ?></h2>
            <?php
            if ($quantity_left > 0):
                ?>
                <i class="fas fa-shopping-bag" style="margin-right: 5px;"></i>
                <span style="color: red; font-size: 1em; font-weight: bold;">

                    <?= $quantity_left; ?> left
                </span>
            <?php else: ?>
                <span style="color: red; font-size: 1em; font-weight: bold;">
                    SOLD OUT
                </span>
            <?php endif; ?>
            <?php
            $original_price = (float) ($product['original_price'] ?? $product['price']);
            $selling_price = (float) $product['price'];
            ?>

            <p>
                <strong>Price:</strong>
                <?php if ($original_price > $selling_price && $original_price > 0): ?>
                    <span style="text-decoration: line-through; color: #888; margin-right: 10px;">
                        RM<?= number_format($original_price, 2); ?>
                    </span>
                    <span style="color: red; font-weight: bold; font-size: 1.2em;">
                        RM<?= number_format($selling_price, 2); ?> (Sale!)
                    </span>
                <?php else: ?>
                    <span style="font-weight: bold; font-size: 1.2em;">
                        RM<?= number_format($selling_price, 2); ?>
                    </span>
                <?php endif; ?>
            </p>

            <p>
                <strong>Seller:</strong>
                <a href="seller-profile.php?seller_id=<?= $product['seller_id']; ?>"
                    style="color: #bf8b2e; text-decoration: none; font-weight: bold;">
                    <?= htmlspecialchars($product['seller_name']); ?>
                </a>
            </p>
            <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>

            <form action="add-to-cart.php" method="POST" class="mt-2">
                <input type="hidden" name="product_image" value="<?= $product['image']; ?>">
                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']); ?>">
                <input type="hidden" name="product_price" value="<?= $product['price']; ?>">
                <input type="hidden" name="seller_id" value="<?= $product['seller_id']; ?>">
                <button type="submit" class="btn btn-warning" <?= $quantity_left <= 0 ? 'disabled' : ''; ?>>
                    <?= $quantity_left <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                </button>
            </form>

            <a href="chat.php?product_id=<?= $product['product_id']; ?>" class="btn btn-success mt-3"><i
                    class="fas fa-comment"></i> Chat Now</a>
        </div>
    </div>

    <hr>

    <div class="reviews-section mt-5">
        <h3>Customer Reviews</h3>
        <?php if ($reviews->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($review['name']); ?></strong>
                        <span class="badge bg-warning text-dark">Rating: <?= $review['rating']; ?>/5</span>
                        <p><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        <small class="text-muted">Reviewed on <?= date('F j, Y', strtotime($review['created_at'])); ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No reviews yet. Purchase and collect this item to be the first to review it!</p>
        <?php endif; ?>

        <h4 class="mt-4">Add a Review</h4>
        <p class="text-muted">To leave a review, you must first purchase and collect the item. Please check your
            <a href="../buyer/order-history.php" class="text-warning">Order History</a> page after pickup.
        </p>
    </div>
</div>

<?php include('../includes/footer.php'); ?>