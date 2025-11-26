<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $stats = [];
    
    // Get return statistics
    $stmt = $pdo->query("SELECT 
        COUNT(CASE WHEN br.status = 'Returned' THEN 1 END) as total_returns,
        COUNT(CASE WHEN br.status = 'Borrowed' THEN 1 END) as active_borrowings,
        COUNT(CASE WHEN br.status = 'Overdue' THEN 1 END) as overdue_returns,
        COUNT(CASE WHEN br.status = 'Missing' THEN 1 END) as missing_items
        FROM borrow_records br");
    $stats['borrow_status'] = $stmt->fetch();
    
    // Get recent returns (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as recent_returns 
                         FROM borrow_records 
                         WHERE status = 'Returned' 
                         AND actual_return_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_returns'] = $stmt->fetch()['recent_returns'];
    
    // Get missing mandatory items
    $stmt = $pdo->query("SELECT COUNT(*) as missing_mandatory 
                         FROM resources r 
                         JOIN borrow_items bi ON r.id = bi.resource_id 
                         JOIN borrow_records br ON bi.borrow_record_id = br.id 
                         WHERE r.category = 'Mandatory' 
                         AND r.status = 'Missing' 
                         AND br.status = 'Borrowed'");
    $stats['missing_mandatory'] = $stmt->fetch()['missing_mandatory'];
    
    // Get return trends (last 30 days)
    $stmt = $pdo->query("SELECT DATE(actual_return_date) as date, COUNT(*) as count
                         FROM borrow_records 
                         WHERE status = 'Returned' 
                         AND actual_return_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         GROUP BY DATE(actual_return_date)
                         ORDER BY date DESC");
    $stats['return_trends'] = $stmt->fetchAll();
    
    // Get guide return performance
    $stmt = $pdo->query("SELECT 
        g.name as guide_name,
        COUNT(CASE WHEN br.status = 'Returned' THEN 1 END) as returns_completed,
        COUNT(CASE WHEN br.status = 'Borrowed' THEN 1 END) as active_borrowings,
        COUNT(CASE WHEN br.status = 'Overdue' THEN 1 END) as overdue_count
        FROM guides g
        LEFT JOIN borrow_records br ON g.id = br.guide_id
        WHERE g.status = 'Active'
        GROUP BY g.id, g.name
        ORDER BY returns_completed DESC");
    $stats['guide_performance'] = $stmt->fetchAll();
    
    // Get resource return statistics
    $stmt = $pdo->query("SELECT 
        r.name as resource_name,
        r.category,
        COUNT(bi.id) as times_borrowed,
        SUM(bi.quantity_returned) as total_returned,
        AVG(bi.quantity_returned) as avg_returned
        FROM resources r
        LEFT JOIN borrow_items bi ON r.id = bi.resource_id
        LEFT JOIN borrow_records br ON bi.borrow_record_id = br.id
        WHERE br.status = 'Returned' OR br.status IS NULL
        GROUP BY r.id, r.name, r.category
        ORDER BY times_borrowed DESC");
    $stats['resource_stats'] = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to get return statistics: ' . $e->getMessage()
    ]);
}
?>
