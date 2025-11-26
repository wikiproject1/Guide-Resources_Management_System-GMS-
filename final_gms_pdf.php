<?php
require_once 'vendor/autoload.php';

use TCPDF as TCPDF;

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('GMS System');
$pdf->SetAuthor('GMS Development Team');
$pdf->SetTitle('Guide Management System - Professional Presentation');

// Set margins
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add a page
$pdf->AddPage();

// Cover Page
$pdf->SetFont('helvetica', 'B', 24);
$pdf->Cell(0, 20, 'GUIDE MANAGEMENT SYSTEM (GMS)', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 15, 'Complete Tour Company Management Solution', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('helvetica', '', 14);
$pdf->Cell(0, 10, 'Professional Resource Management for Safari & Mountain Tours', 0, 1, 'C');
$pdf->Ln(20);

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'Presented by: GMS Development Team', 0, 1, 'C');
$pdf->Cell(0, 10, 'Date: ' . date('F Y'), 0, 1, 'C');

$pdf->AddPage();

// Executive Summary
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 15, 'EXECUTIVE SUMMARY', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, 'The Guide Management System (GMS) is a comprehensive, cloud-based solution designed specifically for tour companies operating in the safari and mountain tourism industry. This system addresses the critical challenges of managing guides, tracking equipment and resources, and maintaining operational efficiency in remote and challenging environments.', 0, 'J');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Key Value Propositions:', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$value_props = [
    '• 30-40% reduction in equipment loss and misplacement',
    '• 25% improvement in guide resource allocation efficiency',
    '• Real-time tracking of critical mandatory equipment',
    '• Automated reporting and compliance management',
    '• Enhanced customer safety through proper resource management'
];

foreach ($value_props as $prop) {
    $pdf->Cell(0, 8, $prop, 0, 1, 'L');
}

$pdf->AddPage();

// System Overview
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 15, 'SYSTEM OVERVIEW', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'What is GMS?', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, 'GMS is a specialized management platform that streamlines the complex operations of tour companies by providing centralized control over guides, equipment, and resources. The system is designed to handle the unique challenges of outdoor tourism, including remote locations, variable weather conditions, and critical safety requirements.', 0, 'J');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Target Industries:', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$industries = [
    '• Safari Tour Companies',
    '• Mountain Climbing Expeditions',
    '• Adventure Tourism Operators',
    '• Wildlife Photography Tours',
    '• Cultural Heritage Tours',
    '• Eco-Tourism Companies'
];

foreach ($industries as $industry) {
    $pdf->Cell(0, 8, $industry, 0, 1, 'L');
}

$pdf->AddPage();

// Key Features
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 15, 'KEY FEATURES & BENEFITS', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Guide Management', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, 'Comprehensive guide profiles with experience tracking, certification management, and performance history. Assign guides to tours based on expertise, availability, and client requirements.', 0, 'J');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Resource & Equipment Tracking', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, 'Real-time inventory management with automatic alerts for low stock and missing mandatory equipment. Track equipment from assignment to return, ensuring nothing is lost or misplaced.', 0, 'J');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Reporting & Analytics', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, 'Comprehensive reporting dashboard with real-time data visualization. Generate reports on guide performance, equipment utilization, missing items, and operational efficiency.', 0, 'J');

$pdf->AddPage();

// ROI & Business Impact
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 15, 'ROI & BUSINESS IMPACT', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Cost Savings:', 0, 1, 'L');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 12);
$cost_savings = [
    '• Equipment Loss Reduction: 30-40% savings',
    '• Administrative Time: 25-35% reduction',
    '• Insurance Premiums: 15-20% reduction',
    '• Compliance Fines: 90% reduction',
    '• Operational Efficiency: 20-30% improvement'
];

foreach ($cost_savings as $saving) {
    $pdf->Cell(0, 8, $saving, 0, 1, 'L');
}

$pdf->AddPage();

// Contact Information
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 15, 'CONTACT INFORMATION', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Ready to Transform Your Tour Operations?', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 8, 'Contact our team today to schedule a personalized demonstration of the GMS system. We\'ll show you how our solution can address your specific challenges and help you achieve operational excellence.', 0, 'J');
$pdf->Ln(10);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 12, 'Contact Details:', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 12);
$contact_info = [
    'Email: sales@gms-system.com',
    'Phone: +255 XXX XXX XXX',
    'Website: www.gms-system.com',
    'Business Hours: Monday - Friday, 8:00 AM - 6:00 PM EAT'
];

foreach ($contact_info as $info) {
    $pdf->Cell(0, 8, $info, 0, 1, 'L');
}

// Output the PDF directly to browser for download
$pdf->Output('GMS_Professional_Presentation.pdf', 'D');
?>
