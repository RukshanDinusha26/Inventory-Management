<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
include 'config.php';
redirectIfNotLoggedIn();
if (!isAdmin()) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $supplier_id = $_POST['supplier_id'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];

        try {
            $stmt = $conn->prepare("INSERT INTO Products (name, description, category_id, supplier_id, price, stock_quantity) VALUES (:name, :description, :category_id, :supplier_id, :price, :stock_quantity)");
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'category_id' => $category_id,
                'supplier_id' => $supplier_id,
                'price' => $price,
                'stock_quantity' => $stock_quantity
            ]);
            $_SESSION['success'] = 'Product added successfully!';
        } catch (Exception $e) {
            $error = "Error adding product: " . $e->getMessage();
        }
        echo "<script>window.location.href='products.php';</script>";
        exit();

    } elseif (isset($_POST['update_product'])) {
        $product_id = $_POST['product_id'];
        $new_quantity = $_POST['new_quantity'];
        $new_price = $_POST['new_price'];

        try {
            $stmt = $conn->prepare("UPDATE Products SET stock_quantity = :new_quantity, price = :new_price WHERE product_id = :product_id");
            $stmt->execute([
                'new_quantity' => $new_quantity,
                'new_price' => $new_price,
                'product_id' => $product_id
            ]);
            $_SESSION['success'] = 'Product updated successfully!';
        } catch (Exception $e) {
             $error = "Error updating product: " . $e->getMessage();
        }
        echo "<script>window.location.href='products.php';</script>";
        exit();
    } elseif (isset($_POST['delete_product'])) {
        $product_id = $_POST['delete_product_id'];
        try {
            // Check for dependencies (Order Items) first, technically should handle FK constraints but for now:
            $stmt = $conn->prepare("DELETE FROM Products WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $product_id]);
            $_SESSION['success'] = 'Product deleted successfully!';
        } catch (PDOException $e) {
            // Likely a FK constraint failure
            $_SESSION['success'] = 'Cannot delete product: It is linked to existing orders.'; 
        }
        echo "<script>window.location.href='products.php';</script>";
        exit();
    }
}

$categories = $conn->query("SELECT * FROM Categories")->fetchAll();
$suppliers = $conn->query("SELECT * FROM Suppliers")->fetchAll();
$products = $conn->query("
    SELECT p.*, c.category_name, s.name as supplier_name 
    FROM Products p 
    LEFT JOIN Categories c ON p.category_id = c.category_id 
    LEFT JOIN Suppliers s ON p.supplier_id = s.supplier_id
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Product Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> Add New Product
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Products</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) { ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($product['description']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td><?= htmlspecialchars($product['supplier_name']) ?></td>
                            <td>Rs. <?= number_format($product['price'], 2) ?></td>
                            <td>
                                <?php if($product['stock_quantity'] < 10): ?>
                                    <span class="badge bg-danger"><?= htmlspecialchars($product['stock_quantity']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= htmlspecialchars($product['stock_quantity']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info text-white" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProductModal"
                                    data-id="<?= $product['product_id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-price="<?= $product['price'] ?>"
                                    data-stock="<?= $product['stock_quantity'] ?>"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteProductModal"
                                    data-id="<?= $product['product_id'] ?>"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="add_product" value="1">
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat) { ?>
                                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <?php foreach ($suppliers as $sup) { ?>
                                <option value="<?= $sup['supplier_id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" name="stock_quantity" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product: <span id="editNameDisplay"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="update_product" value="1">
                <input type="hidden" name="product_id" id="editProductId">
                
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="new_price" id="editPrice" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" name="new_quantity" id="editStock" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-info text-white">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="delete_product" value="1">
                <input type="hidden" name="delete_product_id" id="deleteProductId">
                <p>Are you sure you want to delete this product? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Handle Edit Modal Data
    var editModal = document.getElementById('editProductModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var price = button.getAttribute('data-price');
        var stock = button.getAttribute('data-stock');

        document.getElementById('editProductId').value = id;
        document.getElementById('editNameDisplay').textContent = name;
        document.getElementById('editPrice').value = price;
        document.getElementById('editStock').value = stock;
    });

    // Handle Delete Modal Data
    var deleteModal = document.getElementById('deleteProductModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        document.getElementById('deleteProductId').value = id;
    });
</script>