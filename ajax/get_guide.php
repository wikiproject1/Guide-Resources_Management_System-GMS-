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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $id = (int)($_GET['id'] ?? 0);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Guide ID is required']);
            exit();
        }
        
        // Get guide data
        $stmt = $pdo->prepare("SELECT id, name, type, contact_info FROM guides WHERE id = ?");
        $stmt->execute([$id]);
        $guide = $stmt->fetch();
        
        if (!$guide) {
            echo json_encode(['success' => false, 'message' => 'Guide not found']);
            exit();
        }
        
        echo json_encode(['success' => true, 'guide' => $guide]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

