<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'mm_sdg12';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get ALL user registrations for the last 4 months (including all active users)
    // This shows users who registered, regardless of their activity
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%b %Y') as month_display,
            DATE_FORMAT(created_at, '%Y-%m') as month_sort,
            SUM(CASE WHEN role = 'buyer' THEN 1 ELSE 0 END) as buyers,
            SUM(CASE WHEN role = 'seller' THEN 1 ELSE 0 END) as sellers,
            COUNT(*) as total_users
        FROM users 
        WHERE role IN ('buyer', 'seller')  -- Include both buyers and sellers
          AND is_active = 1                -- Only active users
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 4 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
        ORDER BY month_sort
    ");

    $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If have less than 4 months of data, fill in empty months
    $allMonths = [];
    for ($i = 3; $i >= 0; $i--) {
        $allMonths[] = date('M Y', strtotime("-$i months"));
    }

    // Initialize arrays with zeros for all months
    $months = [];
    $buyersData = [];
    $sellersData = [];
    $totalData = [];

    // Create a map of existing data
    $dataMap = [];
    foreach ($userData as $row) {
        $dataMap[$row['month_display']] = $row;
    }

    // Fill data for all 4 months
    foreach ($allMonths as $month) {
        $months[] = $month;

        if (isset($dataMap[$month])) {
            $buyersData[] = (int) $dataMap[$month]['buyers'];
            $sellersData[] = (int) $dataMap[$month]['sellers'];
            $totalData[] = (int) $dataMap[$month]['total_users'];
        } else {
            $buyersData[] = 0;
            $sellersData[] = 0;
            $totalData[] = 0;
        }
    }

    echo json_encode([
        'months' => $months,
        'buyers' => $buyersData,
        'sellers' => $sellersData,
        'total' => $totalData
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>