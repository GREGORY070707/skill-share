# 🚀 Quick Installation Guide - SkillShare Platform

## Prerequisites Checklist
- [ ] XAMPP installed
- [ ] Project files in htdocs folder
- [ ] Apache and MySQL services started

## Installation Steps

### Step 1: Install XAMPP
1. Download XAMPP from: https://www.apachefriends.org
2. Install XAMPP to default location: `C:\xampp\`
3. Complete the installation wizard

### Step 2: Copy Project Files
1. Locate your XAMPP installation folder
2. Navigate to the `htdocs` directory: `C:\xampp\htdocs\`
3. Copy the entire `skill-share-main` folder here
4. Final path should be: `C:\xampp\htdocs\skill-share-main\`

### Step 3: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Wait for both to show green "Running" status

### Step 4: Setup Database
1. Open your web browser (Chrome recommended)
2. Navigate to: `http://localhost/skill-share-main/setup-database.php`
3. Wait for the setup to complete
4. You should see success messages for:
   - Database creation
   - Table creation
   - Sample data insertion
   - Admin account creation

### Step 5: Access the Application
1. Navigate to: `http://localhost/skill-share-main/index.php`
2. You should see the beautiful homepage!

## Default Login Credentials

### Admin Account
```
Email: admin@skillshare.com
Password: admin123
Role: Admin
```

**⚠️ IMPORTANT: Change this password after first login!**

## Verification Checklist

After installation, verify:
- [ ] Homepage loads correctly
- [ ] Can navigate to Login page
- [ ] Can navigate to Register page
- [ ] Can login with admin credentials
- [ ] Admin dashboard displays correctly
- [ ] Can logout successfully

## Common Issues & Solutions

### Issue: "Connection failed" error
**Solution:**
- Ensure MySQL is running in XAMPP Control Panel
- Check if port 3306 is not blocked by firewall
- Verify database credentials in `config/database.php`

### Issue: "Page not found" error
**Solution:**
- Ensure Apache is running in XAMPP Control Panel
- Check the URL is correct: `http://localhost/skill-share-main/index.php`
- Verify project is in correct folder: `C:\xampp\htdocs\skill-share-main\`

### Issue: Setup page shows errors
**Solution:**
- Ensure MySQL is running
- Check MySQL root password matches in `config/database.php`
- Try accessing phpMyAdmin: `http://localhost/phpmyadmin`

### Issue: Blank white page
**Solution:**
- Enable error display in PHP
- Check Apache error logs in `C:\xampp\apache\logs\error.log`
- Verify all PHP files are properly uploaded

## Testing the Installation

### Test 1: Register New User
1. Go to Register page
2. Fill in the form with test data
3. Select "Student" role
4. Click "Create Account"
5. Should redirect to login page with success message

### Test 2: Login as Student
1. Login with newly created account
2. Should redirect to Student Dashboard
3. Verify dashboard displays correctly

### Test 3: Browse Workshops
1. Click "Workshops" in navigation
2. Should see sample workshops
3. Try filtering by category or mode

### Test 4: Admin Functions
1. Logout and login as admin
2. Access Admin Dashboard
3. Verify statistics display
4. Check pending workshops section

## Next Steps

After successful installation:
1. ✅ Change default admin password
2. ✅ Create test accounts for each role
3. ✅ Explore all features
4. ✅ Customize branding if needed
5. ✅ Add real workshop data

## Database Information

**Database Name:** skillshare_db
**Tables Created:**
- users
- workshops
- enrollments
- resources
- feedback
- notifications
- messages

## File Permissions

Ensure these folders have write permissions (if needed):
- `uploads/` (for future file uploads)
- `logs/` (for error logs)

## Security Recommendations

1. **Change Database Password:**
   - Edit `config/database.php`
   - Update `DB_PASS` constant
   - Update MySQL root password in phpMyAdmin

2. **Change Admin Password:**
   - Login as admin
   - Go to profile settings
   - Update password

3. **Enable HTTPS (Production):**
   - Install SSL certificate
   - Update all URLs to https://

## Support & Help

If you encounter issues:
1. Check XAMPP error logs
2. Review PHP error messages
3. Verify database connection
4. Check file permissions
5. Ensure all services are running

## Quick Commands

### Start XAMPP Services (Command Line)
```bash
cd C:\xampp
xampp-control.exe
```

### Access phpMyAdmin
```
http://localhost/phpmyadmin
```

### Access Project
```
http://localhost/skill-share-main/
```

### Setup Database
```
http://localhost/skill-share-main/setup-database.php
```

## Congratulations! 🎉

Your SkillShare platform is now ready to use!

Start by:
1. Logging in as admin
2. Creating some workshops
3. Registering student accounts
4. Testing the enrollment process

---

**Need Help?** Check the main README.md for detailed documentation.

**Version:** 1.0.0
**Last Updated:** October 2025
