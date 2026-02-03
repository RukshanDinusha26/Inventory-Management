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

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    // Role check logic manual to avoid issues
    $role = $_SESSION['role'] ?? '';
    if ($role !== 'admin' && $role !== 'sales_staff') {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied']);
        exit();
    }

    $stmt = $conn->query("
        SELECT o.order_id, o.order_date, c.name as customer_name, o.total_amount
        FROM Orders o
        LEFT JOIN Customers c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>