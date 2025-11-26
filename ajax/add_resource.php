<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = sanitizeInput($_POST['name']);
        $category = sanitizeInput($_POST['category']);
        $quantity_total = (int)$_POST['quantity_total'];
        $min_stock_level = (int)($_POST['min_stock_level'] ?? 0);
        $description = sanitizeInput($_POST['description'] ?? '');
        $location = sanitizeInput($_POST['location'] ?? '');
        
        // Validate input
        if (empty($name) || empty($category) || $quantity_total <= 0) {
            echo json_encode(['success' => false, 'message' => 'Name, category and quantity are required']);
            exit();
        }
        
        // Check if resource already exists
        $stmt = $pdo->prepare("SELECT id FROM resources WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'A resource with this name already exists']);
            exit();
        }
        
        // Insert new resource
        $stmt = $pdo->prepare("INSERT INTO resources (name, category, quantity_total, quantity_available, min_stock_level, description, location, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available', NOW())");
        $stmt->execute([$name, $category, $quantity_total, $quantity_total, $min_stock_level, $description, $location]);
        
        $resource_id = $pdo->lastInsertId();
        
        // Log activity
        logActivity($pdo, 'resource_added', "Added new resource: $name ($category)", $resource_id);
        
        echo json_encode(['success' => true, 'message' => 'Resource added successfully', 'resource_id' => $resource_id]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

