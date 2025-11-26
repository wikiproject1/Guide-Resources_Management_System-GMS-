<?php
// Database connection for borrowing admin
$host = 'localhost';
$dbname = 'gms_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create necessary tables if they don't exist
    $create_guides_sql = "CREATE TABLE IF NOT EXISTS guides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type ENUM('Mountain Guide', 'Safari Guide') NOT NULL,
        contact_info TEXT,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($create_guides_sql);
    
    $create_resources_sql = "CREATE TABLE IF NOT EXISTS resources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        quantity_total INT NOT NULL DEFAULT 0,
        quantity_available INT NOT NULL DEFAULT 0,
        category ENUM('Mandatory', 'Optional') NOT NULL DEFAULT 'Optional',
        description TEXT,
        location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($create_resources_sql);
    
    $create_borrow_records_sql = "CREATE TABLE IF NOT EXISTS borrow_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        guide_id INT NOT NULL,
        borrow_date TIMESTAMP NOT NULL,
        expected_return_date TIMESTAMP NOT NULL,
        actual_return_date TIMESTAMP NULL,
        status ENUM('Borrowed', 'Returned', 'Overdue', 'Missing') DEFAULT 'Borrowed',
        notes TEXT,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($create_borrow_records_sql);
    
    $create_borrow_items_sql = "CREATE TABLE IF NOT EXISTS borrow_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        borrow_record_id INT NOT NULL,
        resource_id INT NOT NULL,
        quantity_borrowed INT NOT NULL DEFAULT 0,
        quantity_returned INT NOT NULL DEFAULT 0,
        is_returned BOOLEAN DEFAULT FALSE,
        return_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($create_borrow_items_sql);
    
    error_log("All necessary tables created/verified successfully");
    
    // Add sample guides if table is empty
    $check_guides = $pdo->query("SELECT COUNT(*) FROM guides");
    $guides_count = $check_guides->fetchColumn();
    
    if ($guides_count == 0) {
        $sample_guides = [
            ['name' => 'John Smith', 'type' => 'Mountain Guide', 'contact_info' => 'Phone: +1234567890'],
            ['name' => 'Sarah Johnson', 'type' => 'Safari Guide', 'contact_info' => 'Email: sarah@safari.com'],
            ['name' => 'Mike Wilson', 'type' => 'Mountain Guide', 'contact_info' => 'Phone: +1987654321'],
            ['name' => 'Emma Davis', 'type' => 'Safari Guide', 'contact_info' => 'Email: emma@safari.com']
        ];
        
        $insert_guide = $pdo->prepare("INSERT INTO guides (name, type, contact_info, status, created_at) VALUES (?, ?, ?, 'Active', ?)");
        foreach ($sample_guides as $guide) {
            $insert_guide->execute([$guide['name'], $guide['type'], $guide['contact_info'], date('Y-m-d H:i:s')]);
        }
        error_log("Added " . count($sample_guides) . " sample guides");
    }
    
    // Add sample resources if table is empty
    $check_resources = $pdo->query("SELECT COUNT(*) FROM resources");
    $resources_count = $check_resources->fetchColumn();
    
    if ($resources_count == 0) {
        $sample_resources = [
            ['name' => 'Tent (4-person)', 'quantity_total' => 10, 'quantity_available' => 10, 'category' => 'Mandatory', 'description' => '4-person camping tent for outdoor activities', 'location' => 'Storage Room A'],
            ['name' => 'Sleeping Bags', 'quantity_total' => 20, 'quantity_available' => 20, 'category' => 'Mandatory', 'description' => 'Warm sleeping bags for camping trips', 'location' => 'Storage Room A'],
            ['name' => 'First Aid Kit', 'quantity_total' => 5, 'quantity_available' => 5, 'category' => 'Mandatory', 'description' => 'Emergency first aid supplies', 'location' => 'Medical Storage'],
            ['name' => 'GPS Device', 'quantity_total' => 8, 'quantity_available' => 8, 'category' => 'Optional', 'description' => 'Navigation GPS devices for hiking', 'location' => 'Equipment Room'],
            ['name' => 'Water Bottles', 'quantity_total' => 50, 'quantity_available' => 50, 'category' => 'Optional', 'description' => 'Reusable water bottles for trips', 'location' => 'Storage Room B']
        ];
        
        $insert_resource = $pdo->prepare("INSERT INTO resources (name, quantity_total, quantity_available, category, description, location, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($sample_resources as $resource) {
            $insert_resource->execute([$resource['name'], $resource['quantity_total'], $resource['quantity_available'], $resource['category'], $resource['description'], $resource['location'], date('Y-m-d H:i:s')]);
        }
        error_log("Added " . count($sample_resources) . " sample resources");
    }
    
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

$borrowings = [];
$guides = [];
$resources = [];

// Load existing data from database
if ($pdo) {
    try {
        // Load guides
        $stmt = $pdo->query("SELECT * FROM guides WHERE status = 'Active' ORDER BY name");
        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Loaded guides: " . json_encode($guides));
        
        // Load resources
        $stmt = $pdo->query("SELECT * FROM resources WHERE quantity_available > 0 ORDER BY name");
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Loaded resources: " . json_encode($resources));
        
        // Load borrowings
        $stmt = $pdo->query("SELECT br.*, g.name as guide_name, g.type as guide_type 
                             FROM borrow_records br 
                             LEFT JOIN guides g ON br.guide_id = g.id 
                             ORDER BY br.created_at DESC");
        $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Loaded " . count($guides) . " guides, " . count($resources) . " resources, " . count($borrowings) . " borrowings");
        
        // Debug information
        if (empty($guides)) {
            error_log("WARNING: No guides found in database");
        }
        if (empty($resources)) {
            error_log("WARNING: No resources found in database");
        }
        
    } catch(PDOException $e) {
        error_log("Failed to load data: " . $e->getMessage());
        $guides = [];
        $resources = [];
        $borrowings = [];
    }
} else {
    error_log("No database connection available");
    $guides = [];
    $resources = [];
    $borrowings = [];
}

// Test: If no data loaded, add some test data for debugging
if (empty($guides) && $pdo) {
    error_log("No guides found, adding test guide");
    try {
        $stmt = $pdo->prepare("INSERT INTO guides (name, type, contact_info, status, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Test Guide', 'Mountain Guide', 'Test Contact', 'Active', date('Y-m-d H:i:s')]);
        
        // Reload guides
        $stmt = $pdo->query("SELECT * FROM guides WHERE status = 'Active' ORDER BY name");
        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Added test guide, now have " . count($guides) . " guides");
    } catch(PDOException $e) {
        error_log("Failed to add test guide: " . $e->getMessage());
    }
}

if (empty($resources) && $pdo) {
    error_log("No resources found, adding test resource");
    try {
        $stmt = $pdo->prepare("INSERT INTO resources (name, quantity_total, quantity_available, category, description, location, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Test Resource', 10, 10, 'Optional', 'Test Description', 'Test Location', date('Y-m-d H:i:s')]);
        
        // Reload resources
        $stmt = $pdo->query("SELECT * FROM resources WHERE quantity_available > 0 ORDER BY name");
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Added test resource, now have " . count($resources) . " resources");
    } catch(PDOException $e) {
        error_log("Failed to add test resource: " . $e->getMessage());
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'borrow' && $pdo) {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Create borrowing record
                $stmt = $pdo->prepare("INSERT INTO borrow_records (guide_id, borrow_date, expected_return_date, notes, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['guide_id'],
                    date('Y-m-d H:i:s'),
                    $_POST['expected_return_date'] . ' 23:59:59',
                    $_POST['notes'] ?? '',
                    1, // Assuming admin user ID is 1
                    date('Y-m-d H:i:s')
                ]);
                
                $borrow_id = $pdo->lastInsertId();
                
                // Add borrowed items
                if (isset($_POST['resources']) && isset($_POST['quantities'])) {
                    $stmt = $pdo->prepare("INSERT INTO borrow_items (borrow_record_id, resource_id, quantity_borrowed, created_at) VALUES (?, ?, ?, ?)");
                    
                    foreach ($_POST['resources'] as $index => $resource_id) {
                        if (!empty($resource_id) && !empty($_POST['quantities'][$index])) {
                            $quantity = (int)$_POST['quantities'][$index];
                            
                            // Insert borrow item
                            $stmt->execute([$borrow_id, $resource_id, $quantity, date('Y-m-d H:i:s')]);
                            
                            // Update resource availability
                            $update_stmt = $pdo->prepare("UPDATE resources SET quantity_available = quantity_available - ? WHERE id = ?");
                            $update_stmt->execute([$quantity, $resource_id]);
                        }
                    }
                }
                
                // Commit transaction
                $pdo->commit();
                
                $success_message = "Borrowing recorded successfully!";
                error_log("Successfully recorded borrowing ID: $borrow_id");
                
                // Reload data
                $stmt = $pdo->query("SELECT br.*, g.name as guide_name, g.type as guide_type 
                                     FROM borrow_records br 
                                     LEFT JOIN guides g ON br.guide_id = g.id 
                                     ORDER BY br.created_at DESC");
                $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch(PDOException $e) {
                $pdo->rollBack();
                error_log("Failed to record borrowing: " . $e->getMessage());
                $error_message = "Failed to record borrowing: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'return' && $pdo) {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                $borrow_id = $_POST['borrow_id'];
                
                // Update borrow record status
                $stmt = $pdo->prepare("UPDATE borrow_records SET status = 'Returned', actual_return_date = ? WHERE id = ?");
                $stmt->execute([date('Y-m-d H:i:s'), $borrow_id]);
                
                // Update borrow items
                $stmt = $pdo->prepare("UPDATE borrow_items SET is_returned = TRUE, return_date = ? WHERE borrow_record_id = ?");
                $stmt->execute([date('Y-m-d H:i:s'), $borrow_id]);
                
                // Return resources to inventory
                $stmt = $pdo->prepare("UPDATE resources r 
                                      JOIN borrow_items bi ON r.id = bi.resource_id 
                                      SET r.quantity_available = r.quantity_available + bi.quantity_borrowed 
                                      WHERE bi.borrow_record_id = ?");
                $stmt->execute([$borrow_id]);
                
                // Commit transaction
                $pdo->commit();
                
                $success_message = "Return processed successfully!";
                error_log("Successfully processed return for borrowing ID: $borrow_id");
                
                // Reload data
                $stmt = $pdo->query("SELECT br.*, g.name as guide_name, g.type as guide_type 
                                     FROM borrow_records br 
                                     LEFT JOIN guides g ON br.guide_id = g.id 
                                     ORDER BY br.created_at DESC");
                $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch(PDOException $e) {
                $pdo->rollBack();
                error_log("Failed to process return: " . $e->getMessage());
                $error_message = "Failed to process return: " . $e->getMessage();
            }
        }
    }
}

// Test display - remove this after fixing
if ($pdo) {
    echo "<!-- DEBUG: Database connected -->";
    echo "<!-- DEBUG: Guides count: " . count($guides) . " -->";
    echo "<!-- DEBUG: Resources count: " . count($resources) . " -->";
    
    if (!empty($guides)) {
        echo "<!-- DEBUG: First guide: " . $guides[0]['name'] . " -->";
    }
    if (!empty($resources)) {
        echo "<!-- DEBUG: First resource: " . $resources[0]['name'] . " -->";
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-hand-holding me-2"></i>
            Borrowing Administration
        </h1>
        <p class="text-muted">Manage all borrowing records and process returns</p>
    </div>
</div>

<!-- Debug Information -->
<div class="alert alert-info">
    <strong>📊 System Status:</strong>
    <br>Database Connection: <?php echo ($pdo ? '✅ Connected' : '❌ Failed'); ?>
    <br>Guides Available: <?php echo count($guides); ?> (<?php echo empty($guides) ? 'None - Add guides first' : 'Ready for borrowing'; ?>)
    <br>Resources Available: <?php echo count($resources); ?> (<?php echo empty($resources) ? 'None - Add resources first' : 'Ready for borrowing'; ?>)
    <br>Total Borrowings: <?php echo count($borrowings); ?>
</div>



<!-- Success/Error messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <strong>🎉 Success!</strong> <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <strong>❌ Error!</strong> <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- New Borrowing Form -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-plus me-2"></i>
            Record New Borrowing
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" id="borrowForm">
            <input type="hidden" name="action" value="borrow">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="guide_id" class="form-label">Select Guide *</label>
                        <select class="form-select" id="guide_id" name="guide_id" required>
                            <option value="">Choose a guide...</option>
                            <?php foreach ($guides as $guide): ?>
                                <option value="<?php echo $guide['id']; ?>">
                                    <?php echo htmlspecialchars($guide['name']); ?> (<?php echo $guide['type']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="expected_return_date" class="form-label">Expected Return Date *</label>
                        <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" 
                               value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="2" 
                          placeholder="Additional notes about this borrowing"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Select Resources *</label>
                <div id="resourceSelection">
                    <div class="row mb-2 resource-row">
                        <div class="col-md-6">
                            <select class="form-select" name="resources[]" required>
                                <option value="">Choose resource...</option>
                                <?php foreach ($resources as $resource): ?>
                                    <option value="<?php echo $resource['id']; ?>">
                                        <?php echo htmlspecialchars($resource['name']); ?> 
                                        (Available: <?php echo $resource['quantity_available']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" name="quantities[]" 
                                   placeholder="Qty" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-resource" style="display: none;">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary btn-sm" id="addResource">
                    <i class="fas fa-plus me-1"></i>
                    Add Another Resource
                </button>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>
                    Record Borrowing
                </button>
            </div>
        </form>
    </div>
</div>

<!-- All Borrowings Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            All Borrowing Records (<?php echo count($borrowings); ?> total)
        </h6>
        <div>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="printAllBorrowings()">
                <i class="fas fa-print me-1"></i>
                Print All
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($borrowings)): ?>
            <div class="text-center py-5">
                <div class="text-muted">
                    <i class="fas fa-hand-holding fa-3x mb-3"></i>
                    <h5>No Borrowing Records Found</h5>
                    <p>There are currently no borrowing records in the system.</p>
                    <p>Use the form above to record new borrowings.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guide</th>
                            <th>Borrow Date</th>
                            <th>Expected Return</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowings as $borrow): ?>
                            <tr>
                                <td><strong>#<?php echo $borrow['id']; ?></strong></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($borrow['guide_name']); ?></div>
                                    <small class="text-muted"><?php echo $borrow['guide_type']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                                <td>
                                    <?php 
                                    $expected = strtotime($borrow['expected_return_date']);
                                    $today = time();
                                    $is_overdue = $expected < $today && $borrow['status'] !== 'Returned';
                                    ?>
                                    <span class="<?php echo $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo date('M d, Y', $expected); ?>
                                    </span>
                                    <?php if ($is_overdue): ?>
                                        <br><small class="text-danger">OVERDUE!</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = match($borrow['status']) {
                                        'Borrowed' => 'warning',
                                        'Returned' => 'success',
                                        'Overdue' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $borrow['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($borrow['notes'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($borrow['notes']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($borrow['status'] === 'Borrowed'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="processReturn(<?php echo $borrow['id']; ?>)">
                                                <i class="fas fa-check"></i> Return
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewBorrowingDetails(<?php echo $borrow['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="printBorrowing(<?php echo $borrow['id']; ?>)">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Return Confirmation Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Process Return
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="return">
                    <input type="hidden" name="borrow_id" id="return_borrow_id">
                    
                    <div class="text-center">
                        <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                        <h5>Confirm Return</h5>
                        <p>Are you sure you want to process the return for borrowing <strong id="return_borrow_id_display"></strong>?</p>
                        <p class="text-muted">This will mark the borrowing as returned and restore resources to inventory.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>
                        Yes, Process Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Add resource row functionality
document.getElementById('addResource').addEventListener('click', function() {
    const resourceSelection = document.getElementById('resourceSelection');
    const newRow = document.createElement('div');
    newRow.className = 'row mb-2 resource-row';
    newRow.innerHTML = `
        <div class="col-md-6">
            <select class="form-select" name="resources[]" required>
                <option value="">Choose resource...</option>
                <?php foreach ($resources as $resource): ?>
                    <option value="<?php echo $resource['id']; ?>">
                        <?php echo htmlspecialchars($resource['name']); ?> 
                        (Available: <?php echo $resource['quantity_available']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control" name="quantities[]" 
                   placeholder="Qty" min="1" required>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-danger btn-sm remove-resource">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
    `;
    
    resourceSelection.appendChild(newRow);
    
    // Show remove button for all rows except first
    const rows = resourceSelection.querySelectorAll('.resource-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.remove-resource');
        if (index === 0) {
            removeBtn.style.display = 'none';
        } else {
            removeBtn.style.display = 'block';
        }
    });
});

// Remove resource row functionality
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-resource')) {
        const row = e.target.closest('.resource-row');
        if (row) {
            row.remove();
            
            // Update remove button visibility
            const rows = document.querySelectorAll('.resource-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-resource');
                if (index === 0) {
                    removeBtn.style.display = 'none';
                } else {
                    removeBtn.style.display = 'block';
                }
            });
        }
    }
});

// Process return functionality
function processReturn(borrowId) {
    document.getElementById('return_borrow_id').value = borrowId;
    document.getElementById('return_borrow_id_display').textContent = '#' + borrowId;
    new bootstrap.Modal(document.getElementById('returnModal')).show();
}

// View borrowing details
function viewBorrowingDetails(borrowId) {
    // Redirect to borrowing details page
    window.location.href = `index.php?page=borrowing&id=${borrowId}`;
}

// Print borrowing
function printBorrowing(borrowId) {
    window.open(`print_borrowing_receipt.php?id=${borrowId}`, '_blank');
}

// Print all borrowings
function printAllBorrowings() {
    window.open('print_borrowing_receipt.php?all=1', '_blank');
}
</script>
