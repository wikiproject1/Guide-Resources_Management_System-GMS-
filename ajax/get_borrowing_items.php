<?php
header('Content-Type: application/json');

// Include database configuration to access global $pdo
require_once '../config/database.php';

if (!isset($_GET['borrow_id']) || empty($_GET['borrow_id'])) {
    echo json_encode(['success' => false, 'message' => 'Borrow ID is required']);
    exit;
}

$borrow_id = (int)$_GET['borrow_id'];

try {
    // Get borrowing items with resource details - using actual schema
    $stmt = $pdo->prepare("SELECT bi.*, r.name as resource_name, r.category, r.quantity_available,
                                  bi.quantity_borrowed,
                                  CASE WHEN bi.is_returned = 1 THEN 'Returned' ELSE 'Borrowed' END as status
                           FROM borrow_items bi 
                           JOIN resources r ON bi.resource_id = r.id 
                           WHERE bi.borrow_record_id = ? AND bi.is_returned = 0");
    $stmt->execute([$borrow_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ]);
    
} catch(PDOException $e) {
    error_log("Error getting borrowing items: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
