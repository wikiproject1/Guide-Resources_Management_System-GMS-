<?php
// Start output buffering to catch any unwanted output
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors for production

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    // Clear any output buffer
    ob_clean();
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $type = sanitizeInput($_POST['type']);
        $contact_info = sanitizeInput($_POST['contact_info'] ?? '');
        
        // Validate input
        if (empty($id) || empty($name) || empty($type)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID, name and type are required']);
            exit();
        }
        
        // Check if guide exists
        $stmt = $pdo->prepare("SELECT id FROM guides WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Guide not found']);
            exit();
        }
        
        // Check if another guide has the same name and type
        $stmt = $pdo->prepare("SELECT id FROM guides WHERE name = ? AND type = ? AND id != ?");
        $stmt->execute([$name, $type, $id]);
        if ($stmt->fetch()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Another guide with this name and type already exists']);
            exit();
        }
        
        // Update guide
        $stmt = $pdo->prepare("UPDATE guides SET name = ?, type = ?, contact_info = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $type, $contact_info, $id]);
        
        // Log activity
        logActivity($pdo, 'guide_updated', "Updated guide: $name ($type)", $id);
        
        // Clear any output buffer and send JSON response
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Guide updated successfully']);
        
    } catch (Exception $e) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// End output buffering
ob_end_flush();
?>
