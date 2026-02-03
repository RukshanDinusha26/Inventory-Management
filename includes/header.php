<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div id="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-boxes"></i>
            <span>IMS Admin</span>
        </div>

        <ul class="list-unstyled components">
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                    <a href="products.php"><i class="fas fa-box-open"></i> Products</a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
                    <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'suppliers.php' ? 'active' : '' ?>">
                    <a href="suppliers.php"><i class="fas fa-truck"></i> Suppliers</a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                    <a href="users.php"><i class="fas fa-user-shield"></i> Users</a>
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'sales_staff'): ?>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                    <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                    <a href="reports.php"><i class="fas fa-chart-bar"></i> Inventory Reports</a>
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'inventory_manager'): ?>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'update_stock.php' ? 'active' : '' ?>">
                    <a href="update_stock.php"><i class="fas fa-clipboard-check"></i> Update Stock</a>
                </li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                    <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-link text-dark">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-gray-600 small">
                        Logged in as: <strong><?= isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Guest' ?></strong>
                    </span>
                    <a href="profile.php" class="btn btn-sm btn-outline-primary me-2">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show alert-floating" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show alert-floating" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>