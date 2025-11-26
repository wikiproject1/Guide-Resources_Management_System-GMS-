<?php
// Utility functions for the GMS system

/**
 * Get dashboard statistics
 */
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total guides
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM guides WHERE status = 'Active'");
    $stats['total_guides'] = $stmt->fetch()['total'];
    
    // Total resources
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM resources");
    $stats['total_resources'] = $stmt->fetch()['total'];
    
    // Borrowed resources
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM resources WHERE status = 'Borrowed'");
    $stats['borrowed_resources'] = $stmt->fetch()['total'];
    
    // Missing mandatory items
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM resources r 
                         JOIN borrow_items bi ON r.id = bi.resource_id 
                         JOIN borrow_records br ON bi.borrow_record_id = br.id 
                         WHERE r.category = 'Mandatory' AND r.status = 'Missing'");
    $stats['missing_mandatory'] = $stmt->fetch()['total'];
    
    // Overdue returns
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM borrow_records 
                         WHERE status = 'Borrowed' AND expected_return_date < NOW()");
    $stats['overdue_returns'] = $stmt->fetch()['total'];
    
    return $stats;
}

/**
 * Get recent activity
 */
function getRecentActivity($pdo, $limit = 10) {
    $sql = "SELECT 'borrow' as type, br.id, g.name as guide_name, r.name as resource_name, 
                   br.borrow_date as date, 'Borrowed' as action
            FROM borrow_records br
            JOIN guides g ON br.guide_id = g.id
            JOIN borrow_items bi ON br.id = bi.borrow_record_id
            JOIN resources r ON bi.resource_id = r.id
            WHERE br.status = 'Borrowed'
            UNION ALL
            SELECT 'return' as type, br.id, g.name as guide_name, r.name as resource_name,
                   br.actual_return_date as date, 'Returned' as action
            FROM borrow_records br
            JOIN guides g ON br.guide_id = g.id
            JOIN borrow_items bi ON br.id = bi.borrow_record_id
            JOIN resources r ON bi.resource_id = r.id
            WHERE br.status = 'Returned'
            ORDER BY date DESC
            LIMIT " . (int)$limit;
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get borrowing trends data
 */
function getBorrowingTrends($pdo, $days = 30) {
    $sql = "SELECT DATE(br.borrow_date) as date, COUNT(*) as count
            FROM borrow_records br
            WHERE br.borrow_date >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY DATE(br.borrow_date)
            ORDER BY date";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get guide activity data
 */
function getGuideActivity($pdo) {
    $sql = "SELECT g.name, 
                   COUNT(CASE WHEN br.status = 'Borrowed' THEN 1 END) as borrowed,
                   COUNT(CASE WHEN br.status = 'Returned' THEN 1 END) as returned
            FROM guides g
            LEFT JOIN borrow_records br ON g.id = br.guide_id
            WHERE g.status = 'Active'
            GROUP BY g.id, g.name
            ORDER BY borrowed DESC";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get resource category distribution
 */
function getResourceCategoryDistribution($pdo) {
    $sql = "SELECT r.category, COUNT(*) as count
            FROM resources r
            JOIN borrow_items bi ON r.id = bi.resource_id
            JOIN borrow_records br ON bi.borrow_record_id = br.id
            WHERE br.status = 'Borrowed'
            GROUP BY r.category";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Update resource status based on availability
 */
function updateResourceStatus($pdo, $resource_id) {
    $sql = "UPDATE resources 
            SET status = CASE 
                WHEN quantity_available = 0 THEN 'Borrowed'
                WHEN quantity_available < quantity_total THEN 'Borrowed'
                ELSE 'Available'
            END
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$resource_id]);
}

/**
 * Check for missing mandatory items
 */
function checkMissingMandatoryItems($pdo) {
    $sql = "SELECT r.id, r.name, g.name as guide_name, br.id as borrow_id
            FROM resources r
            JOIN borrow_items bi ON r.id = bi.resource_id
            JOIN borrow_records br ON bi.borrow_record_id = br.id
            JOIN guides g ON br.guide_id = g.id
            WHERE r.category = 'Mandatory' 
            AND r.status = 'Missing'
            AND br.status = 'Borrowed'";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get overdue returns
 */
function getOverdueReturns($pdo) {
    $sql = "SELECT br.id, g.name as guide_name, r.name as resource_name,
                   br.borrow_date, br.expected_return_date,
                   DATEDIFF(NOW(), br.expected_return_date) as days_overdue
            FROM borrow_records br
            JOIN guides g ON br.guide_id = g.id
            JOIN borrow_items bi ON br.id = bi.borrow_record_id
            JOIN resources r ON bi.resource_id = r.id
            WHERE br.status = 'Borrowed' 
            AND br.expected_return_date < NOW()
            ORDER BY days_overdue DESC";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $class = 'status-' . strtolower($status);
    return "<span class='status-badge {$class}'>{$status}</span>";
}

/**
 * Get category badge HTML
 */
function getCategoryBadge($category) {
    $class = 'category-' . strtolower($category);
    return "<span class='category-badge {$class}'>{$category}</span>";
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Log activity
 */
function logActivity($pdo, $action, $details, $admin_id) {
    // This could be expanded to log all admin actions
    // For now, we'll just return true
    return true;
}

/**
 * Get return statistics for dashboard
 */
function getReturnStats($pdo) {
    $stats = [];
    
    // Total returns
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM borrow_records WHERE status = 'Returned'");
    $stats['total_returns'] = $stmt->fetch()['total'];
    
    // Recent returns (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) as recent FROM borrow_records WHERE status = 'Returned' AND actual_return_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['recent_returns'] = $stmt->fetch()['recent'];
    
    // Overdue returns
    $stmt = $pdo->query("SELECT COUNT(*) as overdue FROM borrow_records WHERE status = 'Borrowed' AND expected_return_date < NOW()");
    $stats['overdue_returns'] = $stmt->fetch()['overdue'];
    
    // Missing mandatory items
    $stmt = $pdo->query("SELECT COUNT(*) as missing FROM resources r JOIN borrow_items bi ON r.id = bi.resource_id JOIN borrow_records br ON bi.borrow_record_id = br.id WHERE r.category = 'Mandatory' AND r.status = 'Missing' AND br.status = 'Borrowed'");
    $stats['missing_mandatory'] = $stmt->fetch()['missing'];
    
    return $stats;
}

/**
 * Get return trends data
 */
function getReturnTrends($pdo, $days = 30) {
    $sql = "SELECT DATE(actual_return_date) as date, COUNT(*) as count
            FROM borrow_records 
            WHERE status = 'Returned' 
            AND actual_return_date >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY DATE(actual_return_date)
            ORDER BY date";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get guide return performance
 */
function getGuideReturnPerformance($pdo) {
    $sql = "SELECT g.name, 
                   COUNT(CASE WHEN br.status = 'Returned' THEN 1 END) as returns_completed,
                   COUNT(CASE WHEN br.status = 'Borrowed' THEN 1 END) as active_borrowings,
                   COUNT(CASE WHEN br.status = 'Overdue' THEN 1 END) as overdue_count
            FROM guides g
            LEFT JOIN borrow_records br ON g.id = br.guide_id
            WHERE g.status = 'Active'
            GROUP BY g.id, g.name
            ORDER BY returns_completed DESC";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Check if return is overdue
 */
function isReturnOverdue($expected_date) {
    return strtotime($expected_date) < time();
}

/**
 * Get days until return is due
 */
function getDaysUntilDue($expected_date) {
    $expected = strtotime($expected_date);
    $today = time();
    return ceil(($expected - $today) / (60 * 60 * 24));
}

/**
 * Get return status badge with appropriate styling
 */
function getReturnStatusBadge($status, $expected_date = null) {
    switch ($status) {
        case 'Returned':
            return '<span class="badge bg-success">Returned</span>';
        case 'Borrowed':
            if ($expected_date && isReturnOverdue($expected_date)) {
                $days_overdue = abs(getDaysUntilDue($expected_date));
                return '<span class="badge bg-danger">Overdue (' . $days_overdue . ' days)</span>';
            } elseif ($expected_date) {
                $days_left = getDaysUntilDue($expected_date);
                if ($days_left <= 3) {
                    return '<span class="badge bg-warning">Due soon (' . $days_left . ' days)</span>';
                } else {
                    return '<span class="badge bg-info">' . $days_left . ' days left</span>';
                }
            }
            return '<span class="badge bg-primary">Borrowed</span>';
        case 'Overdue':
            return '<span class="badge bg-danger">Overdue</span>';
        case 'Missing':
            return '<span class="badge bg-warning">Missing</span>';
        default:
            return '<span class="badge bg-secondary">' . $status . '</span>';
    }
}
?>
