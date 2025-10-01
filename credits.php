<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits & Team | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .credits-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .hero-section {
            text-align: center;
            color: white;
            padding: 3rem 2rem;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .hero-section p {
            font-size: 1.25rem;
            opacity: 0.95;
        }
        
        .lead-developer {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeInUp 0.8s ease 0.2s both;
            border: 4px solid #f59e0b;
        }
        
        .lead-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .developer-name {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1f2937;
            margin: 1rem 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .role-title {
            font-size: 1.5rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .contributions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .contribution-item {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .contribution-item:hover {
            transform: translateY(-5px);
        }
        
        .contribution-item i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .team-section {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .team-section h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 900;
            color: #1f2937;
            margin-bottom: 2rem;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .team-member {
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .team-member:hover::before {
            left: 100%;
        }
        
        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }
        
        .member-avatar {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 900;
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .member-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .member-role {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .tech-stack {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: white;
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem 0;
            animation: fadeInUp 0.8s ease 0.6s both;
        }
        
        .tech-stack h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 2rem;
        }
        
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
        }
        
        .tech-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .tech-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }
        
        .tech-item i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
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
                    <li><a href="credits.php"><i class="fas fa-users"></i> Credits</a></li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="credits-container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1><i class="fas fa-trophy"></i> Credits & Development Team</h1>
            <p>Meet the talented team behind SkillShare Platform</p>
        </div>

        <!-- Lead Developer Section -->
        <div class="lead-developer">
            <div class="lead-badge">
                <i class="fas fa-crown"></i> LEAD DEVELOPER & ARCHITECT
            </div>
            <div class="developer-name">Gregory R Marak</div>
            <div class="role-title">
                <i class="fas fa-code"></i> Full Stack Developer | UI/UX Designer | Database Architect
            </div>
            
            <div style="background: #f9fafb; padding: 2rem; border-radius: 12px; margin: 2rem 0;">
                <h3 style="color: #1f2937; margin-bottom: 1rem;">
                    <i class="fas fa-star"></i> Primary Contributions
                </h3>
                <div class="contributions">
                    <div class="contribution-item">
                        <i class="fas fa-paint-brush"></i>
                        <h4>UI/UX Design</h4>
                        <p>Complete interface design & user experience</p>
                    </div>
                    <div class="contribution-item">
                        <i class="fas fa-server"></i>
                        <h4>Backend Development</h4>
                        <p>PHP architecture & business logic</p>
                    </div>
                    <div class="contribution-item">
                        <i class="fas fa-database"></i>
                        <h4>Database Design</h4>
                        <p>MySQL schema & optimization</p>
                    </div>
                    <div class="contribution-item">
                        <i class="fas fa-magic"></i>
                        <h4>Animations</h4>
                        <p>CSS animations & transitions</p>
                    </div>
                    <div class="contribution-item">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Security</h4>
                        <p>Authentication & data protection</p>
                    </div>
                    <div class="contribution-item">
                        <i class="fas fa-cogs"></i>
                        <h4>System Integration</h4>
                        <p>Complete platform integration</p>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <span style="display: inline-block; background: #667eea; color: white; padding: 0.5rem 1.5rem; border-radius: 50px; margin: 0.25rem; font-weight: 600;">
                    <i class="fas fa-code"></i> PHP
                </span>
                <span style="display: inline-block; background: #10b981; color: white; padding: 0.5rem 1.5rem; border-radius: 50px; margin: 0.25rem; font-weight: 600;">
                    <i class="fas fa-database"></i> MySQL
                </span>
                <span style="display: inline-block; background: #f59e0b; color: white; padding: 0.5rem 1.5rem; border-radius: 50px; margin: 0.25rem; font-weight: 600;">
                    <i class="fab fa-html5"></i> HTML5
                </span>
                <span style="display: inline-block; background: #3b82f6; color: white; padding: 0.5rem 1.5rem; border-radius: 50px; margin: 0.25rem; font-weight: 600;">
                    <i class="fab fa-css3-alt"></i> CSS3
                </span>
                <span style="display: inline-block; background: #ef4444; color: white; padding: 0.5rem 1.5rem; border-radius: 50px; margin: 0.25rem; font-weight: 600;">
                    <i class="fab fa-js"></i> JavaScript
                </span>
            </div>
        </div>

        <!-- Team Section -->
        <div class="team-section">
            <h2><i class="fas fa-users"></i> Supporting Team Members</h2>
            <p style="text-align: center; color: #6b7280; font-size: 1.125rem; margin-bottom: 2rem;">
                Special thanks to our amazing team for their valuable contributions
            </p>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">AD</div>
                    <div class="member-name">Aditya Depkar</div>
                    <div class="member-role">
                        <i class="fas fa-hands-helping"></i> Team Support
                    </div>
                    <p style="color: #6b7280; font-size: 0.875rem;">
                        Contributed to project planning and testing
                    </p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">AB</div>
                    <div class="member-name">Amurta Bankar</div>
                    <div class="member-role">
                        <i class="fas fa-hands-helping"></i> Team Support
                    </div>
                    <p style="color: #6b7280; font-size: 0.875rem;">
                        Assisted with requirements and documentation
                    </p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">GD</div>
                    <div class="member-name">Gayatri Dange</div>
                    <div class="member-role">
                        <i class="fas fa-hands-helping"></i> Team Support
                    </div>
                    <p style="color: #6b7280; font-size: 0.875rem;">
                        Helped with content and user feedback
                    </p>
                </div>
            </div>
        </div>

        <!-- Technology Stack -->
        <div class="tech-stack">
            <h2><i class="fas fa-laptop-code"></i> Technology Stack</h2>
            <div class="tech-grid">
                <div class="tech-item">
                    <i class="fab fa-php"></i>
                    <h4>PHP 7.4+</h4>
                </div>
                <div class="tech-item">
                    <i class="fas fa-database"></i>
                    <h4>MySQL</h4>
                </div>
                <div class="tech-item">
                    <i class="fab fa-html5"></i>
                    <h4>HTML5</h4>
                </div>
                <div class="tech-item">
                    <i class="fab fa-css3-alt"></i>
                    <h4>CSS3</h4>
                </div>
                <div class="tech-item">
                    <i class="fab fa-js-square"></i>
                    <h4>JavaScript</h4>
                </div>
                <div class="tech-item">
                    <i class="fas fa-chart-line"></i>
                    <h4>Chart.js</h4>
                </div>
            </div>
        </div>

        <!-- Project Info -->
        <div style="background: white; border-radius: 20px; padding: 3rem; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
            <h2 style="color: #1f2937; font-size: 2rem; margin-bottom: 1rem;">
                <i class="fas fa-graduation-cap"></i> SkillShare Platform
            </h2>
            <p style="color: #6b7280; font-size: 1.125rem; max-width: 800px; margin: 0 auto 2rem;">
                A comprehensive skill-sharing network connecting students, teachers, and NGOs to empower communities through education and collaborative learning.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <div style="background: #f9fafb; padding: 1.5rem; border-radius: 12px; min-width: 150px;">
                    <div style="font-size: 2rem; font-weight: 800; color: #667eea;">4</div>
                    <div style="color: #6b7280;">User Roles</div>
                </div>
                <div style="background: #f9fafb; padding: 1.5rem; border-radius: 12px; min-width: 150px;">
                    <div style="font-size: 2rem; font-weight: 800; color: #10b981;">50+</div>
                    <div style="color: #6b7280;">Features</div>
                </div>
                <div style="background: #f9fafb; padding: 1.5rem; border-radius: 12px; min-width: 150px;">
                    <div style="font-size: 2rem; font-weight: 800; color: #f59e0b;">100%</div>
                    <div style="color: #6b7280;">Functional</div>
                </div>
            </div>
        </div>
    </div>

    <footer style="margin-top: 4rem; background: rgba(0, 0, 0, 0.2); padding: 2rem; text-align: center; color: white;">
        <p style="margin: 0; font-size: 1.125rem;">
            <strong>Developed by Gregory R Marak</strong> with support from Aditya Depkar, Amurta Bankar, and Gayatri Dange
        </p>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
            &copy; <?php echo date('Y'); ?> SkillShare Platform | Empowering Through Education
        </p>
    </footer>
</body>
</html>
