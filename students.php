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

// Handle form submission for adding new student
$message = '';
$messageType = '';

if ($_POST && isset($_POST['action']) && $_POST['action'] == 'add_student') {
    try {
        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone, date_of_birth, gender, program, address, enrollment_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['date_of_birth'],
            $_POST['gender'],
            $_POST['program'],
            $_POST['address'],
            $_POST['enrollment_date'],
            $_POST['status']
        ]);
        
        $message = "Student added successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error adding student: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle student deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Student deleted successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error deleting student: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle status update
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    try {
        // Get current status
        $stmt = $pdo->prepare("SELECT status FROM students WHERE student_id = ?");
        $stmt->execute([$_GET['toggle_status']]);
        $current_status = $stmt->fetch(PDO::FETCH_ASSOC)['status'];
        
        // Toggle status
        $new_status = ($current_status == 'Active') ? 'Inactive' : 'Active';
        
        $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE student_id = ?");
        $stmt->execute([$new_status, $_GET['toggle_status']]);
        
        $message = "Student status updated successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error updating status: " . $e->getMessage();
        $messageType = "error";
    }
}

// Fetch all students with search and filter functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_program = isset($_GET['filter_program']) ? $_GET['filter_program'] : '';

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR program LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($filter_status) {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

if ($filter_program) {
    $sql .= " AND program LIKE ?";
    $params[] = "%$filter_program%";
}

$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique programs for filter dropdown
    $program_stmt = $pdo->query("SELECT DISTINCT program FROM students ORDER BY program");
    $programs = $program_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $students = [];
    $programs = [];
    $message = "Error fetching students: " . $e->getMessage();
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - JANALISU Admin</title>
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

        /* Header Styles (same as dashboard) */
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
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            color: #333;
            font-size: 32px;
            font-weight: 300;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            font-size: 16px;
        }

        /* Action Bar */
        .action-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .search-filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input, .filter-select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .search-input:focus, .filter-select:focus {
            border-color: #667eea;
        }

        .search-input {
            width: 300px;
        }

        .filter-select {
            min-width: 120px;
        }

        .add-student-btn {
            background: #4caf50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .add-student-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
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

        /* Students Table */
        .students-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .students-table th,
        .students-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .students-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .students-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #2196f3;
            color: white;
        }

        .btn-edit:hover {
            background: #1976d2;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #d32f2f;
        }

        .btn-toggle {
            background: #ff9800;
            color: white;
        }

        .btn-toggle:hover {
            background: #f57c00;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 300;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .no-results-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
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

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-filters {
                justify-content: center;
            }

            .search-input {
                width: 100%;
            }

            .students-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <div class="logo">JEG</div>
            <div class="org-name">
                JANALISU <span class="empowerment">EMPOWERMENT</span><br>
                <span style="font-size: 18px;">GROUP</span>
            </div>
        </div>
        
        <nav class="nav-menu">
            <a href="admin_dashboard.php" class="nav-item">Dashboard</a>
            <a href="students.php" class="nav-item active">Students</a>
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
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Student Management</h1>
            <p class="page-subtitle">Manage student records, enrollment, and information</p>
        </div>

        <!-- Message Display -->
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="search-filters">
                <form method="GET" style="display: contents;">
                    <input type="text" name="search" class="search-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="filter_status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo $filter_status == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $filter_status == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <select name="filter_program" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Programs</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo htmlspecialchars($program['program']); ?>" 
                                    <?php echo $filter_program == $program['program'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($program['program']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" style="display: none;"></button>
                </form>
            </div>
            
            <button class="add-student-btn" onclick="openModal()">+ Add New Student</button>
        </div>

        <!-- Students Table -->
        <div class="students-container">
            <h3>Students List (<?php echo count($students); ?> total)</h3>
            
            <?php if (empty($students)): ?>
                <div class="no-results">
                    <div class="no-results-icon">🎓</div>
                    <h3>No students found</h3>
                    <p>Try adjusting your search criteria or add a new student.</p>
                </div>
            <?php else: ?>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Program</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><?php echo htmlspecialchars($student['program']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($student['status']); ?>">
                                        <?php echo $student['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?toggle_status=<?php echo $student['student_id']; ?>" 
                                       class="action-btn btn-toggle"
                                       onclick="return confirm('Change student status?')">
                                        Toggle
                                    </a>
                                    <a href="?delete=<?php echo $student['student_id']; ?>" 
                                       class="action-btn btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this student?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Student Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Student</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_student">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="program">Program *</label>
                        <input type="text" id="program" name="program" required placeholder="e.g., Computer Science, Business Administration">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" placeholder="Student's full address"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="enrollment_date">Enrollment Date *</label>
                            <input type="date" id="enrollment_date" name="enrollment_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Add Student</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('studentModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('studentModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('studentModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'login.php';
            }
        }

        // Auto-submit search form on enter
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });

        // Smooth page load animation
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Auto-hide messages after 5 seconds
        const messages = document.querySelectorAll('.message');
        messages.forEach(function(message) {
            setTimeout(function() {
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            }, 5000);
        });
    </script>
</body>
</html>