<?php
/*
|------------------------------------------
| HELPER FUNCTIONS FILE
|------------------------------------------
| Reusable functions used across the project
| Include this file where needed
|------------------------------------------
*/

// =============================================
// SESSION FUNCTION
// =============================================

/*
Learn: We start session here once
and include this file everywhere.
This way we don't forget session_start()
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// REDIRECT FUNCTION
// =============================================

/*
Learn: Instead of writing header() everywhere
we make a simple function.
This is called DRY principle
(Don't Repeat Yourself)
*/
function redirect($url) {
    header("Location: " . $url);
    exit(); // always exit after redirect!
}

// =============================================
// CHECK IF USER IS LOGGED IN
// =============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// =============================================
// REQUIRE LOGIN
// =============================================

/*
Learn: Call this at top of any page that
requires user to be logged in.
If not logged in, redirects to login page.
*/
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../auth/login.php');
    }
}

// =============================================
// REQUIRE SPECIFIC ROLE
// =============================================

/*
Learn: Call this to restrict pages to
specific roles only.
Example: requireRole('admin') on admin pages
*/
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        // Wrong role → redirect to their dashboard
        redirectToDashboard();
    }
}

// =============================================
// REDIRECT TO CORRECT DASHBOARD BY ROLE
// =============================================

function redirectToDashboard() {
    $role = $_SESSION['role'] ?? '';
    switch($role) {
        case 'admin':
            redirect('../admin/index.php');
            break;
        case 'student':
            redirect('../student/index.php');
            break;
        case 'company':
            redirect('../company/index.php');
            break;
        default:
            redirect('../auth/login.php');
    }
}

// =============================================
// SANITIZE INPUT
// =============================================

/*
Learn: Always clean user input before
using it. This removes harmful characters.
htmlspecialchars() converts < > & " ' 
to safe HTML entities
trim() removes extra spaces
*/
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// =============================================
// DISPLAY ALERT MESSAGES
// =============================================

/*
Learn: We use SESSION to pass messages
between pages (after redirect).
Called flash messages.

Example flow:
1. Form submitted → process.php
2. Error found → store message in session
3. Redirect back to form
4. Form page shows message
5. Message cleared from session
*/
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,      // success, danger, warning, info
        'message' => $message
    ];
}

function showFlash() {
    if (isset($_SESSION['flash'])) {
        $type    = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        
        echo "
        <div class='alert alert-{$type} alert-dismissible 
                    fade show' role='alert'>
            <i class='bi bi-" . getAlertIcon($type) . " 
               me-2'></i>
            {$message}
            <button type='button' class='btn-close' 
                    data-bs-dismiss='alert'></button>
        </div>";
        
        // Clear flash after showing
        unset($_SESSION['flash']);
    }
}

function getAlertIcon($type) {
    $icons = [
        'success' => 'check-circle-fill',
        'danger'  => 'x-circle-fill',
        'warning' => 'exclamation-triangle-fill',
        'info'    => 'info-circle-fill'
    ];
    return $icons[$type] ?? 'info-circle-fill';
}

// =============================================
// GET CURRENT USER DATA
// =============================================

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    
    $id   = $_SESSION['user_id'];
    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// =============================================
// COUNT UNREAD NOTIFICATIONS
// =============================================

function getUnreadCount($conn) {
    if (!isLoggedIn()) return 0;
    
    $id   = $_SESSION['user_id'];
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as count 
         FROM notifications 
         WHERE user_id = ? AND is_read = 0"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    return $row['count'];
}

// =============================================
// FORMAT DATE
// =============================================

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60)     return "Just now";
    if ($diff < 3600)   return floor($diff/60) . " min ago";
    if ($diff < 86400)  return floor($diff/3600) . " hours ago";
    if ($diff < 604800) return floor($diff/86400) . " days ago";
    
    return formatDate($datetime);
}

// =============================================
// PROFILE COMPLETION CALCULATOR
// =============================================

function calcProfileCompletion($profile) {
    // Fields that count toward completion
    $fields = [
        'phone', 'address', 'date_of_birth',
        'education', 'field_of_study', 'skills',
        'about', 'resume_file', 'profile_photo'
    ];
    
    $filled = 0;
    foreach ($fields as $field) {
        if (!empty($profile[$field])) {
            $filled++;
        }
    }
    
    return round(($filled / count($fields)) * 100);
}

// =============================================
// GENERATE CERTIFICATE NUMBER
// =============================================

function generateCertNumber() {
    return 'CERT-' . date('Y') . '-' . 
           strtoupper(substr(uniqid(), -6));
}
?>