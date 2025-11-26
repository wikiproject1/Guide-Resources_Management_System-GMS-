<?php
// Database connection for guides
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

$guides = [];

// Load existing guides from database
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM guides ORDER BY id DESC");
        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Loaded " . count($guides) . " guides from database");
    } catch(PDOException $e) {
        error_log("Failed to load guides: " . $e->getMessage());
        $guides = [];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' && $pdo) {
            try {
                // Add new guide to database
                $stmt = $pdo->prepare("INSERT INTO guides (name, type, contact_info, status, created_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['type'],
                    $_POST['contact_info'] ?? '',
                    'Active',
                    date('Y-m-d H:i:s')
                ]);
                
                // Reload guides from database
                $stmt = $pdo->query("SELECT * FROM guides ORDER BY id DESC");
                $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Success!",
                            text: "Guide added successfully to database!",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                </script>';
                
                error_log("Successfully added guide to database");
            } catch(PDOException $e) {
                error_log("Failed to add guide: " . $e->getMessage());
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to add guide to database: ' . $e->getMessage() . '",
                            icon: "error",
                            timer: 3000,
                            showConfirmButton: false
                        });
                    });
                </script>';
            }
        } elseif ($_POST['action'] == 'edit' && $pdo) {
            try {
                // Edit existing guide in database
                $stmt = $pdo->prepare("UPDATE guides SET name = ?, type = ?, contact_info = ?, status = ?, updated_at = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['type'],
                    $_POST['contact_info'] ?? '',
                    $_POST['status'] ?? 'Active',
                    date('Y-m-d H:i:s'),
                    $_POST['id']
                ]);
                
                // Reload guides from database
                $stmt = $pdo->query("SELECT * FROM guides ORDER BY id DESC");
                $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Success!",
                            text: "Guide updated successfully in database!",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                </script>';
                
                error_log("Successfully updated guide in database");
            } catch(PDOException $e) {
                error_log("Failed to update guide: " . $e->getMessage());
            }
        } elseif ($_POST['action'] == 'delete' && $pdo) {
            try {
                // Delete guide from database
                $stmt = $pdo->prepare("DELETE FROM guides WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Reload guides from database
                $stmt = $pdo->query("SELECT * FROM guides ORDER BY id DESC");
                $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Show success message
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            title: "Success!",
                            text: "Guide deleted successfully from database!",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                </script>';
                
                error_log("Successfully deleted guide from database");
            } catch(PDOException $e) {
                error_log("Failed to delete guide: " . $e->getMessage());
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i>
            Guides Management
        </h1>
        <p class="text-muted">Manage mountain and safari guides</p>
        <div class="alert alert-success">
            <strong>Ready!</strong> Guides list is empty. Add your first guide to get started.
        </div>
    </div>
</div>

<!-- Add Guide Modal -->
<div class="modal fade" id="addGuideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Add New Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="addGuideName" class="form-label">Guide Name *</label>
                        <input type="text" class="form-control" id="addGuideName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addGuideType" class="form-label">Guide Type *</label>
                        <select class="form-select" id="addGuideType" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Mountain Guide">Mountain Guide</option>
                            <option value="Safari Guide">Safari Guide</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="addGuideContact" class="form-label">Contact Information</label>
                        <textarea class="form-control" id="addGuideContact" name="contact_info" rows="3" 
                                  placeholder="Phone, email, emergency contact, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Add Guide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Guide Modal -->
<div class="modal fade" id="editGuideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>
                    Edit Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editGuideId">
                    
                    <div class="mb-3">
                        <label for="editGuideName" class="form-label">Guide Name *</label>
                        <input type="text" class="form-control" id="editGuideName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGuideType" class="form-label">Guide Type *</label>
                        <select class="form-select" id="editGuideType" name="type" required>
                            <option value="Mountain Guide">Mountain Guide</option>
                            <option value="Safari Guide">Safari Guide</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGuideContact" class="form-label">Contact Information</label>
                        <textarea class="form-control" id="editGuideContact" name="contact_info" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editGuideStatus" class="form-label">Status</label>
                        <select class="form-select" id="editGuideStatus" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Update Guide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteGuideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this guide?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteGuide()">Delete Guide</button>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="page" value="guides">
                    
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="Mountain Guide">Mountain Guide</option>
                            <option value="Safari Guide">Safari Guide</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Name or contact info">
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
                <button type="button" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#addGuideModal">
                    <i class="fas fa-user-plus me-2"></i>
                    Add New Guide
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Guides Table -->
<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Guides List (<?php echo count($guides); ?> found)
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Contact Info</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guides as $guide): ?>
                        <tr>
                            <td><strong>#<?php echo $guide['id']; ?></strong></td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($guide['name']); ?></div>
                            </td>
                            <td>
                                <?php if ($guide['type'] == 'Mountain Guide'): ?>
                                    <span class="badge bg-primary"><?php echo $guide['type']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?php echo $guide['type']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($guide['contact_info'])): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($guide['contact_info']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">No contact info</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($guide['status'] == 'Active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editGuide(<?php echo htmlspecialchars(json_encode($guide)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="viewBorrowingHistory(<?php echo $guide['id']; ?>)">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteGuide(<?php echo $guide['id']; ?>, '<?php echo htmlspecialchars($guide['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editGuide(guide) {
    document.getElementById('editGuideId').value = guide.id;
    document.getElementById('editGuideName').value = guide.name;
    document.getElementById('editGuideType').value = guide.type;
    document.getElementById('editGuideContact').value = guide.contact_info || '';
    document.getElementById('editGuideStatus').value = guide.status || 'Active';
    
    new bootstrap.Modal(document.getElementById('editGuideModal')).show();
}

function deleteGuide(id, name) {
    Swal.fire({
        title: 'Confirm Delete',
        text: 'Are you sure you want to delete ' + name + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
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

function viewBorrowingHistory(guideId) {
    window.location.href = `index.php?page=borrowing&guide_id=${guideId}`;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Guides page loaded successfully');
    
    // Clear add form when modal is opened
    const addModal = document.getElementById('addGuideModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function() {
            document.getElementById('addGuideName').value = '';
            document.getElementById('addGuideType').value = '';
            document.getElementById('addGuideContact').value = '';
        });
    }
});
</script>

