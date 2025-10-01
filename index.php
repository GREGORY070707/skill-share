<?php
session_start();
require_once 'config/database.php';

// Get real statistics
$conn = getDBConnection();

$total_students = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];
$total_workshops = $conn->query("SELECT COUNT(*) as count FROM workshops WHERE status = 'approved'")->fetch_assoc()['count'];
$total_enrollments = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE status = 'completed'")->fetch_assoc()['count'];
$total_ngos = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'ngo'")->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill-Sharing Network | Empowering Through Education</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.5); }
            50% { box-shadow: 0 0 40px rgba(102, 126, 234, 0.8); }
        }
        
        body {
            scroll-behavior: smooth;
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(139, 92, 246, 0.95)), 
                        url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 8rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.15) 0%, transparent 50%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 3s linear infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .hero-section h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 2px 4px 12px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease;
            line-height: 1.2;
        }
        
        .hero-section p {
            font-size: 1.5rem;
            margin-bottom: 3rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease 0.2s both;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.4s both;
        }
        
        .hero-btn {
            padding: 1.25rem 3rem;
            font-size: 1.125rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .hero-btn:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .hero-btn-primary {
            background: white;
            color: #6366f1;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .hero-btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            animation: glow 2s ease-in-out infinite;
        }
        
        .hero-btn-secondary {
            background: transparent;
            color: white;
            border: 3px solid white;
        }
        
        .hero-btn-secondary:hover {
            background: white;
            color: #6366f1;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 50px rgba(255, 255, 255, 0.3);
        }
        
        .features-section {
            padding: 5rem 2rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
            position: relative;
        }
        
        .section-title {
            text-align: center;
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            color: #1f2937;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 4rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }
        
        .module-card {
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: fadeInUp 0.8s ease;
            animation-fill-mode: both;
        }
        
        .module-card:nth-child(1) { animation-delay: 0.1s; }
        .module-card:nth-child(2) { animation-delay: 0.2s; }
        .module-card:nth-child(3) { animation-delay: 0.3s; }
        .module-card:nth-child(4) { animation-delay: 0.4s; }
        
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .module-card:hover::before {
            left: 100%;
        }
        
        .module-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        }
        
        .module-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .module-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: float 3s ease-in-out infinite;
        }
        
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="2"/></svg>');
            opacity: 0.3;
        }
        
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
            position: relative;
            z-index: 1;
        }
        
        .stat-item {
            animation: fadeInUp 1s ease;
            animation-fill-mode: both;
        }
        
        .stat-item:nth-child(1) { animation-delay: 0.2s; }
        .stat-item:nth-child(2) { animation-delay: 0.3s; }
        .stat-item:nth-child(3) { animation-delay: 0.4s; }
        .stat-item:nth-child(4) { animation-delay: 0.5s; }
        
        .stat-item h3 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .stat-item p {
            font-size: 1.25rem;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i> SkillShare
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="workshops.php"><i class="fas fa-chalkboard-teacher"></i> Workshops</a></li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="register.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Get Started</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero-section">
        <div class="hero-content">
            <div style="display: inline-block; background: rgba(255, 255, 255, 0.2); padding: 0.75rem 2rem; border-radius: 50px; margin-bottom: 2rem; backdrop-filter: blur(10px); animation: fadeInUp 0.8s ease;">
                <i class="fas fa-star" style="color: #fbbf24;"></i>
                <span style="font-weight: 600; font-size: 1rem;">Empowering 1000+ Students Nationwide</span>
            </div>
            <h1>Transform Lives Through<br><span style="background: linear-gradient(90deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Skill Development</span></h1>
            <p>Connect educators, NGOs, and students for collaborative learning and growth. Building a brighter future through education and community empowerment.</p>
            <div class="hero-buttons">
                <a href="register.php" class="hero-btn hero-btn-primary">
                    <i class="fas fa-rocket"></i> Get Started Free
                </a>
                <a href="workshops.php" class="hero-btn hero-btn-secondary">
                    <i class="fas fa-search"></i> Explore Workshops
                </a>
                <a href="credits.php" class="hero-btn hero-btn-secondary">
                    <i class="fas fa-users"></i> Meet the Team
                </a>
            </div>
            <div style="margin-top: 3rem; display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; animation: fadeInUp 1s ease 0.6s both;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.5rem;"></i>
                    <span>100% Free Platform</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.5rem;"></i>
                    <span>Expert Teachers</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.5rem;"></i>
                    <span>Certified Courses</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Highlight -->
    <section style="padding: 4rem 2rem; background: white;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 4rem;">
                <span style="display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 0.5rem 1.5rem; border-radius: 50px; font-weight: 600; margin-bottom: 1rem;">
                    <i class="fas fa-bolt"></i> Why Choose SkillShare?
                </span>
                <h2 style="font-size: 3rem; font-weight: 900; color: #1f2937; margin-bottom: 1rem;">Everything You Need to Succeed</h2>
                <p style="font-size: 1.25rem; color: #6b7280; max-width: 700px; margin: 0 auto;">Comprehensive platform designed for seamless learning and teaching experiences</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
                <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 2rem; border-radius: 16px; text-align: center; animation: fadeInUp 0.8s ease 0.1s both;">
                    <i class="fas fa-laptop-code" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Interactive Learning</h3>
                    <p style="opacity: 0.9;">Engage with live sessions, resources, and collaborative projects</p>
                </div>
                <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 2rem; border-radius: 16px; text-align: center; animation: fadeInUp 0.8s ease 0.2s both;">
                    <i class="fas fa-certificate" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Earn Certificates</h3>
                    <p style="opacity: 0.9;">Get recognized for your achievements with verified certificates</p>
                </div>
                <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 2rem; border-radius: 16px; text-align: center; animation: fadeInUp 0.8s ease 0.3s both;">
                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Community Driven</h3>
                    <p style="opacity: 0.9;">Join a vibrant community of learners and educators</p>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Choose Your Path</h2>
            <p class="section-subtitle">Join our platform as a student, teacher, NGO, or administrator and start making a difference today</p>
            
            <div class="card-grid">
                <div class="card module-card">
                    <a href="register.php?role=student">
                        <div class="module-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3>Students</h3>
                        <p>Discover and enroll in workshops to learn new skills. Access resources, track your progress, and earn certificates.</p>
                        <span class="badge badge-primary">Join Now</span>
                    </a>
                </div>

                <div class="card module-card">
                    <a href="register.php?role=teacher">
                        <div class="module-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3>Teachers</h3>
                        <p>Share your expertise by conducting workshops. Upload resources, manage sessions, and inspire students.</p>
                        <span class="badge badge-success">Teach Now</span>
                    </a>
                </div>

                <div class="card module-card">
                    <a href="register.php?role=ngo">
                        <div class="module-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h3>NGOs</h3>
                        <p>Partner with us to bring quality education to communities. Coordinate workshops and track impact.</p>
                        <span class="badge badge-warning">Partner Up</span>
                    </a>
                </div>

                <div class="card module-card">
                    <a href="login.php?role=admin">
                        <div class="module-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3>Admin</h3>
                        <p>Manage the platform, approve workshops, monitor activities, and ensure smooth operations.</p>
                        <span class="badge badge-danger">Admin Panel</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section style="padding: 5rem 2rem; background: white;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 3rem; font-weight: 900; color: #1f2937; margin-bottom: 1rem;">How It Works</h2>
                <p style="font-size: 1.25rem; color: #6b7280;">Get started in just 3 simple steps</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; max-width: 1000px; margin: 0 auto;">
                <div style="text-align: center; animation: fadeInUp 0.8s ease 0.1s both;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: white; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                        1
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Sign Up</h3>
                    <p style="color: #6b7280; line-height: 1.6;">Create your free account as a student, teacher, or NGO partner</p>
                </div>
                
                <div style="text-align: center; animation: fadeInUp 0.8s ease 0.2s both;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: white; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);">
                        2
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Explore & Enroll</h3>
                    <p style="color: #6b7280; line-height: 1.6;">Browse workshops, enroll in courses, or create your own workshops</p>
                </div>
                
                <div style="text-align: center; animation: fadeInUp 0.8s ease 0.3s both;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: white; box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);">
                        3
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Learn & Grow</h3>
                    <p style="color: #6b7280; line-height: 1.6;">Attend sessions, complete courses, and earn certificates</p>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <h2 class="section-title" style="color: white; font-size: 3rem;">Our Impact in Numbers</h2>
            <p style="font-size: 1.25rem; opacity: 0.95; margin-bottom: 2rem;">Making a real difference in communities</p>
            <div class="stats-container">
                <div class="stat-item">
                    <h3><i class="fas fa-users"></i> <?php echo number_format($total_students); ?><?php echo $total_students > 0 ? '+' : ''; ?></h3>
                    <p>Active Students</p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-chalkboard"></i> <?php echo number_format($total_workshops); ?><?php echo $total_workshops > 0 ? '+' : ''; ?></h3>
                    <p>Workshops Available</p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-award"></i> <?php echo number_format($total_enrollments); ?><?php echo $total_enrollments > 0 ? '+' : ''; ?></h3>
                    <p>Certificates Issued</p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-heart"></i> <?php echo number_format($total_ngos); ?><?php echo $total_ngos > 0 ? '+' : ''; ?></h3>
                    <p>NGO Partners</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section style="padding: 5rem 2rem; background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);">
        <div class="container">
            <div style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 3rem; font-weight: 900; color: #1f2937; margin-bottom: 1rem;">What Our Community Says</h2>
                <p style="font-size: 1.25rem; color: #6b7280;">Real stories from real people</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-left: 4px solid #667eea; animation: fadeInUp 0.8s ease 0.1s both;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                    </div>
                    <p style="color: #374151; font-size: 1.125rem; line-height: 1.7; margin-bottom: 1.5rem;">"SkillShare transformed my career! The workshops are practical, teachers are amazing, and I earned certificates that helped me get a job."</p>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.25rem;">S</div>
                        <div>
                            <strong style="color: #1f2937;">Sarah Johnson</strong>
                            <p style="color: #6b7280; margin: 0; font-size: 0.875rem;">Student</p>
                        </div>
                    </div>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-left: 4px solid #10b981; animation: fadeInUp 0.8s ease 0.2s both;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                    </div>
                    <p style="color: #374151; font-size: 1.125rem; line-height: 1.7; margin-bottom: 1.5rem;">"As a teacher, this platform makes it easy to reach students and share knowledge. The tools are intuitive and the support is excellent!"</p>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.25rem;">M</div>
                        <div>
                            <strong style="color: #1f2937;">Michael Chen</strong>
                            <p style="color: #6b7280; margin: 0; font-size: 0.875rem;">Teacher</p>
                        </div>
                    </div>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-left: 4px solid #f59e0b; animation: fadeInUp 0.8s ease 0.3s both;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                    </div>
                    <p style="color: #374151; font-size: 1.125rem; line-height: 1.7; margin-bottom: 1.5rem;">"Perfect platform for NGOs to track impact and partner with educators. The analytics help us measure our social impact effectively."</p>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.25rem;">P</div>
                        <div>
                            <strong style="color: #1f2937;">Priya Sharma</strong>
                            <p style="color: #6b7280; margin: 0; font-size: 0.875rem;">NGO Director</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title" style="color: white; font-size: 3rem;">Our Impact in Numbers</h2>
            <p style="font-size: 1.25rem; opacity: 0.95; margin-bottom: 2rem;">Making a real difference in communities across the nation</p>
            <div class="stats-container">
                <div class="stat-item">
                    <h3><i class="fas fa-users"></i> <?php echo number_format($total_students); ?><?php echo $total_students > 0 ? '+' : ''; ?></h3>
                    <p>Active Students</p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-chalkboard"></i> <?php echo number_format($total_workshops); ?><?php echo $total_workshops > 0 ? '+' : ''; ?></h3>
                    <p>Workshops Available</p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-award"></i> <?php echo number_format($total_enrollments); ?><?php echo $total_enrollments > 0 ? '+' : ''; ?></h3>
                    <p>Certificates Issued</p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-heart"></i> <?php echo number_format($total_ngos); ?><?php echo $total_ngos > 0 ? '+' : ''; ?></h3>
                    <p>NGO Partners</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section style="padding: 5rem 2rem; background: linear-gradient(135deg, #1f2937 0%, #111827 100%); color: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 3rem; font-weight: 900; margin-bottom: 1rem; animation: fadeInUp 0.8s ease;">Ready to Start Your Learning Journey?</h2>
            <p style="font-size: 1.25rem; opacity: 0.9; margin-bottom: 2.5rem; animation: fadeInUp 0.8s ease 0.2s both;">Join thousands of learners and educators making a difference</p>
            <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap; animation: fadeInUp 0.8s ease 0.4s both;">
                <a href="register.php" class="hero-btn hero-btn-primary">
                    <i class="fas fa-user-plus"></i> Create Free Account
                </a>
                <a href="workshops.php" class="hero-btn hero-btn-secondary">
                    <i class="fas fa-compass"></i> Browse Workshops
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
