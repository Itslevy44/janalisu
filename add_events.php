<?php
session_start();

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
            
            // Create upload directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $image_name = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                $image_name = $upload_path;
            } else {
                $message = "Error uploading image file.";
                $message_type = "error";
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

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        .back-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        /* Message Styles */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            background: white;
            border-radius: 15px;
            padding: 5px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            background: transparent;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #666;
        }

        .tab-btn.active {
            background: #e91e63;
            color: white;
            box-shadow: 0 3px 10px rgba(233, 30, 99, 0.3);
        }

        /* Form Styles */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
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
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input, select, textarea {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #e91e63;
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
            padding: 12px 15px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #e91e63;
            background: #fef7f7;
        }

        .submit-btn {
            background: linear-gradient(135deg, #e91e63, #ad1457);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.4);
        }

        /* Events List */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .event-content {
            padding: 20px;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .event-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
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

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .nav-menu {
                gap: 15px;
            }

            .container {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
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
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Events Management</h1>
            <p class="page-subtitle">Organize and manage your events effectively</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('add-event')">Add New Event</button>
            <button class="tab-btn" onclick="showTab('view-events')">View All Events</button>
        </div>

        <!-- Add Event Tab -->
        <div id="add-event" class="tab-content active">
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="event_title">Event Title *</label>
                            <input type="text" id="event_title" name="event_title" required>
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
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_date">Event Date *</label>
                            <input type="date" id="event_date" name="event_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="event_time">Event Time *</label>
                            <input type="time" id="event_time" name="event_time" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" required placeholder="Event venue">
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
        <div id="view-events" class="tab-content">
            <div class="events-grid">
                <?php if (empty($events)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; color: white; padding: 40px;">
                        <h3>No events found</h3>
                        <p>Start by adding your first event using the "Add New Event" tab.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-image">
                            <?php else: ?>
                                <div class="event-image" style="display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                    📅
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                
                                <div class="event-meta">
                                    <span>📅 <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                    <span>⏰ <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                    <span>📍 <?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                
                                <?php if (!empty($event['description'])): ?>
                                    <p class="event-description">
                                        <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>
                                        <?php if (strlen($event['description']) > 120) echo '...'; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                    <span class="event-status status-<?php echo strtolower($event['status']); ?>">
                                        <?php echo htmlspecialchars($event['status']); ?>
                                    </span>
                                    
                                    <?php if (!empty($event['max_participants'])): ?>
                                        <span style="font-size: 12px; color: #666;">
                                            Max: <?php echo $event['max_participants']; ?> participants
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // File input enhancement
        document.getElementById('event_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                const label = document.querySelector('label[for="event_image"]');
                label.textContent = `Event Image - ${fileName}`;
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['event_title', 'event_date', 'event_time', 'location'];
            let isValid = true;
            
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (!field.value.trim()) {
                    field.style.borderColor = '#f44336';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e0e0e0';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Auto-hide success messages
        setTimeout(() => {
            const successMessage = document.querySelector('.message.success');
            if (successMessage) {
                successMessage.style.opacity = '0';
                successMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 500);
            }
        }, 5000);

        // Page load animation
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>