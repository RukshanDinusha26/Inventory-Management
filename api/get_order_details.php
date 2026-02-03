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

    if (isset($_GET['order_id'])) {
        $order_id = $_GET['order_id'];

        // Fetch Order Items with Product details
        $stmt = $conn->prepare("
            SELECT oi.*, p.name as product_name
            FROM Order_Items oi
            JOIN Products p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($items);
    } else {
        echo json_encode([]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>