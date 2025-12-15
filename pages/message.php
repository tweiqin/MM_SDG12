<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();}
require_once '../config/db.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../pages/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; 


if ($role === 'buyer') {
   
    $stmt = $conn->prepare("
        SELECT m.product_id, m.receiver_id AS user_id, u.name AS other_user, p.name AS product_name
        FROM messages m
        JOIN users u ON m.receiver_id = u.user_id
        JOIN products p ON m.product_id = p.product_id
        WHERE m.sender_id = ?
        GROUP BY m.product_id, m.receiver_id
        ORDER BY MAX(m.created_at) DESC
    ");
    $stmt->bind_param("i", $user_id);
} else {
    
    $stmt = $conn->prepare("
        SELECT m.product_id, m.sender_id AS user_id, u.name AS other_user, p.name AS product_name
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        JOIN products p ON m.product_id = p.product_id
        WHERE m.receiver_id = ?
        GROUP BY m.product_id, m.sender_id
        ORDER BY MAX(m.created_at) DESC
    ");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$results = $stmt->get_result();
?>



<?php
// Include chat info header
if ($role === 'buyer') {
    include('../includes/buyerheader.php');
} else {
    include('../includes/sellerheader.php');
}
?>

<div class="container my-5">
    <h3 class="mb-4"><?= ucfirst($role); ?> Message Centre</h3>

    <?php if ($results->num_rows > 0): ?>
        <?php while ($row = $results->fetch_assoc()): ?>
            <div class="message-box">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($row['product_name']); ?></h5>
                        <p class="mb-1 text-muted">
                            <?= ($role === 'buyer') ? "Seller" : "Buyer" ?>: 
                            <strong><?= htmlspecialchars($row['other_user']); ?></strong>
                        </p>
                    </div>
                    <a 
                        class="btn btn-sm btn-primary" 
                        href="chat.php?product_id=<?= $row['product_id']; ?>&<?= $role === 'buyer' ? 'seller_id' : 'buyer_id'; ?>=<?= $row['user_id']; ?>"
                    >
                        Open Chat
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-muted">No conversations found yet.</p>
    <?php endif; ?>
</div>