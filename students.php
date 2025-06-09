<?php
require_once 'config.php'; // Includes session and DB connection

// Handle form submission for adding new student
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_student') {
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
        $stmt = $pdo->prepare("SELECT status FROM students WHERE student_id = ?");
        $stmt->execute([$_GET['toggle_status']]);
        $current_status = $stmt->fetch(PDO::FETCH_ASSOC)['status'];
        
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
$search = $_GET['search'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$filter_program = $_GET['filter_program'] ?? '';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --secondary: #ec4899;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f4ff 0%, #e6f0ff 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Styles */
        .header {
            background: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%) border-box;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
        }

        .nav-menu {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav-item {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            border-radius: 50px;
            transition: var(--transition);
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
        }

        .nav-item:hover:not(.active) {
            background: #f1f5f9;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }

        /* Main Content */
        .main-content {
            padding: 1.5rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Message Styles */
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid var(--success);
        }

        .message.error {
            background: #fee2e2;
            color: #b91c1c;
            border-left: 4px solid var(--danger);
        }

        /* Action Bar */
        .action-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
        }

        .search-filters {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 0.75rem;
        }

        .search-input, .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            outline: none;
            transition: var(--transition);
        }

        .search-input:focus, .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .filter-select {
            background: white;
            cursor: pointer;
        }

        .add-student-btn {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .add-student-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        /* Students Table */
        .students-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table th,
        .students-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .students-table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--dark);
            position: sticky;
            top: 0;
        }

        .students-table tr:last-child td {
            border-bottom: none;
        }

        .students-table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.85rem;
            margin: 0.15rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
        }

        .btn-edit {
            background: #dbeafe;
            color: var(--primary);
        }

        .btn-edit:hover {
            background: var(--primary);
            color: white;
        }

        .btn-delete {
            background: #fee2e2;
            color: var(--danger);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        .btn-toggle {
            background: #ffedd5;
            color: var(--warning);
        }

        .btn-toggle:hover {
            background: var(--warning);
            color: white;
        }

        /* Mobile table view */
        .mobile-student-card {
            display: none;
            background: white;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .student-card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .student-card-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .student-info {
            display: flex;
            flex-direction: column;
        }

        .student-label {
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .student-value {
            font-weight: 500;
            word-break: break-word;
        }

        .student-card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
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
            overflow: auto;
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 1rem;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFade 0.3s ease-out;
        }

        @keyframes modalFade {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 1rem 1rem 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            opacity: 0.8;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .no-results-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            color: var(--primary);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .action-bar {
                grid-template-columns: 1fr;
            }
            
            .search-filters {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .students-table {
                display: none;
            }
            
            .mobile-student-card {
                display: block;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .nav-toggle {
                display: block;
            }
            
            .nav-menu {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 0.5rem;
                margin-top: 1rem;
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .user-section {
                margin-left: auto;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .student-card-body {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }
            
            .page-header, .action-bar, .students-container {
                padding: 1.25rem;
            }
            
            .modal-content {
                width: 95%;
                margin: 1rem auto;
            }
            
            .student-card-actions {
                flex-wrap: wrap;
            }
            
            .action-btn {
                width: 100%;
                margin: 0.25rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <div class="logo-text">JANALISU EMPOWERMENT</div>
        </div>
        
        <button class="nav-toggle" id="navToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav class="nav-menu" id="navMenu">
            <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="students.php" class="nav-item active"><i class="fas fa-user-graduate"></i> Students</a>
            <a href="staffs.php" class="nav-item"><i class="fas fa-users"></i> Staff</a>
            <a href="add_events.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Events</a>
        </nav>
        
        <div class="user-section">
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
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
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="search-filters">
                <form method="GET" style="display: contents;">
                    <input type="text" name="search" class="search-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="filter_status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo $filter_status == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $filter_status == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    
                    <select name="filter_program" class="filter-select">
                        <option value="">All Programs</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo htmlspecialchars($program['program']); ?>" 
                                    <?php echo $filter_program == $program['program'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($program['program']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="filter-select" style="background: var(--primary); color: white;">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </form>
            </div>
            
            <button class="add-student-btn" onclick="openModal()">
                <i class="fas fa-user-plus"></i> Add New Student
            </button>
        </div>

        <!-- Students Table -->
        <div class="students-container">
            <div class="table-header">
                <h3 class="table-title">Students List</h3>
                <span><?php echo count($students); ?> students</span>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="no-results">
                    <div class="no-results-icon"><i class="fas fa-user-graduate"></i></div>
                    <h3>No students found</h3>
                    <p>Try adjusting your search criteria or add a new student.</p>
                </div>
            <?php else: ?>
                <!-- Desktop Table -->
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
                                    <button onclick="toggleStatus(<?php echo $student['student_id']; ?>)" 
                                       class="action-btn btn-toggle"
                                       title="Change Status">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button onclick="deleteStudent(<?php echo $student['student_id']; ?>)" 
                                       class="action-btn btn-delete"
                                       title="Delete Student">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Mobile Cards -->
                <?php foreach ($students as $student): ?>
                    <div class="mobile-student-card">
                        <div class="student-card-header">
                            <div>
                                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                <div class="status-badge status-<?php echo strtolower($student['status']); ?>">
                                    <?php echo $student['status']; ?>
                                </div>
                            </div>
                            <div>ID: <?php echo $student['student_id']; ?></div>
                        </div>
                        <div class="student-card-body">
                            <div class="student-info">
                                <span class="student-label">Email</span>
                                <span class="student-value"><?php echo htmlspecialchars($student['email']); ?></span>
                            </div>
                            <div class="student-info">
                                <span class="student-label">Phone</span>
                                <span class="student-value"><?php echo htmlspecialchars($student['phone']); ?></span>
                            </div>
                            <div class="student-info">
                                <span class="student-label">Program</span>
                                <span class="student-value"><?php echo htmlspecialchars($student['program']); ?></span>
                            </div>
                            <div class="student-info">
                                <span class="student-label">Enrollment Date</span>
                                <span class="student-value"><?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></span>
                            </div>
                        </div>
                        <div class="student-card-actions">
                            <button onclick="toggleStatus(<?php echo $student['student_id']; ?>)" 
                               class="action-btn btn-toggle"
                               title="Change Status">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button onclick="deleteStudent(<?php echo $student['student_id']; ?>)" 
                               class="action-btn btn-delete"
                               title="Delete Student">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
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
        // Mobile navigation toggle
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

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
                window.location.href = 'home.php';
            }
        }
        
        // Delete student confirmation
        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                window.location.href = `?delete=${id}`;
            }
        }
        
        // Toggle status confirmation
        function toggleStatus(id) {
            if (confirm('Are you sure you want to change this student\'s status?')) {
                window.location.href = `?toggle_status=${id}`;
            }
        }

        // Auto-submit search form on enter
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
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