<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Debug information
error_log("Print borrowing receipt accessed with parameters: " . json_encode($_GET));

// Check if printing all borrowings
$print_all = isset($_GET['all']) && $_GET['all'] == '1';

if ($print_all) {
    error_log("Printing all borrowings");
    try {
        // Get all borrowing records
        $stmt = $pdo->query("SELECT br.*, g.name as guide_name, g.type as guide_type, g.contact_info 
                             FROM borrow_records br 
                             JOIN guides g ON br.guide_id = g.id 
                             ORDER BY br.created_at DESC");
        $all_borrowings = $stmt->fetchAll();
        
        error_log("Found " . count($all_borrowings) . " total borrowings");
        
        // Get all borrowing items
        $stmt = $pdo->query("SELECT bi.*, r.name as resource_name, r.category as resource_category 
                             FROM borrow_items bi 
                             JOIN resources r ON bi.resource_id = r.id 
                             ORDER BY bi.borrow_record_id");
        $all_items = $stmt->fetchAll();
        
        // Group items by borrow record
        $items_by_borrow = [];
        foreach ($all_items as $item) {
            $borrow_id = $item['borrow_record_id'];
            if (!isset($items_by_borrow[$borrow_id])) {
                $items_by_borrow[$borrow_id] = [];
            }
            $items_by_borrow[$borrow_id][] = $item;
        }
        
        // Set variables for template
        $borrow_details = null;
        $borrow_items = [];
        $all_borrowings_data = $all_borrowings;
        $items_by_borrow_data = $items_by_borrow;
        
    } catch (Exception $e) {
        error_log("Database error in print all borrowings: " . $e->getMessage());
        die('Database Error: ' . $e->getMessage());
    }
} else {
    // Get borrow ID from URL - accept both 'id' and 'borrow_id' parameters
    $borrow_id = (int)($_GET['borrow_id'] ?? $_GET['id'] ?? 0);

    error_log("Extracted borrow_id: " . $borrow_id);

    if (empty($borrow_id)) {
        error_log("Error: No borrow ID provided");
        die('Invalid borrow ID. Please provide a valid borrowing ID.');
    }

    try {
        // Get borrowing details
        $stmt = $pdo->prepare("SELECT br.*, g.name as guide_name, g.type as guide_type, g.contact_info FROM borrow_records br JOIN guides g ON br.guide_id = g.id WHERE br.id = ?");
        $stmt->execute([$borrow_id]);
        $borrow_details = $stmt->fetch();
        
        error_log("Borrowing details query result: " . json_encode($borrow_details));
        
        if (!$borrow_details) {
            error_log("Error: Borrowing record not found for ID: " . $borrow_id);
            die('Borrowing record not found. ID: ' . $borrow_id . ' does not exist in the database.');
        }
        
        // Get borrowing items
        $stmt = $pdo->prepare("SELECT bi.*, r.name as resource_name, r.category as resource_category FROM borrow_items bi JOIN resources r ON bi.resource_id = r.id WHERE bi.borrow_record_id = ?");
        $stmt->execute([$borrow_id]);
        $borrow_items = $stmt->fetchAll();
        
        error_log("Borrowing items query result: " . json_encode($borrow_items));
        
    } catch (Exception $e) {
        error_log("Database error in print borrowing: " . $e->getMessage());
        die('Database Error: ' . $e->getMessage());
    }
}

// Separate mandatory and optional items
$mandatory_items = array_filter($borrow_items, function($item) {
    return $item['resource_category'] === 'Mandatory';
});

$optional_items = array_filter($borrow_items, function($item) {
    return $item['resource_category'] === 'Optional';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $print_all ? 'All Borrowings Report' : 'Borrowing Receipt - ' . htmlspecialchars($borrow_details['guide_name']); ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: white; 
            color: #333;
            line-height: 1.6;
        }
        
        .print-header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .document-title {
            font-size: 24px;
            color: #7f8c8d;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .receipt-number {
            font-size: 18px;
            color: #95a5a6;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .info-item {
            margin-bottom: 20px;
        }
        
        .info-label {
            font-weight: bold;
            color: #34495e;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 18px;
            color: #2c3e50;
            font-weight: 600;
            padding: 8px 0;
        }
        
        .resources-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .resources-table th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            font-weight: bold;
            padding: 15px;
            text-align: left;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .resources-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }
        
        .mandatory-row {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 5px solid #f39c12;
        }
        
        .optional-row {
            background: #f8f9fa;
        }
        
        .category-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .mandatory-badge {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            box-shadow: 0 2px 4px rgba(243, 156, 18, 0.3);
        }
        
        .optional-badge {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
            box-shadow: 0 2px 4px rgba(149, 165, 166, 0.3);
        }
        
        .important-notice {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 3px solid #f39c12;
            padding: 25px;
            border-radius: 12px;
            margin: 40px 0;
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.2);
        }
        
        .important-notice h4 {
            color: #d68910;
            margin-bottom: 20px;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
        }
        
        .important-notice ul {
            margin: 0;
            padding-left: 25px;
        }
        
        .important-notice li {
            margin-bottom: 12px;
            color: #d68910;
            font-weight: 600;
        }
        
        .signature-section {
            margin-top: 60px;
        }
        
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px;
            border: 2px solid #bdc3c7;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .signature-line {
            border-top: 2px solid #2c3e50;
            width: 250px;
            margin: 0 auto 15px;
            height: 2px;
        }
        
        .signature-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .signature-date {
            color: #7f8c8d;
            font-size: 14px;
            font-style: italic;
        }
        
        .footer {
            margin-top: 60px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 2px solid #ecf0f1;
            padding-top: 25px;
        }
        
        .footer p {
            margin: 8px 0;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
        }
        
        .print-button:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(52, 152, 219, 0.4);
        }
        
        .print-button:active {
            transform: translateY(0);
        }
        
        @media print {
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Receipt
    </button>
    
    <div class="print-header">
        <div class="company-name">GMS System</div>
        <div class="document-title"><?php echo $print_all ? 'All Borrowings Report' : 'Resource Borrowing Receipt'; ?></div>
        <?php if (!$print_all): ?>
            <div class="receipt-number">Receipt #<?php echo $borrow_details['id']; ?></div>
        <?php endif; ?>
    </div>

    <?php if ($print_all): ?>
        <!-- All Borrowings Report -->
        <div class="section">
            <div class="section-title">Complete Borrowings Report</div>
            <div class="info-item">
                <div class="info-label">Total Borrowings</div>
                <div class="info-value"><?php echo count($all_borrowings_data); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Report Generated</div>
                <div class="info-value"><?php echo date('F j, Y \a\t g:i A'); ?></div>
            </div>
        </div>

        <?php foreach ($all_borrowings_data as $borrow): ?>
            <div class="section page-break">
                <div class="section-title">Borrowing #<?php echo $borrow['id']; ?> - <?php echo htmlspecialchars($borrow['guide_name']); ?></div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Guide Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($borrow['guide_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Guide Type</div>
                        <div class="info-value"><?php echo htmlspecialchars($borrow['guide_type']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Borrow Date</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($borrow['borrow_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Expected Return Date</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($borrow['expected_return_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value"><?php echo htmlspecialchars($borrow['status']); ?></div>
                    </div>
                </div>

                <?php if (!empty($borrow['notes'])): ?>
                <div class="info-item">
                    <div class="info-label">Notes</div>
                    <div class="info-value"><?php echo htmlspecialchars($borrow['notes']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (isset($items_by_borrow_data[$borrow['id']])): ?>
                <div class="section">
                    <div class="section-title">Borrowed Resources</div>
                    <table class="resources-table">
                        <thead>
                            <tr>
                                <th>Resource Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Return Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_by_borrow_data[$borrow['id']] as $item): ?>
                            <tr class="<?php echo $item['resource_category'] === 'Mandatory' ? 'mandatory-row' : 'optional-row'; ?>">
                                <td><strong><?php echo htmlspecialchars($item['resource_name']); ?></strong></td>
                                <td>
                                    <span class="category-badge <?php echo $item['resource_category'] === 'Mandatory' ? 'mandatory-badge' : 'optional-badge'; ?>">
                                        <?php echo $item['resource_category']; ?>
                                    </span>
                                </td>
                                <td><?php echo $item['quantity_borrowed']; ?></td>
                                <td>
                                    <?php if ($item['resource_category'] === 'Mandatory'): ?>
                                        <strong style="color: #e74c3c;">MUST BE RETURNED</strong>
                                    <?php else: ?>
                                        <span style="color: #7f8c8d;">Optional return</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <!-- Single Borrowing Receipt -->
        <div class="section">
            <div class="section-title">Borrowing Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Guide Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($borrow_details['guide_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Guide Type</div>
                    <div class="info-value"><?php echo htmlspecialchars($borrow_details['guide_type']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Borrow Date</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($borrow_details['borrow_date'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Expected Return Date</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($borrow_details['expected_return_date'])); ?></div>
                </div>
            </div>
            
            <?php if (!empty($borrow_details['contact_info'])): ?>
            <div class="info-item">
                <div class="info-label">Contact Information</div>
                <div class="info-value"><?php echo htmlspecialchars($borrow_details['contact_info']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($borrow_details['notes'])): ?>
            <div class="info-item">
                <div class="info-label">Notes</div>
                <div class="info-value"><?php echo htmlspecialchars($borrow_details['notes']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-title">Borrowed Resources</div>
            <table class="resources-table">
                <thead>
                    <tr>
                        <th>Resource Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Return Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrow_items as $item): ?>
                    <tr class="<?php echo $item['resource_category'] === 'Mandatory' ? 'mandatory-row' : 'optional-row'; ?>">
                        <td><strong><?php echo htmlspecialchars($item['resource_name']); ?></strong></td>
                        <td>
                            <span class="category-badge <?php echo $item['resource_category'] === 'Mandatory' ? 'mandatory-badge' : 'optional-badge'; ?>">
                                <?php echo $item['resource_category']; ?>
                            </span>
                        </td>
                        <td><?php echo $item['quantity_borrowed']; ?></td>
                        <td>
                            <?php if ($item['resource_category'] === 'Mandatory'): ?>
                                <strong style="color: #e74c3c;">MUST BE RETURNED</strong>
                            <?php else: ?>
                                <span style="color: #7f8c8d;">Optional return</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="important-notice">
            <h4>⚠️ IMPORTANT NOTICE</h4>
            <ul>
                <li><strong>Mandatory Items:</strong> All items marked as "Mandatory" MUST be returned by <?php echo date('F j, Y', strtotime($borrow_details['expected_return_date'])); ?></li>
                <li><strong>Late Returns:</strong> Late returns may result in penalties or restrictions</li>
                <li><strong>Item Condition:</strong> Return items in the same condition as borrowed</li>
                <li><strong>Contact:</strong> Contact the admin immediately if items are damaged or lost</li>
                <li><strong>Receipt:</strong> Keep this receipt safe and return it with the items</li>
            </ul>
        </div>
        
        <div class="signature-section">
            <div class="section-title">Signatures</div>
            <div class="signature-grid">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Guide Signature</div>
                    <div class="signature-date">Date: <?php echo date('F j, Y'); ?></div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Admin Signature</div>
                    <div class="signature-date">Date: <?php echo date('F j, Y'); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <p><strong>This receipt serves as proof of borrowing. Please keep it safe and return it with the items.</strong></p>
        <p>Generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>
        <p>GMS System - Resource Management</p>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
