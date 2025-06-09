<?php
require_once 'config.php'; // Use config for DB and session

// Handle form submission for adding new event
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_title = trim($_POST['event_title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = trim($_POST['location']);
    $max_participants = $_POST['max_participants'];
    $event_type = trim($_POST['event_type']);
    $status = $_POST['status'];
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['event_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/events/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $image_name = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            
            if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                $message = "Error uploading image file.";
                $message_type = "error";
            } else {
                $image_name = $upload_path;
            }
        } else {
            $message = "Please upload a valid image file (JPEG, PNG, GIF).";
            $message_type = "error";
        }
    }
    
    // Insert into database if no upload errors
    if (empty($message)) {
        try {
            $sql = "INSERT INTO events (event_title, description, event_date, event_time, location, max_participants, event_type, status, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$event_title, $description, $event_date, $event_time, $location, $max_participants, $event_type, $status, $image_name])) {
                $message = "Event added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding event.";
                $message_type = "error";
            }
        } catch(PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch existing events
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC, created_at DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $events = [];
    $message = "Error fetching events: " . $e->getMessage();
    $message_type = "error";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JANALISU - Events Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #e91e63;
            --primary-dark: #ad1457;
            --secondary-color: #667eea;
            --secondary-dark: #5a6fd8;
            --accent-color: #764ba2;
            --success-color: #4caf50;
            --error-color: #f44336;
            --warning-color: #ff9800;
            --info-color: #2196f3;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.15);
            --shadow-lg: 0 8px 25px rgba(0,0,0,0.15);
            --shadow-xl: 0 15px 35px rgba(0,0,0,0.2);
            --border-radius-sm: 6px;
            --border-radius-md: 10px;
            --border-radius-lg: 15px;
            --border-radius-xl: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-color) 100%);
            min-height: 100vh;
            line-height: 1.6;
            color: var(--dark-color);
        }

        /* Header Styles - Mobile First */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-align: center;
        }

        .logo-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--info-color) 100%) border-box;
            box-shadow: var(--shadow-sm);
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--info-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .nav-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }

        .nav-item {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            border-radius: var(--border-radius-xl);
            transition: var(--transition);
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .nav-item.active {
            background: var(--primary-color);
            color: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .nav-item:hover:not(.active) {
            background: var(--gray-100);
            color: var(--dark-color);
        }

        .back-btn {
            background: var(--secondary-color);
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius-xl);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: var(--secondary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }

        .page-title {
            color: var(--white);
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 300;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: clamp(0.9rem, 2vw, 1.1rem);
        }

        /* Message Styles */
        .message {
            padding: 1rem;
            border-radius: var(--border-radius-md);
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }

        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 0.25rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .tab-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            background: transparent;
            border: none;
            border-radius: var(--border-radius-md);
            font-size: clamp(0.9rem, 2vw, 1rem);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            color: var(--gray-600);
            text-align: center;
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .tab-btn:hover:not(.active) {
            background: var(--gray-100);
        }

        /* Form Styles */
        .form-container {
            background: var(--white);
            padding: clamp(1rem, 3vw, 2rem);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        input, select, textarea {
            padding: 0.75rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius-sm);
            background: var(--gray-100);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .file-input:hover {
            border-color: var(--primary-color);
            background: #fef7f7;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            padding: 0.875rem 2rem;
            border: none;
            border-radius: var(--border-radius-xl);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .event-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2rem;
        }

        .event-content {
            padding: 1.5rem;
        }

        .event-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-description {
            color: var(--gray-600);
            line-height: 1.5;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .event-footer {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .event-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius-xl);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-scheduled {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-ongoing {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-completed {
            background: #e8f5e8;
            color: #388e3c;
        }

        .status-cancelled {
            background: #ffebee;
            color: #d32f2f;
        }

        .participants-info {
            font-size: 0.8rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .empty-state {
            text-align: center;
            color: var(--white);
            padding: 3rem 1rem;
            grid-column: 1 / -1;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            opacity: 0.8;
            font-size: 1rem;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-color);
        }

        /* Responsive Design */
        @media (min-width: 480px) {
            .container {
                padding: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .events-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
            
            .event-meta {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .event-footer {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        @media (min-width: 768px) {
            .header-content {
                flex-direction: row;
                justify-content: space-between;
            }
            
            .logo-image {
                width: 60px;
                height: 60px;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
            
            .nav-menu {
                gap: 1rem;
            }
            
            .nav-item, .back-btn {
                font-size: 1rem;
                padding: 0.625rem 1.25rem;
            }
            
            .container {
                padding: 2rem;
            }
            
            .submit-btn {
                width: auto;
                min-width: 200px;
            }
        }

        @media (min-width: 1024px) {
            .header {
                padding: 1.5rem 2rem;
            }
            
            .logo-image {
                width: 70px;
                height: 70px;
            }
            
            .logo-text {
                font-size: 1.6rem;
            }
            
            .form-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .events-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .logo-image {
                width: 80px;
                height: 80px;
            }
            
            .logo-text {
                font-size: 1.8rem;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --white: #1a1a1a;
                --light-color: #2d2d2d;
                --dark-color: #ffffff;
                --gray-100: #2d2d2d;
                --gray-200: #3d3d3d;
                --gray-300: #4d4d4d;
            }
        }

        /* Print styles */
        @media print {
            .header, .tab-navigation, .submit-btn {
                display: none;
            }
            
            body {
                background: white;
                color: black;
            }
            
            .event-card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .nav-item, .tab-btn {
                border: 1px solid;
            }
            
            .event-card {
                border: 2px solid var(--dark-color);
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus styles for accessibility */
        .nav-item:focus,
        .tab-btn:focus,
        .submit-btn:focus,
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid var(--primary-color);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-container">
                <img src="janalisu.jpg" alt="Janalisu Logo" class="logo-image">
                <div class="logo-text">JANALISU EMPOWERMENT GROUP</div>
            </div>
            
            <nav class="nav-menu">
                <a href="admin_dashboard.php" class="nav-item">Dashboard</a>
                <a href="students.php" class="nav-item">Students</a>
                <a href="staffs.php" class="nav-item">Staff</a>
                <a href="add_events.php" class="nav-item active">Events</a>
            </nav>
            
            <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Events Management</h1>
            <p class="page-subtitle">Organize and manage your events effectively</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>" role="alert">
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('add-event')" aria-selected="true">Add New Event</button>
            <button class="tab-btn" onclick="showTab('view-events')" aria-selected="false">View All Events</button>
        </div>

        <!-- Add Event Tab -->
        <div id="add-event" class="tab-content active" role="tabpanel">
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" aria-label="Add new event form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="event_title">Event Title *</label>
                            <input type="text" id="event_title" name="event_title" required aria-required="true">
                        </div>
                        
                        <div class="form-group">
                            <label for="event_type">Event Type</label>
                            <select id="event_type" name="event_type">
                                <option value="">Select Type</option>
                                <option value="Workshop">Workshop</option>
                                <option value="Training">Training</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Conference">Conference</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Community Event">Community Event</option>
                                <option value="Fundraising">Fundraising</option>
                                <option value="Sports">Sports</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_date">Event Date *</label>
                            <input type="date" id="event_date" name="event_date" required aria-required="true" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="event_time">Event Time *</label>
                            <input type="time" id="event_time" name="event_time" required aria-required="true">
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" required aria-required="true" placeholder="Event venue">
                        </div>
                        
                        <div class="form-group">
                            <label for="max_participants">Max Participants</label>
                            <input type="number" id="max_participants" name="max_participants" min="1" placeholder="Maximum number of participants">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Scheduled">Scheduled</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_image">Event Image</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="event_image" name="event_image" accept="image/*" class="file-input">
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Event Description</label>
                            <textarea id="description" name="description" placeholder="Describe the event details, objectives, and any other relevant information..."></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_event" class="submit-btn">Add Event</button>
                </form>
            </div>
        </div>

        <!-- View Events Tab -->
        <div id="view-events" class="tab-content" role="tabpanel">
            <div class="events-grid">
                <?php if (empty($events)): ?>
                    <div class="empty-state">
                        <h3>No events found</h3>
                        <p>Start by adding your first event using the "Add New Event" tab.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <article class="event-card">
                            <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['event_title']); ?>" class="event-image">
                            <?php else: ?>
                                <div class="event-image">
                                    📅
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                
                                <div class="event-meta">
                                    <span>📅 <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                    <span>⏰ <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                    <span>📍 <?php echo htmlspecialchars($event['location']); ?></span>
                                    <?php if (!empty($event['event_type'])): ?>
                                        <span>🏷️ <?php echo htmlspecialchars($event['event_type']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($event['description'])): ?>
                                    <p class="event-description">
                                        <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>
                                        <?php if (strlen($event['description']) > 120) echo '...'; ?>
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
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Enhanced tab switching with accessibility
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.setAttribute('aria-hidden', 'true');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabName);
            selectedTab.classList.add('active');
            selectedTab.setAttribute('aria-hidden', 'false');
            
            // Add active class to clicked button
            event.target.classList.add('active');
            event.target.setAttribute('aria-selected', 'true');
            
            // Focus management for accessibility
            selectedTab.focus();
        }

        // Enhanced file input with preview
        document.getElementById('event_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const wrapper = this.closest('.file-input-wrapper');
            
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                // Update label text
                const label = document.querySelector('label[for="event_image"]');
                label.innerHTML = `Event Image - ${fileName} (${fileSize}MB)`;
                
                // Create preview if it's an image
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = wrapper.querySelector('.image-preview');
                        if (!preview) {
                            preview = document.createElement('img');
                            preview.className = 'image-preview';
                            preview.style.cssText = `
                                max-width: 200px;
                                max-height: 150px;
                                margin-top: 10px;
                                border-radius: 8px;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            `;
                            wrapper.appendChild(preview);
                        }
                        preview.src = e.target.result;
                        preview.alt = 'Event image preview';
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Enhanced form validation with better UX
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['event_title', 'event_date', 'event_time', 'location'];
            let isValid = true;
            let firstInvalidField = null;
            
            // Clear previous validation states
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('error');
            });
            
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const formGroup = field.closest('.form-group');
                
                if (!field.value.trim()) {
                    formGroup.classList.add('error');
                    field.style.borderColor = '#f44336';
                    isValid = false;
                    
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Date validation
            const eventDate = document.getElementById('event_date');
            if (eventDate.value && eventDate.value < new Date().toISOString().split('T')[0]) {
                eventDate.style.borderColor = '#f44336';
                isValid = false;
                if (!firstInvalidField) firstInvalidField = eventDate;
            }
            
            // File size validation
            const fileInput = document.getElementById('event_image');
            if (fileInput.files[0] && fileInput.files[0].size > 5 * 1024 * 1024) { // 5MB limit
                alert('Please select an image smaller than 5MB.');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = fileInput;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Show user-friendly error message
                showNotification('Please fill in all required fields correctly.', 'error');
                
                // Focus first invalid field
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                // Show loading state
                const submitBtn = this.querySelector('.submit-btn');
                submitBtn.textContent = 'Adding Event...';
                submitBtn.disabled = true;
                this.classList.add('loading');
            }
        });

        // Auto-hide success messages with better animation
        function hideMessage() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    message.style.transition = 'all 0.3s ease';
                    setTimeout(() => {
                        message.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        }

        // Enhanced notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `message ${type}`;
            notification.innerHTML = `<span>${message}</span>`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        // Keyboard navigation for tabs
        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('tab-btn')) {
                const tabs = Array.from(document.querySelectorAll('.tab-btn'));
                const currentIndex = tabs.indexOf(e.target);
                
                if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    const nextIndex = e.key === 'ArrowLeft' 
                        ? (currentIndex - 1 + tabs.length) % tabs.length
                        : (currentIndex + 1) % tabs.length;
                    
                    tabs[nextIndex].focus();
                } else if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.target.click();
                }
            }
        });

        // Enhanced page load animation
        document.addEventListener('DOMContentLoaded', function() {
            // Animate page entrance
            document.body.style.opacity = '0';
            document.body.style.transform = 'translateY(20px)';
            document.body.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
                document.body.style.transform = 'translateY(0)';
            }, 100);
            
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe event cards for scroll animation
            setTimeout(() => {
                document.querySelectorAll('.event-card').forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';
                    card.style.transition = `all 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
                    observer.observe(card);
                });
            }, 200);
            
            // Initialize message auto-hide
            hideMessage();
            
            // Initialize tooltips for truncated text
            initializeTooltips();
        });

        // Tooltip functionality for truncated descriptions
        function initializeTooltips() {
            document.querySelectorAll('.event-description').forEach(desc => {
                const fullText = desc.textContent;
                if (fullText.endsWith('...')) {
                    desc.style.cursor = 'pointer';
                    desc.title = 'Click to see full description';
                    
                    desc.addEventListener('click', function() {
                        // Toggle between truncated and full text
                        if (this.dataset.expanded === 'true') {
                            this.textContent = fullText.substring(0, 120) + '...';
                            this.dataset.expanded = 'false';
                        } else {
                            // Get full description from PHP data
                            const eventCard = this.closest('.event-card');
                            const eventTitle = eventCard.querySelector('.event-title').textContent;
                            // This would need to be enhanced to get full description from data
                            this.textContent = fullText; // Simplified for demo
                            this.dataset.expanded = 'true';
                        }
                    });
                }
            });
        }

        // Service Worker registration for offline capability (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('SW registered'))
                    .catch(error => console.log('SW registration failed'));
            });
        }

        // Error handling for images
        document.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG' && e.target.classList.contains('event-image')) {
                e.target.style.display = 'none';
                const placeholder = document.createElement('div');
                placeholder.className = 'event-image';
                placeholder.innerHTML = '📅';
                e.target.parentNode.insertBefore(placeholder, e.target);
            }
        }, true);

        // Auto-save form data to prevent data loss (using sessionStorage)
        const form = document.querySelector('form');
        const formFields = form.querySelectorAll('input:not([type="file"]), select, textarea');
        
        // Load saved data
        formFields.forEach(field => {
            const savedValue = sessionStorage.getItem(`form_${field.name}`);
            if (savedValue && !field.value) {
                field.value = savedValue;
            }
        });
        
        // Save data on input
        formFields.forEach(field => {
            field.addEventListener('input', function() {
                sessionStorage.setItem(`form_${this.name}`, this.value);
            });
        });
        
        // Clear saved data on successful submission
        form.addEventListener('submit', function() {
            if (this.checkValidity()) {
                formFields.forEach(field => {
                    sessionStorage.removeItem(`form_${field.name}`);
                });
            }
        });
    </script>
    
    <!-- Add CSS animations -->
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .form-group.error label {
            color: var(--error-color);
        }
        
        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: var(--error-color);
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
        }
        
        .image-preview {
            display: block;
            margin-top: 0.5rem;
        }
        
        /* Loading spinner */
        .loading .submit-btn::after {
            content: '';
            width: 16px;
            height: 16px;
            margin-left: 8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
    </style>
</body>
</html>