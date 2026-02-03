<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
redirectIfNotLoggedIn();

// 1. Key Metrics
$totalProducts = $conn->query("SELECT COUNT(*) FROM Products")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$totalSuppliers = $conn->query("SELECT COUNT(*) FROM Suppliers")->fetchColumn();
$outOfStockProducts = $conn->query("SELECT COUNT(*) FROM Products WHERE stock_quantity = 0")->fetchColumn();

// 2. Monthly Sales Trend (Last 12 Months)
$salesStmt = $conn->query("
    SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as total
    FROM Orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month ASC
");
$salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
$months = [];
$revenues = [];
foreach ($salesData as $row) {
    $months[] = date("M Y", strtotime($row['month'] . "-01"));
    $revenues[] = $row['total'];
}

// 3. Top 5 Best Selling Products
$topProductsStmt = $conn->query("
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM Order_Items oi
    JOIN Products p ON oi.product_id = p.product_id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Recent Orders (Last 5)
$recentOrdersStmt = $conn->query("
    SELECT o.order_id, c.name as customer, o.total_amount, o.order_date
    FROM Orders o
    LEFT JOIN Customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
    <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-chart-bar fa-sm text-white-50"></i> View Full Reports</a>
</div>

<!-- Stats Rows -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalProducts ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-box fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalOrders ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-shopping-cart fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Out of Stock</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $outOfStockProducts ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Suppliers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalSuppliers ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-truck fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Sales Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Sales Revenue (Last 12 Months)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
            </div>
            <div class="card-body">
                <?php if (count($topProducts) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($topProducts as $idx => $prod): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary rounded-pill me-2"><?= $idx + 1 ?></span>
                                    <?= htmlspecialchars($prod['name']) ?>
                                </div>
                                <span class="badge bg-light text-dark border"><?= $prod['total_sold'] ?> sold</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted text-center">No sales data yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['order_id'] ?></td>
                                    <td><?= htmlspecialchars($order['customer']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($recentOrders) === 0): ?>
                                <tr><td colspan="5" class="text-center">No recent orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Revenue Trend Chart
var ctx = document.getElementById("revenueChart");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($months) ?>,
    datasets: [{
      label: "Revenue",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.05)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: <?= json_encode($revenues) ?>,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
    scales: {
      x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } },
      y: { ticks: { maxTicksLimit: 5, padding: 10, callback: function(value) { return 'Rs. ' + value; } }, grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } },
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgb(255,255,255)",
            bodyColor: "#858796",
            titleColor: '#6e707e',
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
            callbacks: {
                label: function(context) { return 'Revenue: Rs. ' + context.parsed.y.toLocaleString(); }
            }
        }
    }
  }
});
</script>