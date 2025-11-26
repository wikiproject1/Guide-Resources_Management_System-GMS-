<?php
// Database connection for dashboard
$host = 'localhost';
$dbname = 'gms_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

// Initialize variables
$total_guides = 0;
$total_resources = 0;
$borrowed_resources = 0;
$missing_mandatory = 0;
$recent_borrowings = [];
$overdue_items = [];
$resource_categories = [];
$guide_activity = [];

// Load dashboard data if database is connected
if ($pdo) {
    try {
        // Get total guides
        $stmt = $pdo->query("SELECT COUNT(*) FROM guides WHERE status = 'Active'");
        $total_guides = $stmt->fetchColumn();
        
        // Get total resources
        $stmt = $pdo->query("SELECT COUNT(*) FROM resources");
        $total_resources = $stmt->fetchColumn();
        
        // Get borrowed resources count
        $stmt = $pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status = 'Borrowed'");
        $borrowed_resources = $stmt->fetchColumn();
        
        // Get missing mandatory items (borrowed but not returned)
        $stmt = $pdo->query("SELECT COUNT(*) FROM borrow_items bi 
                             JOIN resources r ON bi.resource_id = r.id 
                             JOIN borrow_records br ON bi.borrow_record_id = br.id 
                             WHERE r.category = 'Mandatory' AND br.status = 'Borrowed'");
        $missing_mandatory = $stmt->fetchColumn();
        
        // Get recent borrowings (last 5)
        $stmt = $pdo->query("SELECT br.*, g.name as guide_name, g.type as guide_type 
                             FROM borrow_records br 
                             JOIN guides g ON br.guide_id = g.id 
                             ORDER BY br.created_at DESC LIMIT 5");
        $recent_borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get overdue items
        $stmt = $pdo->query("SELECT br.*, g.name as guide_name, g.type as guide_type 
                             FROM borrow_records br 
                             JOIN guides g ON br.guide_id = g.id 
                             WHERE br.expected_return_date < NOW() AND br.status = 'Borrowed'");
        $overdue_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get resource category distribution
        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM resources GROUP BY category");
        $resource_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get guide activity (guides with most borrowings)
        $stmt = $pdo->query("SELECT g.name, g.type, COUNT(br.id) as borrow_count 
                             FROM guides g 
                             LEFT JOIN borrow_records br ON g.id = br.guide_id 
                             WHERE g.status = 'Active' 
                             GROUP BY g.id 
                             ORDER BY borrow_count DESC 
                             LIMIT 5");
        $guide_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Dashboard data loaded successfully: " . $total_guides . " guides, " . $total_resources . " resources");
        
    } catch(PDOException $e) {
        error_log("Failed to load dashboard data: " . $e->getMessage());
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Overview
        </h1>
        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
    </div>
</div>

<!-- Overview Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Guides
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_guides; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Resources
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_resources; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Borrowed Resources
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $borrowed_resources; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Missing Mandatory
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $missing_mandatory; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Resource Category Distribution -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>
                    Resource Categories
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($resource_categories)): ?>
                    <div class="chart-pie pt-4 pb-2">
                        <?php foreach ($resource_categories as $category): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($category['category']); ?></span>
                                <span class="font-weight-bold"><?php echo $category['count']; ?> items</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No resources available. Add resources to see category distribution.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Guide Activity -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i>
                    Guide Activity
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($guide_activity)): ?>
                    <?php foreach ($guide_activity as $guide): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($guide['name']); ?></strong>
                                <small class="text-muted d-block"><?php echo $guide['type']; ?></small>
                            </div>
                            <span class="badge bg-info"><?php echo $guide['borrow_count']; ?> borrowings</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No guide activity. Add guides and borrowing records to see activity.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity and Alerts -->
<div class="row mb-4">
    <!-- Recent Borrowings -->
    <div class="col-xl-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>
                    Recent Borrowings
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_borrowings)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Guide</th>
                                    <th>Date</th>
                                    <th>Expected Return</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_borrowings as $borrow): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($borrow['guide_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo $borrow['guide_type']; ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($borrow['expected_return_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $borrow['status'] === 'Borrowed' ? 'warning' : 'success'; ?>">
                                                <?php echo $borrow['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent borrowings. Record new borrowings to see activity.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- System Alerts -->
    <div class="col-xl-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    System Alerts
                </h6>
            </div>
            <div class="card-body">
                <?php if ($missing_mandatory > 0): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Warning!</strong> <?php echo $missing_mandatory; ?> mandatory items are currently borrowed
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($overdue_items)): ?>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fas fa-clock me-2"></i>
                        <div>
                            <strong>Overdue!</strong> <?php echo count($overdue_items); ?> items are past due date
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($missing_mandatory == 0 && empty($overdue_items)): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <strong>All Good!</strong> No critical alerts at this time
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="index.php?page=guides" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>
                            Add Guide
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="index.php?page=resources" class="btn btn-success w-100">
                            <i class="fas fa-box-open me-2"></i>
                            Add Resource
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="index.php?page=borrowing" class="btn btn-warning w-100">
                            <i class="fas fa-hand-holding me-2"></i>
                            Record Borrowing
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="index.php?page=borrowingadmin" class="btn btn-info w-100">
                            <i class="fas fa-cogs me-2"></i>
                            Manage Borrowings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.chart-pie {
    position: relative;
    height: 200px;
}

.badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
}
</style>
