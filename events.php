<?php
// Database connection (modify with your actual database credentials)
$host = 'localhost';
$dbname = 'janalisu';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // For demo purposes, we'll use sample data if connection fails
    $pdo = null;
}

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    header('Content-Type: application/json');
    
    $eventId = $_POST['event_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validate required fields
    if (empty($eventId) || empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Save registration to database and update participant count
    if ($pdo) {
        try {
            // Check if event exists and has space
            $stmt = $pdo->prepare("SELECT max_participants, current_participants FROM events WHERE id = ? AND status = 'active'");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event) {
                echo json_encode(['success' => false, 'message' => 'Event not found or not active']);
                exit;
            }
            
            if ($event['current_participants'] >= $event['max_participants']) {
                echo json_encode(['success' => false, 'message' => 'Event is fully booked']);
                exit;
            }
            
            // Check if user already registered
            $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ?");
            $stmt->execute([$eventId, $email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'You are already registered for this event']);
                exit;
            }
            
            // Register user
            $stmt = $pdo->prepare("INSERT INTO event_registrations (event_id, name, email, phone, registered_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$eventId, $name, $email, $phone]);
            
            // Update participant count
            $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants + 1 WHERE id = ?");
            $stmt->execute([$eventId]);
            
            echo json_encode(['success' => true, 'message' => 'Registration successful! We will contact you with more details.']);
            exit;
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get events for display
function getEvents() {
    global $pdo;
    
    // If database is available, use it
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM events WHERE status = 'active' AND event_date >= CURDATE() ORDER BY event_date ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            // Fall back to sample data
        }
    }
    
    // Sample data for demo if database connection fails
    return [
        [
            'id' => 1,
            'title' => 'Community Health Workshop',
            'description' => 'A comprehensive workshop focusing on preventive healthcare and wellness education for community members.',
            'event_date' => '2025-06-15',
            'event_time' => '10:00',
            'location' => 'Community Center, Nairobi',
            'max_participants' => 50,
            'current_participants' => 23,
            'status' => 'active',
            'image_url' => 'https://via.placeholder.com/600x300/ec4899/ffffff?text=Health+Workshop',
            'created_at' => '2025-05-20'
        ],
        [
            'id' => 2,
            'title' => 'Women Empowerment Summit',
            'description' => 'Annual summit bringing together women leaders, entrepreneurs, and activists.',
            'event_date' => '2025-07-10',
            'event_time' => '09:00',
            'location' => 'Kenyatta International Conference Centre',
            'max_participants' => 200,
            'current_participants' => 87,
            'status' => 'active',
            'image_url' => 'https://via.placeholder.com/600x300/8b5cf6/ffffff?text=Women+Summit',
            'created_at' => '2025-05-18'
        ]
    ];
}

$events = getEvents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - JANALISU EMPOWERMENT GROUP</title>
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
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
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

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #ec4899;
        }

        .nav-menu a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .bar {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.9) 0%, rgba(139, 92, 246, 0.9) 100%), url('https://via.placeholder.com/1920x600/333333/ffffff?text=Events+Banner') center/cover;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Main Content */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 15px;
            padding: 0.5rem 1rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            border: none;
            outline: none;
            flex: 1;
            padding: 0.5rem;
            font-size: 1rem;
        }

        .search-icon {
            color: #64748b;
            margin-right: 0.5rem;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: #ec4899;
            background: #ec4899;
            color: white;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            opacity: 1;
            transform: translateY(0);
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(236, 72, 153, 0.15);
        }

        .event-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
        }

        .event-content {
            padding: 2rem;
        }

        .event-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .event-description {
            color: #64748b;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
        }

        .meta-icon {
            font-size: 1.1rem;
        }

        .event-stats {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .participants-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .participants-count {
            font-weight: 600;
            color: #1e293b;
        }

        .spots-left {
            font-size: 0.9rem;
            color: #dc2626;
            font-weight: 500;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
        }

        .event-date {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.5rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .date-day {
            font-size: 1.5rem;
            color: #ec4899;
            line-height: 1;
        }

        .date-month {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
        }

        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .register-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.4);
        }

        .register-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow: hidden;
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        .modal-header {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            padding: 1.5rem 2rem;
            position: relative;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .modal-footer {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* Messages */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        /* Loading Animation */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .mobile-menu {
                display: flex;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .main-container {
                padding: 2rem 1rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
                margin-bottom: 1rem;
            }

            .event-meta {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .modal-body {
                padding: 1.5rem;
            }
        }

        /* Hide filtered events smoothly */
        .event-card.hidden {
            display: none !important;
        }

        .event-card.visible {
            display: block !important;
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
                <li><a href="events.php" class="active">Events</a></li>
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
    <section class="hero">
        <div class="hero-content">
            <h1>Upcoming Events</h1>
            <p>Join us in making a difference in our community. Discover upcoming workshops, seminars, and empowerment programs designed to uplift and inspire.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Messages -->
        <div id="messageContainer"></div>

        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">Featured Events</h2>
            <p class="section-subtitle">Be part of transformative experiences that empower individuals and strengthen our community</p>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Search events..." onkeyup="filterEvents()">
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterByCategory('all')">All Events</button>
                <button class="filter-btn" onclick="filterByCategory('workshop')">Workshops</button>
                <button class="filter-btn" onclick="filterByCategory('summit')">Summits</button>
                <button class="filter-btn" onclick="filterByCategory('program')">Programs</button>
            </div>
        </div>

        <!-- Events Grid -->
        <div class="events-grid" id="eventsGrid">
            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <h3>No Events Available</h3>
                    <p>Check back soon for upcoming events and workshops!</p>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <?php
                    $eventDate = new DateTime($event['event_date']);
                    $spotsLeft = $event['max_participants'] - $event['current_participants'];
                    $progressPercentage = ($event['current_participants'] / $event['max_participants']) * 100;
                    ?>
                    <div class="event-card visible" data-event-id="<?php echo $event['id']; ?>" data-category="<?php echo strtolower(explode(' ', $event['title'])[count(explode(' ', $event['title']))-1]); ?>">
                        <!-- Event Date Badge -->
                        <div class="event-date">
                            <div class="date-day"><?php echo $eventDate->format('d'); ?></div>
                            <div class="date-month"><?php echo $eventDate->format('M'); ?></div>
                        </div>

                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image" onerror="this.src='https://via.placeholder.com/600x300/ec4899/ffffff?text=Event+Image'" loading="lazy">
                        
                        <div class="event-content">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                            
                            <div class="event-meta">
                                <div class="meta-item">
                                    <span class="meta-icon">📅</span>
                                    <span><?php echo $eventDate->format('M d, Y'); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">🕐</span>
                                    <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">📍</span>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-icon">👥</span>
                                    <span><?php echo $event['max_participants']; ?> capacity</span>
                                </div>
                            </div>
                            
                            <div class="event-stats">
                                <div class="participants-info">
                                    <span class="participants-count"><?php echo $event['current_participants']; ?> / <?php echo $event['max_participants']; ?> registered</span>
                                    <?php if ($spotsLeft > 0): ?>
                                        <span class="spots-left"><?php echo $spotsLeft; ?> spots left</span>
                                    <?php else: ?>
                                        <span class="spots-left">Fully booked</span>
                                    <?php endif; ?>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progressPercentage; ?>%"></div>
                                </div>
                            </div>
                            
                            <?php if ($spotsLeft > 0): ?>
                                <button class="register-btn" onclick="openRegistrationModal(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['title']); ?>')">Register Now
                                </button>
                            <?php else: ?>
                                <button class="register-btn" disabled>
                                    Event Full
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Registration Modal -->
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Event Registration</h3>
                <span class="close" onclick="closeRegistrationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalMessages"></div>
                <form id="registrationForm">
                    <input type="hidden" id="eventId" name="event_id">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                       <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input" placeholder="+254 700 000 000">
                    </div>
                    
                    <div class="form-group">
                        <label for="comments" class="form-label">Additional Comments (optional)</label>
                        <textarea id="comments" name="comments" class="form-input" rows="3" placeholder="Any special requirements or questions?"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRegistrationModal()">Cancel</button>
                <button type="submit" form="registrationForm" class="btn btn-primary" id="submitBtn">
                    Register
                    <span class="loading" id="loadingSpinner"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentEvents = [];
        let allEvents = [];

        // Initialize events data
        document.addEventListener('DOMContentLoaded', function() {
            // Get events data from PHP
            allEvents = <?php echo json_encode($events); ?>;
            currentEvents = [...allEvents];
        });

        // Registration Modal Functions
        function openRegistrationModal(eventId, eventTitle) {
            const modal = document.getElementById('registrationModal');
            const modalTitle = document.getElementById('modalTitle');
            const eventIdInput = document.getElementById('eventId');
            
            modalTitle.textContent = `Register for: ${eventTitle}`;
            eventIdInput.value = eventId;
            
            // Clear form
            document.getElementById('registrationForm').reset();
            document.getElementById('modalMessages').innerHTML = '';
            
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function closeRegistrationModal() {
            const modal = document.getElementById('registrationModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('registrationModal');
            if (event.target === modal) {
                closeRegistrationModal();
            }
        }

        // Form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const modalMessages = document.getElementById('modalMessages');
            
            // Show loading state
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            modalMessages.innerHTML = '';
            
            // Get form data
            const formData = new FormData(this);
            
            // Submit registration
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                submitBtn.disabled = false;
                loadingSpinner.style.display = 'none';
                
                if (data.success) {
                    modalMessages.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    
                    // Reset form
                    document.getElementById('registrationForm').reset();
                    
                    // Update participant count in UI
                    updateEventParticipantCount(formData.get('event_id'));
                    
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        closeRegistrationModal();
                        showMessage(data.message, 'success');
                    }, 2000);
                } else {
                    modalMessages.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                loadingSpinner.style.display = 'none';
                modalMessages.innerHTML = '<div class="alert alert-error">Registration failed. Please try again.</div>';
            });
        });

        // Update participant count in the UI
        function updateEventParticipantCount(eventId) {
            const eventCard = document.querySelector(`[data-event-id="${eventId}"]`);
            if (eventCard) {
                const participantsInfo = eventCard.querySelector('.participants-count');
                const spotsLeft = eventCard.querySelector('.spots-left');
                const progressFill = eventCard.querySelector('.progress-fill');
                const registerBtn = eventCard.querySelector('.register-btn');
                
                // Find the event in our data
                const event = allEvents.find(e => e.id == eventId);
                if (event) {
                    event.current_participants += 1;
                    const newSpotsLeft = event.max_participants - event.current_participants;
                    const newProgressPercentage = (event.current_participants / event.max_participants) * 100;
                    
                    // Update UI elements
                    participantsInfo.textContent = `${event.current_participants} / ${event.max_participants} registered`;
                    progressFill.style.width = `${newProgressPercentage}%`;
                    
                    if (newSpotsLeft > 0) {
                        spotsLeft.textContent = `${newSpotsLeft} spots left`;
                    } else {
                        spotsLeft.textContent = 'Fully booked';
                        registerBtn.textContent = 'Event Full';
                        registerBtn.disabled = true;
                    }
                }
            }
        }

        // Show message in main container
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            messageContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
        }

        // Filter Events Functions
        function filterEvents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const eventCards = document.querySelectorAll('.event-card');
            let visibleCount = 0;
            
            eventCards.forEach(card => {
                const title = card.querySelector('.event-title').textContent.toLowerCase();
                const description = card.querySelector('.event-description').textContent.toLowerCase();
                const location = card.querySelector('.meta-item:nth-child(3) span:last-child').textContent.toLowerCase();
                
                const isVisible = title.includes(searchTerm) || 
                                description.includes(searchTerm) || 
                                location.includes(searchTerm);
                
                if (isVisible) {
                    card.classList.remove('hidden');
                    card.classList.add('visible');
                    visibleCount++;
                } else {
                    card.classList.remove('visible');
                    card.classList.add('hidden');
                }
            });
            
            // Show empty state if no results
            showEmptyState(visibleCount === 0 && searchTerm !== '');
        }

        function filterByCategory(category) {
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            const eventCards = document.querySelectorAll('.event-card');
            let visibleCount = 0;
            
            eventCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                const isVisible = category === 'all' || cardCategory.includes(category);
                
                if (isVisible) {
                    card.classList.remove('hidden');
                    card.classList.add('visible');
                    visibleCount++;
                } else {
                    card.classList.remove('visible');
                    card.classList.add('hidden');
                }
            });
            
            // Show empty state if no results
            showEmptyState(visibleCount === 0 && category !== 'all');
        }

        function showEmptyState(show) {
            const eventsGrid = document.getElementById('eventsGrid');
            let emptyState = document.querySelector('.empty-state');
            
            if (show && !emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'empty-state';
                emptyState.innerHTML = `
                    <div class="empty-state-icon">🔍</div>
                    <h3>No Events Found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                `;
                eventsGrid.appendChild(emptyState);
            } else if (!show && emptyState) {
                emptyState.remove();
            }
        }

        // Mobile Menu Toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            const navMenu = document.querySelector('.nav-menu');
            navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
        });

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 4px 30px rgba(236, 72, 153, 0.15)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = '0 4px 30px rgba(236, 72, 153, 0.1)';
            }
        });

        // Form validation
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!name) {
                showFormError('Please enter your full name');
                return false;
            }
            
            if (!email) {
                showFormError('Please enter your email address');
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFormError('Please enter a valid email address');
                return false;
            }
            
            return true;
        }

        function showFormError(message) {
            const modalMessages = document.getElementById('modalMessages');
            modalMessages.innerHTML = `<div class="alert alert-error">${message}</div>`;
        }

        // Add form validation to submit event
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return;
            }
        });

        // Real-time email validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                this.style.borderColor = '#dc2626';
                showFormError('Please enter a valid email address');
            } else {
                this.style.borderColor = '#e5e7eb';
                document.getElementById('modalMessages').innerHTML = '';
            }
        });

        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('254')) {
                value = '+' + value;
            } else if (value.startsWith('0')) {
                value = '+254' + value.substring(1);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>