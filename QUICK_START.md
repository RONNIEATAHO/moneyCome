# Suvan Ventures - Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Step 1: Create the Database
Open your MySQL client and run:
```sql
CREATE DATABASE suvan_ventures;
USE suvan_ventures;
```

Then import the schema from `database/schema.sql`:
```bash
mysql -u root -p suvan_ventures < database/schema.sql
```

### Step 2: Configure Database Connection
Edit `backend/config/database.php` and update these lines:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'suvan_ventures');
```

### Step 3: Set Up Web Server
1. Place the project in Apache's web root (typically `htdocs` for XAMPP)
2. The project should be accessible at: `http://localhost/moneyCome/public/`

### Step 4: Test the Installation
1. Open a browser and navigate to: `http://localhost/moneyCome/public/index.html`
2. Try logging in with:
   - **Admin Account**: admin@suvanventures.com / admin123
   - **Or Register** a new customer account

### Step 5: Test Admin Panel
1. Login as admin
2. Click on "Admin Panel" link in navbar
3. Access: `http://localhost/moneyCome/backend/admin/admin-dashboard.html`

## 📋 Default Test Accounts

### System Admin
```
Email: admin@suvanventures.com
Password: admin123
```

### Company Admin
```
Email: company@suvanventures.com
Password: company123
```

## 🎨 Color Codes Used
- **Dark Background**: #191919
- **Gold Accent**: #B88746 / #F7C873
- **Light Background**: #F8F8F8 / #FAEBCD

These colors are used throughout the entire design for consistency.

## 📱 Key Features

### Customer Features
- ✅ Browse products and services
- ✅ Add items to cart
- ✅ Checkout and pay
- ✅ Book service appointments
- ✅ Submit inquiries
- ✅ View order history

### Admin Features
- ✅ Manage products (add/edit/delete)
- ✅ Manage services (add/edit/delete)
- ✅ View and respond to inquiries
- ✅ Manage customer appointments
- ✅ View orders and payment status
- ✅ Dashboard with statistics

## 🔧 File Structure Quick Reference

```
Frontend (Public)
├── index.html           ← Home page
├── products.html        ← Products listing
├── services.html        ← Services listing
├── contact.html         ← Contact form
├── cart.html            ← Shopping cart
├── payment.html         ← Payment processing
└── order-confirmation.html ← Order successful

Backend (API)
├── auth.php             ← Login/Register API
├── products.php         ← Product management API
├── services.php         ← Service management API
├── cart.php             ← Cart operations API
├── orders.php           ← Order management API
├── payments.php         ← Payment processing API
├── appointments.php     ← Appointment booking API
└── inquiries.php        ← Inquiry management API

Admin Panel
├── admin-dashboard.html     ← Main dashboard
├── admin-products.html      ← Product management
├── admin-services.html      ← Service management
├── admin-inquiries.html     ← Inquiry management
├── admin-appointments.html  ← Appointment management
└── admin-orders.html        ← Order management
```

## ⚠️ Important Notes

1. **Database**: The database will be created with default admin and company admin users.
2. **Passwords**: Currently using SHA256 hashing. Consider upgrading to bcrypt for production.
3. **Payment Gateway**: Payment integration is simulated. Update with real payment provider APIs for production.
4. **Email**: No email notifications configured. Add SMTP settings in production.
5. **Security**: Use HTTPS in production and implement additional security measures.

## 🛠️ Troubleshooting

### Issue: "Cannot connect to database"
**Solution**: 
- Verify MySQL is running
- Check database credentials in `backend/config/database.php`
- Ensure database `suvan_ventures` exists

### Issue: "404 Not Found errors"
**Solution**:
- Check .htaccess file is in the `public` directory
- Enable mod_rewrite in Apache
- Verify URL format: `http://localhost/moneyCome/public/`

### Issue: "API endpoints return errors"
**Solution**:
- Check API base URL in `public/js/common.js`
- Verify backend folder permissions
- Check PHP error logs

### Issue: "Login page doesn't work"
**Solution**:
- Ensure cookies are enabled in browser
- Clear browser cache and cookies
- Check that session is started in `backend/includes/auth.php`

## 📚 Next Steps

1. **Add Sample Data**: Insert test products and services in the database
2. **Configure Payment**: Integrate with PayPal, Stripe, or your preferred provider
3. **Email Setup**: Configure email notifications for orders and inquiries
4. **Security**: Implement HTTPS, update password hashing, add rate limiting
5. **Testing**: Test all features thoroughly before going live

## 📞 Support

For issues or questions:
- Check the main README.md file
- Review code comments in API files
- Check browser console for JavaScript errors
- Check PHP error logs for backend issues

## ✨ Tips for Development

1. Use browser developer tools (F12) to debug frontend issues
2. Check network tab to see API responses
3. Use `showAlert()` function for user feedback
4. Test with different user roles to verify permissions
5. Test on different browsers and devices for responsiveness

---

**Happy Coding! 🎉**

For more detailed information, see README.md
