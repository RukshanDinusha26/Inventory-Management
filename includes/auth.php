<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('isSalesStaff')) {
    function isSalesStaff() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'sales_staff';
    }
}

if (!function_exists('isInventoryManager')) {
    function isInventoryManager() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'inventory_manager';
    }
}

if (!function_exists('redirectIfNotLoggedIn')) {
    function redirectIfNotLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
    }
}

if (!function_exists('redirectIfNotAdmin')) {
    function redirectIfNotAdmin() {
        if (!isAdmin()) {
            header('Location: dashboard.php');
            exit();
        }
    }
}

if (!function_exists('redirectIfNotSalesStaff')) {
    function redirectIfNotSalesStaff() {
        if (!isSalesStaff()) {
            header('Location: dashboard.php');
            exit();
        }
    }
}

if (!function_exists('redirectIfNotInventoryManager')) {
    function redirectIfNotInventoryManager() {
        if (!isInventoryManager()) {
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>