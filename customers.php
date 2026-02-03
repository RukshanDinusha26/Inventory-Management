<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_customer'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $contact_info = $_POST['contact_info']; // Mapping to address or extra info

        $stmt = $conn->prepare("INSERT INTO Customers (name, email, phone, contact_info) VALUES (:name, :email, :phone, :contact_info)");
        try {
            $stmt->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'contact_info' => $contact_info]);
            $_SESSION['success'] = 'Customer added successfully!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error adding customer: ' . $e->getMessage();
        }
    }
    echo "<script>window.location.href='customers.php';</script>";
    exit();
}

$customers = $conn->query("SELECT * FROM Customers")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customers</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fas fa-user-plus"></i> Add Customer
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Customer List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address/Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c) { ?>
                        <tr>
                            <td><?= $c['customer_id'] ?></td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= htmlspecialchars($c['contact_info']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="add_customer" value="1">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address / Contact Info</label>
                    <textarea name="contact_info" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>