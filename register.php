<?php
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($position) || empty($department)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($first_name) < 2 || strlen($last_name) < 2) {
        $error_message = 'First name and last name must be at least 2 characters long.';
    } else {
        try {
            // Check if email already exists in admins table
            $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error_message = 'An account with this email address already exists.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin
                $stmt = $pdo->prepare("
                    INSERT INTO admins (first_name, last_name, email, password, position, department, phone, address, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())
                ");
                
                $result = $stmt->execute([
                    $first_name,
                    $last_name,
                    $email,
                    $hashed_password,
                    $position,
                    $department,
                    $phone,
                    $address
                ]);
                
                if ($result) {
                    $success_message = 'Registration successful! You can now login with your credentials.';
                    
                    // Clear form data after successful registration
                    $first_name = $last_name = $email = $position = $department = $phone = $address = '';
                    
                    // Return JSON response for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode(['success' => true, 'message' => $success_message, 'redirect' => 'login.php']);
                        exit();
                    }
                } else {
                    $error_message = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Database error. Please try again later.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
    
    // Return JSON response for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - JANALISU EMPOWERMENT GROUP</title>
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

        /* Register Container */
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            margin-top: 80px;
        }

        .register-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 600px;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            transition: all 0.4s ease;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 80px rgba(236, 72, 153, 0.15);
        }

        .register-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-weight: 600;
            font-size: 1rem;
        }

        .required {
            color: #dc2626;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #ec4899;
            background: white;
            box-shadow: 0 0 20px rgba(236, 72, 153, 0.1);
            transform: translateY(-2px);
        }

        .form-select {
            cursor: pointer;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #ec4899;
        }

        .register-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(236, 72, 153, 0.3);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(236, 72, 153, 0.4);
        }

        .register-btn:active {
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            color: #64748b;
        }

        .login-link a {
            color: #ec4899;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #8b5cf6;
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc2626;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .success-message {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #16a34a;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #16a34a;
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
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

            .register-card {
                padding: 2rem;
                margin: 1rem;
            }

            .register-title {
                font-size: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .logo-text {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 480px) {
            .logo-image {
                width: 60px;
                height: 60px;
            }

            .logo-text {
                font-size: 1.2rem;
            }

            .register-card {
                padding: 1.5rem;
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
                <li><a href="index.php">Home</a></li>
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

    <!-- Register Container -->
    <div class="register-container">
        <div class="register-card">
            <h2 class="register-title">Join Our Team</h2>
            
            <!-- Error Message -->
            <?php if ($error_message): ?>
                <div class="error-message" style="display: block;"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <!-- Success Message -->
            <?php if ($success_message): ?>
                <div class="success-message" style="display: block;"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <div id="errorMessage" class="error-message"></div>
            <div id="successMessage" class="success-message"></div>
            
            <form id="registerForm" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-input" required>
                            <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                            <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="position" class="form-label">Position <span class="required">*</span></label>
                        <select id="position" name="position" class="form-select" required>
                            <option value="">Select Position</option>
                            <option value="Administrator" <?php echo (isset($position) && $position === 'Administrator') ? 'selected' : ''; ?>>Administrator</option>
                            <option value="Manager" <?php echo (isset($position) && $position === 'Manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="Coordinator" <?php echo (isset($position) && $position === 'Coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                            <option value="Volunteer" <?php echo (isset($position) && $position === 'Volunteer') ? 'selected' : ''; ?>>Volunteer</option>
                            <option value="Field Officer" <?php echo (isset($position) && $position === 'Field Officer') ? 'selected' : ''; ?>>Field Officer</option>
                            <option value="Assistant" <?php echo (isset($position) && $position === 'Assistant') ? 'selected' : ''; ?>>Assistant</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department" class="form-label">Department <span class="required">*</span></label>
                        <select id="department" name="department" class="form-select" required>
                            <option value="">Select Department</option>
                            <option value="Administration" <?php echo (isset($department) && $department === 'Administration') ? 'selected' : ''; ?>>Administration</option>
                            <option value="Programs" <?php echo (isset($department) && $department === 'Programs') ? 'selected' : ''; ?>>Programs</option>
                            <option value="Finance" <?php echo (isset($department) && $department === 'Finance') ? 'selected' : ''; ?>>Finance</option>
                            <option value="Community Outreach" <?php echo (isset($department) && $department === 'Community Outreach') ? 'selected' : ''; ?>>Community Outreach</option>
                            <option value="Youth Development" <?php echo (isset($department) && $department === 'Youth Development') ? 'selected' : ''; ?>>Youth Development</option>
                            <option value="Education" <?php echo (isset($department) && $department === 'Education') ? 'selected' : ''; ?>>Education</option>
                            <option value="Health" <?php echo (isset($department) && $department === 'Health') ? 'selected' : ''; ?>>Health</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+254 700 000 000">
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-input" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="register-btn">
                    Create Account
                    <span class="loading" id="loadingSpinner"></span>
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign In</a>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenu = document.querySelector('.mobile-menu');
        const navMenu = document.querySelector('.nav-menu');

        mobileMenu.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Password toggle functionality
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }

        // Form validation and submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const position = document.getElementById('position').value;
            const department = document.getElementById('department').value;
            
            // Clear previous messages
            hideMessages();
            
            // Client-side validation
            if (!firstName || !lastName || !email || !password || !confirmPassword || !position || !department) {
                e.preventDefault();
                showError('Please fill in all required fields.');
                return;
            }
            
            if (!validateEmail(email)) {
                e.preventDefault();
                showError('Please enter a valid email address.');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showError('Password must be at least 6 characters long.');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showError('Passwords do not match.');
                return;
            }
            
            if (firstName.length < 2 || lastName.length < 2) {
                e.preventDefault();
                showError('First name and last name must be at least 2 characters long.');
                return;
            }
            
            // Show loading state
            const loadingSpinner = document.getElementById('loadingSpinner');
            const submitBtn = document.querySelector('.register-btn');
            
            loadingSpinner.style.display = 'inline-block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
        });

        // Email validation
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        // Show success message
        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
        }

        // Hide all messages
        function hideMessages() {
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';
        }

        // Input field animations
        document.querySelectorAll('.form-input, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('passwordStrength');
            
            // You can add password strength logic here
            if (password.length >= 8 && /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
                // Strong password
                this.style.borderColor = '#16a34a';
            } else if (password.length >= 6) {
                // Medium password
                this.style.borderColor = '#f59e0b';
            } else {
                // Weak password
                this.style.borderColor = '#dc2626';
            }
        });

        // Real-time password confirmation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc2626';
            } else if (confirmPassword && password === confirmPassword) {
                this.style.borderColor = '#16a34a';
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });

        // Add floating animation to register card
        const registerCard = document.querySelector('.register-card');
        let floatDirection = 1;
        
        setInterval(() => {
            const currentTransform = registerCard.style.transform || 'translateY(0px)';
            const currentY = parseFloat(currentTransform.match(/translateY\((-?\d+(?:\.\d+)?)px\)/)?.[1] || 0);
            
            if (currentY >= 5) floatDirection = -1;
            if (currentY <= -5) floatDirection = 1;
            
            const newY = currentY + (floatDirection * 0.5);
            registerCard.style.transform = `translateY(${newY}px)`;
        }, 100);
    </script>
</body>
</html>