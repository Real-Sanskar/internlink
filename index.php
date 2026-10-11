<?php

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if(isLoggedIn()){
    redirectToDashboard();
}


$statsQuery = $conn->query("SELECT
    (SELECT COUNT(*) FROM users WHERE role='student') as students,
    (SELECT COUNT(*) FROM users WHERE role='company') as companies,
    (SELECT COUNT(*) FROM listings WHERE status='actvie') as listings,
    (SELECT COUNT(*) FROM applications) as applications
");

$stats = $statsQuery->fetch_assoc();


$latestListings = $conn->query("
    SELECT l.*, cp.company_name, cp.logo, c.name as category_name
    FROM listings l
    JOIN company_profiles cp ON l.company_id = cp.id
    JOIN categories c ON l.category_id = c.id
    WHERE l.status = 'active' AND l.deadline >= CURDATE()
    ORDER BY l.created_at DESC  
    LIMIT 6
");

$pageTitle = 'Welcome';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>InternLink — Internship & Vocational Training Portal</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
            rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" 
            rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" 
            rel="stylesheet">
        <!-- Custom CSS -->
        <link href="assets/css/style.css" rel="stylesheet">
        <link href="assets/css/landing.css" rel="stylesheet">
    </head>
    <body>
        <!-- ============================================
            NAVIGATION BAR
            ============================================ -->
        <nav class="navbar navbar-expand-lg landing-navbar fixed-top">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand" href="index.php">
                    <i class="bi bi-link-45deg"></i>
                    Intern<span>Link</span>
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Nav Links -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center gap-2">
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#how-it-works">How It Works</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#listings">Listings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">
                                Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-landing-primary" 
                            href="auth/register.php">
                                Get Started
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>


        <section class="hero-section">
            <div class="hero-bg-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <span class="hero-badge">
                                <i class="bi bi-stars me-1"></i>
                                #1 Internship Portal
                            </span>
                            <h1  class="hero-title">
                                Find Your Perfect
                                <span class="gradient-text">
                                    Internship
                                </span>
                                & Training
                            </h1>
                            <p class="hero-subtitle">
                                Connect with top companies, apply for 
                                internships and vocational training 
                                programs. Build your career with 
                                InternLink.
                            </p>
                            <div class="hero-buttons">
                                <a href="auth/register.php" 
                                class="btn btn-hero-primary">
                                    <i class="bi bi-rocket-takeoff me-2"></i>
                                    Get Started Free
                                </a>
                                <a href="#how-it-works" 
                                class="btn btn-hero-outline">
                                    <i class="bi bi-play-circle me-2"></i>
                                    How It Works
                                </a>
                            </div>
                            <div class="hero-stats-mini">
                                <div class="mini-stat">
                                    <strong><?= $stats['students'] ?? 0 ?>+</strong>
                                    <span>Students</span>
                                </div>
                                <div class="mini-stat-divider"></div>
                                <div class="mini-stat">
                                    <strong><?= $stats['companies'] ?? 0 ?>+</strong>
                                    <span>Companies</span>
                                </div>
                                <div class="mini-stat-divider"></div>
                                <div class="mini-stat">
                                    <strong><?= $stats['listings'] ?? 0 ?>+</strong>
                                    <span>Opportunities</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="hero-illustration">
                            <div class="hero-card card-1">
                                <i class="bi bi-briefcase"></i>
                                <span>Web Developer Intern</span>
                                <small>Tech Corp</small>
                            </div>
                            <div class="hero-card card-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Application Accepted!</span>
                            </div>
                            <div class="hero-card card-3">
                                <i class="bi bi-building"></i>
                                <span>50+ Companies</span>
                                <small>Hiring Now</small>
                            </div>
                            <div class="hero-circle"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================
            STATS COUNTER SECTION
            ============================================ -->
        <section class="stats-section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <div class="counter-card">
                            <div class="counter-icon primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="counter-value" data-target="<?= $stats['students'] ?? 0 ?>">
                                0
                            </h3>
                            <p class="counter-label">Registered Students</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="counter-card">
                            <div class="counter-icon success">
                                <i class="bi bi-building"></i>
                            </div>
                            <h3 class="counter-value" data-target="<?= $stats['companies'] ?? 0 ?>">
                                0
                            </h3>
                            <p class="counter-label">Partner Companies</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="counter-card">
                            <div class="counter-icon warning">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <h3 class="counter-value" data-target="<?= $stats['listings'] ?? 0 ?>">
                                0
                            </h3>
                            <p class="counter-label">Active Listings</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="counter-card">
                            <div class="counter-icon danger">
                                <i class="bi bi-send"></i>
                            </div>
                            <h3 class="counter-value" data-target="<?= $stats['applications'] ?? 0 ?>">
                                0
                            </h3>
                            <p class="counter-label">Applications Sent</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- ============================================
            HOW IT WORKS
            ============================================ -->
        <section class="how-it-works-section" id="how-it-works">
            <div class="container">
                <div class="section-header text-center">
                    <span class="section-badge">Simple Process</span>
                    <h2 class="section-heading">How InternLink Works</h2>
                    <p class="section-subheading">
                        Get started in 3 simple steps
                    </p>
                </div>

                <div class="row g-4 mt-4">
                    <!-- Step 1 -->
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <div class="step-icon">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <h5>Create Account</h5>
                            <p>Register as a student or company. 
                            Set up your profile with skills 
                            and preferences.</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="step-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h5>Browse & Apply</h5>
                            <p>Explore internships and training 
                            programs. Apply with one click 
                            and track your applications.</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <div class="step-icon">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <h5>Get Selected</h5>
                            <p>Companies review your profile. 
                            Get accepted, complete your 
                            training and earn certificates.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- ============================================
            FEATURES SECTION
            ============================================ -->
        <section class="features-section" id="features">
            <div class="container">
                <div class="section-header text-center">
                    <span class="section-badge">Why Choose Us</span>
                    <h2 class="section-heading">
                        Everything You Need
                    </h2>
                    <p class="section-subheading">
                        Powerful features for students and companies
                    </p>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-md-4 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon primary">
                                <i class="bi bi-search-heart"></i>
                            </div>
                            <h5>Smart Search</h5>
                            <p>Filter by category, location, 
                            duration and stipend</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon success">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h5>Verified Companies</h5>
                            <p>All companies are verified by 
                            admin before posting</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon warning">
                                <i class="bi bi-bell"></i>
                            </div>
                            <h5>Instant Notifications</h5>
                            <p>Get notified when your application 
                            status changes</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon danger">
                                <i class="bi bi-bookmark-star"></i>
                            </div>
                            <h5>Save Listings</h5>
                            <p>Bookmark interesting opportunities 
                            and apply later</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon info">
                                <i class="bi bi-award"></i>
                            </div>
                            <h5>Certificates</h5>
                            <p>Earn certificates after completing 
                            your internship</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon purple">
                                <i class="bi bi-star"></i>
                            </div>
                            <h5>Reviews & Ratings</h5>
                            <p>Rate companies and read reviews 
                            from other students</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>




        <!-- ============================================
            LATEST LISTINGS PREVIEW
            ============================================ -->
        <section class="listings-preview-section" id="listings">
            <div class="container">
                <div class="section-header text-center">
                    <span class="section-badge">Opportunities</span>
                    <h2 class="section-heading">Latest Listings</h2>
                    <p class="section-subheading">
                        Explore recent internship and training openings
                    </p>
                </div>

                <div class="row g-4 mt-4">
                    <?php if ($latestListings && $latestListings->num_rows > 0): ?>
                        <?php while($listing = $latestListings->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="listing-card">
                                <div class="d-flex justify-content-between 
                                            align-items-start mb-3">
                                    <span class="listing-badge 
                                        <?= $listing['type'] === 'internship' 
                                            ? 'badge-internship' 
                                            : 'badge-vocational' ?>">
                                        <?= ucfirst(str_replace('_', ' ', 
                                            $listing['type'])) ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= timeAgo($listing['created_at']) ?>
                                    </small>
                                </div>

                                <h5 class="listing-title">
                                    <?= htmlspecialchars($listing['title']) ?>
                                </h5>
                                <p class="listing-company">
                                    <i class="bi bi-building me-1"></i>
                                    <?= htmlspecialchars(
                                        $listing['company_name']) ?>
                                </p>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="listing-meta">
                                            <i class="bi bi-geo-alt"></i>
                                            <span><?= htmlspecialchars(
                                                $listing['location'] ?? 'Remote'
                                            ) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="listing-meta">
                                            <i class="bi bi-clock"></i>
                                            <span><?= htmlspecialchars(
                                                $listing['duration'] ?? 'N/A'
                                            ) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="listing-meta">
                                            <i class="bi bi-cash"></i>
                                            <span>
                                                <?= $listing['stipend_type'] === 'paid' 
                                                    ? '₹' . number_format($listing['stipend_amount']) 
                                                    : 'Unpaid' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="listing-meta">
                                            <i class="bi bi-tag"></i>
                                            <span><?= htmlspecialchars(
                                                $listing['category_name']
                                            ) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <a href="auth/register.php" 
                                class="btn btn-apply w-100">
                                    Apply Now
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-inbox" 
                            style="font-size:3rem; 
                                    color:var(--muted)"></i>
                            <p class="text-muted mt-3">
                                No listings available yet. 
                                Check back soon!
                            </p>
                            <a href="auth/register.php" 
                            class="btn btn-landing-primary mt-2">
                                Register to Get Notified
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($latestListings && $latestListings->num_rows > 0): ?>
                <div class="text-center mt-5">
                    <a href="auth/register.php" 
                    class="btn btn-hero-outline">
                        View All Listings
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </section>


        <!-- ============================================
            CTA SECTION
            ============================================ -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-card">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2 class="cta-title">
                                Ready to Start Your Journey?
                            </h2>
                            <p class="cta-text">
                                Join thousands of students and companies
                                already using InternLink.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="auth/register.php" 
                            class="btn btn-cta">
                                Get Started Now
                                <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- ============================================
            FOOTER
            ============================================ -->
        <footer class="landing-footer">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="footer-brand">
                            <h4>
                                <i class="bi bi-link-45deg"></i>
                                Intern<span>Link</span>
                            </h4>
                            <p>Connecting students with the best 
                            internship and vocational training 
                            opportunities.</p>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <h6 class="footer-heading">Quick Links</h6>
                        <ul class="footer-links">
                            <li><a href="#features">Features</a></li>
                            <li><a href="#how-it-works">How It Works</a></li>
                            <li><a href="#listings">Listings</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <h6 class="footer-heading">For Students</h6>
                        <ul class="footer-links">
                            <li><a href="auth/register.php">Register</a></li>
                            <li><a href="auth/login.php">Login</a></li>
                            <li><a href="#">Browse Jobs</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <h6 class="footer-heading">For Companies</h6>
                        <ul class="footer-links">
                            <li><a href="auth/register.php">Register</a></li>
                            <li><a href="auth/login.php">Login</a></li>
                            <li><a href="#">Post Listing</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2">
                        <h6 class="footer-heading">Contact</h6>
                        <ul class="footer-links">
                            <li>
                                <i class="bi bi-envelope me-2"></i>
                                info@internlink.com
                            </li>
                            <li>
                                <i class="bi bi-telephone me-2"></i>
                                +91 98765 43210
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> InternLink. 
                    All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Custom JS -->
        <script src="assets/js/main.js"></script>
        <script src="assets/js/landing.js"></script>
    </body>
</html>