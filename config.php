<?php
include 'includes/db.php';


function initializeCategories($conn) {
    $stmt = $conn->query("SELECT COUNT(*) FROM Categories");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $categories = [
            'Electronics',
            'Furniture',
            'Clothing',
            'Books',
            'Toys'
        ];

        $stmt = $conn->prepare("INSERT INTO Categories (category_name) VALUES (:category_name)");
        foreach ($categories as $category) {
            $stmt->execute(['category_name' => $category]);
        }
    }
}

function initializeAdminUser($conn) {
    $stmt = $conn->query("SELECT COUNT(*) FROM Users WHERE username = 'admin'");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $role = 'admin';

        $stmt = $conn->prepare("INSERT INTO Users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'role' => $role
        ]);
    }
}

// Initialize the application
initializeCategories($conn);
initializeAdminUser($conn);
?>