<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - JANALISU Empowerment Group</title>
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
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu a:hover, .nav-menu a.active {
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

        .nav-menu a:hover::after, .nav-menu a.active::after {
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

        /* Main Content */
        .main-content {
            margin-top: 100px;
            min-height: calc(100vh - 100px);
        }

        /* Hero Section */
        .donate-hero {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.9) 0%, rgba(139, 92, 246, 0.8) 50%, rgba(6, 182, 212, 0.9) 100%);
            padding: 6rem 2rem 4rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .donate-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .donate-hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .donate-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        .donate-hero p {
            font-size: 1.3rem;
            opacity: 0.95;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }

        /* Donation Options */
        .donation-options {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .option-card {
            background: white;
            padding: 3rem;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
        }

        .option-card:hover {
            transform: translateY(-15px);
            border-color: #ec4899;
            box-shadow: 0 25px 60px rgba(236, 72, 153, 0.2);
        }

        .option-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 30px rgba(236, 72, 153, 0.3);
        }

        .option-card h3 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .option-card p {
            color: #475569;
            line-height: 1.7;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* M-Pesa Instructions */
        

        .instruction-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 10px;
        }

        .step-number {
            background: #22c55e;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .step-text {
            color: #1f2937;
            font-weight: 500;
        }

       
        /* Contact Info */
        .contact-info {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            padding: 2rem;
            border-radius: 15px;
            border-left: 5px solid #ec4899;
            margin-top: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .contact-icon {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .contact-text {
            color: #1f2937;
            font-weight: 500;
        }

        .contact-text strong {
            color: #ec4899;
        }

        
        
        /* Button Styles */
        .btn {
            padding: 1.2rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
            margin: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(236, 72, 153, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #ec4899;
            border: 2px solid #ec4899;
        }

        .btn-secondary:hover {
            background: #ec4899;
            color: white;
            transform: translateY(-5px);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease forwards;
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

            .donate-hero h1 {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .options-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .option-card {
                padding: 2rem;
            }

           
        }
        /* Tablet responsiveness */
@media (max-width: 1024px) {
    nav {
        padding: 0 1rem;
    }
    .container {
        max-width: 98vw;
        padding: 0 1rem;
    }
    .options-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .option-card {
        padding: 2rem;
    }
    .donate-hero-content {
        max-width: 95vw;
    }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    nav {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 0.5rem;
    }
    .logo-container {
        margin-bottom: 1rem;
    }
    .nav-menu {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background-color: rgba(255, 255, 255, 0.98);
        width: 100%;
        text-align: center;
        transition: 0.3s;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: 2rem 0;
        z-index: 1001;
    }
    .nav-menu.active {
        left: 0;
    }
    .mobile-menu {
        display: flex;
        margin-left: auto;
    }
    .donate-hero {
        padding: 4rem 1rem 2rem;
    }
    .donate-hero h1 {
        font-size: 2.2rem;
    }
    .donate-hero p {
        font-size: 1rem;
    }
    .section-title {
        font-size: 1.5rem;
    }
    .option-card {
        padding: 1.2rem;
    }
    .option-card h3 {
        font-size: 1.2rem;
    }
    .option-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    .contact-info {
        padding: 1rem;
    }
}

/* Small mobile devices */
@media (max-width: 480px) {
    .logo-image {
        width: 48px;
        height: 48px;
    }
    .logo-text {
        font-size: 1.1rem;
    }
    .donate-hero {
        padding: 2rem 0.5rem 1rem;
    }
    .donate-hero h1 {
        font-size: 1.1rem;
    }
    .donate-hero p {
        font-size: 0.95rem;
    }
    .section-title {
        font-size: 1.1rem;
    }
    .option-card {
        padding: 0.7rem;
    }
    .option-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    .contact-info {
        padding: 0.5rem;
    }
    .btn, .btn-primary, .btn-secondary {
        font-size: 0.95rem;
        padding: 0.8rem 1.2rem;
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
                <li><a href="donate.php" class="active">Donate</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
            <div class="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>
<br><br>
    <main class="main-content">
        <!-- Hero Section -->
        <section class="donate-hero">
            <div class="donate-hero-content">
                <h1>Support Our Mission</h1>
                <p>Your donation helps us empower girls in Wang'chieng Ward through education and sports. Every contribution makes a lasting impact on young lives and transforms communities.</p>
            </div>
        </section>

        <!-- Donation Options -->
        <section class="donation-options">
            <div class="container">
                <h2 class="section-title">Ways to Support JANALISU</h2>
                
              

                    <!-- Physical Support -->
                    <div class="option-card fade-in">
                        <div class="option-icon">🤝</div>
                        <h3>Physical Support & Volunteering</h3>
                        <p>Want to get involved directly? We welcome volunteers, sports equipment donations, educational materials, and other forms of physical support.</p>
                        
                        <div class="contact-info">
                            <h4 style="margin-bottom: 1.5rem; color: #1f2937; text-align: center;">Contact Us for Physical Support</h4>
                            
                            <div class="contact-item">
                                <div class="contact-icon">📞</div>
                                <div class="contact-text">
                                    <strong>Call us:</strong> +254 723 695 920<br>
                                    <small>Available Mon-Fri, 8:00 AM - 6:00 PM</small>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">📧</div>
                                <div class="contact-text">
                                    <strong>Email:</strong> janalisuempowerment@gmail.com<br>
                                    <small>We'll respond within 24 hours</small>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">📍</div>
                                <div class="contact-text">
                                    <strong>Visit us:</strong> Wang'chieng Ward, Homa Bay County<br>
                                    <small>Please call ahead to schedule a visit</small>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">⏰</div>
                                <div class="contact-text">
                                    <strong>Office Hours:</strong> Monday - Friday: 8:00 AM - 6:00 PM<br>
                                    <small>Saturday: 9:00 AM - 2:00 PM</small>
                                </div>
                            </div>
                            
                            <div style="background: rgba(236, 72, 153, 0.1); padding: 1.5rem; border-radius: 10px; margin-top: 2rem;">
                                <h5 style="color: #be185d; margin-bottom: 1rem;">Ways You Can Help:</h5>
                                <ul style="color: #1f2937; padding-left: 1.5rem;">
                                    <li>Donate sports equipment (footballs, boots, jerseys)</li>
                                    <li>Provide educational materials and books</li>
                                    <li>Volunteer as a mentor or coach</li>
                                    <li>Sponsor a girl's education directly</li>
                                    <li>Help with fundraising events</li>
                                    <li>Offer professional skills (accounting, marketing, etc.)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
    </main>

    

    <script>
        // Mobile menu toggle
        const mobileMenu = document.querySelector('.mobile-menu');
        const navMenu = document.querySelector('.nav-menu');

        mobileMenu.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Smooth scrolling for navigation links
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
                // Close mobile menu if open
                navMenu.classList.remove('active');
            });
        });

        // Header background on scroll
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 4px 30px rgba(236, 72, 153, 0.15)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = '0 4px 30px rgba(236, 72, 153, 0.1)';
            }
        });

        // Intersection Observer for animations
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

        document.querySelectorAll('.fade-in').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            observer.observe(el);
        });
    </script>
</body>
</html>