<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requireRole('student');

$userId = $_SESSION['user_id'];

// Get student profile
$stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$studentId = $profile['id'] ?? 0;

// Calculate profile completion
$profileCompletion = calcProfileCompletion($profile);

// Get application stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted
    FROM applications WHERE student_id = ?
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$appStats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get bookmark count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookmarks WHERE student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$bookmarkCount = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get recent applications
$stmt = $conn->prepare("
    SELECT a.*, l.title, l.type, cp.company_name
    FROM applications a
    JOIN listings l ON a.listing_id = l.id
    JOIN company_profiles cp ON l.company_id = cp.id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$recentApps = $stmt->get_result();
$stmt->close();

// Get active listings count
$activeListings = $conn->query("
    SELECT COUNT(*) as count FROM listings 
    WHERE status = 'active' AND deadline >= CURDATE()
")->fetch_assoc()['count'];

include __DIR__ . '/../includes/header.php';
?>


<div class="dashboard-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Navbar -->
        <?php include __DIR__ . '/../includes/topbar.php'; ?>

        <div class="page-content">
            <?php showFlash(); ?>

            <div class="welcome-banner">
                <h4>
                    <i class="bi bi-hand-wave me-2"></i>
                    Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>
                </h4>
                <p>
                    <?php if($profileCompletion < 100): ?>
                        Complete your profile to increase your chances of getting selected.
                    <?php else: ?>
                        Your profile is complete. Start applying for internships!
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($profileCompletion < 100): ?>
            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-600" style="font-size: 0.9rem">
                        Profile Completion
                    </span>
                    <span class="fw-600" style="color: var(--primary)">
                        <?= $profileCompletion ?>%
                    </span>
                </div>
                <div class="completion-bar">
                    <div class="completion-fill"
                        style="width: <?= $profileCompletion ?>%">
                    </div>
                </div>
                <a href="profile.php" class="btn btn-sm mt-2"
                    style="background-color:var(--primary-light);
                            color:var(--primary); font-size: 0.8rem;">
                    <i class="bi bi-pencil me-1"></i>
                    Complete Profile
                </a>
            </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card primary">
                        <div class="stat-icon primary">
                            <i class="bi bi-send"></i>
                        </div>
                        <div class="stat-value">
                            <?= $appStats['total'] ?? 0 ?>
                        </div>
                        <div class="stat-label">
                            Total Applications
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card warning">
                        <div class="stat-icon warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-value">
                            <?= $appStats['pending'] ?? 0 ?>
                        </div>
                        <div class="stat-label">
                            Pending
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-icon success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-value">
                            <?= $appStats['accepted'] ?? 0 ?>
                        </div>
                        <div class="stat-label">
                            Accepted
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-icon danger">
                            <i class="bi bi-bookmark-star"></i>
                        </div>
                        <div class="stat-value">
                            <?= $bookmarkCount ?>
                        </div>
                        <div class="stat-label">
                            Saved Listings
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="row g-4">
                
                <!-- Recent Applications -->
                <div class="col-lg-8">
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-clock-history"></i>
                            Recent Applications
                        </div>

                        <?php if ($recentApps->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Listing</th>
                                        <th>Company</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($app = $recentApps->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($app['title']) ?>
                                            </strong>
                                        </td>
                                        <td><?= htmlspecialchars($app['company_name']) ?></td>
                                        <td>
                                            <span class="listing-badge <?= $app['type'] === 'internship' 
                                                ? 'badge-internship' 
                                                : 'badge-vocational' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $app['type'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $app['status'] ?>">
                                                <?= ucfirst($app['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= timeAgo($app['applied_at']) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="my-applications.php" 
                               style="color:var(--primary); 
                                      font-size:0.9rem; 
                                      font-weight:600;">
                                View All Applications 
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5>No Applications Yet</h5>
                            <p>Start browsing and apply for internships</p>
                            <a href="browse.php" class="btn btn-apply mt-2">
                                Browse Listings
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-lightning"></i>
                            Quick Actions
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <a href="browse.php" class="quick-action-btn">
                                <i class="bi bi-search" 
                                   style="background:var(--primary-light); 
                                          color:var(--primary)"></i>
                                <div>
                                    <strong>Browse Listings</strong>
                                    <small class="d-block text-muted">
                                        <?= $activeListings ?> active opportunities
                                    </small>
                                </div>
                            </a>

                            <a href="profile.php" class="quick-action-btn">
                                <i class="bi bi-person" 
                                   style="background:#D1FAE5; 
                                          color:var(--success)"></i>
                                <div>
                                    <strong>Edit Profile</strong>
                                    <small class="d-block text-muted">
                                        <?= $profileCompletion ?>% complete
                                    </small>
                                </div>
                            </a>

                            <a href="bookmarks.php" class="quick-action-btn">
                                <i class="bi bi-bookmark" 
                                   style="background:#FEF3C7; 
                                          color:var(--warning)"></i>
                                <div>
                                    <strong>Saved Listings</strong>
                                    <small class="d-block text-muted">
                                        <?= $bookmarkCount ?> saved
                                    </small>
                                </div>
                            </a>

                            <a href="certificates.php" class="quick-action-btn">
                                <i class="bi bi-award" 
                                   style="background:#FEE2E2; 
                                          color:var(--danger)"></i>
                                <div>
                                    <strong>My Certificates</strong>
                                    <small class="d-block text-muted">
                                        View earned certificates
                                    </small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Active Listings Count -->
                    <div class="section-card text-center">
                        <i class="bi bi-briefcase" 
                           style="font-size:2rem; color:var(--primary)">
                        </i>
                        <h3 class="mt-2 mb-1" 
                            style="font-weight:700; color:var(--dark)">
                            <?= $activeListings ?>
                        </h3>
                        <p class="text-muted mb-2" 
                           style="font-size:0.9rem;">
                            Active Opportunities
                        </p>
                        <a href="browse.php" class="btn btn-apply btn-sm">
                            Explore Now
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>




<?php include __DIR__ . '/../includes/footer.php'; ?>