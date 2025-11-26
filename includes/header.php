<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GMS - Admin Resource Management System</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/gms-icon.svg">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="assets/images/gms-icon-large.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 2px;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }
        
        .main-content {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #fd7e14 100%);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #e83e8c 100%);
            border: none;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            font-weight: 600;
            color: #495057;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-available { background-color: #d4edda; color: #155724; }
        .status-borrowed { background-color: #fff3cd; color: #856404; }
        .status-missing { background-color: #f8d7da; color: #721c24; }
        
        .category-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .category-mandatory { background-color: #cce5ff; color: #004085; }
        .category-optional { background-color: #e2e3e5; color: #383d41; }
        
        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px;
        }
        
        .metric-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .metric-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .metric-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .metric-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .metric-guides { color: var(--primary-color); }
        .metric-resources { color: var(--success-color); }
        .metric-borrowed { color: var(--warning-color); }
        .metric-missing { color: var(--danger-color); }
        .metric-overdue { color: var(--info-color); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mountain me-2"></i>
                GMS System
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'guides' ? 'active' : ''; ?>" href="index.php?page=guides">
                            <i class="fas fa-users me-1"></i>
                            Guides
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'resources' ? 'active' : ''; ?>" href="index.php?page=resources">
                            <i class="fas fa-boxes me-1"></i>
                            Resources
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'borrowing' ? 'active' : ''; ?>" href="index.php?page=borrowing">
                            <i class="fas fa-hand-holding me-1"></i>
                            Borrowing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'returns' ? 'active' : ''; ?>" href="index.php?page=returns">
                            <i class="fas fa-undo me-1"></i>
                            Returns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'reports' ? 'active' : ''; ?>" href="index.php?page=reports">
                            <i class="fas fa-chart-bar me-1"></i>
                            Reports
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container main-content">
