<?php
session_start();
require_once '../config/db.php';
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

// Fetch Product Details and Seller Info
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

// Fetch average rating and total reviews (New Logic)
$rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?";
$rating_stmt = $conn->prepare($rating_query);
$rating_stmt->bind_param('i', $product_id);
$rating_stmt->execute();
$rating_data = $rating_stmt->get_result()->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'];
$rating_stmt->close();

// Handle Sorting (New Logic)
$sort_option = $_GET['sort'] ?? 'newest';
$order_by = "r.created_at DESC"; // Default

if ($sort_option == 'highest') {
    $order_by = "r.rating DESC, r.created_at DESC";
} elseif ($sort_option == 'lowest') {
    $order_by = "r.rating ASC, r.created_at DESC";
}

// Fetch Reviews with Sorting
$reviews_query = "SELECT r.*, u.name FROM reviews r 
                  JOIN users u ON r.user_id = u.user_id 
                  WHERE r.product_id = ? ORDER BY $order_by";
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

    <div id="reviews" class="reviews-section mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3>Customer Reviews (<?= $total_reviews; ?>)</h3>
                <div class="text-warning">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= round($avg_rating)) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i - 0.5 == $avg_rating) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                    <span class="text-dark ms-2" style="font-size: 0.9em; font-weight: bold;"><?= $avg_rating; ?> out of
                        5</span>
                </div>
            </div>

            <!-- Sorting Form -->
            <div class="d-flex align-items-center">
                <label for="sort" class="me-2 text-nowrap">Sort by:</label>
                <select id="sort" class="form-select form-select-sm"
                    onchange="window.location.href='?product_id=<?= $product_id; ?>&sort=' + this.value + '#reviews'">
                    <option value="newest" <?= $sort_option == 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="highest" <?= $sort_option == 'highest' ? 'selected' : ''; ?>>Highest Rating</option>
                    <option value="lowest" <?= $sort_option == 'lowest' ? 'selected' : ''; ?>>Lowest Rating</option>
                </select>
            </div>
        </div>

        <?php if ($reviews_result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($review['name']); ?></strong>
                            <small class="text-muted"><?= date('F j, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <div class="text-warning mb-2">
                            <?php
                            $user_rating = $review['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $user_rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No reviews yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>