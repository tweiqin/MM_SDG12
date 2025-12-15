<?php
session_start();
require_once '../config/db.php';
include('../includes/adminheader.php');

if (!isset($_GET['seller_id']) || !is_numeric($_GET['seller_id'])) {
    echo "<div class='container my-5 alert alert-danger'>Invalid Seller ID.</div>";
    include('../includes/footer.php');
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

$seller_id = intval($_GET['seller_id']);

$query = "SELECT name, email, phone, address, is_active FROM users WHERE user_id = ? AND role = 'seller'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$stmt->close();

if (!$seller) {
    echo "<div class='container my-5 alert alert-warning'>Seller profile not found.</div>";
    include('../includes/footer.php');
    exit;
}

$is_active = $seller['is_active'];
$status_text = ($is_active == 1) ? 'Active' : 'Blocked';
$status_color = ($is_active == 1) ? 'green' : 'red';
?>

<div class="container my-5">
    <h2 class="text-center mb-4">Seller Profile: <?= htmlspecialchars($seller['name']); ?></h2>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    Contact and Business Information
                </div>
                <div class="card-body">
                    <p>
                        <strong>Account Status:</strong>
                        <span style="color: <?= $status_color; ?>; font-weight: bold;">
                            <?= $status_text; ?>
                        </span>
                    </p>

                    <p><strong>Seller Name:</strong> <?= htmlspecialchars($seller['name']); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($seller['email']); ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($seller['phone']); ?></p>
                    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($seller['address'])); ?></p>

                    <hr>
                    <a href="javascript:history.back()" class="btn btn-secondary">Back to Management</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>