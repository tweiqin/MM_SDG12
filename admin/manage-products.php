<?php
include('../includes/adminheader.php');
require_once '../config/db.php';

// FIX: 1. Add product_status to the SELECT query.
$query = "SELECT p.*, p.product_status, u.name AS seller_name 
          FROM products p 
          JOIN users u ON p.seller_id = u.user_id";
$result = $conn->query($query);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Products</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()):
                        // Determine status color for inline CSS
                        $status = htmlspecialchars($product['product_status']);
                        $status_color = ($status === 'Unavailable' ? 'red' : 'green');
                        $button_text = ($status === 'Unavailable' ? 'Activate' : 'Deactivate');
                        $button_class = ($status === 'Unavailable' ? 'btn-success' : 'btn-danger');
                        ?>
                        <tr>
                            <td><?= $product['product_id']; ?></td>
                            <td>
                                <a href="product-detail-view.php?product_id=<?= $product['product_id']; ?>"
                                    style="font-weight: bold; color: #274081; text-decoration: none;">
                                    <?= htmlspecialchars($product['name']); ?>
                                </a>
                            </td>

                            <td>
                                <a href="seller-profile-view.php?seller_id=<?= $product['seller_id']; ?>"
                                    style="color: #737373; text-decoration: none;">
                                    <?= htmlspecialchars($product['seller_name']); ?>
                                </a>
                            </td>
                            <td>RM <?= number_format($product['price'], 2); ?></td>
                            <td><?= $product['quantity']; ?></td>

                            <td style="color: <?= $status_color; ?>; font-weight: bold;">
                                <?= $status; ?>
                            </td>

                            <td>
                                <a href="edit-product.php?id=<?= $product['product_id']; ?>"
                                    class="btn btn-warning btn-sm btn-custom">Edit</a>

                                <a href="delete-product.php?id=<?= $product['product_id']; ?>"
                                    class="btn <?= $button_class; ?> btn-sm btn-custom"
                                    onclick="return confirm('Are you sure you want to change the status of this product?');">
                                    <?= $button_text; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 ">
        <a href="dashboard.php" class="btn btn-success">Back to Dashboard</a>
    </div>
    <br>
</div>