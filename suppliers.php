<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_supplier'])) {
        $name = $_POST['name'];
        $contact_info = $_POST['contact_info'];

        $stmt = $conn->prepare("INSERT INTO Suppliers (name, contact_info) VALUES (:name, :contact_info)");
        $stmt->execute(['name' => $name, 'contact_info' => $contact_info]);
        $_SESSION['success'] = 'Supplier added successfully!';
    }
    echo "<script>window.location.href='suppliers.php';</script>";
    exit();
}

$suppliers = $conn->query("SELECT * FROM Suppliers")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Suppliers</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="fas fa-truck-loading"></i> Add Supplier
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Supplier List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Information</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s) { ?>
                        <tr>
                            <td><?= $s['supplier_id'] ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['contact_info']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="add_supplier" value="1">
                <div class="mb-3">
                    <label class="form-label">Supplier Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Info (Address, Phone, Email)</label>
                    <textarea name="contact_info" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>