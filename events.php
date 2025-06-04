<?php
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

// Filter parameters
$event_type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query with filters
$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if (!empty($event_type_filter)) {
    $sql .= " AND event_type = ?";
    $params[] = $event_type_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (event_title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY event_date ASC, event_time ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $events = [];
    $error_message = "Error fetching events: " . $e->getMessage();
}

// Get unique event types for filter dropdown
try {
    $type_stmt = $pdo->query("SELECT DISTINCT event_type FROM events WHERE event_type IS NOT NULL AND event_type != '' ORDER BY event_type");
    $event_types = $type_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $event_types = [];
}

// Separate events by status for better organization
$upcoming_events = [];
$ongoing_events = [];
$completed_events = [];

foreach ($events as $event) {
    $event_date = strtotime($event['event_date']);
    $today = strtotime(date('Y-m-d'));
    
    if ($event['status'] === 'Ongoing') {
        $ongoing_events[] = $event;
    } elseif ($event['status'] === 'Completed' || $event_date < $today) {
        $completed_events[] = $event;
    } else {
        $upcoming_events[] = $event;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JANALISU - Upcoming Events</title>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
            background: #f8fafc;
        }

        /* Header and Navigation */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            z-index: 1000;
            padding: 1rem 0;
            box-shadow: 0 4px 30px rgba(236, 72, 153, 0.1);
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
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

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu a:hover {
            color: #ec4899;
            transform: translateY(-2px);
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: -5px;
            left: 0;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .bar {
            width: 25px;
            height: 3px;
            background: #ec4899;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* Hero Section for Events Page */
        .hero-section {
            padding: 140px 2rem 80px;
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.9) 0%, rgba(139, 92, 246, 0.8) 50%, rgba(6, 182, 212, 0.9) 100%);
            text-align: center;
            color: white;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #fff 0%, #f8fafc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 2;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 3rem 2rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input,
        .filter-select {
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .filter-btn {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.4);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        /* Event Cards */
        .event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.4s ease;
            position: relative;
            transform: translateY(20px);
            opacity: 0;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
        }

        .event-content {
            padding: 1.5rem;
        }

        .event-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .event-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .event-meta-icon {
            font-size: 1rem;
        }

        .event-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Event Status */
        .event-status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-scheduled {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .status-ongoing {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .status-completed {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
        }

        .participants-info {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* Badges */
        .new-badge,
        .urgent-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 10;
        }

        .new-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .urgent-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 1rem;
        }

        .empty-state-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 4rem 2rem 1rem;
            position: relative;
            margin-top: 6rem;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .footer-section h3 {
            background: linear-gradient(135deg, #ec4899 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .footer-section p, .footer-section a {
            color: #cbd5e1;
            text-decoration: none;
            margin-bottom: 0.8rem;
            display: block;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ec4899;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #475569;
            color: #94a3b8;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .filter-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
            
            .filter-grid .filter-group:first-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                padding: 2rem 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .mobile-menu {
                display: flex;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .filter-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 2rem;
            }

            .logo-text {
                font-size: 1.4rem;
            }

            .logo-image {
                width: 60px;
                height: 60px;
            }

            .event-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }

            .filter-section {
                padding: 2rem 1rem;
            }

            .hero-section {
                padding: 120px 1rem 60px;
            }

            .events-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .event-card {
                margin: 0;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading States */
        .filter-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1rem; }
        .p-4 { padding: 1rem; }

        /* Print Styles */
        @media print {
            header, .filter-section, footer {
                display: none;
            }
            
            .event-card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

    </style>
</head>
<body>
   <!-- Header -->
    <header>
        <nav>
            <div class="logo-container">
        <img src="janalisu.jpg" alt="Janalisu Logo" class="logo-image">
        <div class="logo-text">JANALISU EMPOWERMENT GROUP</div>
    </div>

            <ul class="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="donate.php">Donate</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
            <div class="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">Upcoming Events</h1>
        <p class="hero-subtitle">
            Join us in making a difference in our community. Discover upcoming workshops, 
            seminars, and empowerment programs designed to uplift and inspire.
        </p>
    </section>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-grid">
            <div class="filter-group">
                <label class="filter-label">Search Events</label>
                <input type="text" name="search" class="filter-input" placeholder="Search by title, description, or location..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Event Type</label>
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <?php foreach ($event_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($event_type_filter === $type) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Scheduled" <?php echo ($status_filter === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Ongoing" <?php echo ($status_filter === 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn">🔍 Filter Events</button>
            </div>
        </form>
    </div>

    <div class="container">
        <!-- Ongoing Events -->
        <?php if (!empty($ongoing_events)): ?>
            <div class="section-header">
                <h2 class="section-title">🔥 Happening Now</h2>
                <p class="section-subtitle">Events currently taking place</p>
            </div>
            
            <div class="events-grid">
                <?php foreach ($ongoing_events as $event): ?>
                    <div class="event-card">
                        <div class="urgent-badge">Live Now</div>
                        
                        <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-image">
                        <?php else: ?>
                            <div class="event-image" style="display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                📅
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-content">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">📅</span>
                                    <span><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">⏰</span>
                                    <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">📍</span>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <?php if (!empty($event['event_type'])): ?>
                                    <div class="event-meta-item">
                                        <span class="event-meta-icon">🏷️</span>
                                        <span><?php echo htmlspecialchars($event['event_type']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($event['description'])): ?>
                                <p class="event-description">
                                    <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>
                                    <?php if (strlen($event['description']) > 150) echo '...'; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="event-footer">
                                <span class="event-status status-<?php echo strtolower($event['status']); ?>">
                                    <?php echo htmlspecialchars($event['status']); ?>
                                </span>
                                
                                <?php if (!empty($event['max_participants'])): ?>
                                    <span class="participants-info">
                                        Max: <?php echo $event['max_participants']; ?> participants
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Upcoming Events -->
        <?php if (!empty($upcoming_events)): ?>
            <div class="section-header">
                <h2 class="section-title">📅 Featured Events</h2>
                <p class="section-subtitle">Be part of transformative experiences that empower individuals and strengthen our community</p>
            </div>
            
            <div class="events-grid">
                <?php foreach ($upcoming_events as $index => $event): ?>
                    <div class="event-card" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <?php 
                        $days_until = ceil((strtotime($event['event_date']) - time()) / (60 * 60 * 24));
                        if ($days_until <= 7 && $days_until > 0): 
                        ?>
                            <div class="new-badge">Soon</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-image">
                        <?php else: ?>
                            <div class="event-image" style="display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                📅
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-content">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">📅</span>
                                    <span><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">⏰</span>
                                    <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">📍</span>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <?php if (!empty($event['event_type'])): ?>
                                    <div class="event-meta-item">
                                        <span class="event-meta-icon">🏷️</span>
                                        <span><?php echo htmlspecialchars($event['event_type']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($event['description'])): ?>
                                <p class="event-description">
                                    <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>
                                    <?php if (strlen($event['description']) > 150) echo '...'; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="event-footer">
                                <span class="event-status status-<?php echo strtolower($event['status']); ?>">
                                    <?php echo htmlspecialchars($event['status']); ?>
                                </span>
                                
                                <?php if (!empty($event['max_participants'])): ?>
                                    <span class="participants-info">
                                        Max: <?php echo $event['max_participants']; ?> participants
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Past Events -->
        <?php if (!empty($completed_events)): ?>
            <div class="section-header">
                <h2 class="section-title">📚 Past Events</h2>
                <p class="section-subtitle">Celebrating our community achievements and memories</p>
            </div>
            
            <div class="events-grid">
                <?php foreach ($completed_events as $event): ?>
                    <div class="event-card" style="opacity: 0.8;">
                        <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-image">
                        <?php else: ?>
                            <div class="event-image" style="display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                📅
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-content">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">📅</span>
                                    <span><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">⏰</span>
                                    <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <span class="event-meta-icon">📍</span>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <?php if (!empty($event['event_type'])): ?>
                                    <div class="event-meta-item">
                                        <span class="event-meta-icon">🏷️</span>
                                        <span><?php echo htmlspecialchars($event['event_type']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($event['description'])): ?>
                                <p class="event-description">
                                    <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>
                                    <?php if (strlen($event['description']) > 150) echo '...'; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="event-footer">
                                <span class="event-status status-<?php echo strtolower($event['status']); ?>">
                                    <?php echo htmlspecialchars($event['status']); ?>
                                </span>
                                
                                <?php if (!empty($event['max_participants'])): ?>
                                    <span class="participants-info">
                                        Max: <?php echo $event['max_participants']; ?> participants
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Empty State -->
        <?php if (empty($events)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📅</div>
                <h3 class="empty-state-title">No Events Found</h3>
                <p class="empty-state-subtitle">
                    <?php if (!empty($search_query) || !empty($event_type_filter) || !empty($status_filter)): ?>
                        Try adjusting your search criteria or filters to find more events.
                    <?php else: ?>
                        Stay tuned! New events will be posted here soon.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Smooth scrolling animation for cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.event-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            cards.forEach(card => {
                observer.observe(card);
            });
        });

        // Auto-clear filters button
        function clearFilters() {
            const form = document.querySelector('.filter-section form');
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'text') {
                    input.value = '';
                } else {
                    input.selectedIndex = 0;
                }
            });
            form.submit();
        }

        // Add clear filters button if filters are active
        if (window.location.search) {
            const filterSection = document.querySelector('.filter-section');
            const clearBtn = document.createElement('button');
                        clearBtn.textContent = '❌ Clear Filters';
            clearBtn.className = 'filter-btn';
            clearBtn.style.marginLeft = '10px';
            clearBtn.style.background = 'linear-gradient(135deg, #666, #333)';
            clearBtn.onclick = function(e) {
                e.preventDefault();
                clearFilters();
            };
            
            const formGroup = document.querySelector('.filter-grid .filter-group:last-child');
            formGroup.style.display = 'flex';
            formGroup.style.alignItems = 'center';
            formGroup.appendChild(clearBtn);
        }

        // Enhance form submission with loading state
        const form = document.querySelector('.filter-section form');
        if (form) {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '⏳ Loading...';
                    submitBtn.disabled = true;
                }
            });
        }

        // Add hover effects to cards programmatically
        const cards = document.querySelectorAll('.event-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.2)';
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>