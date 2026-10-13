# Suvan Ventures Website - Setup Guide

## Project Overview
Suvan Ventures Company Limited website is a comprehensive e-commerce and service booking platform offering:
- Website development services
- Networking solutions
- CCTV camera installation and security solutions
- IT support services
- Custom software development
- School management systems
- POS systems for businesses
- Electronic gadgets and equipment sales

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache with mod_rewrite enabled

## Project Structure

```
moneyCome/
├── public/                          # Frontend files
│   ├── index.html                   # Home page
│   ├── products.html                # Products listing
│   ├── services.html                # Services listing
│   ├── contact.html                 # Contact form
│   ├── cart.html                    # Shopping cart
│   ├── payment.html                 # Payment processing
│   ├── css/
│   │   └── styles.css               # Global styles
│   ├── js/
│   │   └── common.js                # Common JavaScript functions
│   └── images/                      # Image assets
├── backend/
│   ├── config/
│   │   └── database.php             # Database configuration
│   ├── includes/
│   │   ├── auth.php                 # Authentication functions
│   │   └── helpers.php              # Helper functions
│   ├── api/
│   │   ├── auth.php                 # Authentication API
│   │   ├── products.php             # Products API
│   │   ├── services.php             # Services API
│   │   ├── cart.php                 # Cart API
│   │   ├── orders.php               # Orders API
│   │   ├── payments.php             # Payments API
│   │   ├── appointments.php         # Appointments API
│   │   └── inquiries.php            # Inquiries API
│   └── admin/
│       ├── admin-dashboard.html     # Admin dashboard
│       ├── admin-products.html      # Product management
│       ├── admin-services.html      # Service management
│       ├── admin-inquiries.html     # Inquiry management
│       ├── admin-appointments.html  # Appointment management
│       ├── admin-orders.html        # Order management
│       └── admin-users.html         # User management
└── database/
    └── schema.sql                   # Database schema
```

## Installation Instructions

### 1. Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite
- Composer (optional, for dependency management)

### 2. Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE suvan_ventures;
```

2. Import the database schema:
```bash
mysql -u root -p suvan_ventures < database/schema.sql
```

3. Update database credentials in `backend/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'suvan_ventures');
```

### 3. Web Server Configuration

For Apache, create a `.htaccess` file in the `public` directory:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /moneyCome/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.html [L]
</IfModule>
```

### 4. Configuration

1. Update `public/js/common.js` with correct API base URL:
```javascript
const API_BASE_URL = 'http://localhost/moneyCome/backend/api';
```

2. Update `backend/config/database.php` with your server settings

3. For payment integration, update payment gateway settings in `backend/config/database.php`

### 5. File Permissions

Ensure proper file permissions:
```bash
chmod -R 755 backend/
chmod -R 755 public/
chmod 644 database/schema.sql
```

## User Roles and Privileges

### 1. System Administrator
- Full access to codebase
- Can make code changes
- Can manage all system configurations
- Access to admin panel
- Default login: `admin@suvanventures.com` / `admin123`

### 2. Company Administrator
- Can add/edit/delete products
- Can add/edit/delete services
- Can manage customer inquiries
- Can manage service appointments
- Can view orders and customer data
- Cannot access code or system settings

### 3. Customer (End User)
- Can view products and services
- Can add items to shopping cart
- Can book service appointments
- Can request specific services
- Can submit contact inquiries
- Can view their orders and appointments
- Cannot access admin functions

## Features

### Frontend Features
- **Homepage**: Hero section, featured products/services, benefits, CTA sections
- **Products Page**: Display all products with filters and search functionality
- **Services Page**: List services with booking and cart options
- **Contact Page**: Contact form with inquiry submission
- **Shopping Cart**: Add/remove items, update quantities, view totals
- **Payment Page**: Multiple payment method options (credit card, PayPal, bank transfer)
- **Login/Register**: User authentication with role-based dashboard
- **Responsive Design**: Mobile-friendly interface

### Backend Features
- **Authentication**: Secure login/registration with role-based access control
- **Product Management**: CRUD operations for products with stock tracking
- **Service Management**: CRUD operations for services
- **Cart System**: Add to cart, manage quantities, calculate totals
- **Order System**: Create orders from cart, track order status
- **Payment Processing**: Handle payments and update order status
- **Appointment Booking**: Book, view, and manage service appointments
- **Inquiry Management**: Submit and manage customer inquiries
- **Admin Dashboard**: Overview of statistics and recent activities

## Color Scheme
- **Primary Dark**: #191919 (Charcoal Black)
- **Primary Gold**: #B88746 (Metal Gold)
- **Primary Light**: #F8F8F8 (Off-White)
- **Alternative Gold**: #F7C873 (Light Metal Gold)
- **Alternative Light**: #FAEBCD (Ivory)

All colors have been integrated throughout the UI for a consistent, professional appearance.

## Default Test Accounts

### System Administrator
- Email: `admin@suvanventures.com`
- Password: `admin123`

### Company Administrator
- Email: `company@suvanventures.com`
- Password: `company123`

### Register New Customer Account
Use the registration form on the login page to create a new customer account.

## API Endpoints

### Authentication
- `POST /backend/api/auth.php?action=login` - User login
- `POST /backend/api/auth.php?action=register` - User registration
- `GET /backend/api/auth.php?action=check_auth` - Check authentication status

### Products
- `GET /backend/api/products.php?action=get_all` - Get all products
- `GET /backend/api/products.php?action=get_single&id=ID` - Get single product
- `POST /backend/api/products.php?action=add_product` - Add product (Admin only)
- `POST /backend/api/products.php?action=update_product` - Update product (Admin only)
- `POST /backend/api/products.php?action=delete_product` - Delete product (Admin only)

### Services
- `GET /backend/api/services.php?action=get_all` - Get all services
- `GET /backend/api/services.php?action=get_single&id=ID` - Get single service
- `POST /backend/api/services.php?action=add_service` - Add service (Admin only)
- `POST /backend/api/services.php?action=update_service` - Update service (Admin only)
- `POST /backend/api/services.php?action=delete_service` - Delete service (Admin only)

### Cart & Orders
- `POST /backend/api/cart.php?action=add_to_cart` - Add item to cart
- `GET /backend/api/cart.php?action=get_cart` - Get user cart
- `POST /backend/api/orders.php?action=checkout` - Create order from cart

### Payments
- `POST /backend/api/payments.php?action=process_payment` - Process payment

### Appointments
- `POST /backend/api/appointments.php?action=book_appointment` - Book appointment
- `GET /backend/api/appointments.php?action=get_appointments` - Get user appointments

## Security Considerations

1. **Password Security**: Passwords are hashed using SHA256
2. **SQL Injection Prevention**: All inputs are sanitized using `real_escape_string()`
3. **Session Management**: User sessions are maintained with 1-hour timeout
4. **Access Control**: Role-based middleware functions ensure proper authorization
5. **Data Validation**: All form inputs are validated before processing

## Payment Integration

The platform supports multiple payment methods:
1. **Credit/Debit Card**: Demo integration (requires Stripe API setup)
2. **PayPal**: Demo integration (requires PayPal API setup)
3. **Bank Transfer**: Manual processing

Update payment settings in `backend/config/database.php` with actual API keys when ready for production.

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `backend/config/database.php`
- Ensure database `suvan_ventures` exists

### API Endpoint Errors
- Check that PHP is properly configured
- Verify `backend/` directory permissions
- Check PHP error logs for detailed error messages

### Frontend Not Loading
- Clear browser cache
- Check that CSS and JS files are accessible
- Verify correct API base URL in `public/js/common.js`

### Login Issues
- Verify user exists in database
- Check that password is correct
- Ensure browser cookies are enabled
- Clear browser cookies and try again

## Maintenance

### Regular Tasks
1. Backup database regularly
2. Monitor server logs for errors
3. Update administrative users list
4. Review customer inquiries and respond promptly
5. Update product and service information as needed

### Performance Optimization
1. Enable gzip compression
2. Optimize images
3. Use caching for frequently accessed data
4. Monitor database query performance

## Support and Documentation

For additional support or documentation updates, refer to:
- Backend API documentation in each PHP file
- Frontend component documentation in HTML files
- Database schema comments in `database/schema.sql`

## License
© 2024 Suvan Ventures Company Limited. All rights reserved.

## Version
**Version 1.0.0** - Initial Release (October 8, 2024)

---

For questions or issues, contact: support@suvanventures.com
