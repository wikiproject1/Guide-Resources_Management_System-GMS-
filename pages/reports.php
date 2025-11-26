<?php
// Reports page with full functionality
$report_type = $_GET['report'] ?? 'current_borrowed';

// Database queries for different report types
try {
    switch ($report_type) {
        case 'current_borrowed':
            $stmt = $pdo->prepare("
                SELECT br.*, g.name as guide_name, g.type as guide_type, g.contact_info,
                       COUNT(bi.id) as total_items,
                       SUM(CASE WHEN bi.is_returned = 0 THEN 1 ELSE 0 END) as borrowed_items
                FROM borrow_records br
                JOIN guides g ON br.guide_id = g.id
                LEFT JOIN borrow_items bi ON br.id = bi.borrow_record_id
                WHERE br.status = 'Borrowed'
                GROUP BY br.id
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute();
            $current_borrowed = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'missing_mandatory':
            $stmt = $pdo->prepare("
                SELECT r.*, 
                       COALESCE(SUM(CASE WHEN bi.is_returned = 0 THEN bi.quantity_borrowed ELSE 0 END), 0) as borrowed_quantity
                FROM resources r
                LEFT JOIN borrow_items bi ON r.id = bi.resource_id
                LEFT JOIN borrow_records br ON bi.borrow_record_id = br.id AND br.status = 'Borrowed'
                WHERE r.category = 'MANDATORY' AND r.quantity_available < 1
                GROUP BY r.id
                ORDER BY r.quantity_available ASC
            ");
            $stmt->execute();
            $missing_mandatory = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'guide_history':
            $guide_id = $_GET['guide_id'] ?? '';
            $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $date_to = $_GET['date_to'] ?? date('Y-m-d');
            
            $where_conditions = ["br.borrow_date BETWEEN ? AND ?"];
            $params = [$date_from, $date_to];
            
            if ($guide_id) {
                $where_conditions[] = "br.guide_id = ?";
                $params[] = $guide_id;
            }
            
            $where_clause = implode(" AND ", $where_conditions);
            
            $stmt = $pdo->prepare("
                SELECT br.*, g.name as guide_name, g.type as guide_type,
                       COUNT(bi.id) as total_items,
                       SUM(CASE WHEN bi.is_returned = 1 THEN 1 ELSE 0 END) as returned_items,
                       SUM(CASE WHEN bi.is_returned = 0 THEN 1 ELSE 0 END) as borrowed_items
                FROM borrow_records br
                JOIN guides g ON br.guide_id = g.id
                LEFT JOIN borrow_items bi ON br.id = bi.borrow_record_id
                WHERE {$where_clause}
                GROUP BY br.id
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute($params);
            $guide_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'inventory':
            $stmt = $pdo->prepare("
                SELECT r.*, 
                       COALESCE(SUM(CASE WHEN bi.is_returned = 0 THEN bi.quantity_borrowed ELSE 0 END), 0) as borrowed_quantity,
                       COALESCE(SUM(CASE WHEN bi.is_returned = 1 THEN bi.quantity_borrowed ELSE 0 END), 0) as returned_quantity
                FROM resources r
                LEFT JOIN borrow_items bi ON r.id = bi.resource_id
                LEFT JOIN borrow_records br ON bi.borrow_record_id = br.id
                GROUP BY r.id
                ORDER BY r.category, r.name
            ");
            $stmt->execute();
            $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
    // Get guides for filter dropdown
    $stmt = $pdo->prepare("SELECT id, name, type FROM guides WHERE status = 'Active' ORDER BY name");
    $stmt->execute();
    $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
    $error_message = "Database error occurred while generating report.";
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar me-2"></i>
            Reports & Analytics
        </h1>
        <p class="text-muted">Generate comprehensive reports and export data</p>
    </div>
</div>

<!-- Report Selection -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>
                    Select Report Type
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="?page=reports&report=current_borrowed" 
                           class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type == 'current_borrowed' ? 'active' : ''; ?>">
                            <i class="fas fa-hand-holding me-2"></i>
                            Current Borrowed
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=reports&report=missing_mandatory" 
                           class="btn btn-outline-danger w-100 mb-2 <?php echo $report_type == 'missing_mandatory' ? 'active' : ''; ?>">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Missing Items
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=reports&report=guide_history" 
                           class="btn btn-outline-info w-100 mb-2 <?php echo $report_type == 'guide_history' ? 'active' : ''; ?>">
                            <i class="fas fa-history me-2"></i>
                            Guide History
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=reports&report=inventory" 
                           class="btn btn-outline-success w-100 mb-2 <?php echo $report_type == 'inventory' ? 'active' : ''; ?>">
                            <i class="fas fa-boxes me-2"></i>
                            Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Filters (for Guide History) -->
<?php if ($report_type == 'guide_history'): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog me-2"></i>
                        Report Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="page" value="reports">
                        <input type="hidden" name="report" value="guide_history">
                        
                        <div class="col-md-3">
                            <label for="guide_id" class="form-label">Guide</label>
                            <select class="form-select" id="guide_id" name="guide_id">
                                <option value="">All Guides</option>
                                <?php foreach ($guides as $guide): ?>
                                    <option value="<?php echo $guide['id']; ?>" <?php echo ($_GET['guide_id'] ?? '') == $guide['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($guide['name']); ?> (<?php echo $guide['type']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?php echo $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?php echo $_GET['date_to'] ?? date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Report Content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-alt me-2"></i>
                <?php 
                $report_titles = [
                    'current_borrowed' => 'Current Borrowed Resources',
                    'missing_mandatory' => 'Missing Mandatory Items',
                    'guide_history' => 'Borrowing History by Guide',
                    'inventory' => 'Inventory Overview'
                ];
                echo $report_titles[$report_type] ?? 'Report';
                ?>
            </h6>
            <small class="text-muted">
                <?php 
                $report_descriptions = [
                    'current_borrowed' => 'All currently borrowed resources with guide details and return dates',
                    'missing_mandatory' => 'Critical resources that must be returned but are currently missing',
                    'guide_history' => 'Complete transaction history for all guides',
                    'inventory' => 'Complete inventory status with usage statistics'
                ];
                echo $report_descriptions[$report_type] ?? 'Report description';
                ?>
            </small>
        </div>
        
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportToCSV()">
                <i class="fas fa-download me-1"></i>
                Export CSV
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportToPDF()">
                <i class="fas fa-file-pdf me-1"></i>
                Export PDF
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <?php switch ($report_type): 
                case 'current_borrowed': ?>
                    <?php if (!empty($current_borrowed)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="reportTable">
                                <thead>
                                    <tr>
                                        <th>Guide</th>
                                        <th>Type</th>
                                        <th>Borrow Date</th>
                                        <th>Expected Return</th>
                                        <th>Items Borrowed</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($current_borrowed as $borrow): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($borrow['guide_name']); ?></strong></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($borrow['guide_type']); ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                                            <td>
                                                <?php 
                                                $expected_date = strtotime($borrow['expected_return_date']);
                                                $today = time();
                                                $days_remaining = ceil(($expected_date - $today) / (60 * 60 * 24));
                                                $badge_class = $days_remaining < 0 ? 'bg-danger' : ($days_remaining <= 3 ? 'bg-warning' : 'bg-success');
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo date('M d, Y', $expected_date); ?>
                                                    (<?php echo $days_remaining < 0 ? abs($days_remaining) . ' days overdue' : $days_remaining . ' days left'; ?>)
                                                </span>
                                            </td>
                                            <td><span class="badge bg-primary"><?php echo $borrow['borrowed_items']; ?> items</span></td>
                                            <td><span class="badge bg-warning">Borrowed</span></td>
                                            <td><?php echo htmlspecialchars($borrow['notes'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No currently borrowed resources. All items are available.</p>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>
                    
                <?php case 'missing_mandatory': ?>
                    <?php if (!empty($missing_mandatory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="reportTable">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th>Category</th>
                                        <th>Total Quantity</th>
                                        <th>Available</th>
                                        <th>Borrowed</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($missing_mandatory as $item): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                            <td><span class="badge bg-danger"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                            <td><?php echo $item['quantity_total']; ?></td>
                                            <td><span class="badge bg-danger"><?php echo $item['quantity_available']; ?></span></td>
                                            <td><span class="badge bg-warning"><?php echo $item['borrowed_quantity']; ?></span></td>
                                            <td><span class="badge bg-danger">Critical</span></td>
                                            <td>
                                                <a href="?page=borrowing" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-arrow-left me-1"></i>
                                                    Process Return
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">All mandatory items are available. No critical shortages.</p>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>
                    
                <?php case 'guide_history': ?>
                    <?php if (!empty($guide_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="reportTable">
                                <thead>
                                    <tr>
                                        <th>Guide</th>
                                        <th>Type</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Total Items</th>
                                        <th>Returned</th>
                                        <th>Borrowed</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($guide_history as $record): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($record['guide_name']); ?></strong></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($record['guide_type']); ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
                                            <td>
                                                <?php if ($record['actual_return_date']): ?>
                                                    <span class="badge bg-success"><?php echo date('M d, Y', strtotime($record['actual_return_date'])); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-primary"><?php echo $record['total_items']; ?></span></td>
                                            <td><span class="badge bg-success"><?php echo $record['returned_items']; ?></span></td>
                                            <td><span class="badge bg-warning"><?php echo $record['borrowed_items']; ?></span></td>
                                            <td>
                                                <?php if ($record['status'] == 'Returned'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Active</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                            <p class="text-muted">No borrowing history found for the selected criteria.</p>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>
                    
                <?php case 'inventory': ?>
                    <?php if (!empty($inventory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="reportTable">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th>Category</th>
                                        <th>Total</th>
                                        <th>Available</th>
                                        <th>Borrowed</th>
                                        <th>Returned</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventory as $item): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $item['category'] == 'MANDATORY' ? 'bg-danger' : 'bg-secondary'; ?>">
                                                    <?php echo htmlspecialchars($item['category']); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo $item['quantity_total']; ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $item['quantity_available'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $item['quantity_available']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $item['borrowed_quantity'] > 0 ? 'bg-warning' : 'bg-secondary'; ?>">
                                                    <?php echo $item['borrowed_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $item['returned_quantity'] > 0 ? 'bg-info' : 'bg-secondary'; ?>">
                                                    <?php echo $item['returned_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_badge = 'bg-success';
                                                if ($item['quantity_available'] == 0) $status_badge = 'bg-danger';
                                                elseif ($item['quantity_available'] <= ($item['quantity_total'] * 0.2)) $status_badge = 'bg-warning';
                                                ?>
                                                <span class="badge <?php echo $status_badge; ?>">
                                                    <?php 
                                                    if ($item['quantity_available'] == 0) echo 'Out of Stock';
                                                    elseif ($item['quantity_available'] <= ($item['quantity_total'] * 0.2)) echo 'Low Stock';
                                                    else echo 'In Stock';
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No inventory data available.</p>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>
                    
                <?php default: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Select a report type to view data.</p>
                    </div>
            <?php endswitch; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function exportToCSV() {
    const table = document.getElementById('reportTable');
    if (!table) {
        showError('No data available to export.');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Get text content without HTML tags
            let text = cols[j].innerText || cols[j].textContent || '';
            // Clean up the text and escape quotes
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV file
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', '<?php echo $report_type; ?>_report_<?php echo date('Y-m-d'); ?>.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showSuccess('CSV report exported successfully!');
}

function exportToPDF() {
    showError('PDF export functionality will be implemented soon. Use CSV export for now.');
}

// Helper functions for notifications
function showSuccess(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
