<?php
session_start();
include 'includes/header.php';
include 'includes/auth.php';
include 'includes/db.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin(); // Ensure only admins can access this

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO Users (username, password, role) VALUES (:username, :password, :role)");
            $stmt->execute(['username' => $username, 'password' => $hash, 'role' => $role]);
            $_SESSION['success'] = 'User added successfully!';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: Username likely already exists.";
        }
    }
    echo "<script>window.location.href='users.php';</script>";
    exit();
}

$users = $conn->query("SELECT * FROM Users")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus"></i> Add User
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">System Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($user['role']) ?></span>
                            </td>
                            <td>
                                <!-- Placeholder for Edit/Delete logic -->
                                <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="add_user" value="1">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="sales_staff">Sales Staff</option>
                        <option value="inventory_manager">Inventory Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>