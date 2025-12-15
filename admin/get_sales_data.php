<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'mm_sdg12';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // OPTION 3: Get sales for current month + last 3 months
    $currentYearMonth = date('Y-m');
    
    // Get sales for current month (partial)
    $currentMonthStmt = $pdo->prepare("
        SELECT 
            SUM(total_price) as monthly_sales,
            COUNT(*) as order_count
        FROM orders 
        WHERE order_status = 'collected'
          AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $currentMonthStmt->execute([$currentYearMonth]);
    $currentMonth = $currentMonthStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get sales for previous 3 months
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month_sort,
            DATE_FORMAT(created_at, '%b %Y') as month_display,
            SUM(total_price) as monthly_sales,
            COUNT(*) as order_count
        FROM orders 
        WHERE order_status = 'collected'
          AND DATE_FORMAT(created_at, '%Y-%m') < '$currentYearMonth'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
        ORDER BY month_sort DESC
        LIMIT 3
    ");
    
    $previousMonths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $previousMonths = array_reverse($previousMonths); // Oldest first
    
    // Build complete 4-month array
    $months = [];
    $sales = [];
    $orderCounts = [];
    
    // Add previous months
    foreach ($previousMonths as $month) {
        $months[] = $month['month_display'];
        $sales[] = (float)$month['monthly_sales'];
        $orderCounts[] = (int)$month['order_count'];
    }
    
    // Add current month
    $months[] = date('M Y');
    $sales[] = (float)($currentMonth['monthly_sales'] ?? 0);
    $orderCounts[] = (int)($currentMonth['order_count'] ?? 0);
    
    // If we have less than 4 months total, fill with previous months
    if (count($months) < 4) {
        $needed = 4 - count($months);
        for ($i = 0; $i < $needed; $i++) {
            $date = date('M Y', strtotime('-' . (count($months) + $i) . ' months'));
            array_unshift($months, $date);
            array_unshift($sales, 0);
            array_unshift($orderCounts, 0);
        }
    }
    
    echo json_encode([
        'months' => $months,
        'sales' => $sales,
        'order_counts' => $orderCounts,
        'note' => 'Includes current month data'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>