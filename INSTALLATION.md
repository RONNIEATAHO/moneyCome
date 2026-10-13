# Installation and Setup Guide

## Prerequisites

Before starting, ensure you have:
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Apache**: Web server with mod_rewrite enabled
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

## Step-by-Step Installation

### 1. Extract Project Files
Extract the `moneyCome` folder to your web server's root directory.

### 2. Database Setup

#### Create Database
```bash
mysql -u root -p
CREATE DATABASE suvan_ventures CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Import Schema
```bash
cd path/to/moneyCome
mysql -u root -p suvan_ventures < database/schema.sql
```

### 3. Configure Database Connection

Edit `backend/config/database.php`:
```php
define('DB_HOST', 'localhost');        // MySQL host
define('DB_USER', 'root');             // MySQL username
define('DB_PASS', 'your_password');    // MySQL password
define('DB_NAME', 'suvan_ventures');   // Database name
define('BASE_URL', 'http://localhost/moneyCome');
```

### 4. Configure Web Server

#### For Apache:
Ensure `.htaccess` is in the `public` directory with proper RewriteEngine settings.

Enable mod_rewrite if not already enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### For Windows (XAMPP):
1. Place project in `C:\xampp\htdocs\moneyCome\`
2. Access at `http://localhost/moneyCome/public/index.html`

### 5. Set File Permissions

```bash
chmod -R 755 backend/
chmod -R 755 public/
chmod 644 *.md
chmod 644 database/schema.sql
```

### 6. Verify Installation

Open your browser and navigate to:
```
http://localhost/moneyCome/public/index.html
```

You should see the homepage with the Suvan Ventures branding.

## Configuration

### API Endpoints Configuration

Edit `public/js/common.js` to ensure correct API base URL:
```javascript
const API_BASE_URL = 'http://localhost/moneyCome/backend/api';
```

### Environment Variables (Optional)

Copy `backend/config/env.example.php` to `backend/config/env.php` and customize:
```bash
cp backend/config/env.example.php backend/config/env.php
```

### Payment Gateway Setup

1. **For Production**: Update `backend/config/database.php` with real API keys
2. **For Testing**: Use sandbox credentials from your payment provider

## Testing the System

### 1. Test User Login
1. Navigate to Login page
2. Use credentials:
   - Email: `admin@suvanventures.com`
   - Password: `admin123`
3. Click "Login"

### 2. Test Customer Registration
1. On Login page, click "Register"
2. Fill in registration form
3. Click "Create Account"
4. Try logging in with new credentials

### 3. Test Product Listing
1. Navigate to "Products" page
2. Verify products load (if any exist in database)
3. Test search and filter functionality

### 4. Test Shopping Cart
1. Login as a customer
2. Add items to cart
3. Navigate to cart page
4. Verify items display correctly

### 5. Test Admin Panel
1. Login as the system admin
2. Navigate to admin dashboard
3. Use the **Users** page to create admin accounts and deactivate or reactivate customer and regular admin accounts.
4. Test product and service management
5. View inquiries and appointments

Regular admins can use the other admin features and view users, but only the system admin can create admin accounts or deactivate customer and regular admin accounts. Customer self-registration does not grant admin privileges. Deactivation blocks future logins without deleting the account's orders or other records. The system admin cannot deactivate their own account or another system admin.

## Database Information

### Key Tables
- `users` - User accounts with roles
- `products` - Product inventory
- `services` - Service offerings
- `cart` - User shopping carts
- `orders` - Customer orders
- `inquiries` - Customer inquiries
- `appointments` - Service appointments
- `payments` - Payment records

### Default Users
```
System Admin: admin@suvanventures.com / admin123
Company Admin: company@suvanventures.com / company123
```

These credentials are for local testing only. Change both passwords immediately after importing the schema, and never use the defaults on a public server.

## Troubleshooting

### Database Connection Failed
**Error**: "Connection failed: Access denied for user 'root'@'localhost'"

**Solution**:
```bash
# Verify MySQL is running
sudo systemctl status mysql

# Reset MySQL root password if needed
sudo mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
```

### 404 Errors on Frontend Pages
**Error**: Pages return "Not Found" errors

**Solution**:
```bash
# Verify .htaccess exists in public folder
ls -la public/.htaccess

# Check mod_rewrite is enabled
apache2ctl -M | grep rewrite

# Enable if needed
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### API Endpoints Return 500 Error
**Error**: JavaScript console shows API errors

**Solution**:
1. Check PHP error logs
2. Verify database connection: `php backend/config/database.php`
3. Check folder permissions: `chmod -R 755 backend/`

### Incorrect API URL
**Error**: API calls fail with 404

**Solution**:
Check and update `API_BASE_URL` in `public/js/common.js`:
```javascript
const API_BASE_URL = 'http://localhost/moneyCome/backend/api';
// Update to match your installation path
```

### CSS/JavaScript Not Loading
**Error**: Styles look broken, JavaScript not working

**Solution**:
1. Clear browser cache: Ctrl+Shift+Delete
2. Hard refresh page: Ctrl+F5
3. Verify CSS/JS file paths are correct

## Performance Optimization

### Enable Caching
Add to `.htaccess`:
```apache
<IfModule mod_headers.c>
    Header set Cache-Control "public, max-age=3600"
</IfModule>
```

### Enable Gzip Compression
Already configured in `.htaccess`

### Database Optimization
```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_order_user ON orders(user_id);
CREATE INDEX idx_cart_user ON cart(user_id);
```

## Security Checklist

- ✅ Change default admin password
- ✅ Enable HTTPS in production
- ✅ Set strong database passwords
- ✅ Disable debug mode in production
- ✅ Keep PHP and MySQL updated
- ✅ Regular database backups
- ✅ Implement rate limiting
- ✅ Use parameterized queries (already implemented)
- ✅ Validate all user input
- ✅ Sanitize all output

## Maintenance

### Regular Backups
```bash
# Backup database
mysqldump -u root -p suvan_ventures > suvan_ventures_backup.sql

# Backup entire project
tar -czf moneyCome_backup.tar.gz moneyCome/
```

### Monitor Logs
```bash
# PHP errors
tail -f /var/log/apache2/error.log

# MySQL
tail -f /var/log/mysql/error.log

# Access logs
tail -f /var/log/apache2/access.log
```

### Update Dependencies
Keep PHP, MySQL, and Apache updated for security patches.

## Support

For additional help:
1. Review README.md for detailed documentation
2. Check QUICK_START.md for common issues
3. Review code comments in API files
4. Check browser console for JavaScript errors

---

**Installation Complete!** Your Suvan Ventures website is now ready to use.
