<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header("Location: ../login.php");
    exit();
}

include('../includes/sellerheader.php');


if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $seller_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM products WHERE product_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $product_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $original_price = $_POST['original_price'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $quantity = $_POST['quantity'];
        $description = $_POST['description'];

        // Validate inputs
        if (empty($name) || empty($price) || empty($category) || empty($description)) {
            $error = "All fields are required.";
        } else {
            $image_field = "";
            $param_types = "sddsisii";
            $params = [$name, $original_price, $price, $category, $quantity, $description, $product_id, $seller_id];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $_FILES['image']['name'];
                $target = "../assets/images/" . basename($image);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $image_field = ", image = ?";
                    $param_types = "sddsissii";
                    $params = [$name, $original_price, $price, $category, $quantity, $description, $image, $product_id, $seller_id];
                } else {
                    $error = "Error uploading image.";
                }
            }

            if (!isset($error)) {
                $sql = "UPDATE products SET name = ?, original_price = ?, price = ?, category = ?, quantity = ?, description = ?" . $image_field . ", product_status = 'Available' WHERE product_id = ? AND seller_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($param_types, ...$params);

                if ($stmt->execute()) {
                    echo "<script>alert('Product updated and set to Available!'); window.location='manage-products.php';</script>";
                } else {
                    $error = "Error updating product: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
} else {
    echo "Invalid product ID.";
    exit();
}
?>

<div class="container mt-5">
    <h2>Edit Product</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="name" name="name"
                value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Image</label><br>
            <?php if (!empty($product['image'])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image"
                    width="150" class="img-thumbnail mb-2">
            <?php else: ?>
                <p>No image available.</p>
            <?php endif; ?>
            <input type="file" class="form-control" id="image" name="image">
        </div>
        <div class="mb-3">
            <label for="original_price" class="form-label">Original Price</label>
            <input type="number" class="form-control" id="original_price" name="original_price" step="0.01"
                value="<?php echo htmlspecialchars($product['original_price']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01"
                value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" name="category" required>
                <option value="Hotels" <?= $product['category'] === 'Hotels' ? 'selected' : ''; ?>>Hotels</option>
                <option value="Restaurants & Cafes" <?= $product['category'] === 'Restaurants & Cafes' ? 'selected' : ''; ?>>Restaurants & Cafes</option>
                <option value="Bakeries" <?= $product['category'] === 'Bakeries' ? 'selected' : ''; ?>>Bakeries</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Stock Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity"
                value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"
                required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Product</button>
        <a href="manage-products.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<br>