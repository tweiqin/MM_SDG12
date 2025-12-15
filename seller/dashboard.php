<?php
//  include database connection
include('../includes/sellerheader.php');
require_once '../config/db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// --- METRIC 1: Total Sales (Number of orders completed/received) ---
$total_sales_query = "SELECT COUNT(DISTINCT o.order_id) as total_sales 
                      FROM orders o
                      JOIN order_items oi ON o.order_id = oi.order_id
                      JOIN products p ON oi.product_id = p.product_id
                      WHERE p.seller_id = ?";
$stmt_sales = $conn->prepare($total_sales_query);
$stmt_sales->bind_param("i", $user_id);
$stmt_sales->execute();
$total_sales = $stmt_sales->get_result()->fetch_assoc()['total_sales'] ?? 0;
$stmt_sales->close();

// --- METRIC 2: Revenue (Total amount earned) ---
$revenue_query = "SELECT SUM(oi.quantity * oi.price) as total_revenue 
                  FROM orders o
                  JOIN order_items oi ON o.order_id = oi.order_id
                  JOIN products p ON oi.product_id = p.product_id
                  WHERE p.seller_id = ?";
$stmt_revenue = $conn->prepare($revenue_query);
$stmt_revenue->bind_param("i", $user_id);
$stmt_revenue->execute();
$total_revenue = $stmt_revenue->get_result()->fetch_assoc()['total_revenue'] ?? 0;
$stmt_revenue->close();

// --- METRIC 3 & 4: Meals Saved / CO2 Saved (Total quantity sold) ---
$meals_sold_query = "SELECT SUM(oi.quantity) as total_meals_saved 
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.product_id
                     WHERE p.seller_id = ?";
$stmt_meals = $conn->prepare($meals_sold_query);
$stmt_meals->bind_param("i", $user_id);
$stmt_meals->execute();
$total_meals_saved = $stmt_meals->get_result()->fetch_assoc()['total_meals_saved'] ?? 0;
$stmt_meals->close();

// Calculate CO2 Saved: Assume 1 meal = 0.7kg waste, saves 1.75 kg CO2e
// CO2e = (Total Meals Sold) * 1.75 kg CO2e/meal
$co2_saved = number_format($total_meals_saved * 1.75, 2);
?>

<div class="container my-5">
    <h1 class="text-center mb-4">Vendor Dashboard</h1>
    
    <div class="row g-4 mb-5">
        
        <div class="col-md-3">
            <div class="card text-center shadow-sm" style="height: 100%; border-radius: 12px;">
                <div class="card-body">
                    <i class="fas fa-box fa-2x float-end text-muted"></i>
                    <h4 class="text-start mb-0">Total Sales</h4>
                    <h2 class="text-start fw-bold mt-2 text-primary"><?= $total_sales; ?></h2>
                    <p class="text-start text-muted mb-0 small">Total Orders Received</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm" style="height: 100%; border-radius: 12px;">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-2x float-end text-muted"></i>
                    <h4 class="text-start mb-0">Revenue</h4>
                    <h2 class="text-start fw-bold mt-2 text-warning">RM<?= number_format($total_revenue, 2); ?></h2>
                    <p class="text-start text-muted mb-0 small">Total Amount Earned</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm" style="height: 100%; border-radius: 12px;">
                <div class="card-body">
                    <i class="fas fa-utensils fa-2x float-end text-muted"></i>
                    <h4 class="text-start mb-0">Meals Saved</h4>
                    <h2 class="text-start fw-bold mt-2 text-info"><?= $total_meals_saved; ?></h2>
                    <p class="text-start text-muted mb-0 small">Quantity of Food Sold</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm" style="height: 100%; border-radius: 12px;">
                <div class="card-body">
                    <i class="fas fa-leaf fa-2x float-end text-muted"></i>
                    <h4 class="text-start mb-0">CO₂ Saved</h4>
                    <h2 class="text-start fw-bold mt-2 text-success"><?= $co2_saved; ?> kg</h2>
                    <p class="text-start text-muted mb-0 small">Environmental Impact</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container mb-5">
        <h2 class="text-center mb-4">Orders Received</h2>
        <div class="table-responsive">
             <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Box Name</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Order Date & Time</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch detailed orders related to seller's products (SECURELY)
                    $orders_query = "
                    SELECT o.order_id, p.name AS product_name, oi.quantity, o.created_at, o.order_status
                    FROM orders o
                    INNER JOIN order_items oi ON o.order_id = oi.order_id
                    INNER JOIN products p ON oi.product_id = p.product_id
                    WHERE p.seller_id = ?
                    ORDER BY o.created_at DESC
                ";
                    $stmt_orders = $conn->prepare($orders_query);
                    $stmt_orders->bind_param("i", $user_id);
                    $stmt_orders->execute();
                    $orders_result = $stmt_orders->get_result();

                    if ($orders_result->num_rows > 0) {
                        while ($row = $orders_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['order_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . $row['quantity'] . "</td>";
                            echo "<td>" . date("d M Y H:i", strtotime($row['created_at'])) . "</td>";
                            echo "<td><span class='badge bg-" .
                                ($row['order_status'] == 'completed' ? 'success' :
                                    ($row['order_status'] == 'pending' ? 'warning text-dark' : 'secondary')) .
                                "'>" . ucfirst($row['order_status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No orders found.</td></tr>";
                    }
                    $stmt_orders->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>