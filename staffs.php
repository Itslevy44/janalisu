<?php
// Include configuration file first
require_once 'config.php';

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Create database connection using config constants
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize message variables
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add':
                // Add new staff member
                $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, email, phone, position, department, salary, hire_date, status, address, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt->execute([
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['position'],
                    $_POST['department'],
                    $_POST['salary'],
                    $_POST['hire_date'],
                    $_POST['status'],
                    $_POST['address'],
                    $hashed_password
                ]);
                $message = "Staff member added successfully!";
                $message_type = "success";
                break;
                
            case 'edit':
                // Update existing staff member
                $update_fields = [
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['position'],
                    $_POST['department'],
                    $_POST['salary'],
                    $_POST['hire_date'],
                    $_POST['status'],
                    $_POST['address'],
                    $_POST['employee_id']
                ];
                
                $sql = "UPDATE employees SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    position = ?, 
                    department = ?, 
                    salary = ?, 
                    hire_date = ?, 
                    status = ?, 
                    address = ? 
                WHERE employee_id = ?";
                
                if (!empty($_POST['password'])) {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = "UPDATE employees SET 
                        first_name = ?, 
                        last_name = ?, 
                        email = ?, 
                        phone = ?, 
                        position = ?, 
                        department = ?, 
                        salary = ?, 
                        hire_date = ?, 
                        status = ?, 
                        address = ?,
                        password = ? 
                    WHERE employee_id = ?";
                    $update_fields = [
                        $_POST['first_name'],
                        $_POST['last_name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['position'],
                        $_POST['department'],
                        $_POST['salary'],
                        $_POST['hire_date'],
                        $_POST['status'],
                        $_POST['address'],
                        $hashed_password,
                        $_POST['employee_id']
                    ];
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($update_fields);
                $message = "Staff member updated successfully!";
                $message_type = "success";
                break;
                
            case 'delete':
                // Delete staff member
                $stmt = $pdo->prepare("DELETE FROM employees WHERE employee_id = ?");
                $stmt->execute([$_POST['employee_id']]);
                $message = "Staff member deleted successfully!";
                $message_type = "success";
                break;
        }
    } catch(PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    } catch(Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch all staff members
try {
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC");
    $staff_members = $stmt->fetchAll();
} catch(PDOException $e) {
    $staff_members = [];
    $message = "Error fetching staff: " . $e->getMessage();
    $message_type = "error";
}

// Get staff for editing
$edit_staff = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_staff = $stmt->fetch();
    } catch(PDOException $e) {
        $message = "Error fetching staff details: " . $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - JANALISU Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6bff;
            --primary-dark: #3a56cc;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --white: #ffffff;
            --gray-light: #e9ecef;
            --gray: #6c757d;
            --gray-dark: #495057;
            --border-radius: 0.375rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        .header {
            background: var(--white);
            padding: 1rem 2rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-right: 1rem;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid transparent;
            background: linear-gradient(var(--white), var(--white)) padding-box,
                        linear-gradient(135deg, var(--danger-color) 0%, var(--primary-color) 50%, var(--info-color) 100%) border-box;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--danger-color) 0%, var(--primary-color) 50%, var(--info-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin: 1rem 0;
        }

        .nav-item {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            border-radius: 2rem;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .nav-item.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .nav-item:hover {
            background: var(--gray-light);
            color: var(--dark-color);
        }

        .nav-item.active:hover {
            background: var(--primary-dark);
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-text {
            color: var(--gray);
            font-size: 0.8rem;
            text-align: right;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 2rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: var(--danger-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        /* Main Content */
        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Card Styles */
        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 500;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Message Styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 107, 255, 0.25);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        /* Grid System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .col {
            flex: 1 0 0%;
            padding: 0 0.75rem;
            margin-bottom: 1.5rem;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid var(--gray-light);
            text-align: left;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid var(--gray-light);
            background: var(--light-color);
            color: var(--gray-dark);
            font-weight: 600;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }

        .badge-success {
            color: #fff;
            background-color: var(--success-color);
        }

        .badge-danger {
            color: #fff;
            background-color: var(--danger-color);
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--gray) !important;
        }

        .hidden {
            display: none !important;
        }

        /* Toggle Button */
        .toggle-btn {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 2rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toggle-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-dark);
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .mobile-menu-btn {
                display: block;
            }

            .nav-menu {
                display: none;
                width: 100%;
                flex-direction: column;
                margin: 1rem 0 0;
            }

            .nav-menu.show {
                display: flex;
            }

            .nav-item {
                width: 100%;
                text-align: center;
            }

            .user-section {
                width: 100%;
                justify-content: center;
                margin-top: 1rem;
            }

            .col {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .table th, 
            .table td {
                padding: 0.5rem;
                font-size: 0.85rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .card-header {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .form-control {
                padding: 0.5rem;
            }

            .toggle-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <img src="janalisu.jpg" alt="Janalisu Logo" class="logo-image">
            <h1 class="logo-text">JANALISU</h1>
        </div>
        
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav class="nav-menu" id="navMenu">
            <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="students.php" class="nav-item"><i class="fas fa-users"></i> Students</a>
            <a href="staffs.php" class="nav-item active"><i class="fas fa-user-tie"></i> Staff</a>
            <a href="add_events.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Events</a>
            
            <div class="user-section">
                <div class="welcome-text">
                    <div>Welcome,</div>
                    <strong>Admin</strong>
                </div>
                <button class="btn btn-danger" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <div class="page-header fade-in">
            <h1 class="page-title">Staff Management</h1>
            <p class="page-subtitle">Manage your organization's staff members</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?> fade-in">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Toggle Button -->
        <button class="toggle-btn" id="toggleFormBtn">
            <i class="fas fa-plus"></i>
            <?php echo $edit_staff ? 'Edit Staff Member' : 'Add New Staff Member'; ?>
        </button>

        <!-- Add/Edit Staff Form -->
        <div id="staffForm" class="card <?php echo !$edit_staff ? 'hidden' : ''; ?> fade-in">
            <div class="card-header">
                <?php echo $edit_staff ? 'Edit Staff Member' : 'Add New Staff Member'; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_staff ? 'edit' : 'add'; ?>">
                    <?php if ($edit_staff): ?>
                        <input type="hidden" name="employee_id" value="<?php echo $edit_staff['employee_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required
                                       value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['first_name']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required
                                       value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['last_name']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Phone *</label>
                                <input type="tel" name="phone" class="form-control" required
                                       value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['phone']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Position *</label>
                                <input type="text" name="position" class="form-control" required
                                       value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['position']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Department *</label>
                                <select name="department" class="form-control form-select" required>
                                    <option value="">Select Department</option>
                                    <option value="Administration" <?php echo ($edit_staff && $edit_staff['department'] == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                                    <option value="Training" <?php echo ($edit_staff && $edit_staff['department'] == 'Training') ? 'selected' : ''; ?>>Training</option>
                                    <option value="Community Outreach" <?php echo ($edit_staff && $edit_staff['department'] == 'Community Outreach') ? 'selected' : ''; ?>>Community Outreach</option>
                                    <option value="Finance" <?php echo ($edit_staff && $edit_staff['department'] == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                                    <option value="Human Resources" <?php echo ($edit_staff && $edit_staff['department'] == 'Human Resources') ? 'selected' : ''; ?>>Human Resources</option>
                                    <option value="Project Management" <?php echo ($edit_staff && $edit_staff['department'] == 'Project Management') ? 'selected' : ''; ?>>Project Management</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Salary</label>
                                <input type="number" name="salary" class="form-control" step="0.01" min="0"
                                       value="<?php echo $edit_staff ? $edit_staff['salary'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Hire Date</label>
                                <input type="date" name="hire_date" class="form-control"
                                       value="<?php echo $edit_staff ? $edit_staff['hire_date'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control form-select">
                                    <option value="Active" <?php echo ($edit_staff && $edit_staff['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($edit_staff && $edit_staff['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">Password <?php echo $edit_staff ? '(Leave blank to keep current)' : '*'; ?></label>
                                <input type="password" name="password" class="form-control" <?php echo !$edit_staff ? 'required' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo $edit_staff ? htmlspecialchars($edit_staff['address']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_staff ? 'Update Staff' : 'Add Staff'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Staff List -->
        <div class="card fade-in">
            <div class="card-header">
                Staff Members (<?php echo count($staff_members); ?> total)
            </div>
            
            <div class="card-body">
                <?php if (empty($staff_members)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-tie fa-3x mb-3"></i>
                        <h4>No staff members found</h4>
                        <p>Add your first staff member using the button above</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_members as $staff): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($staff['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($staff['position']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['department']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $staff['status'] == 'Active' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $staff['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="?edit=<?php echo $staff['employee_id']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="employee_id" value="<?php echo $staff['employee_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('navMenu').classList.toggle('show');
        });

        // Toggle form visibility
        const toggleFormBtn = document.getElementById('toggleFormBtn');
        const staffForm = document.getElementById('staffForm');
        
        function toggleForm() {
            staffForm.classList.toggle('hidden');
            if (staffForm.classList.contains('hidden')) {
                toggleFormBtn.innerHTML = '<i class="fas fa-plus"></i> Add New Staff Member';
                // Clear edit mode if form is hidden
                if (window.location.search.includes('edit=')) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            } else {
                toggleFormBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
            }
        }
        
        toggleFormBtn.addEventListener('click', toggleForm);

        // Cancel form
        function cancelForm() {
            staffForm.classList.add('hidden');
            toggleFormBtn.innerHTML = '<i class="fas fa-plus"></i> Add New Staff Member';
            // Clear edit mode
            if (window.location.search.includes('edit=')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'index.php';
            }
        }

        // Auto-hide messages after 5 seconds
        const messages = document.querySelectorAll('.alert');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 5000);
        });

        // If in edit mode, ensure form is visible
        <?php if ($edit_staff): ?>
            document.addEventListener('DOMContentLoaded', function() {
                staffForm.classList.remove('hidden');
                toggleFormBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
            });
        <?php endif; ?>
    </script>
</body>
</html>