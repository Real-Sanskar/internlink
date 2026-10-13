<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requireRole('company');

$userId = $_SESSION['user_id'];

// Get company profile
$stmt = $conn->prepare(
    "SELECT * FROM company_profiles WHERE user_id = ?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

$companyId = $company['id'] ?? 0;
$companyStatus = $company['status'] ?? 'pending';

// Listing stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
    FROM listings WHERE company_id = ?
");
$stmt->bind_param("i", $companyId);
$stmt->execute();
$listingStats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Application stats for this company's listings
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
        SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END) as accepted
    FROM applications a
    JOIN listings l ON a.listing_id = l.id
    WHERE l.company_id = ?
");
$stmt->bind_param("i", $companyId);
$stmt->execute();
$appStats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Total views on listings
$stmt = $conn->prepare(
    "SELECT COALESCE(SUM(views), 0) as total_views 
     FROM listings WHERE company_id = ?"
);
$stmt->bind_param("i", $companyId);
$stmt->execute();
$totalViews = $stmt->get_result()->fetch_assoc()['total_views'];
$stmt->close();

// Recent applications
$stmt = $conn->prepare("
    SELECT a.*, l.title, u.name as student_name, sp.education
    FROM applications a
    JOIN listings l ON a.listing_id = l.id
    JOIN student_profiles sp ON a.student_id = sp.id
    JOIN users u ON sp.user_id = u.id
    WHERE l.company_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $companyId);
$stmt->execute();
$recentApps = $stmt->get_result();
$stmt->close();

// Recent listings
$stmt = $conn->prepare("
    SELECT l.*, c.name as category_name,
           (SELECT COUNT(*) FROM applications WHERE listing_id = l.id) as app_count
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.company_id = ?
    ORDER BY l.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $companyId);
$stmt->execute();
$recentListings = $stmt->get_result();
$stmt->close();

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/../includes/topbar.php'; ?>

        <div class="page-content">

            <?php showFlash(); ?>

            <!-- Company Pending Warning -->
            <?php if ($companyStatus === 'pending'): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-split"></i>
                Your company account is pending admin approval. 
                You can set up your profile but cannot post listings yet.
            </div>
            <?php elseif ($companyStatus === 'rejected'): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-x-circle"></i>
                Your company account was rejected.
                <?php if (!empty($company['rejection_reason'])): ?>
                    Reason: <?= htmlspecialchars($company['rejection_reason']) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h4>
                    <i class="bi bi-building me-2"></i>
                    Welcome, <?= htmlspecialchars(
                        $company['company_name'] ?? $_SESSION['name']
                    ) ?>!
                </h4>
                <p>
                    Manage your listings, review applicants, 
                    and grow your talent pipeline.
                </p>
            </div>

            <!-- Stat Cards -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card primary">
                        <div class="stat-icon primary">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <div class="stat-value">
                            <?= $listingStats['total'] ?? 0 ?>
                        </div>
                        <div class="stat-label">Total Listings</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-icon success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-value">
                            <?= $listingStats['active'] ?? 0 ?>
                        </div>
                        <div class="stat-label">Active Listings</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card warning">
                        <div class="stat-icon warning">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value">
                            <?= $appStats['total'] ?? 0 ?>
                        </div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-icon danger">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="stat-value">
                            <?= $totalViews ?>
                        </div>
                        <div class="stat-label">Total Views</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                <!-- Recent Applicants -->
                <div class="col-lg-8">
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-people"></i>
                            Recent Applicants
                        </div>

                        <?php if ($recentApps->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Listing</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($app = $recentApps->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($app['student_name']) ?>
                                            </strong>
                                            <?php if (!empty($app['education'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($app['education']) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($app['title']) ?></td>
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
                            <a href="applicants.php"
                               style="color:var(--primary);font-size:0.9rem;font-weight:600;">
                                View All Applicants
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5>No Applicants Yet</h5>
                            <p>Post a listing to start receiving applications</p>
                            <?php if ($companyStatus === 'approved'): ?>
                            <a href="post-listing.php" class="btn btn-apply mt-2">
                                Post Listing
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Listings -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-briefcase"></i>
                            Your Listings
                        </div>

                        <?php if ($recentListings->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Applicants</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($listing = $recentListings->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($listing['title']) ?>
                                            </strong>
                                            <br>
                                            <span class="listing-badge <?= $listing['type'] === 'internship' 
                                                ? 'badge-internship' 
                                                : 'badge-vocational' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $listing['type'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($listing['category_name']) ?></td>
                                        <td>
                                            <span class="fw-600">
                                                <?= $listing['app_count'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $listing['status'] ?>">
                                                <?= ucfirst($listing['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bi bi-eye me-1 text-muted"></i>
                                            <?= $listing['views'] ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="my-listings.php"
                               style="color:var(--primary);font-size:0.9rem;font-weight:600;">
                                Manage All Listings
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-briefcase"></i>
                            <h5>No Listings Yet</h5>
                            <p>Create your first internship or training listing</p>
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
                            <?php if ($companyStatus === 'approved'): ?>
                            <a href="post-listing.php" class="quick-action-btn">
                                <i class="bi bi-plus-circle"
                                   style="background:var(--primary-light);color:var(--primary)"></i>
                                <div>
                                    <strong>Post New Listing</strong>
                                    <small class="d-block text-muted">
                                        Create internship or training
                                    </small>
                                </div>
                            </a>
                            <?php endif; ?>

                            <a href="applicants.php" class="quick-action-btn">
                                <i class="bi bi-people"
                                   style="background:#D1FAE5;color:var(--success)"></i>
                                <div>
                                    <strong>Review Applicants</strong>
                                    <small class="d-block text-muted">
                                        <?= $appStats['pending'] ?? 0 ?> pending review
                                    </small>
                                </div>
                            </a>

                            <a href="my-listings.php" class="quick-action-btn">
                                <i class="bi bi-briefcase"
                                   style="background:#FEF3C7;color:var(--warning)"></i>
                                <div>
                                    <strong>Manage Listings</strong>
                                    <small class="d-block text-muted">
                                        <?= $listingStats['active'] ?? 0 ?> active
                                    </small>
                                </div>
                            </a>

                            <a href="profile.php" class="quick-action-btn">
                                <i class="bi bi-building"
                                   style="background:#FEE2E2;color:var(--danger)"></i>
                                <div>
                                    <strong>Company Profile</strong>
                                    <small class="d-block text-muted">
                                        Update company info
                                    </small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Application Breakdown -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-pie-chart"></i>
                            Applicants Overview
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Pending</span>
                                <span class="status-badge status-pending">
                                    <?= $appStats['pending'] ?? 0 ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Shortlisted</span>
                                <span class="status-badge status-shortlisted">
                                    <?= $appStats['shortlisted'] ?? 0 ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Accepted</span>
                                <span class="status-badge status-accepted">
                                    <?= $appStats['accepted'] ?? 0 ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>