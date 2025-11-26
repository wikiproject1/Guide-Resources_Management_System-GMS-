<?php
// Use global database connection from config/database.php
// No need to create new connection - $pdo is already available

// Initialize variables
$current_borrowings = [];
$returned_items = [];
$completed_returns = [];
$guides = [];
$success_message = '';
$error_message = '';

// Load data if database is connected
if ($pdo) {
    try {
        error_log("Starting to load returns data...");
        
        // Get current active borrowings - using actual schema
        $query = "SELECT br.*, g.name as guide_name, g.type as guide_type 
                  FROM borrow_records br 
                  JOIN guides g ON br.guide_id = g.id 
                  WHERE br.status = 'Borrowed' 
                  ORDER BY br.borrow_date DESC";
        error_log("Executing query: " . $query);
        
        $stmt = $pdo->query($query);
        $current_borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Current borrowings loaded: " . count($current_borrowings));
        error_log("Borrowings data: " . json_encode($current_borrowings));
        
        // Get returned items history - using actual schema
        $query = "SELECT bi.*, r.name as resource_name, r.category, br.borrow_date, br.expected_return_date, 
                         g.name as guide_name, g.type as guide_type, br.notes
                  FROM borrow_items bi 
                  JOIN resources r ON bi.resource_id = r.id 
                  JOIN borrow_records br ON bi.borrow_record_id = br.id 
                  JOIN guides g ON br.guide_id = g.id 
                  WHERE bi.is_returned = 1 
                  ORDER BY bi.return_date DESC";
        error_log("Executing query: " . $query);
        
        $stmt = $pdo->query($query);
        $returned_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Returned items loaded: " . count($returned_items));
        
        // Get completed returns summary - using actual schema
        $query = "SELECT br.*, g.name as guide_name, g.type as guide_type, 
                         COUNT(bi.id) as total_items, 
                         SUM(CASE WHEN bi.is_returned = 1 THEN 1 ELSE 0 END) as returned_items
                  FROM borrow_records br 
                  JOIN guides g ON br.guide_id = g.id 
                  LEFT JOIN borrow_items bi ON br.id = bi.borrow_record_id 
                  WHERE br.status = 'Returned' 
                  GROUP BY br.id 
                  ORDER BY br.actual_return_date DESC";
        error_log("Executing query: " . $query);
        
        $stmt = $pdo->query($query);
        $completed_returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Completed returns loaded: " . count($completed_returns));
        
        // Get all guides for dropdown
        $query = "SELECT id, name, type FROM guides WHERE status = 'Active' ORDER BY name";
        error_log("Executing query: " . $query);
        
        $stmt = $pdo->query($query);
        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Guides loaded: " . count($guides));
        
        error_log("Returns data loaded successfully: " . count($current_borrowings) . " borrowings, " . count($returned_items) . " returned items");
        
    } catch(PDOException $e) {
        error_log("Failed to load returns data: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
    }
} else {
    error_log("No database connection available");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $pdo) {
    if ($_POST['action'] == 'return') {
        try {
            $borrow_record_id = (int)$_POST['borrow_record_id'];
            $notes = trim($_POST['notes'] ?? '');
            
            if (empty($borrow_record_id)) {
                $error_message = "Please select a borrowing record to return.";
            } else {
                // Get borrowing details
                $stmt = $pdo->prepare("SELECT * FROM borrow_records WHERE id = ? AND status = 'Borrowed'");
                $stmt->execute([$borrow_record_id]);
                $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($borrowing) {
                    // Start transaction
                    $pdo->beginTransaction();
                    
                    // Update borrow record status - using actual schema
                    $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'Returned', actual_return_date = NOW(), notes = ? WHERE id = ?");
                    $stmt->execute([$notes, $borrow_record_id]);
                    
                    // Update all borrow items to returned - using actual schema
                    $stmt = $pdo->prepare("UPDATE borrow_items SET is_returned = 1, return_date = NOW() WHERE borrow_record_id = ?");
                    $stmt->execute([$borrow_record_id]);
                    
                    // Restore resource quantities
                    $stmt = $pdo->prepare("UPDATE resources r 
                                          JOIN borrow_items bi ON r.id = bi.resource_id 
                                          SET r.quantity_available = r.quantity_available + bi.quantity_borrowed 
                                          WHERE bi.borrow_record_id = ?");
                    $stmt->execute([$borrow_record_id]);
                    
                    $pdo->commit();
                    $success_message = "Return processed successfully! All items have been returned and resources restored.";
                    
                    // Reload data
                    header("Location: index.php?page=returns&success=1");
                    exit();
                    
                } else {
                    $error_message = "Invalid borrowing record or already returned.";
                }
            }
            
        } catch(PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = "Error processing return: " . $e->getMessage();
            error_log("Return processing error: " . $e->getMessage());
        }
    }
}

// Show success message if redirected
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Return processed successfully! All items have been returned and resources restored.";
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-undo me-2"></i>
            Returns Management
        </h1>
        <p class="text-muted">Process resource returns and track return history</p>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Debug Information -->
<div class="alert alert-info">
    <strong>🔍 Debug Info:</strong>
    <br>Database Connection: <?php echo ($pdo ? '✅ Connected' : '❌ Failed'); ?>
    <br>Current Borrowings: <?php echo count($current_borrowings); ?>
    <br>Returned Items: <?php echo count($returned_items); ?>
    <br>Completed Returns: <?php echo count($completed_returns); ?>
    <br>Guides: <?php echo count($guides); ?>
    <?php if (!empty($current_borrowings)): ?>
        <br><strong>First Borrowing:</strong> <?php echo json_encode($current_borrowings[0]); ?>
    <?php endif; ?>
</div>

<!-- Return Form for Specific Borrowing -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-undo me-2"></i>
            Process Return for Borrowing
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" id="returnForm">
            <input type="hidden" name="action" value="return">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="borrow_record" class="form-label">Select Borrowing Record:</label>
                    <select class="form-select" id="borrow_record" name="borrow_record_id" required>
                        <option value="">Choose a borrowing record...</option>
                        <?php if (!empty($current_borrowings)): ?>
                            <?php foreach ($current_borrowings as $borrow): ?>
                                <option value="<?php echo $borrow['id']; ?>">
                                    <?php echo htmlspecialchars($borrow['guide_name']); ?> - 
                                    <?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status:</label>
                    <div class="form-control-plaintext">
                        <?php if (!empty($current_borrowings)): ?>
                            <span class="badge bg-success"><?php echo count($current_borrowings); ?> active borrowings</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No active borrowings</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Return Items</label>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>Category</th>
                                <th>Borrowed Qty</th>
                                <th>Return Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="returnItemsTable">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <?php if (!empty($current_borrowings)): ?>
                                        Select a borrowing record above to see items
                                    <?php else: ?>
                                        No items to return. Add borrowing data first.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Return Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="2" 
                          placeholder="Any notes about the return"></textarea>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg" <?php echo empty($current_borrowings) ? 'disabled' : ''; ?>>
                    <i class="fas fa-check me-2"></i>
                    Process Return
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Returned Items History -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-history me-2"></i>
            Returned Items History (<?php echo count($returned_items); ?> items)
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($returned_items)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Resource</th>
                            <th>Category</th>
                            <th>Guide</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($returned_items as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['resource_name']); ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['guide_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item['borrow_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($item['return_date'])); ?></td>
                                <td><?php echo $item['quantity_borrowed']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-undo fa-3x text-muted mb-3"></i>
                <p class="text-muted">No returned items found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Completed Returns Summary -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-check-circle me-2"></i>
            Completed Returns Summary (<?php echo count($completed_returns); ?> returns)
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($completed_returns)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Guide</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Total Items</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_returns as $return): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($return['guide_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo $return['guide_type']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($return['borrow_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($return['actual_return_date'])); ?></td>
                                <td><?php echo $return['total_items']; ?> items</td>
                                <td>
                                    <?php if ($return['notes']): ?>
                                        <span class="text-muted"><?php echo htmlspecialchars($return['notes']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                <p class="text-muted">No completed returns found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Current Borrowings -->
<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Current Borrowings (<?php echo count($current_borrowings); ?> active)
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($current_borrowings)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Guide</th>
                            <th>Borrow Date</th>
                            <th>Expected Return</th>
                            <th>Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_borrowings as $borrow): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($borrow['guide_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo $borrow['guide_type']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                                <td>
                                    <?php 
                                    $expected_date = strtotime($borrow['expected_return_date']);
                                    $is_overdue = $expected_date < time();
                                    $date_class = $is_overdue ? 'text-danger' : 'text-success';
                                    ?>
                                    <span class="<?php echo $date_class; ?>">
                                        <?php echo date('M d, Y', $expected_date); ?>
                                        <?php if ($is_overdue): ?>
                                            <i class="fas fa-exclamation-triangle ms-1" title="Overdue"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    // Get item count for this borrowing
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_items WHERE borrow_record_id = ?");
                                    $stmt->execute([$borrow['id']]);
                                    $item_count = $stmt->fetchColumn();
                                    echo $item_count . " item" . ($item_count != 1 ? 's' : '');
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning">Active</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-hand-holding fa-3x text-muted mb-3"></i>
                <p class="text-muted">No active borrowings at this time.</p>
                <p class="text-muted">Database connection: <?php echo ($pdo ? 'Working' : 'Failed'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewBorrowingDetails(borrowId) {
    // Show borrowing details modal or redirect to detailed view
    window.location.href = `index.php?page=returns&borrow_id=${borrowId}`;
}

// Load borrowing items when selection changes
document.addEventListener('DOMContentLoaded', function() {
    const borrowSelect = document.getElementById('borrow_record');
    const returnItemsTable = document.getElementById('returnItemsTable');
    
    if (borrowSelect) {
        borrowSelect.addEventListener('change', function() {
            const borrowId = this.value;
            
            if (borrowId) {
                // Load borrowing items
                fetch(`ajax/get_borrowing_items.php?borrow_id=${borrowId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayReturnItems(data.items);
                        } else {
                            returnItemsTable.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading items</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        returnItemsTable.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load items</td></tr>';
                    });
            } else {
                returnItemsTable.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Select a borrowing record above to see items</td></tr>';
            }
        });
    }
});

function displayReturnItems(items) {
    const table = document.getElementById('returnItemsTable');
    
    if (items.length === 0) {
        table.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items found for this borrowing</td></tr>';
        return;
    }
    
    let html = '';
    
    items.forEach(item => {
        html += `
            <tr>
                <td><strong>${item.resource_name}</strong></td>
                <td><span class="badge bg-secondary">${item.category}</span></td>
                <td>${item.quantity_borrowed}</td>
                <td>${item.quantity_borrowed}</td>
                <td><span class="badge bg-warning">${item.status}</span></td>
            </tr>
        `;
    });
    
    table.innerHTML = html;
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const returnForm = document.getElementById('returnForm');
    if (returnForm) {
        returnForm.addEventListener('submit', function(e) {
            const borrowId = document.getElementById('borrow_record').value;
            if (!borrowId) {
                e.preventDefault();
                alert('Please select a borrowing record to return.');
                return;
            }
            
            // Confirm return
            if (!confirm('Are you sure you want to process this return? This will restore all resources to available inventory.')) {
                e.preventDefault();
                return;
            }
        });
    }
});
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
}

.table th {
    background-color: #f8f9fc;
    border-top: none;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
