<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'mm_sdg12';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get top 5 sellers by sales
    $stmt = $pdo->query("
        SELECT 
            u.name as seller_name,
            COALESCE(SUM(o.total_price), 0) as total_sales
        FROM users u
        LEFT JOIN products p ON u.user_id = p.seller_id
        LEFT JOIN order_items oi ON p.product_id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'collected'
        WHERE u.role = 'seller'
        GROUP BY u.user_id, u.name
        ORDER BY total_sales DESC
        LIMIT 5
    ");
    
    $sellersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sellers = [];
    $sales = [];
    
    foreach ($sellersData as $row) {
        $sellers[] = $row['seller_name'];
        $sales[] = (float)$row['total_sales'];
    }
    
    echo json_encode([
        'sellers' => $sellers,
        'sales' => $sales
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>