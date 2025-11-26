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
        $name = sanitizeInput($_POST['name']);
        $type = sanitizeInput($_POST['type']);
        $contact_info = sanitizeInput($_POST['contact_info'] ?? '');
        
        // Validate input
        if (empty($name) || empty($type)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Name and type are required']);
            exit();
        }
        
        // Check if guide already exists
        $stmt = $pdo->prepare("SELECT id FROM guides WHERE name = ? AND type = ?");
        $stmt->execute([$name, $type]);
        if ($stmt->fetch()) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'A guide with this name and type already exists']);
            exit();
        }
        
        // Insert new guide
        $stmt = $pdo->prepare("INSERT INTO guides (name, type, contact_info, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$name, $type, $contact_info]);
        
        $guide_id = $pdo->lastInsertId();
        
        // Log activity
        logActivity($pdo, 'guide_added', "Added new guide: $name ($type)", $guide_id);
        
        // Clear any output buffer and send JSON response
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Guide added successfully', 'guide_id' => $guide_id]);
        
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
