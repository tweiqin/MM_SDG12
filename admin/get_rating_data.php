<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'mm_sdg12';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get rating distribution
    $stmt = $pdo->query("
        SELECT 
            rating,
            COUNT(*) as count
        FROM reviews 
        GROUP BY rating
        ORDER BY rating
    ");
    
    $ratingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create array for ratings 1-5
    $ratings = [0, 0, 0, 0, 0];
    
    foreach ($ratingsData as $row) {
        $ratings[$row['rating'] - 1] = (int)$row['count'];
    }
    
    echo json_encode(['ratings' => $ratings]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>