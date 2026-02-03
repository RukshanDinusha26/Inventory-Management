<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
redirectIfNotLoggedIn();

// Ensure only authorized roles can access
if (!isAdmin() && !isInventoryManager()) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = $_POST['product_id'];
    $adjustment = (int)$_POST['adjustment']; // Positive or negative
    
    // Simple update query
    $stmt = $conn->prepare("UPDATE Products SET stock_quantity = stock_quantity + :adj WHERE product_id = :id");
    $stmt->execute(['adj' => $adjustment, 'id' => $product_id]);
    
    $_SESSION['success'] = "Stock updated successfully!";
    echo "<script>window.location.href='update_stock.php';</script>";
    exit();
}

$products = $conn->query("SELECT * FROM Products")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quick Stock Update</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Adjust Stock Level</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="update_stock" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Product</label>
                        <select name="product_id" class="form-select" id="productSelect" required>
                            <option value="">Choose...</option>
                            <?php foreach ($products as $p) { ?>
                                <option value="<?= $p['product_id'] ?>" data-stock="<?= $p['stock_quantity'] ?>">
                                    <?= htmlspecialchars($p['name']) ?> (Current: <?= $p['stock_quantity'] ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adjustment Amount</label>
                        <div class="input-group">
                            <input type="number" name="adjustment" class="form-control" placeholder="e.g., 10 (add) or -5 (remove)" required>
                        </div>
                        <small class="text-muted">Enter a positive number to add stock, negative to remove.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Stock</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>