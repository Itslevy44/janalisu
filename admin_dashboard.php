<?php
session_start();

// Optional: Check if admin is logged in (uncomment when login system is ready)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit();
// }

// Database connection - XAMPP default settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "janalisu";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch statistics
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
    
    // Upcoming events (scheduled events)
    $stmt = $pdo->query("SELECT COUNT(*) as upcoming_events FROM events WHERE status = 'Scheduled' AND event_date >= CURDATE()");
    $upcoming_events = $stmt->fetch(PDO::FETCH_ASSOC)['upcoming_events'];
    
    // Total events
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events");
    $total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];
    
    // Calculate success rate (completed events / total events)
    $stmt = $pdo->query("SELECT COUNT(*) as completed_events FROM events WHERE status = 'Completed'");
    $completed_events = $stmt->fetch(PDO::FETCH_ASSOC)['completed_events'];
    
    $success_rate = $total_events > 0 ? round(($completed_events / $total_events) * 100) : 0;
    
    // Calculate monthly changes (comparison with previous month)
    $stmt = $pdo->query("SELECT COUNT(*) as last_month_students FROM students WHERE MONTH(enrollment_date) = MONTH(CURDATE()) - 1 AND YEAR(enrollment_date) = YEAR(CURDATE())");
    $last_month_students = $stmt->fetch(PDO::FETCH_ASSOC)['last_month_students'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as this_month_students FROM students WHERE MONTH(enrollment_date) = MONTH(CURDATE()) AND YEAR(enrollment_date) = YEAR(CURDATE())");
    $this_month_students = $stmt->fetch(PDO::FETCH_ASSOC)['this_month_students'];
    
    $student_change = $this_month_students - $last_month_students;
    
} catch(PDOException $e) {
    // Set default values if database query fails
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
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }

        .org-name {
            color: #667eea;
            font-size: 24px;
            font-weight: bold;
        }

        .org-name .empowerment {
            color: #e91e63;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-item {
            padding: 10px 20px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-item.active {
            background: #e91e63;
            color: white;
        }

        .nav-item:hover {
            background: #f0f0f0;
            color: #333;
        }

        .nav-item.active:hover {
            background: #c2185b;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .welcome-text {
            color: #666;
            font-size: 14px;
        }

        .logout-btn {
            background: #f44336;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #d32f2f;
        }

        /* Main Content */
        .main-content {
            padding: 50px 30px;
            text-align: center;
        }

        .page-title {
            color: white;
            font-size: 48px;
            font-weight: 300;
            margin-bottom: 15px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            margin-bottom: 50px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 50px;
        }

        .stat-card {
            background: white;
            padding: 30px 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .stat-change {
            color: #4caf50;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-change.negative {
            color: #f44336;
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .nav-btn {
            background: white;
            color: #666;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .nav-btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }

        .nav-btn.active {
            background: #e91e63;
            color: white;
        }

        .nav-btn.active:hover {
            background: #c2185b;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .nav-menu {
                gap: 15px;
            }

            .stats-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .nav-buttons {
                flex-direction: column;
                align-items: center;
            }

            .page-title {
                font-size: 36px;
            }
        }
         .logo-container {
    display: flex;
    align-items: center;
    gap: 15px;
}

.logo-image {
    width: 80px;           /* Increased size */
    height: 80px;          /* Same as width for perfect circle */
    border-radius: 50%;    /* Makes it perfectly round */
    object-fit: cover;     /* Ensures image fills the circle properly */
    border: 3px solid transparent;
    background: linear-gradient(white, white) padding-box,
                linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%) border-box;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.logo-image:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.logo-text {
    font-size: 1.8rem;
    font-weight: bold;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
}

    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
         <div class="logo-container">
        <img src="janalisu.jpg" alt="Janalisu Logo" class="logo-image">
        <div class="logo-text">JANALISU EMPOWERMENT GROUP</div>
        
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-item">Dashboard</a>
            <a href="students.php" class="nav-item">Students</a>
            <a href="staffs.php" class="nav-item">Staff</a>
            <a href="add_events.php" class="nav-item">Events</a>
        </nav>
        
        <div class="user-section">
            <span class="welcome-text">Welcome,<br>Admin</span>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Manage your organization's data efficiently</p>
        
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card" onclick="navigateTo('students.php')">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $total_students; ?></div>
                <div class="stat-label">Total Students</div>
                <div class="stat-change">+<?php echo $active_students; ?> Active</div>
            </div>
            
            <div class="stat-card" onclick="navigateTo('staffs.php')">
                <div class="stat-icon">👨‍💼</div>
                <div class="stat-number"><?php echo $total_staff; ?></div>
                <div class="stat-label">Staff Members</div>
                <div class="stat-change">+<?php echo $active_staff; ?> Active</div>
            </div>
            
            <div class="stat-card" onclick="navigateTo('events.php')">
                <div class="stat-icon">📅</div>
                <div class="stat-number"><?php echo $upcoming_events; ?></div>
                <div class="stat-label">Upcoming Events</div>
                <div class="stat-change">+<?php echo $total_events; ?> Total</div>
            </div>
            
            <div class="stat-card">
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
            <a href="events.php" class="nav-btn">Events</a>
        </div>
    </main>

    <script>
        // Navigation function
        function navigateTo(page) {
            window.location.href = page;
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                // Redirect to login page (you can create logout.php later)
                window.location.href = 'home.php';
            }
        }

        // Add click effects to cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-5px)';
                }, 100);
            });
        });

        // Add active state to navigation buttons
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Remove active class from all buttons
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
            });
        });

        // Smooth scrolling and page transitions
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>