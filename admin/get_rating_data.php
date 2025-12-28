<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get rating distribution
    $stmt = $conn->prepare("
        SELECT 
            rating,
            COUNT(*) as count
        FROM reviews 
        GROUP BY rating
        ORDER BY rating
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    // Create array for ratings 1-5
    $ratings = [0, 0, 0, 0, 0];

    while ($row = $result->fetch_assoc()) {
        $ratingIndex = (int) $row['rating'] - 1;
        if (isset($ratings[$ratingIndex])) {
            $ratings[$ratingIndex] = (int) $row['count'];
        }
    }

    echo json_encode(['ratings' => $ratings]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>