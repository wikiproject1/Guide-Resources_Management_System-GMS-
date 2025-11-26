<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gms_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// First connect without database to create it if needed
try {
    $pdo_temp = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Now connect to the specific database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables($pdo) {
    // Users table (Admin)
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('Admin') DEFAULT 'Admin',
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Guides table
    $sql = "CREATE TABLE IF NOT EXISTS guides (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        type ENUM('Mountain Guide', 'Safari Guide') NOT NULL,
        contact_info TEXT,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Resources table
    $sql = "CREATE TABLE IF NOT EXISTS resources (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        category ENUM('Mandatory', 'Optional') NOT NULL,
        quantity_total INT NOT NULL DEFAULT 0,
        quantity_available INT NOT NULL DEFAULT 0,
        description TEXT,
        status ENUM('Available', 'Borrowed', 'Missing') DEFAULT 'Available',
        min_stock_level INT DEFAULT 0,
        location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Borrow records table
    $sql = "CREATE TABLE IF NOT EXISTS borrow_records (
        id INT PRIMARY KEY AUTO_INCREMENT,
        guide_id INT NOT NULL,
        borrow_date TIMESTAMP NOT NULL,
        expected_return_date TIMESTAMP NOT NULL,
        actual_return_date TIMESTAMP NULL,
        status ENUM('Borrowed', 'Returned', 'Overdue', 'Missing') DEFAULT 'Borrowed',
        notes TEXT,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (guide_id) REFERENCES guides(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Borrow items table
    $sql = "CREATE TABLE IF NOT EXISTS borrow_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        borrow_record_id INT NOT NULL,
        resource_id INT NOT NULL,
        quantity_borrowed INT NOT NULL DEFAULT 0,
        quantity_returned INT NOT NULL DEFAULT 0,
        is_returned BOOLEAN DEFAULT FALSE,
        return_date TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (borrow_record_id) REFERENCES borrow_records(id) ON DELETE CASCADE,
        FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Activity log table
    $sql = "CREATE TABLE IF NOT EXISTS activity_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        admin_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Insert default admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password_hash, role) VALUES ('admin', 'admin@gms.com', ?, 'Admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$password_hash]);
    }

    // Insert sample data if tables are empty
    // IMPORTANT: DO NOT automatically insert sample data - let users add their own
    // This prevents unwanted data from appearing after clearing tables
    // $stmt = $pdo->prepare("SELECT COUNT(*) FROM guides");
    // $stmt->execute();
    // if ($stmt->fetchColumn() == 0) {
    //     $sql = "INSERT INTO guides (name, type, contact_info) VALUES 
    //             ('John Smith', 'Mountain Guide', 'Phone: +1234567890'),
    //             ('Sarah Johnson', 'Safari Guide', 'Email: sarah@safari.com'),
    //             ('Mike Wilson', 'Mountain Guide', 'Phone: +1987654321')";
    //     $pdo->exec($sql);
    // }
}

// Create tables
createTables($pdo);
?>
