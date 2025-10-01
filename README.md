# 🎓 SkillShare Platform

> **Empowering Communities Through Education and Skill Development**

A comprehensive web-based platform connecting students, teachers, and NGOs to facilitate collaborative learning and skill development workshops in rural and semi-urban areas.

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

---

##  Development Credits

**Lead Developer & Architect:** [Gregory R Marak](https://github.com/GREGORY070707)
- Complete UI/UX Design & Enhancement
- Backend Development & Integration
- Database Architecture & Optimization
- Security Implementation
- All Features & Animations

**Supporting Team:**
- Aditya Depkar
- Amurta Bankar
- Gayatri Dange

---

##  Features

###  Multi-Role System
- **Students**: Browse and enroll in workshops, track progress, access resources
- **Teachers**: Create and manage workshops, upload materials, view enrolled students
- **NGOs**: Partner with workshops, track beneficiaries, view impact reports
- **Admins**: Manage users, approve workshops, view analytics and reports

###  Modern UI/UX
- Beautiful gradient designs with smooth animations
- Fully responsive layout for all devices
- Intuitive navigation and user experience
- Font Awesome icons throughout
- Clean, professional interface

###  Security Features
- Secure password hashing with PHP password_hash()
- Prepared SQL statements to prevent SQL injection
- Session management with proper authentication
- Role-based access control
- Input validation and sanitization

###  Database Integration
- Complete MySQL database with relational structure
- User management system
- Workshop creation and enrollment
- Resource sharing capabilities
- Feedback and rating system
- Notifications and messaging

##  Installation Guide

### Prerequisites
- **XAMPP** (or any PHP development environment)
- **PHP 7.4+**
- **MySQL 5.7+**
- Modern web browser

### Step-by-Step Installation

#### 1. Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)

#### 2. Clone/Copy Project
Copy the `skill-share-main` folder to your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\skill-share-main\
```

#### 3. Start XAMPP Services
- Open XAMPP Control Panel
- Start **Apache** server
- Start **MySQL** server

#### 4. Setup Database
Open your browser and navigate to:
```
http://localhost/skill-share-main/setup-database.php
```

This will automatically:
- Create the `skillshare_db` database
- Create all required tables
- Insert sample data
- Create default admin account

#### 5. Access the Application
Navigate to:
```
http://localhost/skill-share-main/index.php
```

##  Default Login Credentials

### Admin Account
- **Email**: admin@skillshare.com
- **Password**: admin123
- **Role**: Admin

>   **Important**: Change the default admin password after first login!

##  Project Structure

```
skill-share-main/
assets/
css/
style.css              # Modern CSS styling
config/
database.php               # Database configuration
database/
schema.sql                 # Database schema
index.php                      # Homepage
login.php                      # Login page
register.php                   # Registration page
logout.php                     # Logout handler
dashboard.php                  # Dashboard router
student-dashboard.php          # Student dashboard
teacher-dashboard.php          # Teacher dashboard
ngo-dashboard.php              # NGO dashboard
admin-dashboard.php            # Admin dashboard
workshops.php                  # Browse workshops
workshop-details.php           # Workshop details page
enroll.php                     # Enrollment handler
setup-database.php             # Database setup script
README.md                      # This file
```

##  Database Schema

### Tables
- **users**: User accounts (students, teachers, NGOs, admins)
- **workshops**: Workshop information and scheduling
- **enrollments**: Student workshop enrollments
- **resources**: Learning materials and files
- **feedback**: Workshop ratings and reviews
- **notifications**: User notifications
- **messages**: Internal messaging system

##  Usage Guide

### For Students
1. Register with role "Student"
2. Browse available workshops
3. Enroll in workshops of interest
4. Access learning resources
5. Track your progress
6. Provide feedback

### For Teachers
1. Register with role "Teacher"
2. Create new workshops
3. Upload learning materials
4. View enrolled students
5. Manage workshop sessions
6. Review student feedback

### For NGOs
1. Register with role "NGO"
2. Browse available workshops
3. Partner with workshops
4. Track beneficiaries
5. View impact reports
6. Coordinate with teachers

### For Admins
1. Login with admin credentials
2. Approve pending workshops
3. Manage users (activate/deactivate)
4. View platform analytics
5. Monitor system activity
6. Configure platform settings

##  Configuration

### Database Settings
Edit `config/database.php` to change database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Md7903435363@');
define('DB_NAME', 'skillshare_db');
```

### Customization
- **Colors**: Modify CSS variables in `assets/css/style.css`
- **Logo**: Update logo text in header sections
- **Footer**: Edit footer content in individual PHP files

##  Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL with MySQLi
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Inter)
- **Design**: Custom CSS with CSS Variables

##  Key Features Implemented

Multi-Role System
Role-based dashboards
Workshop creation and management
Student enrollment system
Real-time participant tracking
Responsive design
Modern UI with gradients and animations
Database-driven content
Secure password handling
SQL injection prevention
Session management
Search and filter functionality

##  Browser Support

-  Chrome (recommended)
-  Firefox
-  Safari
-  Edge
-  Opera

##  Troubleshooting

### Database Connection Issues
- Verify MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Ensure database exists (run setup-database.php)

### Permission Errors
- Ensure proper file permissions
- Check XAMPP is running as administrator (Windows)

### Page Not Found
- Verify Apache is running
- Check the URL path is correct
- Clear browser cache

### Session Issues
- Check PHP session settings
- Ensure cookies are enabled
- Clear browser cookies

##  Future Enhancements

- [ ] Email notifications
- [ ] Video conferencing integration
- [ ] Certificate generation
- [ ] Payment gateway integration
- [ ] Mobile app version
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Social media integration

##  License

This project is created for educational purposes.

##  Developer Notes

### Security Best Practices Implemented
- Password hashing with bcrypt
- Prepared statements for SQL queries
- Input validation and sanitization
- Session security measures
- XSS prevention with htmlspecialchars()

### Code Quality
- Clean, readable code structure
- Consistent naming conventions
- Proper error handling
- Database connection management
- Modular file organization

##  Support

For issues or questions:
1. Check the troubleshooting section
2. Review the installation guide
3. Verify database setup
4. Check XAMPP error logs

##  Getting Started

1. **Install XAMPP**
2. **Copy project to htdocs**
3. **Run setup-database.php**
4. **Login and explore!**

---

**Made with  for empowering communities through education**

*Version 1.0.0 - October 2025*