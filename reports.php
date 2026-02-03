<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
redirectIfNotLoggedIn();

// Fetch ALL products with supplier names
$inventory = $conn->query("
    SELECT p.*, s.name as supplier_name 
    FROM Products p 
    LEFT JOIN Suppliers s ON p.supplier_id = s.supplier_id
    ORDER BY p.stock_quantity ASC
")->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Inventory Reports</h1>
</div>

<div class="row">
    <!-- Inventory Levels Card -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Current Inventory Levels</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Stock Quantity</th>
                                <th>Status</th>
                                <th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td>Rs. <?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['stock_quantity'] ?></td>
                                    <td>
                                        <?php if ($item['stock_quantity'] == 0): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php elseif ($item['stock_quantity'] < 10): ?>
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Analytics Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Sales Overview</h6>
                <div class="d-flex align-items-center">
                    <div class="dropdown no-arrow">
                        <button onclick="generateSalesReportPDF()" class="btn btn-sm btn-danger shadow-sm me-2">
                            <i class="fas fa-file-pdf fa-sm text-white-50"></i> Export PDF
                        </button>
                        <a href="export_sales.php" class="btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Export CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="salesLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
             <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Performance Stats</h6>
            </div>
            <div class="card-body">
                <?php
                    // Calculate Total Revenue (All Time)
                    $totalRevenue = $conn->query("SELECT SUM(total_amount) FROM Orders")->fetchColumn();
                    // Calculate Today's Revenue
                    $todayRevenue = $conn->query("SELECT SUM(total_amount) FROM Orders WHERE DATE(order_date) = CURDATE()")->fetchColumn();
                ?>
                <div class="mb-4">
                    <div class="small text-gray-500">Total Revenue (All Time)</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">Rs. <?= number_format($totalRevenue ?? 0, 2) ?></div>
                </div>
                <div class="mb-4">
                    <div class="small text-gray-500">Revenue Today</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">Rs. <?= number_format($todayRevenue ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// PDF Generation Function
function generateSalesReportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Company Header
    doc.setFontSize(22);
    doc.setTextColor(40, 40, 40);
    doc.text("IMS Inventory System", 14, 20);

    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.text("123 Business Road, Colombo 03, Sri Lanka", 14, 26);
    doc.text("Phone: +94 11 234 5678 | Email: info@ims.lk", 14, 31);
    
    // Line separator
    doc.setDrawColor(200, 200, 200);
    doc.line(14, 35, 196, 35);

    // Report Title & Date
    doc.setFontSize(16);
    doc.setTextColor(40);
    doc.text("Sales Report", 14, 45);
    
    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.text("Generated on: " + new Date().toLocaleString(), 14, 51);

    // Fetch Data
    $.ajax({
        url: 'api/get_sales_report.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            
            // Calculate Totals
            let totalSales = 0;
            data.forEach(row => {
                totalSales += parseFloat(row.total_amount);
            });

            // Summary Section
            doc.setFontSize(11);
            doc.setTextColor(0);
            doc.text(`Total Orders: ${data.length}`, 14, 60);
            doc.text(`Total Revenue: Rs. ${totalSales.toLocaleString('en-US', {minimumFractionDigits: 2})}`, 14, 66);

            // Table Data Preparation
            const tableBody = data.map(row => [
                '#' + row.order_id,
                row.order_date,
                row.customer_name,
                "Rs. " + parseFloat(row.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})
            ]);

            // AutoTable
            doc.autoTable({
                head: [['Order ID', 'Date', 'Customer', 'Total Amount']],
                body: tableBody,
                startY: 75,
                theme: 'grid',
                headStyles: { 
                    fillColor: [78, 115, 223],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 30 }, // Order ID
                    1: { cellWidth: 40 }, // Date
                    2: { cellWidth: 'auto' }, // Customer
                    3: { halign: 'right', cellWidth: 40 } // Amount
                },
                foot: [['', '', 'Grand Total', "Rs. " + totalSales.toLocaleString('en-US', {minimumFractionDigits: 2})]],
                footStyles: {
                    fillColor: [240, 240, 240],
                    textColor: 0,
                    fontStyle: 'bold',
                    halign: 'right'
                },
                didDrawPage: function (data) {
                    // Footer
                    var str = "Page " + doc.internal.getNumberOfPages();
                    doc.setFontSize(10);
                    var pageSize = doc.internal.pageSize;
                    var pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
                    doc.text(str, data.settings.margin.left, pageHeight - 10);
                }
            });

            doc.save("Sales_Report_" + new Date().toISOString().slice(0,10) + ".pdf");
        },
        error: function(xhr, status, error) {
            console.error("PDF Export Error:", error);
            alert("Failed to fetch data for PDF. Please check the console.");
        }
    });
}

// Chart Global
var myLineChart;

function fetchChartData() {
    console.log("Fetching chart data...");
    $.ajax({
        url: 'api/get_sales_analytics.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log("Data received:", data);
            var ctx = document.getElementById("salesLineChart");
            
            if (myLineChart) {
                myLineChart.destroy();
            }

            myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "Earnings",
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
                        data: data.data,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                    scales: {
                        x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } },
                        y: { ticks: { maxTicksLimit: 5, padding: 10, callback: function(value) { return 'Rs. ' + value; } }, grid: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } },
                    },
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { return context.dataset.label + ': Rs. ' + context.parsed.y; } } } }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Chart Error:", error);
            console.log(xhr.responseText);
            // alert("Failed to load chart data. Check console.");
        }
    });
}

// Init
$(document).ready(function() {
    fetchChartData(); // Default (Last 30 Days)
});
</script>