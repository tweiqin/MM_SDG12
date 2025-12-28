<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get ALL user registrations for the last 4 months (including all active users)
    $stmt = $conn->prepare("
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
    $stmt->execute();
    $result = $stmt->get_result();

    $userData = [];
    while ($row = $result->fetch_assoc()) {
        $userData[] = $row;
    }

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

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>