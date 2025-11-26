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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$resource_id = (int)($_POST['resource_id'] ?? 0);
$borrow_id = (int)($_POST['borrow_id'] ?? 0);
$status = $_POST['status'] ?? '';
$notes = sanitizeInput($_POST['notes'] ?? '');
$quantity_found = (int)($_POST['quantity_found'] ?? 0);

if (empty($action) || empty($resource_id) || empty($borrow_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    switch ($action) {
        case 'mark_found':
            // Mark item as found and update resource status
            $stmt = $pdo->prepare("UPDATE resources SET status = 'Available', quantity_available = quantity_available + ? WHERE id = ?");
            $stmt->execute([$quantity_found, $resource_id]);
            
            // Update borrow item to mark as returned
            $stmt = $pdo->prepare("UPDATE borrow_items SET quantity_returned = quantity_returned + ?, is_returned = TRUE, return_date = NOW() WHERE borrow_record_id = ? AND resource_id = ?");
            $stmt->execute([$quantity_found, $borrow_id, $resource_id]);
            
            // Check if all items in this borrow record are returned
            $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN is_returned = 1 THEN 1 ELSE 0 END) as returned FROM borrow_items WHERE borrow_record_id = ?");
            $stmt->execute([$borrow_id]);
            $item_status = $stmt->fetch();
            
            if ($item_status['total'] == $item_status['returned']) {
                // All items returned, update borrow record status
                $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'Returned', actual_return_date = NOW(), notes = CONCAT(COALESCE(notes, ''), '\nItem found and returned on ', NOW()) WHERE id = ?");
                $stmt->execute([$borrow_id]);
            }
            
            // Log the action (create table if not exists)
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    action VARCHAR(255) NOT NULL,
                    details TEXT,
                    admin_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                $stmt = $pdo->prepare("INSERT INTO activity_log (action, details, admin_id) VALUES (?, ?, ?)");
                $stmt->execute(['Missing item found', "Resource ID: $resource_id, Borrow ID: $borrow_id, Quantity: $quantity_found", $_SESSION['user_id']]);
            } catch (Exception $e) {
                // Activity logging failed, but don't fail the main operation
                error_log("Failed to log activity: " . $e->getMessage());
            }
            
            break;
            
        case 'mark_lost':
            // Mark item as permanently lost
            $stmt = $pdo->prepare("UPDATE resources SET status = 'Lost', quantity_available = 0 WHERE id = ?");
            $stmt->execute([$resource_id]);
            
            // Update borrow record status to reflect loss
            $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'Missing', notes = CONCAT(COALESCE(notes, ''), '\nItem marked as permanently lost on ', NOW()) WHERE id = ?");
            $stmt->execute([$borrow_id]);
            
            // Log the action
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    action VARCHAR(255) NOT NULL,
                    details TEXT,
                    admin_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                $stmt = $pdo->prepare("INSERT INTO activity_log (action, details, admin_id) VALUES (?, ?, ?)");
                $stmt->execute(['Item marked as lost', "Resource ID: $resource_id, Borrow ID: $borrow_id", $_SESSION['user_id']]);
            } catch (Exception $e) {
                error_log("Failed to log activity: " . $e->getMessage());
            }
            
            break;
            
        case 'update_status':
            // Update resource status based on admin input
            $stmt = $pdo->prepare("UPDATE resources SET status = ? WHERE id = ?");
            $stmt->execute([$status, $resource_id]);
            
            // Add notes to borrow record
            if (!empty($notes)) {
                $stmt = $pdo->prepare("UPDATE borrow_records SET notes = CONCAT(COALESCE(notes, ''), '\nStatus updated on ', NOW(), ': ', ?) WHERE id = ?");
                $stmt->execute([$notes, $borrow_id]);
            }
            
            // Log the action
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    action VARCHAR(255) NOT NULL,
                    details TEXT,
                    admin_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                $stmt = $pdo->prepare("INSERT INTO activity_log (action, details, admin_id) VALUES (?, ?, ?)");
                $stmt->execute(['Status updated', "Resource ID: $resource_id, Borrow ID: $borrow_id, New Status: $status", $_SESSION['user_id']]);
            } catch (Exception $e) {
                error_log("Failed to log activity: " . $e->getMessage());
            }
            
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Action completed successfully',
        'action' => $action,
        'resource_id' => $resource_id,
        'borrow_id' => $borrow_id
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
