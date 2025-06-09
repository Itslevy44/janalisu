<?php
require_once 'config.php'; // Includes session and DB connection

// Optional: Check if admin is logged in
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit();
// }

try {
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as total_students FROM students");
    $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];
    
    // Active students
    $stmt = $pdo->query("SELECT COUNT(*) as active_students FROM students WHERE status = 'Active'");
    $active_students = $stmt->fetch(PDO::FETCH_ASSOC)['active_students'];
    
    // Total staff/employees
    $stmt = $pdo->query("SELECT COUNT(*) as total_staff FROM employees");
    $total_staff = $stmt->fetch(PDO::FETCH_ASSOC)['total_staff'];
    
    // Active staff
    $stmt = $pdo->query("SELECT COUNT(*) as active_staff FROM employees WHERE status = 'Active'");
    $active_staff = $stmt->fetch(PDO::FETCH_ASSOC)['active_staff'];
    
    // Upcoming events
    $stmt = $pdo->query("SELECT COUNT(*) as upcoming_events FROM events WHERE status = 'Scheduled' AND event_date >= CURDATE()");
    $upcoming_events = $stmt->fetch(PDO::FETCH_ASSOC)['upcoming_events'];
    
    // Total events
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events");
    $total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];
    
    // Completed events
    $stmt = $pdo->query("SELECT COUNT(*) as completed_events FROM events WHERE status = 'Completed'");
    $completed_events = $stmt->fetch(PDO::FETCH_ASSOC)['completed_events'];
    
    // Success rate
    $success_rate = $total_events > 0 ? round(($completed_events / $total_events) * 100) : 0;
    
    // Monthly student changes
    $stmt = $pdo->query("SELECT COUNT(*) as last_month_students 
                         FROM students 
                         WHERE MONTH(enrollment_date) = MONTH(CURDATE()) - 1 
                         AND YEAR(enrollment_date) = YEAR(CURDATE())");
    $last_month_students = $stmt->fetch(PDO::FETCH_ASSOC)['last_month_students'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as this_month_students 
                         FROM students 
                         WHERE MONTH(enrollment_date) = MONTH(CURDATE()) 
                         AND YEAR(enrollment_date) = YEAR(CURDATE())");
    $this_month_students = $stmt->fetch(PDO::FETCH_ASSOC)['this_month_students'];
    
    $student_change = $this_month_students - $last_month_students;
    
} catch(PDOException $e) {
    // Set defaults on error
    $total_students = 0;
    $active_students = 0;
    $total_staff = 0;
    $active_staff = 0;
    $upcoming_events = 0;
    $success_rate = 0;
    $student_change = 0;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JANALISU Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Mobile-first approach */
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 10px 15px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: center;
        }

        .logo-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%) border-box;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .logo-image:hover {
            transform: scale(1.05);
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: left;
        }

        /* Mobile Navigation */
        .nav-toggle {
            display: block;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 5px;
        }

        .nav-menu {
            display: none;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            margin-top: 15px;
        }

        .nav-menu.active {
            display: flex;
        }

        .nav-item {
            padding: 12px 20px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-align: center;
            display: block;
        }

        .nav-item.active {
            background: #e91e63;
            color: white;
        }

        .nav-item:hover {
            background: #f0f0f0;
            color: #333;
            transform: translateY(-2px);
        }

        .nav-item.active:hover {
            background: #c2185b;
        }

        .user-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-align: center;
        }

        .welcome-text {
            color: #666;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            padding: 30px 15px;
            text-align: center;
        }

        .page-title {
            color: white;
            font-size: 2rem;
            font-weight: 300;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            margin-bottom: 30px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .stat-card {
            background: white;
            padding: 25px 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-card:active {
            transform: translateY(-2px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            filter: grayscale(20%);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-change {
            color: #4caf50;
            font-size: 0.9rem;
            font-weight: 500;
            background: rgba(76, 175, 80, 0.1);
            padding: 4px 12px;
            border-radius: 15px;
            display: inline-block;
        }

        .stat-change.negative {
            color: #f44336;
            background: rgba(244, 67, 54, 0.1);
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 300px;
            margin: 0 auto;
        }

        .nav-btn {
            background: white;
            color: #666;
            padding: 15px 25px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .nav-btn.active {
            background: #e91e63;
            color: white;
        }

        .nav-btn.active:hover {
            background: #c2185b;
        }

        /* Tablet Styles - 768px and up */
        @media (min-width: 768px) {
            .container {
                padding: 0 30px;
            }

            .header {
                padding: 15px 30px;
            }

            .header-container {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .logo-container {
                gap: 15px;
            }

            .logo-image {
                width: 60px;
                height: 60px;
            }

            .logo-text {
                font-size: 1.5rem;
            }

            .nav-toggle {
                display: none;
            }

            .nav-menu {
                display: flex;
                flex-direction: row;
                gap: 20px;
                margin-top: 0;
                width: auto;
            }

            .nav-item {
                padding: 10px 20px;
            }

            .user-section {
                flex-direction: row;
                gap: 15px;
            }

            .welcome-text {
                font-size: 1rem;
                text-align: right;
            }

            .main-content {
                padding: 40px 30px;
            }

            .page-title {
                font-size: 2.5rem;
                margin-bottom: 15px;
            }

            .page-subtitle {
                font-size: 1.1rem;
                margin-bottom: 40px;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
                margin-bottom: 50px;
            }

            .stat-card {
                padding: 30px 25px;
            }

            .stat-icon {
                font-size: 3rem;
                margin-bottom: 20px;
            }

            .stat-number {
                font-size: 3rem;
                margin-bottom: 10px;
            }

            .stat-label {
                font-size: 1.1rem;
                margin-bottom: 15px;
            }

            .nav-buttons {
                flex-direction: row;
                max-width: 600px;
                gap: 20px;
            }

            .nav-btn {
                flex: 1;
            }
        }

        /* Desktop Styles - 1024px and up */
        @media (min-width: 1024px) {
            .logo-image {
                width: 70px;
                height: 70px;
            }

            .logo-text {
                font-size: 1.8rem;
            }

            .nav-menu {
                gap: 30px;
            }

            .main-content {
                padding: 50px 30px;
            }

            .page-title {
                font-size: 3rem;
            }

            .page-subtitle {
                font-size: 1.2rem;
                margin-bottom: 50px;
            }

            .stats-container {
                grid-template-columns: repeat(4, 1fr);
                gap: 30px;
            }

            .stat-icon {
                font-size: 3.5rem;
            }

            .stat-number {
                font-size: 3.5rem;
            }
        }

        /* Large Desktop - 1440px and up */
        @media (min-width: 1440px) {
            .container {
                max-width: 1400px;
            }

            .logo-image {
                width: 80px;
                height: 80px;
            }

            .main-content {
                padding: 60px 30px;
            }

            .page-title {
                font-size: 3.5rem;
            }

            .stats-container {
                gap: 35px;
            }

            .stat-card {
                padding: 35px 30px;
            }
        }

        /* Ultra-wide screens - 1920px and up */
        @media (min-width: 1920px) {
            .container {
                max-width: 1600px;
            }

            .main-content {
                padding: 80px 30px;
            }

            .page-title {
                font-size: 4rem;
            }

            .page-subtitle {
                font-size: 1.4rem;
            }
        }

        /* Portrait orientation adjustments */
        @media (orientation: portrait) and (max-width: 768px) {
            .stats-container {
                gap: 15px;
            }

            .stat-card {
                padding: 20px 15px;
            }

            .stat-number {
                font-size: 2rem;
            }

            .stat-icon {
                font-size: 2rem;
            }
        }

        /* Landscape orientation for mobile */
        @media (orientation: landscape) and (max-height: 600px) {
            .main-content {
                padding: 20px 15px;
            }

            .page-title {
                font-size: 1.8rem;
                margin-bottom: 8px;
            }

            .page-subtitle {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }

            .header {
                padding: 8px 15px;
            }
        }

        /* Loading and transition effects */
        .fade-in {
            opacity: 0;
            animation: fadeIn 0.6s ease-in-out forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Touch device optimizations */
        @media (pointer: coarse) {
            .nav-item,
            .nav-btn,
            .logout-btn {
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .stat-card {
                min-height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2),
               (min-resolution: 192dpi) {
            .logo-image {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .header {
                background: rgba(30, 30, 30, 0.95);
            }
            
            .nav-item {
                color: #ccc;
            }
            
            .welcome-text {
                color: #ccc;
            }
            
            .logo-text {
                filter: brightness(1.2);
            }
        }
    </style>
</head>
<body class="fade-in">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-container">
                <div class="logo-container">
                    <img src="janalisu.jpg" alt="Janalisu Logo" class="logo-image">
                    <div class="logo-text">JANALISU EMPOWERMENT GROUP</div>
                </div>
                
                <button class="nav-toggle" onclick="toggleNav()" aria-label="Toggle navigation">
                    ☰
                </button>
                
                <nav class="nav-menu" id="navMenu">
                    <a href="admin_dashboard.php" class="nav-item active">Dashboard</a>
                    <a href="students.php" class="nav-item">Students</a>
                    <a href="staffs.php" class="nav-item">Staff</a>
                    <a href="add_events.php" class="nav-item">Events</a>
                </nav>
                
                <div class="user-section">
                    <span class="welcome-text">Welcome,<br>Admin</span>
                    <button class="logout-btn" onclick="logout()">Logout</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-subtitle">Manage your organization's data efficiently</p>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card" onclick="navigateTo('students.php')" tabindex="0" role="button" aria-label="View students">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-change">+<?php echo $active_students; ?> Active</div>
                </div>
                
                <div class="stat-card" onclick="navigateTo('staffs.php')" tabindex="0" role="button" aria-label="View staff">
                    <div class="stat-icon">👨‍💼</div>
                    <div class="stat-number"><?php echo $total_staff; ?></div>
                    <div class="stat-label">Staff Members</div>
                    <div class="stat-change">+<?php echo $active_staff; ?> Active</div>
                </div>
                
                <div class="stat-card" onclick="navigateTo('events.php')" tabindex="0" role="button" aria-label="View events">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo $upcoming_events; ?></div>
                    <div class="stat-label">Upcoming Events</div>
                    <div class="stat-change">+<?php echo $total_events; ?> Total</div>
                </div>
                
                <div class="stat-card" tabindex="0" role="button" aria-label="View success rate">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number"><?php echo $success_rate; ?>%</div>
                    <div class="stat-label">Success Rate</div>
                    <div class="stat-change <?php echo $student_change < 0 ? 'negative' : ''; ?>">
                        <?php echo $student_change >= 0 ? '+' . $student_change : $student_change; ?> This Month
                    </div>
                </div>
            </div>
            
            <!-- Navigation Buttons -->
            <div class="nav-buttons">
                <a href="students.php" class="nav-btn active">Students</a>
                <a href="staffs.php" class="nav-btn">Staff</a>
                <a href="add_events.php" class="nav-btn">Events</a>
            </div>
        </div>
    </main>

    <script>
        // Mobile navigation toggle
        function toggleNav() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }

        // Close mobile nav when clicking outside
        document.addEventListener('click', function(event) {
            const navMenu = document.getElementById('navMenu');
            const navToggle = document.querySelector('.nav-toggle');
            
            if (!navMenu.contains(event.target) && !navToggle.contains(event.target)) {
                navMenu.classList.remove('active');
            }
        });

        // Navigation function
        function navigateTo(page) {
            window.location.href = page;
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'home.php';
            }
        }

        // Keyboard navigation for stat cards
        document.querySelectorAll('.stat-card[tabindex="0"]').forEach(card => {
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        // Add click effects to cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-5px)';
                }, 150);
            });
        });

        // Add active state to navigation buttons
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Responsive navigation handling
        function handleResize() {
            const navMenu = document.getElementById('navMenu');
            if (window.innerWidth >= 768) {
                navMenu.classList.remove('active');
            }
        }

        // Listen for window resize
        window.addEventListener('resize', handleResize);

        // Touch/swipe handling for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                const navMenu = document.getElementById('navMenu');
                if (diff > 0) {
                    // Swipe left - close menu
                    navMenu.classList.remove('active');
                }
            }
        }

        // Page load animation
        document.addEventListener('DOMContentLoaded', function() {
            // Stagger card animations
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Performance optimization - Intersection Observer for animations
        if ('IntersectionObserver' in window) {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.stat-card').forEach(card => {
                observer.observe(card);
            });
        }
    </script>
</body>
</html>