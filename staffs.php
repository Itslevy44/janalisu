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

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
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
                    if (!empty($_POST['password'])) {
                        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, position=?, department=?, salary=?, hire_date=?, status=?, address=?, password=? WHERE employee_id=?");
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
                            $hashed_password,
                            $_POST['employee_id']
                        ]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, position=?, department=?, salary=?, hire_date=?, status=?, address=? WHERE employee_id=?");
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
                            $_POST['employee_id']
                        ]);
                    }
                    $message = "Staff member updated successfully!";
                    $message_type = "success";
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM employees WHERE employee_id = ?");
                    $stmt->execute([$_POST['employee_id']]);
                    $message = "Staff member deleted successfully!";
                    $message_type = "success";
                    break;
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch all staff members
try {
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC");
    $staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $staff_members = [];
    $message = "Error fetching staff: " . $e->getMessage();
    $message_type = "error";
}

// Get staff for editing
$edit_staff = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_staff = $stmt->fetch(PDO::FETCH_ASSOC);
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            color: white;
            font-size: 36px;
            font-weight: 300;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        /* Message Styles */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
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

        /* Form Container */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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

        .form-label {
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-input {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Staff List */
        .staff-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .list-header {
            background: #667eea;
            color: white;
            padding: 20px 30px;
            font-size: 20px;
            font-weight: 500;
        }

        .staff-table {
            width: 100%;
            border-collapse: collapse;
        }

        .staff-table th,
        .staff-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .staff-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .staff-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 15px;
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .toggle-form-btn {
            background: #e91e63;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .toggle-form-btn:hover {
            background: #c2185b;
            transform: translateY(-2px);
        }

        .hidden {
            display: none;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .staff-table {
                font-size: 12px;
            }

            .staff-table th,
            .staff-table td {
                padding: 8px;
            }

            .action-btns {
                flex-direction: column;
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
            <a href="staffs.php" class="nav-item active">Staff</a>
            <a href="add_events.php" class="nav-item">Events</a>
        </nav>
        
        <div class="user-section">
            <span class="welcome-text">Welcome,<br>Admin</span>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Staff Management</h1>
            <p class="page-subtitle">Manage your organization's staff members</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Toggle Form Button -->
        <button class="toggle-form-btn" onclick="toggleForm()">
            <?php echo $edit_staff ? 'Edit Staff Member' : '+ Add New Staff Member'; ?>
        </button>

        <!-- Add/Edit Staff Form -->
        <div id="staffForm" class="form-container <?php echo !$edit_staff ? 'hidden' : ''; ?>">
            <h2 class="form-title"><?php echo $edit_staff ? 'Edit Staff Member' : 'Add New Staff Member'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $edit_staff ? 'edit' : 'add'; ?>">
                <?php if ($edit_staff): ?>
                    <input type="hidden" name="employee_id" value="<?php echo $edit_staff['employee_id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-input" required
                               value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['first_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-input" required
                               value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['last_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" required
                               value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-input" required
                               value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Position *</label>
                        <input type="text" name="position" class="form-input" required
                               value="<?php echo $edit_staff ? htmlspecialchars($edit_staff['position']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Department *</label>
                        <select name="department" class="form-input" required>
                            <option value="">Select Department</option>
                            <option value="Administration" <?php echo ($edit_staff && $edit_staff['department'] == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                            <option value="Training" <?php echo ($edit_staff && $edit_staff['department'] == 'Training') ? 'selected' : ''; ?>>Training</option>
                            <option value="Community Outreach" <?php echo ($edit_staff && $edit_staff['department'] == 'Community Outreach') ? 'selected' : ''; ?>>Community Outreach</option>
                            <option value="Finance" <?php echo ($edit_staff && $edit_staff['department'] == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                            <option value="Human Resources" <?php echo ($edit_staff && $edit_staff['department'] == 'Human Resources') ? 'selected' : ''; ?>>Human Resources</option>
                            <option value="Project Management" <?php echo ($edit_staff && $edit_staff['department'] == 'Project Management') ? 'selected' : ''; ?>>Project Management</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-input" step="0.01" min="0"
                               value="<?php echo $edit_staff ? $edit_staff['salary'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-input"
                               value="<?php echo $edit_staff ? $edit_staff['hire_date'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            <option value="Active" <?php echo ($edit_staff && $edit_staff['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($edit_staff && $edit_staff['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password <?php echo $edit_staff ? '(Leave blank to keep current)' : '*'; ?></label>
                        <input type="password" name="password" class="form-input" <?php echo !$edit_staff ? 'required' : ''; ?>>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-textarea"><?php echo $edit_staff ? htmlspecialchars($edit_staff['address']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_staff ? 'Update Staff' : 'Add Staff'; ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelForm()">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Staff List -->
        <div class="staff-list">
            <div class="list-header">
                Staff Members (<?php echo count($staff_members); ?> total)
            </div>
            
            <?php if (empty($staff_members)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    No staff members found. Add your first staff member above.
                </div>
            <?php else: ?>
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Hire Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                <td><?php echo htmlspecialchars($staff['position']); ?></td>
                                <td><?php echo htmlspecialchars($staff['department']); ?></td>
                                <td><?php echo $staff['hire_date'] ? date('M j, Y', strtotime($staff['hire_date'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $staff['status'] == 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $staff['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="?edit=<?php echo $staff['employee_id']; ?>" class="btn btn-small btn-edit">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="employee_id" value="<?php echo $staff['employee_id']; ?>">
                                            <button type="submit" class="btn btn-small btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Toggle form visibility
        function toggleForm() {
            const form = document.getElementById('staffForm');
            const button = document.querySelector('.toggle-form-btn');
            
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                button.textContent = 'Cancel';
            } else {
                form.classList.add('hidden');
                button.textContent = '+ Add New Staff Member';
            }
        }

        // Cancel form
        function cancelForm() {
            window.location.href = 'staffs.php';
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'login.php';
            }
        }

        // Form animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);

            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>