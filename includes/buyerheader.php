<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<?php include('../config/db.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consumer Dashboard</title>
    <link rel="icon" type="image-icon" href="../assets/images/icon.png">
    <!-- fontawesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

</head>

<body>
    <header class="bg-dark">
        <nav class="navbar navbar-expand-lg navbar-dark container sticky-top">
            <a class="navbar-brand" href="../pages/index.php">
                <img src="../assets/images/logo3.png" alt="Makan Mystery" class="logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/search.php">Mystery Boxes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="../buyer/buyerdashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/message.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../buyer/profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/cart.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../buyer/order-history.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>