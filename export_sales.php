<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

session_start();
redirectIfNotLoggedIn();

if (!isAdmin() && !isSalesStaff()) {
    die("Access Denied");
}

$filename = "sales_report_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header Row
fputcsv($output, ['Order ID', 'Date', 'Customer', 'Total Amount']);

// Data Rows
$stmt = $conn->query("
    SELECT o.order_id, o.order_date, c.name as customer_name, o.total_amount
    FROM Orders o
    LEFT JOIN Customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>