<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('../includes/buyerheader.php'); ?>
    <div class="container text-center my-5">
        <h1 class="text-success">Thank You!</h1>
        <p style="font-size:20px">Your order has been successfully placed. We will contact you soon with further
            details.</p>
        <a href="index.php" class="btn btn-success" style="font-size:18px">Continue Shopping</a>
        <a href="../buyer/order-history.php" class="btn btn-primary ms-2" style="font-size:18px">View Order History</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>