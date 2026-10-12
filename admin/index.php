<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requireRole('admin');

// Overall stats
$stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'student') as students,
        (SELECT COUNT(*) FROM users WHERE role = 'company') as companies,
        (SELECT COUNT(*) FROM company_profiles WHERE status = 'pending') as pending_companies,
        (SELECT COUNT(*) FROM listings) as total_listings,
        (SELECT COUNT(*) FROM listings WHERE status = 'active') as active_listings,
        (SELECT COUNT(*) FROM applications) as total_applications,
        (SELECT COUNT(*) FROM users WHERE status = 'banned') as banned_users,
        (SELECT COUNT(*) FROM categories) as categories
")->fetch_assoc();

// Pending companies for approval
$pendingCompanies = $conn->query("
    SELECT cp.*, u.email, u.name as contact_name, u.created_at as registered_at
    FROM company_profiles cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.status = 'pending'
    ORDER BY cp.created_at DESC
    LIMIT 5
");

// Recent users
$recentUsers = $conn->query("
    SELECT id, name, email, role, status, created_at
    FROM users
    WHERE role != 'admin'
    ORDER BY created_at DESC
    LIMIT 5
");

// Recent applications
$recentApps = $conn->query("
    SELECT a.status, a.applied_at, l.title, 
           u.name as student_name, cp.company_name
    FROM applications a
    JOIN listings l ON a.listing_id = l.id
    JOIN student_profiles sp ON a.student_id = sp.id
    JOIN users u ON sp.user_id = u.id
    JOIN company_profiles cp ON l.company_id = cp.id
    ORDER BY a.applied_at DESC
    LIMIT 5
");

// Applications by status for simple breakdown
$appBreakdown = $conn->query("
    SELECT status, COUNT(*) as count
    FROM applications
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

// Convert to associative
$appByStatus = [];
foreach ($appBreakdown as $row) {
    $appByStatus[$row['status']] = $row['count'];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/../includes/topbar.php'; ?>

        <div class="page-content">

            <?php showFlash(); ?>

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h4>
                    <i class="bi bi-shield-check me-2"></i>
                    Admin Dashboard
                </h4>
                <p>
                    Manage users, approve companies, 
                    monitor listings and applications.
                </p>
            </div>

            <!-- Stat Cards Row 1 -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card primary">
                        <div class="stat-icon primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value">
                            <?= $stats['students'] ?>
                        </div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-icon success">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="stat-value">
                            <?= $stats['companies'] ?>
                        </div>
                        <div class="stat-label">Companies</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card warning">
                        <div class="stat-icon warning">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <div class="stat-value">
                            <?= $stats['active_listings'] ?>
                        </div>
                        <div class="stat-label">Active Listings</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card danger">
                        <div class="stat-icon danger">
                            <i class="bi bi-send"></i>
                        </div>
                        <div class="stat-value">
                            <?= $stats['total_applications'] ?>
                        </div>
                        <div class="stat-label">Applications</div>
                    </div>
                </div>
            </div>

            <!-- Pending Alert -->
            <?php if ($stats['pending_companies'] > 0): ?>
            <div class="alert alert-warning d-flex align-items-center 
                        justify-content-between">
                <div>
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong><?= $stats['pending_companies'] ?></strong> 
                    company account(s) waiting for approval
                </div>
                <a href="companies.php" class="btn btn-sm btn-warning">
                    Review Now
                </a>
            </div>
            <?php endif; ?>

            <div class="row g-4">

                <!-- Pending Companies -->
                <div class="col-lg-8">
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-hourglass-split"></i>
                            Pending Company Approvals
                        </div>

                        <?php if ($pendingCompanies->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($co = $pendingCompanies->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($co['company_name']) ?>
                                            </strong>
                                            <?php if (!empty($co['industry'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($co['industry']) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($co['contact_name']) ?></td>
                                        <td>
                                            <small><?= htmlspecialchars($co['email']) ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= timeAgo($co['registered_at']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="companies.php" 
                                               class="btn btn-sm btn-apply">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <h5>All Caught Up!</h5>
                            <p>No companies pending approval</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Applications -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-send"></i>
                            Recent Applications
                        </div>

                        <?php if ($recentApps->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Listing</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($app = $recentApps->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['student_name']) ?></td>
                                        <td><?= htmlspecialchars($app['title']) ?></td>
                                        <td><?= htmlspecialchars($app['company_name']) ?></td>
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
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5>No Applications Yet</h5>
                            <p>Applications will appear here</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">

                    <!-- Quick Actions -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-lightning"></i>
                            Quick Actions
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <a href="companies.php" class="quick-action-btn">
                                <i class="bi bi-building"
                                   style="background:var(--primary-light);color:var(--primary)"></i>
                                <div>
                                    <strong>Manage Companies</strong>
                                    <small class="d-block text-muted">
                                        <?= $stats['pending_companies'] ?> pending
                                    </small>
                                </div>
                            </a>
                            <a href="users.php" class="quick-action-btn">
                                <i class="bi bi-people"
                                   style="background:#D1FAE5;color:var(--success)"></i>
                                <div>
                                    <strong>Manage Users</strong>
                                    <small class="d-block text-muted">
                                        <?= $stats['students'] + $stats['companies'] ?> total
                                    </small>
                                </div>
                            </a>
                            <a href="listings.php" class="quick-action-btn">
                                <i class="bi bi-briefcase"
                                   style="background:#FEF3C7;color:var(--warning)"></i>
                                <div>
                                    <strong>All Listings</strong>
                                    <small class="d-block text-muted">
                                        <?= $stats['total_listings'] ?> total
                                    </small>
                                </div>
                            </a>
                            <a href="categories.php" class="quick-action-btn">
                                <i class="bi bi-tag"
                                   style="background:#FEE2E2;color:var(--danger)"></i>
                                <div>
                                    <strong>Categories</strong>
                                    <small class="d-block text-muted">
                                        <?= $stats['categories'] ?> categories
                                    </small>
                                </div>
                            </a>
                            <a href="announcements.php" class="quick-action-btn">
                                <i class="bi bi-megaphone"
                                   style="background:#CFFAFE;color:#0891B2"></i>
                                <div>
                                    <strong>Announcements</strong>
                                    <small class="d-block text-muted">
                                        Post system notices
                                    </small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Users -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-person-plus"></i>
                            Recent Users
                        </div>

                        <?php if ($recentUsers->num_rows > 0): ?>
                            <?php while ($user = $recentUsers->fetch_assoc()): ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="user-avatar" style="width:36px;height:36px;font-size:0.8rem;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <strong style="font-size:0.9rem;">
                                        <?= htmlspecialchars($user['name']) ?>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= ucfirst($user['role']) ?> · 
                                        <?= timeAgo($user['created_at']) ?>
                                    </small>
                                </div>
                                <span class="status-badge status-<?= $user['status'] === 'active' ? 'active' : ($user['status'] === 'banned' ? 'rejected' : 'pending') ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No users yet</p>
                        <?php endif; ?>
                    </div>

                    <!-- Platform Summary -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="bi bi-bar-chart"></i>
                            Platform Summary
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total Listings</span>
                                <strong><?= $stats['total_listings'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Banned Users</span>
                                <strong><?= $stats['banned_users'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Categories</span>
                                <strong><?= $stats['categories'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Pending Apps</span>
                                <strong><?= $appByStatus['pending'] ?? 0 ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>