<?php
require_once 'config.php';

// Fetch all events from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date ASC, start_time ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching events: " . $e->getMessage();
    $events = [];
}

// Filter events based on search and filters if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$event_type = isset($_GET['event_type']) ? $_GET['event_type'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

if (!empty($search) || $event_type !== 'all' || $status_filter !== 'all') {
    $filtered_events = [];
    foreach ($events as $event) {
        $match = true;
        
        // Search filter
        if (!empty($search)) {
            $search_text = strtolower($search);
            if (strpos(strtolower($event['title']), $search_text) === false &&
                strpos(strtolower($event['description']), $search_text) === false &&
                strpos(strtolower($event['location']), $search_text) === false) {
                $match = false;
            }
        }
        
        // Status filter
        if ($status_filter !== 'all' && strtolower($event['status']) !== strtolower($status_filter)) {
            $match = false;
        }
        
        if ($match) {
            $filtered_events[] = $event;
        }
    }
    $events = $filtered_events;
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Function to format time
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Function to get status badge class
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'scheduled':
            return 'status-scheduled';
        case 'ongoing':
            return 'status-ongoing';
        case 'completed':
            return 'status-completed';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-scheduled';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events - JANALISU EMPOWERMENT GROUP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #e91e63 0%, #9c27b0 25%, #673ab7 50%, #3f51b5 75%, #00bcd4 100%);
            padding: 2rem 0;
            text-align: center;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #e91e63;
        }

        /* Mobile menu styles */
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

        .hero {
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .filters {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .filters h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.2rem;
        }

        .filter-row {
            display: grid;
            grid-template-columns: 1fr 200px 200px;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .search-input, .filter-select {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: #e91e63;
        }

        .filter-btn {
            background: linear-gradient(135deg, #e91e63, #9c27b0);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .event-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .event-header {
            background: linear-gradient(135deg, #e91e63 0%, #9c27b0 25%, #673ab7 50%, #3f51b5 75%, #00bcd4 100%);
            padding: 1.5rem;
            color: white;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .event-date {
            font-size: 1rem;
            opacity: 0.9;
        }

        .event-body {
            padding: 1.5rem;
        }

        .event-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .event-details {
            display: grid;
            gap: 0.5rem;
        }

        .event-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
            font-size: 0.9rem;
        }

        .event-detail-icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-scheduled {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-ongoing {
            background: #e8f5e8;
            color: #388e3c;
        }

        .status-completed {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .status-cancelled {
            background: #ffebee;
            color: #d32f2f;
        }

        .no-events {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-events h3 {
            margin-bottom: 1rem;
            color: #333;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
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
            font-size: 1.2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .nav-content {
                flex-direction: row;
                gap: 1rem;
            }
            
            .nav-links {
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
                z-index: 1001;
                gap: 1rem;
            }

            .nav-links.active {
                left: 0;
            }

            .mobile-menu {
                display: flex;
            }

            .logo-container {
                gap: 0.5rem;
            }

            .logo-image {
                width: 40px;
                height: 40px;
            }

            .logo-text {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .logo-image {
                width: 35px;
                height: 35px;
            }
            
            .logo-text {
                font-size: 0.9rem;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .filters {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-content">
            <div class="logo-container">
                <img src="janalisu.jpg" alt="Janalisu Logo" class="logo-image">
                <div class="logo-text">JANALISU EMPOWERMENT GROUP</div>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php" class="active">Events</a></li>
                <li><a href="donate.php">Donate</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
            <div class="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <div class="header">
        <div class="container">
            <div class="hero">
                <h1>Upcoming Events</h1>
                <p>Join us in making a difference in our community. Discover upcoming workshops, seminars, and empowerment programs designed to uplift and inspire.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="filters">
            <h3>SEARCH EVENTS</h3>
            <form method="GET" action="">
                <div class="filter-row">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Search by title, description, or location..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <select name="event_type" class="filter-select">
                        <option value="all">All Types</option>
                        <option value="workshop" <?php echo $event_type === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                        <option value="seminar" <?php echo $event_type === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                        <option value="training" <?php echo $event_type === 'training' ? 'selected' : ''; ?>>Training</option>
                        <option value="meeting" <?php echo $event_type === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                    </select>
                    <select name="status" class="filter-select">
                        <option value="all">All Status</option>
                        <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="ongoing" <?php echo $status_filter === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Search Events</button>
            </form>
        </div>

        <?php if (empty($events)): ?>
            <div class="no-events">
                <h3>No Events Found</h3>
                <p>There are currently no events matching your criteria. Please check back later or modify your search.</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-header">
                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            <div class="event-date"><?php echo formatDate($event['event_date']); ?></div>
                        </div>
                        <div class="event-body">
                            <?php if (!empty($event['description'])): ?>
                                <div class="event-description">
                                    <?php echo nl2br(htmlspecialchars(substr($event['description'], 0, 150))); ?>
                                    <?php if (strlen($event['description']) > 150): ?>...<?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-details">
                                <div class="event-detail">
                                    <span>🕒</span>
                                    <span>
                                        <?php echo formatTime($event['start_time']); ?>
                                        <?php if ($event['end_time']): ?>
                                            - <?php echo formatTime($event['end_time']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="event-detail">
                                    <span>📍</span>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                
                                <?php if (!empty($event['organizer'])): ?>
                                    <div class="event-detail">
                                        <span>👤</span>
                                        <span><?php echo htmlspecialchars($event['organizer']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="event-detail">
                                    <span class="status-badge <?php echo getStatusBadge($event['status']); ?>">
                                        <?php echo htmlspecialchars($event['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenu = document.querySelector('.mobile-menu');
        const navMenu = document.querySelector('.nav-links');

        mobileMenu.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    </script>
</body>
</html>