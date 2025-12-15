<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        header("Location: manage-products.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $error = "";

        $updateQuery = "UPDATE products SET name = ?, description = ? WHERE product_id = ?";
        $updateStmt = $conn->prepare($updateQuery);

        $updateStmt->bind_param("ssi", $name, $description, $productId);

        if ($updateStmt->execute()) {
            $updateStmt->close();
            header("Location: manage-products.php?status=edited");
            exit;
        } else {
            $error = "Error updating product: " . $conn->error;
            $updateStmt->close();
        }
    }
} else {
    header("Location: manage-products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: bold;
        }

        .btn-custom {
            background-color: #28a745;
            color: white;

        }

        .btn-custom:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Edit Product</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name (for correction)</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']); ?>"
                    required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Product Description (for correction)</label>
                <textarea class="form-control" id="description" name="description" rows="4"
                    required><?= htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Current Selling Price</label>
                <input type="text" class="form-control" value="RM <?= number_format($product['price'], 2); ?>" disabled>
                <small class="form-text text-muted">Only the Seller can change the price.</small>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-custom">Save Corrections</button>
                <a href="manage-products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>