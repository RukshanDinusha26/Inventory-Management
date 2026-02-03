<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $customer_id = $_POST['customer_id'];
    $products = $_POST['products']; // Array of product IDs
    $quantities = $_POST['quantities']; // Array of quantities
    
    // Calculate total amount and validate stock
    $total_amount = 0;
    $order_items = [];
    $valid_order = true;

    // Start transaction
    $conn->beginTransaction();

    try {
        // Insert Order
        $stmt = $conn->prepare("INSERT INTO Orders (customer_id, order_date, total_amount) VALUES (:customer_id, NOW(), 0)");
        $stmt->execute(['customer_id' => $customer_id]);
        $order_id = $conn->lastInsertId();

        foreach ($products as $index => $prod_id) {
            $qty = (int)$quantities[$index];
            if ($qty > 0) {
                // Get product details (price, current stock)
                $stmt = $conn->prepare("SELECT price, stock_quantity FROM Products WHERE product_id = :id FOR UPDATE");
                $stmt->execute(['id' => $prod_id]);
                $prod_data = $stmt->fetch();

                if ($prod_data['stock_quantity'] >= $qty) {
                    $price = $prod_data['price'];
                    $line_total = $price * $qty;
                    $total_amount += $line_total;

                    // Insert Order Item
                    $stmt = $conn->prepare("INSERT INTO Order_Items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
                    $stmt->execute([
                        'order_id' => $order_id,
                        'product_id' => $prod_id,
                        'quantity' => $qty,
                        'price' => $price
                    ]);

                    // Update Stock
                    $stmt = $conn->prepare("UPDATE Products SET stock_quantity = stock_quantity - :qty WHERE product_id = :id");
                    $stmt->execute(['qty' => $qty, 'id' => $prod_id]);
                } else {
                    throw new Exception("Insufficient stock for product ID: $prod_id");
                }
            }
        }

        // Update Order Total
        $stmt = $conn->prepare("UPDATE Orders SET total_amount = :total WHERE order_id = :id");
        $stmt->execute(['total' => $total_amount, 'id' => $order_id]);

        $conn->commit();
        $_SESSION['success'] = "Order #$order_id created successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Failed to create order: " . $e->getMessage();
    }
    
    echo "<script>window.location.href='orders.php';</script>";
    exit();
}

// Fetch Data
$customers = $conn->query("SELECT * FROM Customers")->fetchAll();
$products_list = $conn->query("SELECT * FROM Products WHERE stock_quantity > 0")->fetchAll();
$orders = $conn->query("
    SELECT o.order_id, o.order_date, o.total_amount, c.name as customer_name 
    FROM Orders o 
    LEFT JOIN Customers c ON o.customer_id = c.customer_id 
    ORDER BY o.order_date DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Orders</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
        <i class="fas fa-plus"></i> New Order
    </button>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Order History</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) { ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= $order['order_date'] ?></td>
                            <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info text-white view-details-btn" 
                                        data-id="<?= $order['order_id'] ?>">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="create_order" value="1">
                
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Select Customer...</option>
                        <?php foreach ($customers as $c) { ?>
                            <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <hr>
                <h6>Order Items</h6>
                <div id="order-items-container">
                    <div class="row mb-2 item-row">
                        <div class="col-md-7">
                            <select name="products[]" class="form-select" required>
                                <option value="">Select Product...</option>
                                <?php foreach ($products_list as $p) { ?>
                                    <option value="<?= $p['product_id'] ?>">
                                        <?= htmlspecialchars($p['name']) ?> (Rs. <?= $p['price'] ?>) - Stock: <?= $p['stock_quantity'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="quantities[]" class="form-control" placeholder="Qty" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-row" disabled><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="add-row-btn">
                    <i class="fas fa-plus"></i> Add Another Product
                </button>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </div>
        </form>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details #<span id="detailOrderId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="orderDetailsBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <strong>Total: Rs. <span id="detailOrderTotal"></span></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- Optional: Add Print Button -->
                <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Dynamic Form Rows
    document.getElementById('add-row-btn').addEventListener('click', function() {
        var container = document.getElementById('order-items-container');
        var firstRow = container.querySelector('.item-row');
        var newRow = firstRow.cloneNode(true);
        
        // Clear values
        newRow.querySelector('select').value = '';
        newRow.querySelector('input').value = '';
        newRow.querySelector('.remove-row').disabled = false; // Enable delete button
        
        container.appendChild(newRow);
    });

    // Event Delegation for Remove Row
    document.getElementById('order-items-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            var row = e.target.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
            }
        }
    });

    // Details Modal Trigger (AJAX)
    $('.view-details-btn').click(function() {
        var orderId = $(this).data('id');
        var orderTotal = $(this).closest('tr').find('td:eq(3)').text().replace('Rs. ', ''); // Grab total from table row
        
        $('#detailOrderId').text(orderId);
        $('#detailOrderTotal').text(orderTotal);
        $('#orderDetailsBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        
        $('#orderDetailsModal').modal('show');

        // AJAX Fetch
        $.ajax({
            url: 'api/get_order_details.php',
            type: 'GET',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(data) {
                var rows = '';
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        var lineTotal = parseFloat(item.quantity) * parseFloat(item.price);
                        rows += '<tr>';
                        rows += '<td>' + item.product_name + '</td>';
                        rows += '<td>' + item.quantity + '</td>';
                        rows += '<td>Rs. ' + parseFloat(item.price).toFixed(2) + '</td>';
                        rows += '<td>Rs. ' + lineTotal.toFixed(2) + '</td>';
                        rows += '</tr>';
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center">No items found.</td></tr>';
                }
                $('#orderDetailsBody').html(rows);
            },
            error: function() {
                $('#orderDetailsBody').html('<tr><td colspan="4" class="text-center text-danger">Error fetching details.</td></tr>');
            }
        });
    });
</script>