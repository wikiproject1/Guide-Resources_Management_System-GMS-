<?php
// Database connection for resources
$host = 'localhost';
$dbname = 'gms_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create resources table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS resources (
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
    
    $pdo->exec($create_table_sql);
    error_log("Resources table created/verified successfully");
    
    // IMPORTANT: Never add sample data automatically - respect user's choice
    // If user cleared the data, don't add anything back
    
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

$resources = [];

// Load existing resources from database
if ($pdo) {
    try {
        // Check if table exists first
        $table_exists = $pdo->query("SHOW TABLES LIKE 'resources'")->rowCount() > 0;
        
        if ($table_exists) {
            $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Loaded " . count($resources) . " resources from database");
        } else {
            $resources = [];
            error_log("Resources table does not exist yet");
        }
    } catch(PDOException $e) {
        error_log("Failed to load resources: " . $e->getMessage());
        $resources = [];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' && $pdo) {
            try {
                // Add new resource to database
                $stmt = $pdo->prepare("INSERT INTO resources (name, quantity_total, quantity_available, category, description, location, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    (int)$_POST['quantity_total'],
                    (int)$_POST['quantity_total'],
                    $_POST['category'],
                    $_POST['description'] ?? '',
                    $_POST['location'] ?? 'Storage Area',
                    date('Y-m-d H:i:s')
                ]);
                
                // Reload resources from database
                $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
                $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message on same page
                $success_message = "Resource added successfully to database!";
                error_log("Successfully added resource to database");
                
                // Close the modal automatically
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var modal = bootstrap.Modal.getInstance(document.getElementById("addResourceModal"));
                        if (modal) {
                            modal.hide();
                        }
                    });
                </script>';
                
            } catch(PDOException $e) {
                error_log("Failed to add resource: " . $e->getMessage());
                $error_message = "Failed to add resource to database: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'edit' && $pdo) {
            try {
                // Edit existing resource in database
                $stmt = $pdo->prepare("UPDATE resources SET name = ?, quantity_total = ?, quantity_available = ?, category = ?, description = ?, location = ?, updated_at = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    (int)$_POST['quantity_total'],
                    (int)$_POST['quantity_available'],
                    $_POST['category'],
                    $_POST['description'] ?? '',
                    $_POST['location'] ?? 'Storage Area',
                    date('Y-m-d H:i:s'),
                    $_POST['id']
                ]);
                
                // Reload resources from database
                $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
                $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Success!",
                            text: "Resource updated successfully in database!",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                </script>';
                
                error_log("Successfully updated resource in database");
            } catch(PDOException $e) {
                error_log("Failed to update resource: " . $e->getMessage());
            }
        } elseif ($_POST['action'] == 'delete' && $pdo) {
            try {
                // Delete resource from database
                $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Reload resources from database
                $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
                $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Success!",
                            text: "Resource deleted successfully from database!",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                </script>';
                
                error_log("Successfully deleted resource from database");
            } catch(PDOException $e) {
                error_log("Failed to delete resource: " . $e->getMessage());
            }
        } elseif ($_POST['action'] == 'clear_all' && $pdo) {
            try {
                // Method 1: Try TRUNCATE first (safer, preserves table structure)
                try {
                    $pdo->exec("TRUNCATE TABLE resources");
                    $success_message = "All resources cleared successfully using TRUNCATE! Table structure preserved and ready for fresh data.";
                    error_log("Successfully cleared all resources using TRUNCATE");
                } catch(PDOException $e1) {
                    // Method 2: If TRUNCATE fails, use DELETE with foreign key handling
                    error_log("TRUNCATE failed, trying DELETE method: " . $e1->getMessage());
                    
                    // Disable foreign key checks temporarily
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Clear all resources from database
                    $stmt = $pdo->prepare("DELETE FROM resources");
                    $stmt->execute();
                    
                    // Reset auto-increment to start from 1
                    $pdo->exec("ALTER TABLE resources AUTO_INCREMENT = 1");
                    
                    // Re-enable foreign key checks
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                    
                    $success_message = "All resources cleared successfully using DELETE method! Table structure preserved and ready for fresh data.";
                    error_log("Successfully cleared all resources using DELETE method");
                }
                
                // Force reload resources from database to confirm they're gone
                $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
                $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message on same page
                error_log("Final resource count after clearing: " . count($resources));
                
                // Add a flag to prevent any automatic data insertion
                $_SESSION['resources_cleared'] = true;
                
            } catch(PDOException $e) {
                // Re-enable foreign key checks in case of error
                try {
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                } catch(Exception $e2) {
                    error_log("Failed to re-enable foreign key checks: " . $e2->getMessage());
                }
                
                error_log("Failed to clear resources: " . $e->getMessage());
                $error_message = "Failed to clear resources: " . $e->getMessage();
            }
        }
    }
}

// Pagination logic
$items_per_page = 10;
$current_page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$total_items = count($resources);
$total_pages = max(1, ceil($total_items / $items_per_page));

// Ensure current page is valid
if ($current_page < 1) {
    $current_page = 1;
}
if ($current_page > $total_pages) {
    $current_page = $total_pages;
}

$start_index = ($current_page - 1) * $items_per_page;
$end_index = min($start_index + $items_per_page, $total_items);
$current_resources = array_slice($resources, $start_index, $items_per_page);

// Fallback: if no resources on current page, show all resources
if (empty($current_resources) && !empty($resources)) {
    $current_resources = $resources;
    $current_page = 1;
    $total_pages = 1;
    $start_index = 0;
    $end_index = count($resources);
}

// Final fallback: if still no resources, show empty state
if (empty($current_resources)) {
    $current_resources = [];
    $current_page = 1;
    $total_pages = 1;
    $start_index = 0;
    $end_index = 0;
}

// Force reload resources if we have none
if (empty($current_resources) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_items = count($resources);
        $total_pages = max(1, ceil($total_items / $items_per_page));
        $current_resources = array_slice($resources, 0, $items_per_page);
        $start_index = 0;
        $end_index = count($current_resources);
        error_log("Forced reload of resources from database - found: " . count($resources));
    } catch(PDOException $e) {
        error_log("Failed to force reload resources: " . $e->getMessage());
    }
}

// Debug info
error_log("Resources page loaded - Total: $total_items, Pages: $total_pages, Current: $current_page, Showing: " . count($current_resources));
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-boxes me-2"></i>
                    Resources Management
                </h1>
                <p class="text-muted">Manage inventory and track resource availability</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                    <i class="fas fa-plus me-2"></i>
                    Add New Resource
                </button>
                <button type="button" class="btn btn-success btn-lg ms-2" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt me-2"></i>
                    Refresh Page
                </button>
                <button type="button" class="btn btn-danger btn-lg ms-2" data-bs-toggle="modal" data-bs-target="#clearAllModal">
                    <i class="fas fa-trash me-2"></i>
                    Clear All Data
                </button>
            </div>
        </div>
        
        <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <strong>🎉 Success!</strong> Your new resource has been added to the database and is now visible below.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Success/Error messages from form submission -->
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
        
        <!-- Empty state message when no resources exist -->
        <?php if (empty($resources)): ?>
            <div class="alert alert-info mt-3">
                <strong>📋 Database Status:</strong> 
                <br>✅ Resources table is completely empty and ready for fresh data.
                <br>🚀 Click "Add New Resource" to start building your inventory!
            </div>
        <?php endif; ?>
        
        <!-- Debug information -->
        <div class="alert alert-info mt-3">
            <strong>Debug Info:</strong>
            <br>Database connection: <?php echo ($pdo ? 'Connected' : 'Failed'); ?>
            <br>Total resources: <?php echo $total_items; ?>
            <br>Total pages: <?php echo $total_pages; ?>
            <br>Current page: <?php echo $current_page; ?>
            <br>Resources on this page: <?php echo count($current_resources); ?>
            <br>Start index: <?php echo $start_index; ?>
            <br>End index: <?php echo $end_index; ?>
        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addResourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-box-open me-2"></i>
                    Add New Resource
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Resource Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Mandatory">Mandatory (Must Return)</option>
                                    <option value="Optional">Optional (Flexible)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity_total" class="form-label">Total Quantity *</label>
                                <input type="number" class="form-control" id="quantity_total" name="quantity_total" min="1" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_stock_level" class="form-label">Minimum Stock Level</label>
                                <input type="number" class="form-control" id="min_stock_level" name="min_stock_level" min="0" value="0">
                                <small class="text-muted">Alert when stock falls below this level</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Detailed description of the resource"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               placeholder="Where this resource is stored">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Add Resource
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Resource Modal -->
<div class="modal fade" id="editResourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Edit Resource
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Resource Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_category" class="form-label">Category *</label>
                                <select class="form-select" id="edit_category" name="category" required>
                                    <option value="Mandatory">Mandatory (Must Return)</option>
                                    <option value="Optional">Optional (Flexible)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_quantity_total" class="form-label">Total Quantity *</label>
                                <input type="number" class="form-control" id="edit_quantity_total" name="quantity_total" min="1" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_quantity_available" class="form-label">Available Quantity *</label>
                                <input type="number" class="form-control" id="edit_quantity_available" name="quantity_available" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_location" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="edit_location" name="location">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Update Resource
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clear All Data Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Clear All Resources
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="clear_all">
                    
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">⚠️ Complete Database Reset!</h5>
                        <p>This will <strong>PERMANENTLY DELETE ALL RESOURCES</strong> and:</p>
                        <ul class="text-start text-danger">
                            <li>Remove all resource data from MySQL database</li>
                            <li>Drop and recreate the resources table</li>
                            <li>Reset auto-increment ID counter to 1</li>
                            <li>Start completely fresh with empty table</li>
                        </ul>
                        <p class="text-muted">This action cannot be undone and will completely reset your resources system.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Yes, Clear All Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="page" value="resources">
                    
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="Mandatory">Mandatory</option>
                            <option value="Optional">Optional</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                            <option value="Missing">Missing</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Name, description, or location">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <button type="button" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                    <i class="fas fa-box-open me-2"></i>
                    Add New Resource
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Resources Table -->
<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Resources Inventory (<?php echo $total_items; ?> total, showing <?php echo count($current_resources); ?> on this page)
        </h6>
    </div>
    <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($current_resources)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <h5>No Resources Found</h5>
                                    <p>There are currently no resources in the system.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                                        <i class="fas fa-plus me-2"></i>
                                        Add Your First Resource
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($current_resources as $resource): ?>
                            <tr>
                                <td><strong>#<?php echo $resource['id']; ?></strong></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($resource['name']); ?></div>
                                    <?php if (!empty($resource['description'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($resource['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                <?php if ($resource['category'] == 'Mandatory'): ?>
                                    <span class="badge bg-danger">Mandatory</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Optional</span>
                                <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <strong><?php echo $resource['quantity_available']; ?></strong> / <?php echo $resource['quantity_total']; ?>
                                        </div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <?php 
                                            $percentage = $resource['quantity_total'] > 0 ? ($resource['quantity_available'] / $resource['quantity_total']) * 100 : 0;
                                            $color = $percentage > 50 ? 'success' : ($percentage > 20 ? 'warning' : 'danger');
                                            ?>
                                            <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                <span class="badge bg-success">Available</span>
                                </td>
                                <td>
                                    <?php if (!empty($resource['location'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($resource['location']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Not specified</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editResource(<?php echo htmlspecialchars(json_encode($resource)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewBorrowingHistory(<?php echo $resource['id']; ?>)">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteResource(<?php echo $resource['id']; ?>, '<?php echo htmlspecialchars($resource['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
        </div>
        
        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Resources pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=resources&page_num=<?php echo $current_page - 1; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="fas fa-chevron-left"></i> Previous
                            </span>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=resources&page_num=1">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=resources&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=resources&page_num=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Next Page -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=resources&page_num=<?php echo $current_page + 1; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">
                                Next <i class="fas fa-chevron-right"></i>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <!-- Page Info -->
            <div class="text-center text-muted mt-2">
                Showing <?php echo $start_index + 1; ?> to <?php echo $end_index; ?> of <?php echo $total_items; ?> resources
                (Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>)
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function editResource(resource) {
    document.getElementById('edit_id').value = resource.id;
    document.getElementById('edit_name').value = resource.name;
    document.getElementById('edit_category').value = resource.category;
    document.getElementById('edit_quantity_total').value = resource.quantity_total;
    document.getElementById('edit_quantity_available').value = resource.quantity_available;
    document.getElementById('edit_description').value = resource.description;
    document.getElementById('edit_location').value = resource.location;
    
    new bootstrap.Modal(document.getElementById('editResourceModal')).show();
}

function deleteResource(id, name) {
    // Use SweetAlert2 for better UX
    Swal.fire({
        title: 'Delete Resource?',
        html: `Are you sure you want to delete <strong>${name}</strong>?<br><br><span class="text-danger">This action cannot be undone!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function viewBorrowingHistory(resourceId) {
    window.location.href = `index.php?page=borrowing&resource_id=${resourceId}`;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Resources page loaded successfully');
    
    // Check if we have resources displayed
    const resourceRows = document.querySelectorAll('tbody tr');
    console.log('Found ' + resourceRows.length + ' resource rows');
    
    // If no resources displayed but we should have some, reload the page
    if (resourceRows.length === 0 && <?php echo $total_items; ?> > 0) {
        console.log('No resources displayed but total is ' + <?php echo $total_items; ?> + ', reloading page');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
    
    // Show loading indicator when navigating between pages
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (!this.parentElement.classList.contains('disabled')) {
                document.getElementById('loadingIndicator').style.display = 'block';
            }
        });
    });
    
    // Hide loading indicator after page loads
    setTimeout(() => {
        document.getElementById('loadingIndicator').style.display = 'none';
    }, 500);
    
    // Optimize table rendering
    const table = document.querySelector('.table');
    if (table) {
        table.style.opacity = '0';
        setTimeout(() => {
            table.style.transition = 'opacity 0.3s ease-in';
            table.style.opacity = '1';
        }, 100);
    }
});
</script>
