<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include header
include 'includes/header.php';

// Route to appropriate page
switch ($page) {
    case 'dashboard':
        include 'pages/dashboard.php';
        break;
    case 'guides':
        include 'pages/guides.php';
        break;
    case 'resources':
        include 'pages/resources.php';
        break;
    case 'borrowing':
        include 'pages/borrowing.php';
        break;
    case 'borrowingadmin':
        include 'pages/borrowingadmin.php';
        break;
    case 'returns':
        include 'pages/returns.php';
        break;
    case 'reports':
        include 'pages/reports.php';
        break;
    default:
        include 'pages/dashboard.php';
}

// Include footer
include 'includes/footer.php';
?>
