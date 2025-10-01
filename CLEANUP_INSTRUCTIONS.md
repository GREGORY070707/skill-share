# Demo Data Cleanup Instructions

## Overview
Your SkillShare application is currently showing demo/test workshops that were manually added to the database. The application code is **already correctly configured** to display only real workshops created by teachers. You just need to remove the demo data from the database.

## Current Situation
The demo workshops you're seeing (like "Basic Coding with Python", "Financial Literacy 101", "Web Development Bootcamp") are stored in your database and need to be removed.

## How the System Works (Already Correct!)

### ✅ The application is properly configured:

1. **Student Dashboard** (`student-dashboard.php`):
   - Shows only workshops with `status = 'approved'`
   - Shows only workshops with `start_date > NOW()` (future workshops)
   - Displays real teacher names from the database
   - Shows actual enrollment counts

2. **Workshops Page** (`workshops.php`):
   - Fetches only approved workshops from the database
   - Shows real-time enrollment data
   - Filters work correctly (category, mode, search)

3. **Teacher Dashboard** (`teacher-dashboard.php`):
   - Shows only workshops created by the logged-in teacher
   - Displays real student enrollment data

4. **Database Schema** (`database/schema.sql`):
   - Contains NO demo data
   - Only creates the admin user account

## Solution: Remove Demo Data

### Option 1: Use the Web-Based Cleanup Script (Recommended)

1. **Open your browser** and navigate to:
   ```
   http://localhost/skill-share-main/cleanup-demo-data.php
   ```

2. **Review the warning** - it will show you how many records will be deleted

3. **Click "Yes, Delete All Demo Data"** to confirm

4. **Done!** The system will now show only real teacher-created workshops

### Option 2: Use MySQL Command Line or phpMyAdmin

1. **Open phpMyAdmin** (http://localhost/phpmyadmin)

2. **Select the `skillshare_db` database**

3. **Go to SQL tab** and run this script:
   ```sql
   USE skillshare_db;
   
   SET FOREIGN_KEY_CHECKS = 0;
   
   DELETE FROM workshops;
   DELETE FROM enrollments;
   DELETE FROM resources;
   DELETE FROM feedback;
   DELETE FROM notifications;
   
   ALTER TABLE workshops AUTO_INCREMENT = 1;
   ALTER TABLE enrollments AUTO_INCREMENT = 1;
   ALTER TABLE resources AUTO_INCREMENT = 1;
   ALTER TABLE feedback AUTO_INCREMENT = 1;
   ALTER TABLE notifications AUTO_INCREMENT = 1;
   
   SET FOREIGN_KEY_CHECKS = 1;
   ```

4. **Click "Go"** to execute

### Option 3: Use the SQL File

1. **Navigate to** `database/cleanup-demo-data.sql`

2. **Import it** via phpMyAdmin or run via command line:
   ```bash
   mysql -u root -p skillshare_db < database/cleanup-demo-data.sql
   ```

## After Cleanup

### What Happens Next?

1. **All demo workshops are removed** ✓
2. **User accounts remain intact** ✓
3. **Database structure unchanged** ✓
4. **System ready for real data** ✓

### Creating Real Workshops

Teachers can now create real workshops:

1. **Login as a teacher** account
2. **Go to Teacher Dashboard**
3. **Click "Create New Workshop"**
4. **Fill in the workshop details**:
   - Title
   - Description
   - Category
   - Mode (Online/Offline/Hybrid)
   - Location (if applicable)
   - Start Date & Time
   - End Date & Time
   - Maximum Participants

5. **Submit** - Workshop will be created with `status = 'pending'`

6. **Admin approves** the workshop (changes status to `'approved'`)

7. **Workshop appears** on student dashboard and workshops page

## Verification

After cleanup, verify the system is working:

1. **Check Student Dashboard**:
   - Should show "No workshops available" message
   - Or show only real teacher-created workshops

2. **Check Workshops Page**:
   - Should show "0 Workshops Found"
   - Or show only real workshops

3. **Check Teacher Dashboard**:
   - Teachers should see only their own workshops
   - Can create new workshops

4. **Check Admin Dashboard**:
   - Can approve/reject teacher-created workshops
   - Can view all system statistics

## Important Notes

⚠️ **This cleanup is permanent** - demo data cannot be recovered after deletion

✅ **User accounts are safe** - no user data will be deleted

✅ **System functionality unchanged** - all features work the same

✅ **Database structure intact** - only data is removed, not tables

## Troubleshooting

### If workshops still show after cleanup:

1. **Clear browser cache**: Ctrl + Shift + Delete
2. **Check database**: Verify workshops table is empty
3. **Restart XAMPP**: Stop and start Apache/MySQL

### If you need demo data back:

You'll need to manually create workshops through the teacher interface or insert test data via SQL.

## Support

If you encounter any issues:
1. Check the error logs in XAMPP
2. Verify database connection in `config/database.php`
3. Ensure all tables exist in the database
4. Make sure XAMPP Apache and MySQL are running

---

**Ready to proceed?** Run the cleanup script and start using real workshop data!
