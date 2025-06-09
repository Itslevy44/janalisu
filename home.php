<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JANALISU - Empowering Girls Through Education and Sports</title>
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
    padding: 0.5rem 0;
    box-shadow: 0 4px 30px rgba(236, 72, 153, 0.1);
    transition: all 0.3s ease;
}

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-image {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid transparent;
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
    font-size: 1.2rem;
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
    gap: 1.5rem;
}

.nav-menu a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    font-size: 0.9rem;
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
    height: 2px;
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
    width: 22px;
    height: 2px;
    background: #ec4899;
    margin: 2px 0;
    transition: 0.3s;
    border-radius: 2px;
}

/* Hero Section */
.hero {
    height: 100vh;
    min-height: 600px;
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.9) 0%, rgba(139, 92, 246, 0.8) 50%, rgba(6, 182, 212, 0.9) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    padding: 0 1rem;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    max-width: 900px;
    padding: 0 1rem;
    animation: fadeInUp 1s ease;
    position: relative;
    z-index: 2;
    width: 100%;
}

.hero h1 {
    font-size: clamp(2rem, 5vw, 4rem);
    margin-bottom: 1rem;
    font-weight: 800;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
    background: linear-gradient(45deg, #fff 0%, #f8fafc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.hero p {
    font-size: clamp(1rem, 2.5vw, 1.4rem);
    margin-bottom: 2rem;
    opacity: 0.95;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 0.875rem 1.75rem;
    border: none;
    border-radius: 50px;
    font-size: clamp(0.9rem, 2vw, 1.1rem);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s ease;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    min-width: 140px;
    text-align: center;
}

.btn-primary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(15px);
}

.btn-primary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
}

.btn-secondary {
    background: white;
    color: #ec4899;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: transparent;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
}

/* Rotating Images Section */
.rotating-images {
    position: relative;
    height: clamp(250px, 50vw, 400px);
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    margin: 0 auto;
    max-width: 100%;
}

.image-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.rotating-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.rotating-image.active {
    opacity: 1;
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    color: white;
    padding: 1rem;
    text-align: center;
}

.image-overlay h3 {
    font-size: clamp(1rem, 3vw, 1.5rem);
    margin-bottom: 0.5rem;
}

.image-overlay p {
    font-size: clamp(0.8rem, 2vw, 1rem);
}

/* About Section */
.about {
    padding: clamp(3rem, 8vw, 6rem) 1rem;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.section-title {
    text-align: center;
    font-size: clamp(2rem, 5vw, 3rem);
    margin-bottom: clamp(2rem, 5vw, 3rem);
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    line-height: 1.2;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: clamp(2rem, 5vw, 4rem);
    align-items: center;
}

.about-text {
    font-size: clamp(1rem, 2.2vw, 1.1rem);
    line-height: 1.8;
    color: #475569;
}

.about-text p {
    margin-bottom: 1.5rem;
}

.highlight {
    color: #ec4899;
    font-weight: 700;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Vision, Mission, Founders */
.vision-mission {
    padding: clamp(3rem, 8vw, 6rem) 1rem;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
    color: white;
    position: relative;
}

.vision-mission::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="20" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
    opacity: 0.3;
}

.vision-mission .container {
    position: relative;
    z-index: 2;
}

.vision-mission .section-title {
    color: white !important;
    background: white;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.vm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: clamp(1.5rem, 4vw, 3rem);
    margin-top: 2rem;
}

.vm-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    padding: clamp(1.5rem, 4vw, 2.5rem);
    border-radius: 20px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s ease;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
}

.vm-card:hover {
    transform: translateY(-10px) scale(1.02);
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.vm-card h3 {
    font-size: clamp(1.2rem, 3vw, 1.8rem);
    margin-bottom: 1rem;
    color: #fff;
    font-weight: 700;
}

.vm-card p {
    font-size: clamp(0.9rem, 2.2vw, 1.1rem);
    line-height: 1.7;
    opacity: 0.95;
}

/* Objectives Section */
.objectives {
    padding: clamp(3rem, 8vw, 6rem) 1rem;
    background: white;
}

.objectives-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: clamp(1.5rem, 4vw, 2.5rem);
    margin-top: clamp(2rem, 5vw, 4rem);
}

.objective-card {
    background: linear-gradient(135deg, #fdf2f8 0%, #f3e8ff 100%);
    padding: clamp(1.5rem, 4vw, 2.5rem);
    border-radius: 20px;
    border-left: 4px solid #ec4899;
    transition: all 0.4s ease;
    box-shadow: 0 10px 30px rgba(236, 72, 153, 0.1);
    position: relative;
    overflow: hidden;
}

.objective-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: clamp(60px, 15vw, 100px);
    height: clamp(60px, 15vw, 100px);
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.objective-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(236, 72, 153, 0.2);
    border-left-color: #8b5cf6;
}

.objective-card h4 {
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    font-weight: 700;
    position: relative;
    z-index: 2;
}

.objective-card p {
    color: #475569;
    line-height: 1.7;
    position: relative;
    z-index: 2;
    font-size: clamp(0.9rem, 2vw, 1rem);
}

/* Stats Section */
.stats {
    padding: clamp(3rem, 8vw, 6rem) 1rem;
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.05) 0%, rgba(139, 92, 246, 0.05) 50%, rgba(6, 182, 212, 0.05) 100%);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: clamp(1.5rem, 4vw, 2.5rem);
    margin-top: 2rem;
}

.stat-card {
    text-align: center;
    padding: clamp(2rem, 5vw, 3rem) 1rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
}

.stat-card:hover {
    transform: translateY(-8px) scale(1.03);
    border-color: #ec4899;
    box-shadow: 0 20px 50px rgba(236, 72, 153, 0.2);
}

.stat-number {
    font-size: clamp(2rem, 8vw, 3.5rem);
    font-weight: 900;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-label {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: #475569;
    font-weight: 600;
}

/* Footer */
footer {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: clamp(2rem, 5vw, 4rem) 1rem 1rem;
    position: relative;
}

footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #06b6d4 100%);
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: clamp(1.5rem, 4vw, 3rem);
}

.footer-section h3 {
    background: linear-gradient(135deg, #ec4899 0%, #06b6d4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
    font-size: clamp(1.1rem, 2.5vw, 1.4rem);
    font-weight: 700;
}

.footer-section p, .footer-section a {
    color: #cbd5e1;
    text-decoration: none;
    margin-bottom: 0.6rem;
    display: block;
    transition: color 0.3s ease;
    font-size: clamp(0.9rem, 2vw, 1rem);
}

.footer-section a:hover {
    color: #ec4899;
}

.footer-bottom {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #475569;
    color: #94a3b8;
    font-size: clamp(0.8rem, 2vw, 0.9rem);
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
    animation: fadeInUp 0.8s ease;
}

/* Floating animation */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}

/* Responsive Breakpoints */

/* Extra Small Devices (phones, 320px to 575px) */
@media (max-width: 575px) {
    .nav-menu {
        position: fixed;
        left: -100%;
        top: 60px;
        flex-direction: column;
        background-color: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        width: 100%;
        text-align: center;
        transition: 0.3s;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: 1.5rem 0;
        gap: 1rem;
    }

    .nav-menu.active {
        left: 0;
    }

    .mobile-menu {
        display: flex;
    }

    .hero {
        min-height: 500px;
        padding-top: 80px;
    }

    .cta-buttons {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .btn {
        width: 80%;
        max-width: 250px;
    }

    .about-grid,
    .vm-grid,
    .objectives-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

/* Small Devices (landscape phones, tablets, 576px to 767px) */
@media (min-width: 576px) and (max-width: 767px) {
    .nav-menu {
        position: fixed;
        left: -100%;
        top: 65px;
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

    .hero {
        min-height: 550px;
    }

    .logo-image {
        width: 55px;
        height: 55px;
    }

    .logo-text {
        font-size: 1.3rem;
    }
}

/* Medium Devices (tablets, 768px to 991px) */
@media (min-width: 768px) and (max-width: 991px) {
    .nav-menu {
        gap: 1.2rem;
    }

    .logo-image {
        width: 60px;
        height: 60px;
    }

    .logo-text {
        font-size: 1.4rem;
    }

    .hero {
        min-height: 650px;
    }

    .vm-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .objectives-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
}

/* Large Devices (desktops, 992px to 1199px) */
@media (min-width: 992px) and (max-width: 1199px) {
    .nav-menu {
        gap: 1.8rem;
    }

    .logo-image {
        width: 65px;
        height: 65px;
    }

    .logo-text {
        font-size: 1.5rem;
    }

    nav {
        padding: 0 1.5rem;
    }

    .container {
        padding: 0 1.5rem;
    }
}

/* Extra Large Devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
    .nav-menu {
        gap: 2rem;
    }

    .logo-image {
        width: 70px;
        height: 70px;
    }

    .logo-text {
        font-size: 1.6rem;
    }

    nav {
        padding: 0 2rem;
    }

    .container {
        padding: 0 2rem;
    }
}

/* Ultra Wide Screens (1400px and up) */
@media (min-width: 1400px) {
    .logo-image {
        width: 80px;
        height: 80px;
    }

    .logo-text {
        font-size: 1.8rem;
    }
}

/* Landscape Orientation Adjustments */
@media (orientation: landscape) and (max-height: 500px) {
    .hero {
        height: auto;
        min-height: 100vh;
        padding: 100px 1rem 50px;
    }

    .hero h1 {
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        margin-bottom: 0.5rem;
    }

    .hero p {
        font-size: clamp(0.9rem, 2vw, 1.1rem);
        margin-bottom: 1.5rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .hero::before,
    .vision-mission::before {
        background-size: 50px 50px;
    }
}

/* Print Styles */
@media print {
    header {
        position: static;
        box-shadow: none;
    }
    
    .hero {
        height: auto;
        background: #f8f9fa;
        color: #333;
        page-break-after: always;
    }
    
    .btn {
        display: none;
    }
    
    .mobile-menu {
        display: none;
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
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
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
<br><br><br><br>
    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1 class="float-animation">Empowering Girls Through Education & Sports</h1>
            <p>Breaking barriers in Wang'chieng Ward, Homa Bay County. We use football and mentorship to keep girls in school and build resilient communities.</p>
            <div class="cta-buttons">
                <a href="donate.php" class="btn btn-primary">Support Our Mission</a>
                <a href="#about" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">About JANALISU</h2>
            <div class="about-grid">
                <div class="about-text">
                    <p>In Wang'chieng Ward, Homa Bay County, many girls face significant challenges accessing education due to <span class="highlight">poverty, lack of mentorship, and high HIV/AIDS prevalence (30%)</span>. These socio-economic factors force girls into early income-generating activities or cause them to drop out of school.</p>
                    
                    <p>JANALISU addresses these challenges through an integrated, community-based approach using <span class="highlight">sports as a key engagement tool</span>. We organize girls' football tournaments, mentorship programs, coaching sessions, and peer learning activities to raise awareness, promote education, and discourage risky behaviors.</p>
                </div>
                <div class="rotating-images">
                    <div class="image-container">
                        <img src="image1.jpg" alt="Girls playing football" class="rotating-image active">
                        <img src="image2.jpg" alt="Community meeting" class="rotating-image">
                        <img src="image1.jpg" alt="Girls in Classroom" class="rotating-image">
                        <img src="image1.jpg" alt="Mentorship session" class="rotating-image">
                        <div class="image-overlay">
                            <h3 id="image-title">Empowering Through Sports</h3>
                            <p id="image-description">Building confidence and skills through football</p>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 3rem;">
                <div class="about-text">
                    <p>Our comprehensive approach includes providing <span class="highlight">education sponsorships and family support</span> to retain girls in school, reduce HIV/AIDS prevalence through sensitization, and build a strong, informed, and resilient girl-child population.</p>
                    
                    <p>Through football and community outreach, we're not just changing individual lives – we're <span class="highlight">transforming entire communities</span> and breaking cycles of vulnerability that have persisted for generations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision, Mission, Founders -->
    <section class="vision-mission">
        <div class="container">
            <h2 class="section-title">Our Foundation</h2>
            <div class="vm-grid">
                <div class="vm-card">
                    <h3>Our Vision</h3>
                    <p>A Wang'chieng community where all girls access education to their highest level, breaking cycles of poverty and building a foundation for sustainable development and empowerment.</p>
                </div>
                <div class="vm-card">
                    <h3>Our Mission</h3>
                    <p>Empower girls and their families and local communities through sports to build their esteem and create awareness to higher levels of education, fostering resilience and opportunity.</p>
                </div>
                <div class="vm-card">
                    <h3>Our Founders</h3>
                    <p>JANALISU was founded by dedicated community leaders, educators, and youth advocates who recognized the urgent need to address educational inequality in Wang'chieng Ward. Our founders bring together expertise in education, community development, and sports mentorship to create lasting change.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Objectives Section -->
    <section class="objectives">
        <div class="container">
            <h2 class="section-title">Our Objectives</h2>
            <div class="objectives-grid">
                <div class="objective-card">
                    <h4>Promote Education</h4>
                    <p>Provide scholarships and sponsorships to enable girls to stay in school and pursue higher education opportunities that will transform their futures.</p>
                </div>
                <div class="objective-card">
                    <h4>Empower Through Sports</h4>
                    <p>Use girls' football as a platform for community outreach, talent development, confidence building, and education awareness in our community.</p>
                </div>
                <div class="objective-card">
                    <h4>Mentorship & Counseling</h4>
                    <p>Organize regular mentorship programs and peer counseling to support girls emotionally, mentally, and academically throughout their journey.</p>
                </div>
                <div class="objective-card">
                    <h4>Community Sensitization</h4>
                    <p>Conduct HIV/AIDS sensitization sessions during sports events to educate the community about prevention and healthy practices.</p>
                </div>
                <div class="objective-card">
                    <h4>Capacity Building</h4>
                    <p>Offer training and coaching for organization members to improve program delivery and maximize our impact in the community.</p>
                </div>
                <div class="objective-card">
                    <h4>Strengthen Livelihoods</h4>
                    <p>Support households with alternative income opportunities to reduce economic pressure on school-going girls and their families.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <h2 class="section-title">Our Impact</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">30%</div>
                    <div class="stat-label">HIV/AIDS Prevalence We're Addressing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Girls We Aim to Support</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">6</div>
                    <div class="stat-label">Key Program Areas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">1</div>
                    <div class="stat-label">Community Transformed</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Contact Information</h3>
                <p>📍 Wang'chieng Ward, Homa Bay County, Kenya</p>
                <p>📞 Phone: +254 723 695 920</p>
                <p>✉️ Email: janalisuempowerment@gmail.com</p>
                <p>🌐 Website: www.janalisu.org</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="#about">About Us</a>
                <a href="events.php">Events</a>
                <a href="donate.php">Donate</a>
                <a href="login.php">Login</a>
            </div>
            <div class="footer-section">
                <h3>Our Programs</h3>
                <p>Education Sponsorship</p>
                <p>Girls Football Training</p>
                <p>Mentorship Programs</p>
                <p>HIV/AIDS Sensitization</p>
                <p>Community Outreach</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <a href="https://www.facebook.com/profile.php?id=61576991230740">Facebook</a>
                
                <p>Join our community and stay updated on our impact!</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 JANALISU. All rights reserved. Empowering girls, transforming communities. <p>metatechsolutions.com</p></p>
           
        </div>
    </footer>

    <script>
        // Mobile menu toggle - FIXED VERSION
const mobileMenu = document.querySelector('.mobile-menu');
const navMenu = document.querySelector('.nav-menu');
const bars = document.querySelectorAll('.bar');

mobileMenu.addEventListener('click', () => {
    // Toggle the active class on both elements
    navMenu.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    
    // Animate hamburger bars
    if (navMenu.classList.contains('active')) {
        // Transform bars into X
        bars[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        bars[1].style.opacity = '0';
        bars[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
    } else {
        // Reset bars to hamburger
        bars[0].style.transform = 'none';
        bars[1].style.opacity = '1';
        bars[2].style.transform = 'none';
    }
});

// Close mobile menu when clicking on navigation links
document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        mobileMenu.classList.remove('active');
        
        // Reset hamburger bars
        bars[0].style.transform = 'none';
        bars[1].style.opacity = '1';
        bars[2].style.transform = 'none';
    });
});

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
    if (!mobileMenu.contains(e.target) && !navMenu.contains(e.target)) {
        navMenu.classList.remove('active');
        mobileMenu.classList.remove('active');
        
        // Reset hamburger bars
        bars[0].style.transform = 'none';
        bars[1].style.opacity = '1';
        bars[2].style.transform = 'none';
    }
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
        mobileMenu.classList.remove('active');
        
        // Reset hamburger bars
        bars[0].style.transform = 'none';
        bars[1].style.opacity = '1';
        bars[2].style.transform = 'none';
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

// Rotating Images Functionality
const images = document.querySelectorAll('.rotating-image');
const imageTitle = document.getElementById('image-title');
const imageDescription = document.getElementById('image-description');

const imageData = [
    {
        title: "Empowering Through Sports",
        description: "Building confidence and skills through football"
    },
    {
        title: "Education First",
        description: "Creating pathways to academic success"
    },
    {
        title: "Community Engagement",
        description: "Bringing families and communities together"
    },
    {
        title: "Mentorship Programs",
        description: "Guiding girls toward brighter futures"
    }
];

let currentImageIndex = 0;

function rotateImages() {
    // Remove active class from current image
    images[currentImageIndex].classList.remove('active');
    
    // Move to next image
    currentImageIndex = (currentImageIndex + 1) % images.length;
    
    // Add active class to new image
    images[currentImageIndex].classList.add('active');
    
    // Update overlay text
    if (imageTitle && imageDescription) {
        imageTitle.textContent = imageData[currentImageIndex].title;
        imageDescription.textContent = imageData[currentImageIndex].description;
    }
}

// Start image rotation
setInterval(rotateImages, 4000); // Change image every 4 seconds

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

// Observe elements for animation
document.querySelectorAll('.objective-card, .stat-card, .vm-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
});

// Counter animation for stats
const animateCounters = () => {
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const target = counter.innerText;
        const isPercentage = target.includes('%');
        const isPlus = target.includes('+');
        const numericValue = parseInt(target.replace(/[^\d]/g, ''));
        
        let current = 0;
        const increment = numericValue / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= numericValue) {
                current = numericValue;
                clearInterval(timer);
            }
            
            let displayValue = Math.floor(current);
            if (isPercentage) displayValue += '%';
            if (isPlus && current >= numericValue) displayValue += '+';
            
            counter.innerText = displayValue;
        }, 30);
    });
};

// Trigger counter animation when stats section is visible
const statsSection = document.querySelector('.stats');
if (statsSection) {
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    statsObserver.observe(statsSection);
}

// Add parallax effect to hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    const heroContent = document.querySelector('.hero-content');
    
    if (hero && heroContent) {
        heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
        hero.style.transform = `translateY(${scrolled * 0.3}px)`;
    }
});

// Add hover effects to cards
document.querySelectorAll('.objective-card, .vm-card, .stat-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = this.style.transform.replace('translateY(0px)', '') + ' translateY(-10px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = this.style.transform.replace('translateY(-10px)', 'translateY(0px)');
    });
});

// Add loading animation
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
    document.body.style.transition = 'opacity 0.5s ease-in';
});

// Initialize body opacity
document.body.style.opacity = '0';
          </script>
</body>
</html>