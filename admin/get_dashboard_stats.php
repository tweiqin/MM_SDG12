<?php
header('Content-Type: application/json');

// Database connection
$host = '127.0.0.1';
$dbname = 'mm_sdg12';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stats = [];
    
    // Total Revenue (from collected orders)
    $stmt = $pdo->query("SELECT SUM(total_price) as total_revenue FROM orders WHERE order_status = 'collected'");
    $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
    
    // Total Users (excluding admin)
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role IN ('buyer', 'seller')");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'] ?? 0;
    
    // Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;
    
    // Active Products
    $stmt = $pdo->query("SELECT COUNT(*) as active_products FROM products WHERE product_status = 'Available'");
    $stats['active_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_products'] ?? 0;
    
    echo json_encode($stats);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>