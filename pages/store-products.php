<?php
session_start();
require_once '../config/db.php';
include('../includes/header.php'); 

if (!isset($_GET['seller_id']) || !is_numeric($_GET['seller_id'])) {
    echo "<div class='container my-5 alert alert-danger'>Invalid Seller ID.</div>";
    include('../includes/footer.php');
    exit;
}

$seller_id = intval($_GET['seller_id']);

// 1. Fetch Seller Name (for display title)
$name_query = "SELECT name FROM users WHERE user_id = ?";
$stmt_name = $conn->prepare($name_query);
$stmt_name->bind_param('i', $seller_id);
$stmt_name->execute();
$seller_name = $stmt_name->get_result()->fetch_assoc()['name'] ?? 'Vendor';
$stmt_name->close();

// 2. Fetch all ACTIVE products including the 'quantity' and 'seller_id'
$products_query = "SELECT product_id, name, price, original_price, image, quantity, seller_id FROM products 
                   WHERE seller_id = ? AND product_status = 'Available'
                   ORDER BY created_at DESC";
$stmt = $conn->prepare($products_query);
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$products_result = $stmt->get_result();
$stmt->close();
?>

<div class="container my-5">
    <h1 class="text-center mb-4">Mystery Boxes from <?= htmlspecialchars($seller_name); ?></h1>

    <section class="featured-products py-3 bg-light">
        <div class="container text-center">
            <?php if ($products_result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($product = $products_result->fetch_assoc()): 
                        // Now $product['quantity'] exists and is a number
                        $original_price = (float)($product['original_price'] ?? $product['price']);
                        $selling_price = (float)$product['price'];
                        $is_discounted = ($original_price > $selling_price && $original_price > 0);
                        
                        // Ensure quantity is treated as an integer
                        $quantity_left = (int)$product['quantity']; 
                    ?>
                        <div class="col-md-3 mb-4">
                            <div class="card product-card">
                                <img src="../assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'default.jpg'; ?>"
                                    class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>

                                    <p class="card-text">
                                        <?php if ($is_discounted): ?>
                                            <span style="text-decoration: line-through; color: #888; margin-right: 5px;">
                                                RM<?= number_format($original_price, 2); ?>
                                            </span>
                                            <span style="color: red; font-weight: bold;">
                                                RM<?= number_format($selling_price, 2); ?> (Sale!)
                                            </span>
                                        <?php else: ?>
                                            RM<?= number_format($selling_price, 2); ?>
                                        <?php endif; ?>
                                    </p>

                                    <a href="product-detail.php?product_id=<?= $product['product_id']; ?>"
                                        class="btn btn-outline-secondary" style="border-radius: 20px;">View Details</a>

                                    <form action="add-to-cart.php" method="POST" class="mt-2">
                                        <input type="hidden" name="product_image" value="<?= $product['image']; ?>">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                                        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']); ?>">
                                        <input type="hidden" name="product_price" value="<?= $product['price']; ?>">
                                        <input type="hidden" name="seller_id" value="<?= $product['seller_id']; ?>">
                                        <button type="submit" class="btn btn-success" <?= $quantity_left <= 0 ? 'disabled' : ''; ?>>
                                            <?= $quantity_left <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">This vendor currently has no Mystery Boxes available.</div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include('../includes/footer.php'); ?>