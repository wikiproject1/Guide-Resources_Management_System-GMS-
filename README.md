# GMS - Admin-Only Resource Management System for Guides

A professional, modern resource management system designed specifically for mountain and safari guides. This system tracks mandatory and optional resources, manages borrowing and returning workflows, and provides comprehensive reporting and analytics.

## 🎯 Features

### Core Functionality
- **Admin-Only Access**: Secure login system with single admin user
- **Guide Management**: Add, edit, delete, and track mountain and safari guides
- **Resource Management**: Complete inventory tracking with mandatory vs optional categories
- **Borrowing System**: Multi-resource borrowing with automatic inventory updates
- **Returns Processing**: Flexible return system with partial returns support
- **Professional Dashboard**: Real-time metrics, charts, and activity feeds

### Advanced Features
- **Real-time Inventory**: Automatic status updates and quantity tracking
- **Mandatory Item Enforcement**: Critical resource tracking with alerts
- **Comprehensive Reporting**: Multiple report types with CSV export
- **Modern UI/UX**: Professional design with responsive layout
- **Activity Tracking**: Complete audit trail of all operations

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation

1. **Clone or Download**
   ```bash
   # If using git
   git clone <repository-url>
   cd GMS
   
   # Or simply download and extract to your web server directory
   ```

2. **Database Setup**
   - Create a new MySQL database named `gms_system`
   - Update database credentials in `config/database.php` if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'gms_system');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

3. **Web Server Configuration**
   - Place files in your web server directory (e.g., `/var/www/html/` or `htdocs/`)
   - Ensure PHP has write permissions for session management

4. **Access the System**
   - Navigate to your web server URL
   - The system will automatically create tables and sample data
   - Default login: `admin` / `admin123`

## 📁 File Structure

```
GMS/
├── config/
│   └── database.php          # Database configuration and table creation
├── includes/
│   ├── header.php            # Main navigation and styling
│   ├── footer.php            # Scripts and closing tags
│   └── functions.php         # Utility functions and helpers
├── pages/
│   ├── dashboard.php         # Main dashboard with charts and metrics
│   ├── guides.php            # Guide management (CRUD operations)
│   ├── resources.php         # Resource management and inventory
│   ├── borrowing.php         # Borrowing workflow and forms
│   ├── returns.php           # Return processing and tracking
│   └── reports.php           # Comprehensive reporting system
├── index.php                 # Main entry point and routing
├── login.php                 # Admin authentication
├── logout.php                # Session termination
└── README.md                 # This file
```

## 🔐 Default Login

- **Username**: `admin`
- **Password**: `admin123`

**Important**: Change the default password after first login for security.

## 🎨 System Overview

### Dashboard
- **Overview Cards**: Total guides, resources, borrowed items, missing mandatory items
- **Interactive Charts**: Borrowing trends, guide activity, resource distribution
- **Quick Actions**: Add guides, resources, record borrowing/returns
- **Real-time Alerts**: Missing items, overdue returns, system notifications

### Guide Management
- **Guide Types**: Mountain Guide / Safari Guide
- **Contact Information**: Phone, email, emergency contacts
- **Status Tracking**: Active/Inactive guides
- **Borrowing History**: Complete transaction logs per guide

### Resource Management
- **Categories**: 
  - **Mandatory**: Must be returned (tents, binoculars, safety equipment)
  - **Optional**: Flexible tracking (drinks, snacks)
- **Inventory Tracking**: Automatic quantity calculations
- **Stock Alerts**: Low inventory notifications
- **Location Management**: Storage location tracking

### Borrowing & Returns
- **Multi-Resource Borrowing**: Select multiple items with quantities
- **Expected Return Dates**: Configurable return timeframes
- **Partial Returns**: Support for incomplete returns
- **Automatic Updates**: Real-time inventory status changes

### Reporting System
- **Current Borrowed Resources**: Active loans with guide details
- **Missing Mandatory Items**: Critical alerts with investigation tools
- **Guide History**: Complete transaction logs with filtering
- **Inventory Overview**: Stock levels and usage statistics
- **Export Options**: CSV download for all reports

## 🔧 Technical Details

### Technology Stack
- **Backend**: PHP 7.4+ with PDO database abstraction
- **Database**: MySQL with proper relationships and indexing
- **Frontend**: Bootstrap 5, Chart.js, Font Awesome
- **Security**: Session management, SQL injection protection, XSS prevention

### Database Schema
- **users**: Admin user management
- **guides**: Guide information and status
- **resources**: Inventory items with categories
- **borrow_records**: Main borrowing transactions
- **borrow_items**: Individual resource items in borrows

### Security Features
- **Authentication**: Secure admin login system
- **Input Validation**: Sanitized user inputs
- **SQL Protection**: Prepared statements
- **Session Security**: Secure session handling

## 📊 Usage Examples

### Adding a New Guide
1. Navigate to Guides → Add New Guide
2. Enter name, select type (Mountain/Safari)
3. Add contact information
4. Save and guide is immediately available for borrowing

### Recording a Borrowing
1. Go to Borrowing → Record New Borrowing
2. Select guide from dropdown
3. Choose resources and quantities
4. Set expected return date
5. Add notes if needed
6. System automatically updates inventory

### Processing Returns
1. Navigate to Returns page
2. Select borrowing record to process
3. Enter returned quantities for each item
4. System updates inventory and marks items returned
5. Automatic status updates for complete returns

### Generating Reports
1. Go to Reports page
2. Select report type (Current Borrowed, Missing Items, etc.)
3. Apply filters if needed
4. View data in organized tables
5. Export to CSV for external analysis

## 🚨 Important Notes

### Mandatory vs Optional Resources
- **Mandatory Resources**: Must be returned within specified timeframe
- **Optional Resources**: Tracked but no strict return enforcement
- **Missing Alerts**: System automatically flags missing mandatory items

### Inventory Management
- **Automatic Updates**: Quantities update in real-time
- **Status Tracking**: Available, Borrowed, Missing statuses
- **Stock Alerts**: Low inventory notifications

### Data Integrity
- **Transaction Safety**: All operations use database transactions
- **Cascade Deletes**: Proper cleanup when guides/resources are removed
- **Audit Trail**: Complete history of all operations

## 🔮 Future Enhancements

### Planned Features
- **Barcode/QR Integration**: Quick resource scanning
- **Mobile Interface**: Admin mobile app
- **API Integration**: Third-party system connections
- **Advanced Analytics**: Predictive insights and trends
- **Multi-location Support**: Multiple storage locations

### Scalability
- **Performance Optimization**: Query optimization and caching
- **User Roles**: Additional admin levels
- **Backup Systems**: Automated backup and recovery
- **Audit Logging**: Enhanced activity tracking

## 🆘 Support & Troubleshooting

### Common Issues

**Database Connection Error**
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database permissions

**Permission Errors**
- Ensure web server has read/write access
- Check PHP session directory permissions
- Verify file ownership

**Charts Not Displaying**
- Check browser console for JavaScript errors
- Ensure Chart.js library is loading
- Verify data is being passed correctly

### Performance Tips
- **Database Indexing**: Tables are pre-configured with proper indexes
- **Query Optimization**: All queries use prepared statements
- **Caching**: Consider implementing Redis for session storage
- **CDN**: Use CDN for external libraries (already configured)

## 📝 License

This project is developed for internal use. Please ensure compliance with your organization's policies and requirements.

## 🤝 Contributing

For internal development teams:
1. Follow PSR-12 coding standards
2. Test all functionality before deployment
3. Document any new features
4. Maintain backward compatibility

---

**GMS System** - Professional Resource Management for Mountain and Safari Guides

*Built with modern web technologies and best practices for optimal performance and user experience.*
