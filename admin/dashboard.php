<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

include('../includes/adminheader.php');
require_once '../config/db.php';


// Initialize variables
$totalUsers = 0;
$totalProducts = 0;
$totalRevenue = 0;
$totalOrders = 0;
$activeProducts = 0;
$pendingOrders = 0;

// Total Users (excluding admin)
$query_users = "SELECT COUNT(*) AS count FROM users WHERE role IN ('buyer', 'seller')";
$result_users = $conn->query($query_users);
if ($result_users) {
    $totalUsers = $result_users->fetch_assoc()['count'];
}

// Total Products
$query_products = "SELECT COUNT(*) AS count FROM products";
$result_products = $conn->query($query_products);
if ($result_products) {
    $totalProducts = $result_products->fetch_assoc()['count'];
}

// Active Products
$query_active_products = "SELECT COUNT(*) AS count FROM products WHERE product_status = 'Available'";
$result_active_products = $conn->query($query_active_products);
if ($result_active_products) {
    $activeProducts = $result_active_products->fetch_assoc()['count'];
}

// Total Revenue (from collected orders)
$query_revenue = "SELECT SUM(total_price) AS revenue FROM orders WHERE order_status = 'collected'";
$result_revenue = $conn->query($query_revenue);
if ($result_revenue) {
    $revenueData = $result_revenue->fetch_assoc();
    $totalRevenue = $revenueData['revenue'] ?? 0;
}

// Total Orders
$query_orders = "SELECT COUNT(*) AS count FROM orders";
$result_orders = $conn->query($query_orders);
if ($result_orders) {
    $totalOrders = $result_orders->fetch_assoc()['count'];
}

// Pending Orders (not collected)
$query_pending = "SELECT COUNT(*) AS count FROM orders WHERE order_status != 'collected'";
$result_pending = $conn->query($query_pending);
if ($result_pending) {
    $pendingOrders = $result_pending->fetch_assoc()['count'];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">📊 Admin Dashboard</h1>
    </div>

    <!-- Section 1: Statistics Overview -->
    <div class="card mb-5 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h4 class="mb-0 text-primary"><i class="fas fa-chart-bar me-2"></i> Platform Statistics</h4>
            <p class="text-muted mb-0 small">Overview of MakanMystery's performance</p>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Stats Cards - Non-clickable -->
                <div class="col-md-3 mb-4">
                    <div class="card border-0 h-100" style="background: #f8f9fa;">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title text-muted mb-2">Total Revenue</h5>
                            <h2 class="mb-0 fw-bold text-dark">RM <?= number_format($totalRevenue, 2); ?></h2>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-info-circle me-1"></i> Collected Orders
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card border-0 h-100" style="background: #f8f9fa;">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                            <h5 class="card-title text-muted mb-2">Total Users</h5>
                            <h2 class="mb-0 fw-bold text-dark"><?= $totalUsers; ?></h2>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-user-tag me-1"></i> Buyers & Sellers
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card border-0 h-100" style="background: #f8f9fa;">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-shopping-cart fa-2x text-warning"></i>
                            </div>
                            <h5 class="card-title text-muted mb-2">Total Orders</h5>
                            <h2 class="mb-0 fw-bold text-dark"><?= $totalOrders; ?></h2>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-clock me-1"></i> <?= $pendingOrders ?> pending
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card border-0 h-100" style="background: #f8f9fa;">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-box fa-2x text-info"></i>
                            </div>
                            <h5 class="card-title text-muted mb-2">Active Products</h5>
                            <h2 class="mb-0 fw-bold text-dark"><?= $activeProducts; ?></h2>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-boxes me-1"></i> <?= $totalProducts ?> total
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Quick Actions -->
    <div class="card mb-5 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h4 class="mb-0 text-primary"><i class="fas fa-bolt me-2"></i> Quick Actions</h4>
            <p class="text-muted mb-0 small">Click on any card below to perform actions</p>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Action Cards - Clickable -->
                <div class="col-md-4 mb-4">
                    <a href="manage-users.php" class="text-decoration-none">
                        <div class="card action-card h-100 border-primary border-2"
                            style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-users fa-3x text-primary"></i>
                                </div>
                                <h4 class="card-title text-primary mb-3">Manage Users</h4>
                                <p class="text-muted mb-4">View, edit, or remove user accounts</p>
                                <div class="action-indicator">
                                    <span class="btn btn-primary px-4">
                                        <i class="fas fa-external-link-alt me-2"></i> Go to Users
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 mb-4">
                    <a href="manage-products.php" class="text-decoration-none">
                        <div class="card action-card h-100 border-success border-2"
                            style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-box fa-3x text-success"></i>
                                </div>
                                <h4 class="card-title text-success mb-3">Manage Products</h4>
                                <p class="text-muted mb-4">Manage food listings and availability</p>
                                <div class="action-indicator">
                                    <span class="btn btn-success px-4">
                                        <i class="fas fa-external-link-alt me-2"></i> Go to Products
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 mb-4">
                    <a href="reports.php" class="text-decoration-none">
                        <div class="card action-card h-100 border-warning border-2"
                            style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-chart-line fa-3x text-warning"></i>
                                </div>
                                <h4 class="card-title text-warning mb-3">View Reports</h4>
                                <p class="text-muted mb-4">Analytics and performance insights</p>
                                <div class="action-indicator">
                                    <span class="btn btn-warning px-4 text-dark">
                                        <i class="fas fa-external-link-alt me-2"></i> View Analytics
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Additional Tools -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h4 class="mb-0 text-primary"><i class="fas fa-tools me-2"></i> Additional Tools</h4>
            <p class="text-muted mb-0 small">Other administrative functions</p>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="#" class="text-decoration-none">
                        <div class="card h-100 border-secondary border-1 hover-shadow">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="flex-shrink-0 me-4">
                                    <i class="fas fa-cog fa-2x text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title text-secondary mb-1">Settings</h5>
                                    <p class="text-muted mb-0 small">Configure system preferences</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chevron-right text-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 mb-3">
                    <a href="#" class="text-decoration-none">
                        <div class="card h-100 border-info border-1 hover-shadow">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="flex-shrink-0 me-4">
                                    <i class="fas fa-receipt fa-2x text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title text-info mb-1">Order Management</h5>
                                    <p class="text-muted mb-0 small">View and manage customer orders</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chevron-right text-info"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .action-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .action-card:hover .action-indicator {
        transform: scale(1.05);
    }

    .hover-shadow {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .hover-shadow:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-2px);
    }

    .card-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        padding: 1.5rem 1.5rem 0.5rem 1.5rem !important;
    }

    .card {
        border-radius: 10px !important;
    }

    .card[style*="background: #f8f9fa;"] {
        border: 1px solid #e9ecef !important;
    }

    .action-card {
        border-width: 2px !important;
    }
</style>

<?php include('../includes/end.php'); ?>