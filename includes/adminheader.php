<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MM - Admin Dashboard</title>
    <link rel="icon" type="image-icon" href="../assets/images/icon.png">
     <!-- fontawesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>

         body {
            background-color:rgb(228, 228, 231);
        }
        .login-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container .alert {
            margin-bottom: 15px;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
        }
        .table th, .table td {
            text-align: center;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-custom {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <header class="bg-dark">
        <nav class="navbar navbar-expand-lg navbar-dark container sticky-top">
            <a class="navbar-brand" href="#">
                <img src="../assets/images/logo3.png" alt="Makan Mystery" class="logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link " href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-products.php">Manage Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-users.php">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/logout.php">Logout</a>
                    </li>
                    
                </ul>
            </div>
        </nav>
    </header>