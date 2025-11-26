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
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Guide ID is required']);
            exit();
        }
        
        // Check if guide exists
        $stmt = $pdo->prepare("SELECT name, type FROM guides WHERE id = ?");
        $stmt->execute([$id]);
        $guide = $stmt->fetch();
        
        if (!$guide) {
            echo json_encode(['success' => false, 'message' => 'Guide not found']);
            exit();
        }
        
        // Check if guide has active borrowings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE guide_id = ? AND status = 'Borrowed'");
        $stmt->execute([$id]);
        $active_borrowings = $stmt->fetchColumn();
        
        if ($active_borrowings > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete guide with active borrowings']);
            exit();
        }
        
        // Delete guide
        $stmt = $pdo->prepare("DELETE FROM guides WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        logActivity($pdo, 'guide_deleted', "Deleted guide: {$guide['name']} ({$guide['type']})", $id);
        
        echo json_encode(['success' => true, 'message' => 'Guide deleted successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

