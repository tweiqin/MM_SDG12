<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../pages/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    echo "<div class='container mt-5 alert alert-danger'>Product ID is missing.</div>";
    exit;
}

// Get seller_id and seller name from product
$stmt = $conn->prepare("SELECT p.seller_id, u.name AS seller_name, p.name AS product_name FROM products p JOIN users u ON p.seller_id = u.user_id WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='container mt-5 alert alert-danger'>Product not found.</div>";
    exit;
}

$seller_id = $product['seller_id'];
$product_name = htmlspecialchars($product['product_name']);
$seller_name = htmlspecialchars($product['seller_name']);

if ($role === 'buyer') {
    $buyer_id = $user_id;
    if ($seller_id == $buyer_id) {
        echo "<div class='container mt-5 alert alert-warning'>You cannot chat about your own product.</div>";
        exit;
    }
    $chat_partner_name = "Seller";
    $chat_partner_display = $seller_name;
    include('../includes/buyerheader.php');
} elseif ($role === 'seller') {
    $buyer_id = $_GET['buyer_id'] ?? null;

    if (!$buyer_id) {
        echo "<div class='container mt-5 alert alert-warning'>You cannot initiate chat as seller without a buyer ID.</div>";
        exit;
    }
    if ($user_id != $seller_id) {
        echo "<div class='container mt-5 alert alert-warning'>Unauthorized access.</div>";
        exit;
    }
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $buyer = $stmt->get_result()->fetch_assoc();

    if (!$buyer) {
        echo "<div class='container mt-5 alert alert-warning'>Buyer not found.</div>";
        exit;
    }

    $chat_partner_name = "Buyer";
    $chat_partner_display = $buyer['name'];
    include('../includes/sellerheader.php');
} else {
    echo "<div class='container mt-5 alert alert-danger'>Invalid role.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty(trim($_POST['message']))) {
    $message = trim($_POST['message']);
    $sender_id = $user_id;
    $receiver_id = ($sender_id == $buyer_id) ? $seller_id : $buyer_id;

    $stmt = $conn->prepare("INSERT INTO messages (product_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $sender_id, $receiver_id, $message);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT m.*, u.name FROM messages m JOIN users u ON m.sender_id = u.user_id WHERE m.product_id = ? AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)) ORDER BY m.created_at ASC");
$stmt->bind_param("iiiii", $product_id, $buyer_id, $seller_id, $seller_id, $buyer_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<style>
    body {
        background-color: #f1f1f1;
    }

    .chat-box {
        max-width: 800px;
        margin: 20px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        height: 80vh;
    }

    .chat-header {
        padding: 20px;
        background-color: #004d40;
        color: white;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .chat-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #f9f9f9;
        display: flex;
        flex-direction: column;
    }

    .chat-message {
        max-width: 70%;
        margin-bottom: 12px;
        padding: 12px 18px;
        border-radius: 20px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .mine {
        background-color: #c8e6c9;
        margin-left: auto;
        align-self: flex-end;
    }

    .theirs {
        background-color: #eceff1;
        margin-right: auto;
        align-self: flex-start;
    }

    .chat-footer {
        padding: 15px;
        background-color: #eeeeee;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
</style>

<div class="chat-box">
    <div class="chat-header">
        <h5 class="mb-1">Chat about: <?= $product_name ?></h5>
        <small>You are chatting with <?= $chat_partner_name ?>:
            <a href="seller-profile.php?seller_id=<?= $seller_id; ?>" class="text-white fw-bold">
                <strong><?= htmlspecialchars($chat_partner_display) ?></strong>
            </a>
        </small>
    </div>

    <div class="chat-body">
        <?php while ($msg = $messages->fetch_assoc()): ?>
            <div class="chat-message <?= $msg['sender_id'] == $user_id ? 'mine' : 'theirs' ?>">
                <div><strong><?= htmlspecialchars($msg['name']) ?>:</strong></div>
                <div><?= htmlspecialchars($msg['message']) ?></div>
                <div class="text-muted small mt-1 text-end"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="chat-footer">
        <form method="POST" class="d-flex">
            <input type="text" name="message" class="form-control me-2" placeholder="Type your message..." required>
            <button class="btn btn-success">Send</button>
        </form>
    </div>
</div>

</body>

</html>