<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get top 5 sellers by sales
    $stmt = $conn->prepare("
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
    $stmt->execute();
    $result = $stmt->get_result();

    $sellers = [];
    $sales = [];

    while ($row = $result->fetch_assoc()) {
        $sellers[] = $row['seller_name'];
        $sales[] = (float) $row['total_sales'];
    }

    echo json_encode([
        'sellers' => $sellers,
        'sales' => $sales
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>