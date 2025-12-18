<?php
include('../includes/sellerheader.php');
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header("Location: ../login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$sql = "SELECT *, product_status FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($sql);  // Prepare statement to prevent SQL injection
$stmt->bind_param('i', $seller_id);  // Bind the seller's ID to the query
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container mt-5">
    <h2 class="mb-4">Your Mystery Boxes</h2>

    <!-- Check if the seller has products listed -->
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Ori Price</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo 'RM' . htmlspecialchars($row['original_price']); ?></td>
                        <td><?php echo 'RM' . htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>

                        <td
                            style="color: <?= $row['product_status'] === 'Unavailable' ? 'red' : 'green'; ?>; font-weight: bold;">
                            <?= htmlspecialchars($row['product_status']); ?>
                        </td>

                        <td>
                            <a href="edit-product.php?id=<?php echo $row['product_id']; ?>"
                                class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete-product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to deactivate this product?');">
                                Deactivate </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No products found. <a href="add-product.php" class="btn btn-success">Upload Mystery Box</a></p>
    <?php endif; ?>

</div>