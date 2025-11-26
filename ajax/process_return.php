<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['borrow_record_id']) || !isset($data['returned_items'])) {
        throw new Exception('Missing required data');
    }
    
    $borrow_record_id = (int)$data['borrow_record_id'];
    $returned_items = $data['returned_items'];
    $return_quantities = $data['return_quantities'] ?? [];
    $notes = sanitizeInput($data['notes'] ?? '');
    
    if (empty($borrow_record_id) || empty($returned_items)) {
        throw new Exception('Invalid return data');
    }
    
    $pdo->beginTransaction();
    
    $all_returned = true;
    $total_items = 0;
    $returned_count = 0;
    $returned_items_details = [];
    
    // Process each returned item
    foreach ($returned_items as $item_id) {
        $quantity = (int)($return_quantities[$item_id] ?? 0);
        if ($quantity > 0) {
            // Get current borrow item details
            $stmt = $pdo->prepare("SELECT bi.*, r.id as resource_id, r.name as resource_name, r.quantity_total 
                                  FROM borrow_items bi 
                                  JOIN resources r ON bi.resource_id = r.id 
                                  WHERE bi.id = ?");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch();
            
            if (!$item) {
                throw new Exception('Invalid item ID: ' . $item_id);
            }
            
            // Validate return quantity
            if ($quantity > $item['quantity_borrowed']) {
                throw new Exception('Return quantity cannot exceed borrowed quantity for ' . $item['resource_name']);
            }
            
            // Update borrow item
            $stmt = $pdo->prepare("UPDATE borrow_items SET 
                                  quantity_returned = quantity_returned + ?, 
                                  is_returned = (quantity_returned + ? >= quantity_borrowed),
                                  return_date = NOW() 
                                  WHERE id = ?");
            $stmt->execute([$quantity, $quantity, $item_id]);
            
            // Update resource quantity
            $stmt = $pdo->prepare("UPDATE resources SET quantity_available = quantity_available + ? WHERE id = ?");
            $stmt->execute([$quantity, $item['resource_id']]);
            
            // Update resource status
            updateResourceStatus($pdo, $item['resource_id']);
            
            $returned_count += $quantity;
            $total_items += $item['quantity_borrowed'];
            
            $returned_items_details[] = [
                'resource_name' => $item['resource_name'],
                'quantity_returned' => $quantity,
                'total_borrowed' => $item['quantity_borrowed']
            ];
        }
    }
    
    // Check if all items are returned
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN bi.is_returned = 1 THEN 1 ELSE 0 END) as returned 
                           FROM borrow_items bi WHERE bi.borrow_record_id = ?");
    $stmt->execute([$borrow_record_id]);
    $status_check = $stmt->fetch();
    
    if ($status_check['total'] > 0 && $status_check['total'] == $status_check['returned']) {
        // All items returned - update borrow record status
        $stmt = $pdo->prepare("UPDATE borrow_records SET 
                              status = 'Returned', 
                              actual_return_date = NOW(),
                              notes = CONCAT(COALESCE(notes, ''), '\nReturn processed: ', NOW())
                              WHERE id = ?");
        $stmt->execute([$borrow_record_id]);
    }
    
    $pdo->commit();
    
    // Get updated borrow record info
    $stmt = $pdo->prepare("SELECT br.*, g.name as guide_name FROM borrow_records br 
                           JOIN guides g ON br.guide_id = g.id WHERE br.id = ?");
    $stmt->execute([$borrow_record_id]);
    $borrow_info = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Return processed successfully!',
        'data' => [
            'borrow_id' => $borrow_record_id,
            'guide_name' => $borrow_info['guide_name'],
            'returned_items' => $returned_items_details,
            'total_returned' => $returned_count,
            'status' => $borrow_info['status']
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to process return: ' . $e->getMessage()
    ]);
}
?>
