<?php
// GMS System Presentation Generator
// This script creates a professional PDF presentation for tour companies

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include TCPDF
require_once 'vendor/autoload.php';

use TCPDF as TCPDF;

// Define TCPDF constants if not already defined
if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');
if (!defined('PDF_MARGIN_LEFT')) define('PDF_MARGIN_LEFT', 15);
if (!defined('PDF_MARGIN_TOP')) define('PDF_MARGIN_TOP', 27);
if (!defined('PDF_MARGIN_RIGHT')) define('PDF_MARGIN_RIGHT', 15);
if (!defined('PDF_MARGIN_BOTTOM')) define('PDF_MARGIN_BOTTOM', 25);
if (!defined('PDF_MARGIN_HEADER')) define('PDF_MARGIN_HEADER', 5);
if (!defined('PDF_MARGIN_FOOTER')) define('PDF_MARGIN_FOOTER', 10);
if (!defined('PDF_FONT_NAME_MAIN')) define('PDF_FONT_NAME_MAIN', 'helvetica');
if (!defined('PDF_FONT_SIZE_MAIN')) define('PDF_FONT_SIZE_MAIN', 10);
if (!defined('PDF_FONT_NAME_DATA')) define('PDF_FONT_NAME_DATA', 'helvetica');
if (!defined('PDF_FONT_SIZE_DATA')) define('PDF_FONT_SIZE_DATA', 8);
if (!defined('PDF_FONT_MONOSPACED')) define('PDF_FONT_MONOSPACED', 'courier');
if (!defined('PDF_IMAGE_SCALE_RATIO')) define('PDF_IMAGE_SCALE_RATIO', 1.25);

class GMSPresentation extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'GUIDE MANAGEMENT SYSTEM (GMS)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

try {
    // Create new PDF document
    $pdf = new GMSPresentation(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('GMS System');
    $pdf->SetAuthor('GMS Development Team');
    $pdf->SetTitle('Guide Management System - Professional Presentation');
    $pdf->SetSubject('Tour Company Management Solution');
    $pdf->SetKeywords('GMS, Guide Management, Tour Company, Resource Management, Safari, Mountain');

    // Set default header data
    $pdf->SetHeaderData('', 0, '', '');

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add a page
    $pdf->AddPage();

    // ========================================
    // COVER PAGE
    // ========================================
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

    // ========================================
    // TABLE OF CONTENTS
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, 'TABLE OF CONTENTS', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 12);
    $toc_items = [
        '1. Executive Summary',
        '2. System Overview',
        '3. Key Features & Benefits',
        '4. System Modules',
        '5. Technical Specifications',
        '6. Implementation & Support',
        '7. ROI & Business Impact',
        '8. Case Studies',
        '9. Pricing & Packages',
        '10. Contact Information'
    ];

    foreach ($toc_items as $item) {
        $pdf->Cell(0, 10, $item, 0, 1, 'L');
    }

    $pdf->AddPage();

    // ========================================
    // 1. EXECUTIVE SUMMARY
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '1. EXECUTIVE SUMMARY', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'The Guide Management System (GMS) is a comprehensive, cloud-based solution designed specifically for tour companies operating in the safari and mountain tourism industry. This system addresses the critical challenges of managing guides, tracking equipment and resources, and maintaining operational efficiency in remote and challenging environments.', 0, 'J');
    $pdf->Ln(5);

    $pdf->MultiCell(0, 8, 'GMS provides real-time visibility into resource allocation, guide performance, and equipment status, enabling tour companies to optimize operations, reduce costs, and enhance customer satisfaction. Built with modern web technologies, the system offers seamless access from any device, anywhere in the world.', 0, 'J');
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

    // ========================================
    // 2. SYSTEM OVERVIEW
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '2. SYSTEM OVERVIEW', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'What is GMS?', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'GMS is a specialized management platform that streamlines the complex operations of tour companies by providing centralized control over guides, equipment, and resources. The system is designed to handle the unique challenges of outdoor tourism, including remote locations, variable weather conditions, and critical safety requirements.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Core Purpose:', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'To ensure that every tour operates with the right guides, proper equipment, and adequate resources while maintaining complete transparency and accountability throughout the entire process. GMS transforms chaotic, paper-based operations into streamlined, digital workflows that enhance safety, efficiency, and profitability.', 0, 'J');
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
        '• Eco-Tourism Companies',
        '• Outdoor Education Programs'
    ];

    foreach ($industries as $industry) {
        $pdf->Cell(0, 8, $industry, 0, 1, 'L');
    }

    $pdf->AddPage();

    // ========================================
    // 3. KEY FEATURES & BENEFITS
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '3. KEY FEATURES & BENEFITS', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Guide Management', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Comprehensive guide profiles with experience tracking, certification management, and performance history. Assign guides to tours based on expertise, availability, and client requirements. Monitor guide performance and maintain detailed records for compliance and quality assurance.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Resource & Equipment Tracking', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Real-time inventory management with automatic alerts for low stock and missing mandatory equipment. Track equipment from assignment to return, ensuring nothing is lost or misplaced. Categorize resources as mandatory or optional based on safety requirements.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Borrowing & Return System', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Streamlined process for guides to borrow equipment and resources. Automated tracking of due dates with overdue alerts. Digital return processing with condition reporting and maintenance scheduling. Complete audit trail for all transactions.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Reporting & Analytics', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Comprehensive reporting dashboard with real-time data visualization. Generate reports on guide performance, equipment utilization, missing items, and operational efficiency. Export data in multiple formats for analysis and compliance reporting.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Mobile Accessibility', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Responsive web interface accessible from any device, including smartphones and tablets. Field staff can update information in real-time, even in remote locations with limited connectivity. Offline capability for critical operations.', 0, 'J');

    $pdf->AddPage();

    // ========================================
    // 4. SYSTEM MODULES
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '4. SYSTEM MODULES', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Dashboard & Overview', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Central command center displaying key metrics, alerts, and system status. Real-time overview of guides, resources, and active tours. Quick access to critical functions and emergency procedures.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Guide Management Module', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Complete guide lifecycle management from recruitment to retirement. Profile management, certification tracking, performance evaluation, and availability scheduling. Integration with training and development programs.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Resource Management Module', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Comprehensive inventory control with categorization, quantity tracking, and condition monitoring. Automatic reorder alerts, maintenance scheduling, and depreciation tracking. Integration with suppliers and service providers.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Borrowing & Returns Module', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Digital workflow for equipment checkout and return processes. Automated notifications, due date tracking, and condition reporting. Integration with maintenance and repair systems for damaged equipment.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Reporting & Analytics Module', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Advanced reporting engine with customizable dashboards and automated report generation. Data export capabilities, trend analysis, and performance benchmarking. Integration with business intelligence tools.', 0, 'J');

    $pdf->AddPage();

    // ========================================
    // 5. TECHNICAL SPECIFICATIONS
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '5. TECHNICAL SPECIFICATIONS', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Technology Stack', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $tech_stack = [
        '• Backend: PHP 8.0+ with modern frameworks',
        '• Database: MySQL 8.0 with optimized queries',
        '• Frontend: HTML5, CSS3, JavaScript (ES6+)',
        '• UI Framework: Bootstrap 5 for responsive design',
        '• Security: HTTPS, SQL injection protection, XSS prevention',
        '• Hosting: Cloud-based with 99.9% uptime guarantee'
    ];

    foreach ($tech_stack as $tech) {
        $pdf->Cell(0, 8, $tech, 0, 1, 'L');
    }
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'System Requirements', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'GMS is a web-based application that requires only a modern web browser and internet connection. No software installation is required on client devices. The system is optimized for both desktop and mobile usage with responsive design principles.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Security Features', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $security_features = [
        '• Role-based access control (RBAC)',
        '• Multi-factor authentication (MFA)',
        '• Encrypted data transmission (TLS 1.3)',
        '• Regular security audits and updates',
        '• GDPR compliance for data protection',
        '• Automated backup and disaster recovery'
    ];

    foreach ($security_features as $feature) {
        $pdf->Cell(0, 8, $feature, 0, 1, 'L');
    }
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Scalability & Performance', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Built with scalability in mind, GMS can handle from small tour companies with 5-10 guides to large operations with 100+ guides. The system automatically scales resources based on usage patterns and maintains consistent performance under varying loads.', 0, 'J');

    $pdf->AddPage();

    // ========================================
    // 6. IMPLEMENTATION & SUPPORT
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '6. IMPLEMENTATION & SUPPORT', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Implementation Process', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $implementation_steps = [
        'Phase 1: System Setup & Configuration (1-2 weeks)',
        'Phase 2: Data Migration & Import (1 week)',
        'Phase 3: User Training & Testing (1 week)',
        'Phase 4: Go-Live & Support (1 week)',
        'Total Implementation Time: 4-5 weeks'
    ];

    foreach ($implementation_steps as $step) {
        $pdf->Cell(0, 8, $step, 0, 1, 'L');
    }
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Training & Onboarding', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Comprehensive training program for all users including administrators, managers, and field staff. Interactive training sessions, user manuals, video tutorials, and ongoing support. Certification program for system administrators.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Ongoing Support', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $support_services = [
        '• 24/7 technical support via phone, email, and chat',
        '• Regular system updates and feature enhancements',
        '• Performance monitoring and optimization',
        '• User training and refresher courses',
        '• Custom report development and modifications',
        '• Integration with third-party systems'
    ];

    foreach ($support_services as $service) {
        $pdf->Cell(0, 8, $service, 0, 1, 'L');
    }

    $pdf->AddPage();

    // ========================================
    // 7. ROI & BUSINESS IMPACT
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '7. ROI & BUSINESS IMPACT', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Cost Savings', 0, 1, 'L');
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
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Revenue Enhancement', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Improved operational efficiency leads to increased tour capacity and better customer satisfaction. Enhanced safety records enable premium pricing and expanded service offerings. Better resource utilization allows for more tours with the same equipment base.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Risk Mitigation', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Comprehensive tracking and documentation reduces liability exposure. Automated compliance monitoring prevents regulatory violations. Enhanced safety protocols protect both staff and customers, reducing insurance claims and legal risks.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Competitive Advantage', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'GMS provides a significant competitive advantage through operational excellence, enhanced safety records, and improved customer experience. The system enables tour companies to scale operations while maintaining quality and safety standards.', 0, 'J');

    $pdf->AddPage();

    // ========================================
    // 8. CASE STUDIES
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '8. CASE STUDIES', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Case Study 1: Safari Tour Company (Tanzania)', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'A leading safari tour company in Tanzania implemented GMS to manage 25 guides and over 200 pieces of equipment across multiple locations. Results: 35% reduction in equipment loss, 40% improvement in guide allocation efficiency, and 25% increase in tour capacity.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Case Study 2: Mountain Climbing Company (Kenya)', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'A mountain climbing company managing Kilimanjaro expeditions implemented GMS for safety-critical equipment tracking. Results: Zero equipment-related incidents, 100% compliance with safety regulations, and 30% reduction in insurance premiums.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Case Study 3: Adventure Tourism Operator (Uganda)', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'An adventure tourism operator implemented GMS to streamline operations across multiple national parks. Results: 50% reduction in administrative overhead, improved customer satisfaction scores, and expansion to three new locations within 12 months.', 0, 'J');

    $pdf->AddPage();

    // ========================================
    // 9. PRICING & PACKAGES
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '9. PRICING & PACKAGES', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Starter Package - Small Tour Companies (5-15 guides)', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Perfect for small tour companies just starting their digital transformation journey. Includes core GMS functionality, up to 15 user licenses, basic reporting, and email support. Monthly subscription with no long-term commitment.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Professional Package - Medium Tour Companies (15-50 guides)', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Comprehensive solution for growing tour companies. Includes all GMS features, up to 50 user licenses, advanced reporting and analytics, priority support, and custom training. Annual subscription with volume discounts available.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Enterprise Package - Large Tour Companies (50+ guides)', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'Full-featured solution for large tour operations. Includes unlimited user licenses, custom integrations, dedicated account manager, 24/7 priority support, and on-site training. Custom pricing based on requirements and scale.', 0, 'J');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Implementation & Training', 0, 1, 'L');
    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 8, 'One-time implementation fee covers system setup, data migration, user training, and go-live support. Training packages available for ongoing user education and new staff onboarding. Custom training programs for specific requirements.', 0, 'J');

    $pdf->AddPage();

    // ========================================
    // 10. CONTACT INFORMATION
    // ========================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 15, '10. CONTACT INFORMATION', 0, 1, 'L');
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
        'Address: [Your Company Address]',
        'Business Hours: Monday - Friday, 8:00 AM - 6:00 PM EAT'
    ];

    foreach ($contact_info as $info) {
        $pdf->Cell(0, 8, $info, 0, 1, 'L');
    }
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Next Steps:', 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 12);
    $next_steps = [
        '1. Schedule a personalized demo',
        '2. Receive customized proposal',
        '3. Begin implementation planning',
        '4. Start your digital transformation journey'
    ];

    foreach ($next_steps as $step) {
        $pdf->Cell(0, 8, $step, 0, 1, 'L');
    }

    // Output the PDF
    $pdf->Output('GMS_Professional_Presentation.pdf', 'D');
    
} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
