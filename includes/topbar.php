<?php
require_once __DIR__ . '/../config/db.php';

$unreadNotifs = getUnreadCount($conn);
$userInitial = strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1));
$userName = $_SESSION['name'] ?? 'User';
$userRole = $_SESSION['role'] ?? '';
?>

<nav class="top-navbar">
    
    <!-- Left → Mobile toggle + Page title -->
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle d-lg-none" 
                onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        
        <h5 class="page-title">
            <?= $pageTitle ?? 'Dashboard' ?>
        </h5>
    </div>

    <!-- Right → Notifications + User -->
    <div class="navbar-right">
        
        <!-- Notification Bell -->
        <div class="dropdown">
            <button class="notif-btn" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if ($unreadNotifs > 0): ?>
                    <span class="notif-badge"><?= $unreadNotifs ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                <div class="notif-header">
                    <h6>Notifications</h6>
                    <?php if ($unreadNotifs > 0): ?>
                        <small><?= $unreadNotifs ?> new</small>
                    <?php endif; ?>
                </div>
                <div class="notif-body">
                    <p class="text-center text-muted py-3 mb-0">
                        <i class="bi bi-bell-slash"></i><br>
                        No notifications yet
                    </p>
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <div class="user-menu" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <?= $userInitial ?>
                </div>
                <div class="d-none d-md-block">
                    <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                    <small class="user-role"><?= ucfirst($userRole) ?></small>
                </div>
                <i class="bi bi-chevron-down ms-1" 
                   style="font-size:0.7rem;color:var(--muted)"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" 
                       href="../auth/logout.php">
                        <i class="bi bi-box-arrow-left me-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>