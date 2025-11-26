<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$borrow_id = (int)($_GET['borrow_id'] ?? 0);

if (empty($borrow_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing borrow ID']);
    exit;
}

try {
    // Get borrowing details
    $stmt = $pdo->prepare("SELECT br.*, g.name as guide_name, g.type as guide_type, g.contact_info FROM borrow_records br JOIN guides g ON br.guide_id = g.id WHERE br.id = ?");
    $stmt->execute([$borrow_id]);
    $borrow_details = $stmt->fetch();
    
    if (!$borrow_details) {
        echo json_encode(['success' => false, 'message' => 'Borrowing record not found']);
        exit;
    }
    
    // Get borrowing items
    $stmt = $pdo->prepare("SELECT bi.*, r.name as resource_name, r.category as resource_category FROM borrow_items bi JOIN resources r ON bi.resource_id = r.id WHERE bi.borrow_record_id = ?");
    $stmt->execute([$borrow_id]);
    $borrow_items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'borrow_details' => $borrow_details,
        'borrow_items' => $borrow_items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
