<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "janalisu");
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Helper: fetch all rows from a table
function fetch_all($mysqli, $table) {
    $result = $mysqli->query("SELECT * FROM `$table`");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Helper: send JSON response
function send_json_response($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    // Always include current data
    $response['students'] = fetch_all($GLOBALS['mysqli'], 'students');
    $response['staff'] = fetch_all($GLOBALS['mysqli'], 'employees');
    $response['events'] = fetch_all($GLOBALS['mysqli'], 'events');
    
    // Merge any additional data
    $response = array_merge($response, $data);
    
    echo json_encode($response);
    exit;
}

// AJAX fetch for JS dashboard
if (isset($_GET['fetch']) && $_GET['fetch'] === 'all') {
    header('Content-Type: application/json');
    echo json_encode([
        'students' => fetch_all($mysqli, 'students'),
        'staff' => fetch_all($mysqli, 'employees'),
        'events' => fetch_all($mysqli, 'events')
    ]);
    exit;
}

// AJAX CRUD for JS dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity'])) {
    $entity = $_POST['entity'];
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);

    try {
        // Students
        if ($entity === 'student') {
            if ($action === 'add' || $action === 'edit') {
                $first_name = $mysqli->real_escape_string($_POST['first_name'] ?? $_POST['first-name'] ?? '');
                $last_name = $mysqli->real_escape_string($_POST['last_name'] ?? $_POST['last-name'] ?? '');
                $email = $mysqli->real_escape_string($_POST['email'] ?? '');
                $phone = $mysqli->real_escape_string($_POST['phone'] ?? '');
                $program = $mysqli->real_escape_string($_POST['program'] ?? '');
                $status = $mysqli->real_escape_string($_POST['status'] ?? 'Active');
                $dob = $mysqli->real_escape_string($_POST['date_of_birth'] ?? $_POST['dob'] ?? '');
                $gender = $mysqli->real_escape_string($_POST['gender'] ?? '');
                $address = $mysqli->real_escape_string($_POST['address'] ?? '');
                $enrollment_date = $mysqli->real_escape_string($_POST['enrollment_date'] ?? $_POST['enrollment-date'] ?? '');

                // Validate required fields
                if (empty($first_name) || empty($last_name) || empty($email)) {
                    send_json_response(false, 'First name, last name, and email are required.');
                }

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    send_json_response(false, 'Please enter a valid email address.');
                }

                if ($action === 'add') {
                    // Check if email already exists
                    $check_email = $mysqli->query("SELECT student_id FROM students WHERE email = '$email'");
                    if ($check_email && $check_email->num_rows > 0) {
                        send_json_response(false, 'A student with this email already exists.');
                    }

                    $query = "INSERT INTO students (first_name, last_name, email, phone, program, status, date_of_birth, gender, address, enrollment_date)
                        VALUES ('$first_name', '$last_name', '$email', '$phone', '$program', '$status', " . 
                        ($dob ? "'$dob'" : "NULL") . ", '$gender', '$address', " . 
                        ($enrollment_date ? "'$enrollment_date'" : "NULL") . ")";
                    
                    if ($mysqli->query($query)) {
                        send_json_response(true, 'Student added successfully!');
                    } else {
                        send_json_response(false, 'Failed to add student: ' . $mysqli->error);
                    }
                } else {
                    // Check if email already exists for other students
                    $check_email = $mysqli->query("SELECT student_id FROM students WHERE email = '$email' AND student_id != $id");
                    if ($check_email && $check_email->num_rows > 0) {
                        send_json_response(false, 'A student with this email already exists.');
                    }

                    $query = "UPDATE students SET 
                        first_name='$first_name', 
                        last_name='$last_name', 
                        email='$email', 
                        phone='$phone', 
                        program='$program', 
                        status='$status', 
                        date_of_birth=" . ($dob ? "'$dob'" : "NULL") . ", 
                        gender='$gender', 
                        address='$address', 
                        enrollment_date=" . ($enrollment_date ? "'$enrollment_date'" : "NULL") . " 
                        WHERE student_id=$id";
                    
                    if ($mysqli->query($query)) {
                        send_json_response(true, 'Student updated successfully!');
                    } else {
                        send_json_response(false, 'Failed to update student: ' . $mysqli->error);
                    }
                }
            } elseif ($action === 'delete') {
                if ($mysqli->query("DELETE FROM students WHERE student_id=$id")) {
                    send_json_response(true, 'Student deleted successfully!');
                } else {
                    send_json_response(false, 'Failed to delete student: ' . $mysqli->error);
                }
            }
        }

        // Employees
        if ($entity === 'employee') {
            if ($action === 'add' || $action === 'edit') {
                $first_name = $mysqli->real_escape_string($_POST['first_name'] ?? $_POST['first-name'] ?? '');
                $last_name = $mysqli->real_escape_string($_POST['last_name'] ?? $_POST['last-name'] ?? '');
                $email = $mysqli->real_escape_string($_POST['email'] ?? '');
                $phone = $mysqli->real_escape_string($_POST['phone'] ?? '');
                $position = $mysqli->real_escape_string($_POST['position'] ?? '');
                $department = $mysqli->real_escape_string($_POST['department'] ?? '');
                $salary = floatval($_POST['salary'] ?? 0);
                $hire_date = $mysqli->real_escape_string($_POST['hire_date'] ?? $_POST['hire-date'] ?? '');
                $status = $mysqli->real_escape_string($_POST['status'] ?? 'Active');
                $address = $mysqli->real_escape_string($_POST['address'] ?? '');

                // Validate required fields
                if (empty($first_name) || empty($last_name) || empty($email) || empty($position) || empty($department)) {
                    send_json_response(false, 'First name, last name, email, position, and department are required.');
                }

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    send_json_response(false, 'Please enter a valid email address.');
                }

                if ($action === 'add') {
                    // Check if email already exists
                    $check_email = $mysqli->query("SELECT employee_id FROM employees WHERE email = '$email'");
                    if ($check_email && $check_email->num_rows > 0) {
                        send_json_response(false, 'An employee with this email already exists.');
                    }

                    // Note: password field is required in your schema but not handled in form
                    $default_password = password_hash('defaultpass123', PASSWORD_DEFAULT);
                    
                    $query = "INSERT INTO employees (first_name, last_name, email, phone, position, department, salary, hire_date, status, address, password)
                        VALUES ('$first_name', '$last_name', '$email', '$phone', '$position', '$department', $salary, " . 
                        ($hire_date ? "'$hire_date'" : "NULL") . ", '$status', '$address', '$default_password')";
                    
                    if ($mysqli->query($query)) {
                        send_json_response(true, 'Staff member added successfully!');
                    } else {
                        send_json_response(false, 'Failed to add staff member: ' . $mysqli->error);
                    }
                } else {
                    // Check if email already exists for other employees
                    $check_email = $mysqli->query("SELECT employee_id FROM employees WHERE email = '$email' AND employee_id != $id");
                    if ($check_email && $check_email->num_rows > 0) {
                        send_json_response(false, 'An employee with this email already exists.');
                    }

                    $query = "UPDATE employees SET 
                        first_name='$first_name', 
                        last_name='$last_name', 
                        email='$email', 
                        phone='$phone', 
                        position='$position', 
                        department='$department', 
                        salary=$salary, 
                        hire_date=" . ($hire_date ? "'$hire_date'" : "NULL") . ", 
                        status='$status', 
                        address='$address' 
                        WHERE employee_id=$id";
                    
                    if ($mysqli->query($query)) {
                        send_json_response(true, 'Staff member updated successfully!');
                    } else {
                        send_json_response(false, 'Failed to update staff member: ' . $mysqli->error);
                    }
                }
            } elseif ($action === 'delete') {
                if ($mysqli->query("DELETE FROM employees WHERE employee_id=$id")) {
                    send_json_response(true, 'Staff member deleted successfully!');
                } else {
                    send_json_response(false, 'Failed to delete staff member: ' . $mysqli->error);
                }
            }
        }

        // Events
        if ($entity === 'event') {
            if ($action === 'add' || $action === 'edit') {
                $title = $mysqli->real_escape_string($_POST['title'] ?? '');
                $description = $mysqli->real_escape_string($_POST['description'] ?? '');
                $event_date = $mysqli->real_escape_string($_POST['event_date'] ?? $_POST['event-date'] ?? '');
                $event_time = $mysqli->real_escape_string($_POST['event_time'] ?? $_POST['event-time'] ?? '');
                $location = $mysqli->real_escape_string($_POST['location'] ?? '');
                $max_participants = intval($_POST['max_participants'] ?? $_POST['max-participants'] ?? 0);
                $event_type = $mysqli->real_escape_string($_POST['event_type'] ?? $_POST['event-type'] ?? '');
                $status = $mysqli->real_escape_string($_POST['status'] ?? 'Scheduled');

                // Validate required fields
                if (empty($title) || empty($event_date) || empty($location)) {
                    send_json_response(false, 'Title, event date, and location are required.');
                }

                // Validate date format
                if (!strtotime($event_date)) {
                    send_json_response(false, 'Please enter a valid event date.');
                }

                if ($action === 'add') {
                    // Note: Using event_title column name from your schema
                    $query = "INSERT INTO events (event_title, description, event_date, event_time, location, max_participants, event_type, status)
                        VALUES ('$title', '$description', '$event_date', " . 
                        ($event_time ? "'$event_time'" : "NULL") . ", '$location', $max_participants, '$event_type', '$status')";
                    
                    if ($mysqli->query($query)) {
                        send_json_response(true, 'Event added successfully!');
                    } else {
                        send_json_response(false, 'Failed to add event: ' . $mysqli->error);
                    }
                } else {
                    $query = "UPDATE events SET 
                        event_title='$title', 
                        description='$description', 
                        event_date='$event_date', 
                        event_time=" . ($event_time ? "'$event_time'" : "NULL") . ", 
                        location='$location', 
                        max_participants=$max_participants, 
                        event_type='$event_type', 
                        status='$status' 
                        WHERE event_id=$id";
                    
                    if ($mysqli->query($query)) {
                        send_json_response(true, 'Event updated successfully!');
                    } else {
                        send_json_response(false, 'Failed to update event: ' . $mysqli->error);
                    }
                }
            } elseif ($action === 'delete') {
                if ($mysqli->query("DELETE FROM events WHERE event_id=$id")) {
                    send_json_response(true, 'Event deleted successfully!');
                } else {
                    send_json_response(false, 'Failed to delete event: ' . $mysqli->error);
                }
            }
        }

        // If we get here, no valid entity was processed
        send_json_response(false, 'Invalid request parameters.');

    } catch (Exception $e) {
        send_json_response(false, 'An error occurred: ' . $e->getMessage());
    }
}

// Close database connection
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JANALISU Admin Dashboard</title>
    <style>
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
            color: #333;
        }

        /* Header Styles */
        header {
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-link {
            text-decoration: none;
            color: #64748b;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.9rem;
            color: #16a34a;
            font-weight: 600;
        }

        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .tab-btn {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
            padding: 1rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .tab-btn:hover,
        .tab-btn.active {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.4);
        }

        /* Tab Content */
        .tab-content {
            display: none;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border-radius: 12px;
            padding: 0.75rem;
            max-width: 300px;
            flex: 1;
        }

        .search-icon {
            margin-right: 0.5rem;
            color: #64748b;
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            flex: 1;
            font-size: 1rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #ec4899;
            color: #ec4899;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-edit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        /* Data Table */
        .data-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-graduated {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-completed {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-cancelled {
            background: #fef2f2;
            color: #ef4444;
        }

        .status-terminated {
            background: #fafafa;
            color: #6b7280;
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
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: modalShow 0.3s ease;
        }

        @keyframes modalShow {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #ec4899;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: none;
        }

        .alert.show {
            display: block;
            animation: alertShow 0.3s ease;
        }

        @keyframes alertShow {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-menu {
                display: none;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: none;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 0.5rem;
            }
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #64748b;
            font-style: italic;
        }
    </style>
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">JANALISU ADMIN</div>
            <ul class="nav-menu">
                <li><a href="#dashboard" class="nav-link active">Dashboard</a></li>
                <li><a href="#students" class="nav-link">Students</a></li>
                <li><a href="#staff" class="nav-link">Staff</a></li>
                <li><a href="#events" class="nav-link">Events</a></li>
            </ul>
            <div class="admin-info">
                <span>Welcome, Admin</span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </nav>
    </header>

    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-subtitle">Manage your organization's data efficiently</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">👥</span>
                <div class="stat-number" id="total-students">0</div>
                <div class="stat-label">Total Students</div>
                <div class="stat-change" id="active-students">+0 Active</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">👨‍💼</span>
                <div class="stat-number" id="total-staff">0</div>
                <div class="stat-label">Staff Members</div>
                <div class="stat-change" id="active-staff">+0 Active</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📅</span>
                <div class="stat-number" id="total-events">0</div>
                <div class="stat-label">Upcoming Events</div>
                <div class="stat-change" id="active-events">+0 Active</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📊</span>
                <div class="stat-number">95%</div>
                <div class="stat-label">Success Rate</div>
                <div class="stat-change">+5% This Month</div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" data-tab="students">Students</button>
            <button class="tab-btn" data-tab="staff">Staff</button>
            <button class="tab-btn" data-tab="events">Events</button>
        </div>

        <!-- Students Tab -->
        <div id="students-tab" class="tab-content active">
            <div class="action-bar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search students..." id="student-search">
                </div>
                <button class="btn btn-primary" onclick="openModal('student-modal')">
                    ➕ Add Student
                </button>
            </div>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Staff Tab -->
        <div id="staff-tab" class="tab-content">
            <div class="action-bar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search staff..." id="staff-search">
                </div>
                <button class="btn btn-primary" onclick="openModal('staff-modal')">
                    ➕ Add Staff
                </button>
            </div>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="staff-table">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Events Tab -->
        <div id="events-tab" class="tab-content">
            <div class="action-bar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search events..." id="event-search">
                </div>
                <button class="btn btn-primary" onclick="openModal('event-modal')">
                    ➕ Add Event
                </button>
            </div>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="events-table">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Modal -->
    <div id="student-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add/Edit Student</h2>
                <button class="close-btn" onclick="closeModal('student-modal')">&times;</button>
            </div>
            <div class="alert alert-success" id="student-success"></div>
            <div class="alert alert-error" id="student-error"></div>
            <form id="student-form">
                <input type="hidden" id="student-id" name="id">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="student-first-name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="student-last-name" name="last_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" id="student-email" name="email" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="tel" class="form-control" id="student-phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="student-dob" name="date_of_birth">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-control" id="student-gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Program *</label>
                        <select class="form-control" id="student-program" name="program" required>
                            <option value="">Select Program</option>
                            <option value="Digital Literacy">Digital Literacy</option>
                            <option value="Entrepreneurship">Entrepreneurship</option>
                            <option value="Life Skills">Life Skills</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Sports">Sports</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" id="student-address" name="address" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Enrollment Date</label>
                        <input type="date" class="form-control" id="student-enrollment-date" name="enrollment_date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="student-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="graduated">Graduated</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('student-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Modal -->
    <div id="staff-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add/Edit Staff</h2>
                <button class="close-btn" onclick="closeModal('staff-modal')">&times;</button>
            </div>
            <div class="alert alert-success" id="staff-success"></div>
            <div class="alert alert-error" id="staff-error"></div>
            <form id="staff-form">
                <input type="hidden" id="staff-id" name="id">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="staff-first-name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="staff-last-name" name="last_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" id="staff-email" name="email" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="tel" class="form-control" id="staff-phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position *</label>
                        <input type="text" class="form-control" id="staff-position" name="position" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Department *</label>
                        <select class="form-control" id="staff-department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Administration">Administration</option>
                            <option value="Training">Training</option>
                            <option value="Outreach">Outreach</option>
                            <option value="Finance">Finance</option>
                            <option value="IT">IT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salary</label>
                        <input type="number" class="form-control" id="staff-salary" name="salary" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Hire Date</label>
                        <input type="date" class="form-control" id="staff-hire-date" name="hire_date" name="hire_date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="staff-status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" id="staff-address" name="address" rows="3"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('staff-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="event-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add/Edit Event</h2>
                <button class="close-btn" onclick="closeModal('event-modal')">&times;</button>
            </div>
            <div class="alert alert-success" id="event-success"></div>
            <div class="alert alert-error" id="event-error"></div>
            <form id="event-form">
                <input type="hidden" id="event-id" name="id">
                <div class="form-group">
                    <label class="form-label">Event Title *</label>
                    <input type="text" class="form-control" id="event-title" name="title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="event-description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" id="event-date" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time</label>
                        <input type="time" class="form-control" id="event-time" name="event_time">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Location *</label>
                        <input type="text" class="form-control" id="event-location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Participants</label>
                        <input type="number" class="form-control" id="event-max-participants" name="max_participants" min="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Event Type</label>
                        <select class="form-control" id="event-type" name="event_type">
                            <option value="">Select Type</option>
                            <option value="workshop">Workshop</option>
                            <option value="seminar">Seminar</option>
                            <option value="training">Training</option>
                            <option value="conference">Conference</option>
                            <option value="meeting">Meeting</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="event-status" name="status">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('event-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <script>
  // filepath: c:\Users\Studyroom\Desktop\danalisu\admin_dashboard.php

// Global variables
let students = [];
let staff = [];
let events = [];
let currentTab = 'students';

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', function() {
    fetchAllData();
    setupEventListeners();
});

function fetchAllData() {
    fetch('admin_dashboard.php?fetch=all')
        .then(res => res.json())
        .then(data => {
            students = data.students || [];
            staff = data.staff || [];
            events = data.events || [];
            renderStudents();
            renderStaff();
            renderEvents();
            updateStatistics();
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            showAlert('error-alert', 'Failed to load data. Please refresh the page.');
        });
}

function setupEventListeners() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });

    // Search event listeners
    const studentSearch = document.getElementById('student-search');
    const staffSearch = document.getElementById('staff-search');
    const eventSearch = document.getElementById('event-search');
    
    if (studentSearch) studentSearch.addEventListener('input', filterStudents);
    if (staffSearch) staffSearch.addEventListener('input', filterStaff);
    if (eventSearch) eventSearch.addEventListener('input', filterEvents);

    // Form event listeners
    const studentForm = document.getElementById('student-form');
    const staffForm = document.getElementById('staff-form');
    const eventForm = document.getElementById('event-form');
    
    if (studentForm) studentForm.addEventListener('submit', handleStudentSubmit);
    if (staffForm) staffForm.addEventListener('submit', handleStaffSubmit);
    if (eventForm) eventForm.addEventListener('submit', handleEventSubmit);

    // Modal event listeners
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    // Close modal buttons
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) closeModal(modal.id);
        });
    });
}

function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeBtn) activeBtn.classList.add('active');
    
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    const activeTab = document.getElementById(`${tabName}-tab`);
    if (activeTab) activeTab.classList.add('active');
    
    currentTab = tabName;
}

// Render functions
function renderStudents() {
    const tbody = document.getElementById('students-table');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    if (students.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="no-data">No students found.</td></tr>`;
        return;
    }
    students.forEach(student => {
        const row = document.createElement('tr');
        row.setAttribute('data-id', student.id);
        row.innerHTML = `
            <td>STU${String(student.id).padStart(3, '0')}</td>
            <td>${student.first_name} ${student.last_name}</td>
            <td>${student.email}</td>
            <td>${student.program}</td>
            <td><span class="status-badge status-${student.status}">${capitalize(student.status)}</span></td>
            <td>
                <button class="btn btn-edit" onclick="editStudent(${student.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteStudent(${student.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderStaff() {
    const tbody = document.getElementById('staff-table');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    if (staff.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="no-data">No staff found.</td></tr>`;
        return;
    }
    staff.forEach(member => {
        const row = document.createElement('tr');
        row.setAttribute('data-id', member.id);
        row.innerHTML = `
            <td>STA${String(member.id).padStart(3, '0')}</td>
            <td>${member.first_name} ${member.last_name}</td>
            <td>${member.position}</td>
            <td>${member.department}</td>
            <td><span class="status-badge status-${member.status}">${capitalize(member.status)}</span></td>
            <td>
                <button class="btn btn-edit" onclick="editStaff(${member.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteStaff(${member.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderEvents() {
    const tbody = document.getElementById('events-table');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    if (events.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="no-data">No events found.</td></tr>`;
        return;
    }
    events.forEach(event => {
        const row = document.createElement('tr');
        row.setAttribute('data-id', event.id);
        const eventDate = event.event_date ? new Date(event.event_date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }) : '';
        row.innerHTML = `
            <td>${event.title}</td>
            <td>${eventDate}</td>
            <td>${event.location}</td>
            <td>${event.max_participants || 'N/A'}</td>
            <td><span class="status-badge status-${event.status}">${capitalize(event.status)}</span></td>
            <td>
                <button class="btn btn-edit" onclick="editEvent(${event.id})">Edit</button>
                <button class="btn btn-danger" onclick="deleteEvent(${event.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filter functions
function filterStudents() {
    const searchTerm = document.getElementById('student-search').value.toLowerCase();
    const filtered = students.filter(student =>
        student.first_name.toLowerCase().includes(searchTerm) ||
        student.last_name.toLowerCase().includes(searchTerm) ||
        student.email.toLowerCase().includes(searchTerm) ||
        student.program.toLowerCase().includes(searchTerm)
    );
    renderFilteredTable(filtered, 'students-table', renderStudentsRow);
}

function filterStaff() {
    const searchTerm = document.getElementById('staff-search').value.toLowerCase();
    const filtered = staff.filter(member =>
        member.first_name.toLowerCase().includes(searchTerm) ||
        member.last_name.toLowerCase().includes(searchTerm) ||
        member.position.toLowerCase().includes(searchTerm) ||
        member.department.toLowerCase().includes(searchTerm)
    );
    renderFilteredTable(filtered, 'staff-table', renderStaffRow);
}

function filterEvents() {
    const searchTerm = document.getElementById('event-search').value.toLowerCase();
    const filtered = events.filter(event =>
        event.title.toLowerCase().includes(searchTerm) ||
        event.location.toLowerCase().includes(searchTerm) ||
        (event.description && event.description.toLowerCase().includes(searchTerm))
    );
    renderFilteredTable(filtered, 'events-table', renderEventsRow);
}

function renderFilteredTable(data, tableId, rowFn) {
    const tbody = document.getElementById(tableId);
    if (!tbody) return;
    
    tbody.innerHTML = '';
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="no-data">No data found.</td></tr>`;
        return;
    }
    data.forEach(rowFn);
}

function renderStudentsRow(student) {
    const tbody = document.getElementById('students-table');
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.setAttribute('data-id', student.id);
    row.innerHTML = `
        <td>STU${String(student.id).padStart(3, '0')}</td>
        <td>${student.first_name} ${student.last_name}</td>
        <td>${student.email}</td>
        <td>${student.program}</td>
        <td><span class="status-badge status-${student.status}">${capitalize(student.status)}</span></td>
        <td>
            <button class="btn btn-edit" onclick="editStudent(${student.id})">Edit</button>
            <button class="btn btn-danger" onclick="deleteStudent(${student.id})">Delete</button>
        </td>
    `;
    tbody.appendChild(row);
}

function renderStaffRow(member) {
    const tbody = document.getElementById('staff-table');
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.setAttribute('data-id', member.id);
    row.innerHTML = `
        <td>STA${String(member.id).padStart(3, '0')}</td>
        <td>${member.first_name} ${member.last_name}</td>
        <td>${member.position}</td>
        <td>${member.department}</td>
        <td><span class="status-badge status-${member.status}">${capitalize(member.status)}</span></td>
        <td>
            <button class="btn btn-edit" onclick="editStaff(${member.id})">Edit</button>
            <button class="btn btn-danger" onclick="deleteStaff(${member.id})">Delete</button>
        </td>
    `;
    tbody.appendChild(row);
}

function renderEventsRow(event) {
    const tbody = document.getElementById('events-table');
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.setAttribute('data-id', event.id);
    const eventDate = event.event_date ? new Date(event.event_date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    }) : '';
    row.innerHTML = `
        <td>${event.title}</td>
        <td>${eventDate}</td>
        <td>${event.location}</td>
        <td>${event.max_participants || 'N/A'}</td>
        <td><span class="status-badge status-${event.status}">${capitalize(event.status)}</span></td>
        <td>
            <button class="btn btn-edit" onclick="editEvent(${event.id})">Edit</button>
            <button class="btn btn-danger" onclick="deleteEvent(${event.id})">Delete</button>
        </td>
    `;
    tbody.appendChild(row);
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const hiddenId = form.querySelector('input[type="hidden"]');
            if (hiddenId) hiddenId.value = '';
        }
        const alerts = modal.querySelectorAll('.alert');
        alerts.forEach(alert => alert.classList.remove('show'));
    }
}

// Student CRUD operations
function editStudent(id) {
    const student = students.find(s => s.id == id);
    if (!student) return;
    
    const fields = {
        'student-id': student.id,
        'student-first-name': student.first_name,
        'student-last-name': student.last_name,
        'student-email': student.email,
        'student-phone': student.phone,
        'student-program': student.program,
        'student-status': student.status,
        'student-dob': student.date_of_birth,
        'student-gender': student.gender,
        'student-address': student.address,
        'student-enrollment-date': student.enrollment_date
    };
    
    Object.entries(fields).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field && value) field.value = value;
    });
    
    openModal('student-modal');
}

function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student?')) return;
    
    showAlert('loading-alert', 'Deleting student...');
    
    fetch('admin_dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            entity: 'student',
            action: 'delete',
            id: id
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            students = data.students || [];
            renderStudents();
            updateStatistics();
            showSuccessMessage('Student deleted successfully!');
        } else {
            showErrorMessage(data.message || 'Failed to delete student');
        }
    })
    .catch(error => {
        console.error('Error deleting student:', error);
        showErrorMessage('Failed to delete student. Please try again.');
    });
}

function handleStudentSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const action = formData.get('id') ? 'edit' : 'add';
    formData.append('entity', 'student');
    formData.append('action', action);
    
    // Map form field names to match PHP expectations
    const fieldMapping = {
        'first-name': 'first_name',
        'last-name': 'last_name',
        'enrollment-date': 'enrollment_date',
        'date-of-birth': 'date_of_birth',
        'dob': 'date_of_birth'
    };
    
    // Create a new FormData with properly mapped field names
    const mappedFormData = new FormData();
    for (let [key, value] of formData.entries()) {
        const mappedKey = fieldMapping[key] || key;
        mappedFormData.append(mappedKey, value);
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }
    
    fetch('admin_dashboard.php', {
        method: 'POST',
        body: mappedFormData
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        console.log('Server response:', data); // Debug log
        if (data.success) {
            students = data.students || [];
            renderStudents();
            updateStatistics();
            closeModal('student-modal');
            showSuccessMessage(data.message || (action === 'add' ? 'Student added successfully!' : 'Student updated successfully!'));
        } else {
            showErrorMessage(data.message || 'Failed to save student');
        }
    })
    .catch(error => {
        console.error('Error saving student:', error);
        showErrorMessage('Failed to save student. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        }
    });
}

// Staff CRUD operations
function editStaff(id) {
    const member = staff.find(s => s.id == id);
    if (!member) return;
    
    const fields = {
        'staff-id': member.id,
        'staff-first-name': member.first_name,
        'staff-last-name': member.last_name,
        'staff-email': member.email,
        'staff-phone': member.phone,
        'staff-position': member.position,
        'staff-department': member.department,
        'staff-status': member.status,
        'staff-salary': member.salary,
        'staff-hire-date': member.hire_date,
        'staff-address': member.address
    };
    
    Object.entries(fields).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field && value) field.value = value;
    });
    
    openModal('staff-modal');
}

function deleteStaff(id) {
    if (!confirm('Are you sure you want to delete this staff member?')) return;
    
    showAlert('loading-alert', 'Deleting staff member...');
    
    fetch('admin_dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            entity: 'employee',
            action: 'delete',
            id: id
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            staff = data.staff || [];
            renderStaff();
            updateStatistics();
            showSuccessMessage('Staff member deleted successfully!');
        } else {
            showErrorMessage(data.message || 'Failed to delete staff member');
        }
    })
    .catch(error => {
        console.error('Error deleting staff:', error);
        showErrorMessage('Failed to delete staff member. Please try again.');
    });
}

function handleStaffSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const action = formData.get('id') ? 'edit' : 'add';
    formData.append('entity', 'employee');
    formData.append('action', action);
    
    // Map form field names to match PHP expectations
    const fieldMapping = {
        'first-name': 'first_name',
        'last-name': 'last_name',
        'hire-date': 'hire_date'
    };
    
    // Create a new FormData with properly mapped field names
    const mappedFormData = new FormData();
    for (let [key, value] of formData.entries()) {
        const mappedKey = fieldMapping[key] || key;
        mappedFormData.append(mappedKey, value);
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }
    
    fetch('admin_dashboard.php', {
        method: 'POST',
        body: mappedFormData
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        console.log('Server response:', data); // Debug log
        if (data.success) {
            staff = data.staff || [];
            renderStaff();
            updateStatistics();
            closeModal('staff-modal');
            showSuccessMessage(data.message || (action === 'add' ? 'Staff member added successfully!' : 'Staff member updated successfully!'));
        } else {
            showErrorMessage(data.message || 'Failed to save staff member');
        }
    })
    .catch(error => {
        console.error('Error saving staff:', error);
        showErrorMessage('Failed to save staff member. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        }
    });
}

// Event CRUD operations
function editEvent(id) {
    const event = events.find(e => e.id == id);
    if (!event) return;
    
    const fields = {
        'event-id': event.id,
        'event-title': event.title,
        'event-date': event.event_date,
        'event-location': event.location,
        'event-status': event.status,
        'event-description': event.description,
        'event-time': event.event_time,
        'event-max-participants': event.max_participants,
        'event-type': event.event_type
    };
    
    Object.entries(fields).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field && value) field.value = value;
    });
    
    openModal('event-modal');
}

function deleteEvent(id) {
    if (!confirm('Are you sure you want to delete this event?')) return;
    
    showAlert('loading-alert', 'Deleting event...');
    
    fetch('admin_dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            entity: 'event',
            action: 'delete',
            id: id
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            events = data.events || [];
            renderEvents();
            updateStatistics();
            showSuccessMessage('Event deleted successfully!');
        } else {
            showErrorMessage(data.message || 'Failed to delete event');
        }
    })
    .catch(error => {
        console.error('Error deleting event:', error);
        showErrorMessage('Failed to delete event. Please try again.');
    });
}

function handleEventSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const action = formData.get('id') ? 'edit' : 'add';
    formData.append('entity', 'event');
    formData.append('action', action);
    
    // Map form field names to match PHP expectations
    const fieldMapping = {
        'event-date': 'event_date',
        'event-time': 'event_time',
        'max-participants': 'max_participants',
        'event-type': 'event_type'
    };
    
    // Create a new FormData with properly mapped field names
    const mappedFormData = new FormData();
    for (let [key, value] of formData.entries()) {
        const mappedKey = fieldMapping[key] || key;
        mappedFormData.append(mappedKey, value);
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }
    
    fetch('admin_dashboard.php', {
        method: 'POST',
        body: mappedFormData
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        console.log('Server response:', data); // Debug log
        if (data.success) {
            events = data.events || [];
            renderEvents();
            updateStatistics();
            closeModal('event-modal');
            showSuccessMessage(data.message || (action === 'add' ? 'Event added successfully!' : 'Event updated successfully!'));
        } else {
            showErrorMessage(data.message || 'Failed to save event');
        }
    })
    .catch(error => {
        console.error('Error saving event:', error);
        showErrorMessage('Failed to save event. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        }
    });
}

// Update statistics
function updateStatistics() {
    const totalStudents = students.length;
    const activeStudents = students.filter(s => s.status === 'active').length;
    const totalStaff = staff.length;
    const activeStaff = staff.filter(s => s.status === 'active').length;
    const totalEvents = events.length;
    const activeEvents = events.filter(e => e.status === 'active').length;

    const elements = {
        'total-students': totalStudents,
        'active-students': `+${activeStudents} Active`,
        'total-staff': totalStaff,
        'active-staff': `+${activeStaff} Active`,
        'total-events': totalEvents,
        'active-events': `+${activeEvents} Active`
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });
}

// Enhanced alert system
function showAlert(alertId, message) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.textContent = message;
        alert.classList.add('show');
        setTimeout(() => {
            alert.classList.remove('show');
        }, 3000);
    }
}

function showSuccessMessage(message) {
    // Create a success notification
    const notification = createNotification(message, 'success');
    document.body.appendChild(notification);
    
    // Show the notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function showErrorMessage(message) {
    // Create an error notification
    const notification = createNotification(message, 'error');
    document.body.appendChild(notification);
    
    // Show the notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function createNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : '✕'}</span>
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Add some basic styling
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 500px;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        ${type === 'success' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'}
    `;
    
    notification.querySelector('.notification-content').style.cssText = `
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    
    notification.querySelector('.notification-icon').style.cssText = `
        font-weight: bold;
        font-size: 16px;
    `;
    
    notification.querySelector('.notification-close').style.cssText = `
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        margin-left: auto;
        padding: 0;
        color: inherit;
    `;
    
    // Add show class styles
    const style = document.createElement('style');
    style.textContent = `
        .notification.show {
            transform: translateX(0) !important;
        }
    `;
    document.head.appendChild(style);
    
    return notification;
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'home.php';
    }
}
    </script>
</body>
</html>