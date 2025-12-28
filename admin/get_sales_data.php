<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get sales for current month + last 3 months
    $currentYearMonth = date('Y-m');

    // Get sales for current month (partial)
    $currentMonthStmt = $conn->prepare("
        SELECT 
            SUM(total_price) as monthly_sales,
            COUNT(*) as order_count
        FROM orders 
        WHERE order_status = 'collected'
          AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $currentMonthStmt->bind_param("s", $currentYearMonth);
    $currentMonthStmt->execute();
    $result = $currentMonthStmt->get_result();
    $currentMonth = $result->fetch_assoc();

    // Get sales for previous 3 months
    // Note: DATE_FORMAT in WHERE clause can be string compared safely here
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month_sort,
            DATE_FORMAT(created_at, '%b %Y') as month_display,
            SUM(total_price) as monthly_sales,
            COUNT(*) as order_count
        FROM orders 
        WHERE order_status = 'collected'
          AND DATE_FORMAT(created_at, '%Y-%m') < ?
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
        ORDER BY month_sort DESC
        LIMIT 3
    ");
    $stmt->bind_param("s", $currentYearMonth);
    $stmt->execute();
    $result = $stmt->get_result();

    $previousMonths = [];
    while ($row = $result->fetch_assoc()) {
        $previousMonths[] = $row;
    }
    $previousMonths = array_reverse($previousMonths); // Oldest first

    // Build complete 4-month array
    $months = [];
    $sales = [];
    $orderCounts = [];

    // Add previous months
    foreach ($previousMonths as $month) {
        $months[] = $month['month_display'];
        $sales[] = (float) $month['monthly_sales'];
        $orderCounts[] = (int) $month['order_count'];
    }

    // Add current month
    $months[] = date('M Y');
    $sales[] = (float) ($currentMonth['monthly_sales'] ?? 0);
    $orderCounts[] = (int) ($currentMonth['order_count'] ?? 0);

    // If have less than 4 months total, fill with previous months
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

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>