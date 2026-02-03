<?php
// Prevent unwanted output
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Manual check instead of function to avoid redirect issues in API
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(order_date, '%Y-%m') as date, SUM(total_amount) as total
        FROM Orders
        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 36 MONTH)
        GROUP BY DATE_FORMAT(order_date, '%Y-%m')
        ORDER BY date ASC
    ");
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];

    foreach ($data as $row) {
        $labels[] = date("M Y", strtotime($row['date'] . "-01"));
        $values[] = (float)$row['total'];
    }

    echo json_encode(['labels' => $labels, 'data' => $values]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>