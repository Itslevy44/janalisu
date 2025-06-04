<?php
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            // Check if user exists and get user data
            $stmt = $pdo->prepare("SELECT admin_id, first_name, last_name, email, password, position, department, status FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] !== 'Active') {
                    $error_message = 'Your account is not active. Please contact the administrator.';
                } else {
                    // Login successful - set session variables
                    $_SESSION['admin_id'] = $user['admin_id'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['admin_position'] = $user['position'];
                    $_SESSION['admin_department'] = $user['department'];
                    $_SESSION['login_time'] = time();
                    
                    // Update last login
                    $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
                    $updateStmt->execute([$user['admin_id']]);
                    
                    // Handle remember me functionality
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60); // 30 days
                        
                        // Store token in database
                        $tokenStmt = $pdo->prepare("INSERT INTO remember_tokens (admin_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?)) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
                        $tokenStmt->execute([$user['admin_id'], hash('sha256', $token), $expires]);
                        
                        // Set cookie
                        setcookie('remember_token', $token, $expires, '/', '', true, true);
                    }
                    
                    // Return JSON response for AJAX requests
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode(['success' => true, 'message' => 'Login successful!', 'redirect' => 'admin_dashboard.php']);
                        exit();
                    }
                    
                    // Redirect to dashboard
                    header('Location: admin_dashboard.php');
                    exit();
                }
            } else {
                $error_message = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error_message = 'Login failed. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
    
    // Return JSON response for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit();
    }
}

// Check for success message from registration
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $success_message = 'Registration successful! You can now login with your credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JANALISU EMPOWERMENT GROUP</title>
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

        /* Login Container */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
            margin-top: 80px;
        }

        .login-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            transition: all 0.4s ease;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 80px rgba(236, 72, 153, 0.15);
        }

        .login-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        .login-subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
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

        .form-input {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #ec4899;
            background: white;
            box-shadow: 0 0 20px rgba(236, 72, 153, 0.1);
            transform: translateY(-2px);
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #ec4899;
            cursor: pointer;
        }

        .forgot-password {
            color: #ec4899;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #8b5cf6;
        }

        .login-btn {
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

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(236, 72, 153, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .register-link {
            text-align: center;
            color: #64748b;
        }

        .register-link a {
            color: #ec4899;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
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

        .social-login {
            margin: 2rem 0;
            text-align: center;
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 2rem 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #64748b;
            font-size: 0.9rem;
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

            .login-card {
                padding: 2rem;
                margin: 1rem;
            }

            .login-title {
                font-size: 2rem;
            }

            .logo-text {
                font-size: 1.4rem;
            }

            .form-options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
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

            .login-card {
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
                <li><a href="home.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="donate.php">Donate</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
            <div class="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <h2 class="login-title">Welcome Back</h2>
            <p class="login-subtitle">Sign in to your account to continue</p>
            
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
            
            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email ?? ''); ?>" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password <span class="required">*</span></label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
                        <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-btn">
                    Sign In
                    <span class="loading" id="loadingSpinner"></span>
                </button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Join Our Team</a>
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
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            // Clear previous messages
            hideMessages();
            
            // Client-side validation
            if (!email || !password) {
                e.preventDefault();
                showError('Please enter both email and password.');
                return;
            }
            
            if (!validateEmail(email)) {
                e.preventDefault();
                showError('Please enter a valid email address.');
                return;
            }
            
            // Show loading state
            const loadingSpinner = document.getElementById('loadingSpinner');
            const submitBtn = document.querySelector('.login-btn');
            
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
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Auto-hide success message from URL parameter
        setTimeout(() => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('registered') === 'success') {
                // Remove the parameter from URL without refreshing
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        }, 5000);

        // Add floating animation to login card
        const loginCard = document.querySelector('.login-card');
        let floatDirection = 1;
        
        setInterval(() => {
            const currentTransform = loginCard.style.transform || 'translateY(0px)';
            const currentY = parseFloat(currentTransform.match(/translateY\((-?\d+(?:\.\d+)?)px\)/)?.[1] || 0);
            
            if (currentY >= 5) floatDirection = -1;
            if (currentY <= -5) floatDirection = 1;
            
            const newY = currentY + (floatDirection * 0.5);
            loginCard.style.transform = `translateY(${newY}px)`;
        }, 100);

        // Handle form submission with AJAX (optional enhancement)
        function submitLoginForm(formData) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    const loadingSpinner = document.getElementById('loadingSpinner');
                    const submitBtn = document.querySelector('.login-btn');
                    
                    loadingSpinner.style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showSuccess(response.message);
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            showError(response.message);
                        }
                    } catch (e) {
                        showError('An error occurred. Please try again.');
                    }
                }
            };
            
            xhr.send(formData);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Enter key to submit form when focused on form elements
            if (e.key === 'Enter' && (e.target.matches('.form-input') || e.target.matches('.login-btn'))) {
                const form = document.getElementById('loginForm');
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Focus on email field when page loads
        window.addEventListener('load', function() {
            const emailField = document.getElementById('email');
            if (emailField && !emailField.value) {
                emailField.focus();
            }
        });
    </script>
</body>
</html>