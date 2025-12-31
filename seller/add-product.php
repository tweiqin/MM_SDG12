<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    header('Location: ../index.php');
    exit();
}

include('../includes/sellerheader.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $original_price = $_POST['original_price'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $target = "../assets/images/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {

        $seller_id = $_SESSION['user_id'];

        $sql = "INSERT INTO products (name, original_price, price, category, description, image, quantity, seller_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {

            $stmt->bind_param("sddsssis", $name, $original_price, $price, $category, $description, $image, $quantity, $seller_id);

            if ($stmt->execute()) {
                echo "<script>alert('Product added successfully!'); window.location='manage-products.php';</script>";
            } else {
                echo "<script>alert('Error adding product.');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Database error.');</script>";
        }
    } else {
        echo "<script>alert('Error uploading image.');</script>";
    }
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Define the entire form sequence and its dependencies
        const fieldSequence = [
            { id: 'name', type: 'text', nextId: 'original_price', isMandatory: true },
            { id: 'original_price', type: 'number', nextId: 'price', isMandatory: false },
            { id: 'price', type: 'number', nextId: 'category', isMandatory: true },
            { id: 'category', type: 'select', nextId: 'quantity', isMandatory: true },
            { id: 'quantity', type: 'number', nextId: 'description', isMandatory: true },
            { id: 'description', type: 'textarea', nextId: 'image_input', isMandatory: false },
            { id: 'image_input', type: 'file', nextId: 'publish_btn', isMandatory: true } // Final step
        ];

        const descriptionBox = document.getElementById('description');
        const publishButton = document.getElementById('publish_btn');

        // --- TEMPLATE LOGIC ---
        const template =
            "Ops Hours: 11:00 - 21:00\n" +
            "Pick-up Time: 17:00 - 18:00\n" +
            "";

        window.injectTemplate = function (forceReset = false) {
            if (descriptionBox && (descriptionBox.value.trim() === '' || forceReset)) {
                descriptionBox.value = template;
            }
        }
        window.resetTemplate = function () {
            if (confirm('Are you sure you want to reset the description? You will lose the current text.')) {
                injectTemplate(true);
            }
        }
        injectTemplate();

        // Core Validity Check
        function isFieldValid(element, isMandatory) {
            if (!element) return false;
            const value = element.value.trim();

            // Mandatory fields must have *some* value
            if (isMandatory) {
                // Check select boxes for default option (which has value="")
                if (element.tagName === 'SELECT') {
                    return value !== '';
                }
                return value !== '';
            }
            // Optional fields are always valid (pass this check)
            return true;
        }

        // Main State Update Function
        function updateFieldState() {
            let currentFieldEnabled = true;
            let allMandatoryFieldsValid = true;

            for (let i = 0; i < fieldSequence.length; i++) {
                const field = fieldSequence[i];
                const currentInput = document.getElementById(field.id);
                const nextFieldElement = document.getElementById(field.nextId);
                const isMandatory = field.isMandatory;

                if (!currentInput) continue;

                const inputContainer = currentInput.closest('.form-group') || currentInput.closest('div');

                if (i > 0) {
                    // Determine if this field should be enabled based on the PRECEDING field's validity
                    const precedingField = fieldSequence[i - 1];
                    const precedingInput = document.getElementById(precedingField.id);

                    // If preceding field is mandatory AND not valid, we stop enabling the chain.
                    const precedingMandatoryAndInvalid = precedingField.isMandatory && !isFieldValid(precedingInput, true);

                    if (precedingMandatoryAndInvalid) {
                        currentFieldEnabled = false;
                    }
                }

                if (currentFieldEnabled) {
                    // Remove disabled and opacity
                    currentInput.removeAttribute('disabled');
                    currentInput.removeAttribute('readonly'); // Ensure Description is editable
                    if (inputContainer) inputContainer.style.opacity = 1;
                } else {
                    // Apply disabled and gray out
                    currentInput.setAttribute('disabled', true);
                    if (inputContainer) inputContainer.style.opacity = 0.5;
                }

                // Track overall form validity
                if (isMandatory && !isFieldValid(currentInput, true)) {
                    allMandatoryFieldsValid = false;
                }
            }

            // Final Button Check: Should be enabled only if ALL mandatory fields are valid
            if (allMandatoryFieldsValid) {
                publishButton.removeAttribute('disabled');
            } else {
                publishButton.setAttribute('disabled', true);
            }
        }

        // Attach listeners to trigger update on every change/input
        fieldSequence.forEach(field => {
            const input = document.getElementById(field.id);
            if (input) {
                input.addEventListener('input', updateFieldState);
                input.addEventListener('change', updateFieldState);
            }
        });

        // Run once on load to set initial state (e.g., if sticky form data exists)
        updateFieldState();
    });
</script>

<section class="add-product py-5">
    <div class="container">
        <h2 class="text-center mb-4">Add New Mystery Box:</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group mb-3" id="name_group">
                <label for="name">Product Name:</label>
                <input type="text" class="form-control" name="name" id="name" required
                    value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="form-group mb-3" id="original_price_group" style="opacity: 0.5;">
                <label for="original_price">Original Price (Optional):</label>
                <input type="number" class="form-control" name="original_price" id="original_price" step="0.01"
                    value="<?= htmlspecialchars($_POST['original_price'] ?? ''); ?>" disabled>
            </div>

            <div class="form-group mb-3" id="price_group" style="opacity: 0.5;">
                <label for="price">Price (Discounted/Selling Price):</label>
                <input type="number" class="form-control" name="price" id="price" step="0.01" required
                    value="<?= htmlspecialchars($_POST['price'] ?? ''); ?>" disabled>
            </div>

            <div class="form-group mb-3" id="category_group" style="opacity: 0.5;">
                <label for="category">Category:</label>
                <select class="form-control" name="category" id="category" required disabled>
                    <option value="">-- Select Category --</option>
                    <option value="Hotels" <?= (isset($_POST['category']) && $_POST['category'] == 'Hotels') ? 'selected' : ''; ?>>Hotels</option>
                    <option value="Restaurants & Cafes" <?= (isset($_POST['category']) && $_POST['category'] == 'Restaurants & Cafes') ? 'selected' : ''; ?>>Restaurants & Cafes</option>
                    <option value="Bakeries" <?= (isset($_POST['category']) && $_POST['category'] == 'Bakeries') ? 'selected' : ''; ?>>Bakeries</option>
                </select>
            </div>

            <div class="form-group mb-3" id="quantity_group" style="opacity: 0.5;">
                <label for="quantity">Stock Quantity:</label>
                <input type="number" class="form-control" name="quantity" id="quantity" min="1" required
                    value="<?= htmlspecialchars($_POST['quantity'] ?? ''); ?>" disabled>
            </div>

            <div id="description_group" class="form-group mb-3" style="opacity: 0.5;">
                <label for="description">Description (Optional):</label>
                <textarea class="form-control" name="description" id="description" rows="8"
                    readonly><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <small class="form-text text-muted">Mandatory pick-up/dine-in details are pre-filled (edit details when
                    necessary).</small>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="resetTemplate()">Reset
                    Template</button>
            </div>

            <div id="image_group" class="form-group mb-4" style="opacity: 0.5;">
                <label for="image">Product Image:</label>
                <input type="file" class="form-control-file" name="image" id="image_input" required disabled>
            </div>

            <button type="submit" id="publish_btn" class="btn btn-warning w-100" disabled>
                <i class="fas fa-upload" style="margin-right: 8px;"></i> Publish Mystery Box
            </button>
        </form>
    </div>
</section>