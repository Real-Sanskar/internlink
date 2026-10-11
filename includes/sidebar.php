<?php
// This file only contains the sidebar
$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = $_SESSION['role'] ?? '';
?>

<aside class="sidebar" id="sidebar">
    
    <!-- Brand Logo -->
    <div class="sidebar-brand">
        <h4>
            <i class="bi bi-link-45deg"></i>
            Intern<span>Link</span>
        </h4>

        <!-- Close button → only visible on mobile -->
        <button class="sidebar-close d-lg-none" 
                onclick="toggleSidebar()">
            <i class="bi bi-x-lg"></i>
        </button>

        <small style="color:rgba(255,255,255,0.4); font-size:0.7rem;">
            <?= ucfirst($userRole) ?> Panel
        </small>
    </div>

    <ul class="sidebar-menu">

        <?php if ($userRole === 'student'): ?>
        <!-- STUDENT MENU -->
        <li class="sidebar-label">Main</li>
        <li>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-divider"></li>
        <li class="sidebar-label">Internships</li>
        
        <li>
            <a href="browse.php" class="<?= $currentPage === 'browse.php' ? 'active' : '' ?>">
                <i class="bi bi-search"></i>
                <span>Browse Listings</span>
            </a>
        </li>
        <li>
            <a href="my-applications.php" class="<?= $currentPage === 'my-applications.php' ? 'active' : '' ?>">
                <i class="bi bi-send"></i>
                <span>My Applications</span>
            </a>
        </li>
        <li>
            <a href="bookmarks.php" class="<?= $currentPage === 'bookmarks.php' ? 'active' : '' ?>">
                <i class="bi bi-bookmark"></i>
                <span>Saved Listings</span>
            </a>
        </li>

        <li class="sidebar-divider"></li>
        <li class="sidebar-label">Account</li>
        
        <li>
            <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
            </a>
        </li>
        <li>
            <a href="certificates.php" class="<?= $currentPage === 'certificates.php' ? 'active' : '' ?>">
                <i class="bi bi-award"></i>
                <span>Certificates</span>
            </a>
        </li>
        <li>
            <a href="reviews.php" class="<?= $currentPage === 'reviews.php' ? 'active' : '' ?>">
                <i class="bi bi-star"></i>
                <span>My Reviews</span>
            </a>
        </li>

        <?php elseif ($userRole === 'company'): ?>
        <!-- COMPANY MENU -->
        <li class="sidebar-label">Main</li>
        <li>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-divider"></li>
        <li class="sidebar-label">Listings</li>
        
        <li>
            <a href="post-listing.php" class="<?= $currentPage === 'post-listing.php' ? 'active' : '' ?>">
                <i class="bi bi-plus-circle"></i>
                <span>Post Listing</span>
            </a>
        </li>
        <li>
            <a href="my-listings.php" class="<?= $currentPage === 'my-listings.php' ? 'active' : '' ?>">
                <i class="bi bi-briefcase"></i>
                <span>My Listings</span>
            </a>
        </li>
        <li>
            <a href="applicants.php" class="<?= $currentPage === 'applicants.php' ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Applicants</span>
            </a>
        </li>

        <li class="sidebar-divider"></li>
        <li class="sidebar-label">Account</li>
        
        <li>
            <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                <i class="bi bi-building"></i>
                <span>Company Profile</span>
            </a>
        </li>
        <li>
            <a href="issue-certificate.php" class="<?= $currentPage === 'issue-certificate.php' ? 'active' : '' ?>">
                <i class="bi bi-award"></i>
                <span>Issue Certificates</span>
            </a>
        </li>

        <?php elseif ($userRole === 'admin'): ?>
        <!-- ADMIN MENU -->
        <li class="sidebar-label">Main</li>
        <li>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-divider"></li>
        <li class="sidebar-label">Management</li>
        
        <li>
            <a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Manage Users</span>
            </a>
        </li>
        <li>
            <a href="companies.php" class="<?= $currentPage === 'companies.php' ? 'active' : '' ?>">
                <i class="bi bi-building"></i>
                <span>Companies</span>
            </a>
        </li>
        <li>
            <a href="listings.php" class="<?= $currentPage === 'listings.php' ? 'active' : '' ?>">
                <i class="bi bi-briefcase"></i>
                <span>All Listings</span>
            </a>
        </li>
        <li>
            <a href="applications.php" class="<?= $currentPage === 'applications.php' ? 'active' : '' ?>">
                <i class="bi bi-send"></i>
                <span>Applications</span>
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?= $currentPage === 'categories.php' ? 'active' : '' ?>">
                <i class="bi bi-tag"></i>
                <span>Categories</span>
            </a>
        </li>

        <li class="sidebar-divider"></li>
        <li class="sidebar-label">Tools</li>
        
        <li>
            <a href="announcements.php" class="<?= $currentPage === 'announcements.php' ? 'active' : '' ?>">
                <i class="bi bi-megaphone"></i>
                <span>Announcements</span>
            </a>
        </li>
        <li>
            <a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i>
                <span>Reports</span>
            </a>
        </li>

        <?php endif; ?>

        <!-- LOGOUT -->
        <li class="sidebar-divider"></li>
        <li>
            <a href="../auth/logout.php" class="logout-link">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>
</aside>