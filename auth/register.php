<?php
// Include helper functions (also starts session)
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}

$error   = '';
$success = '';

// =============================================
// PROCESS FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /*
    Learn: $_SERVER['REQUEST_METHOD'] tells us
    HOW the page was accessed.
    GET  = just visiting the page
    POST = form was submitted
    */

    // Get and sanitize inputs
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = sanitize($_POST['role'] ?? '');

    // ---- VALIDATION ----
    if (empty($name) || empty($email) || 
        empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        /*
        Learn: filter_var() is PHP's built-in 
        validation function.
        FILTER_VALIDATE_EMAIL checks email format
        */
        $error = 'Please enter a valid email address.';
    
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    
    } elseif (!in_array($role, ['student', 'company'])) {
        /*
        Learn: Never trust what user sends!
        Even if we have a dropdown, someone could
        modify the form and send 'admin'.
        We validate on server side too!
        */
        $error = 'Invalid role selected.';
    
    } else {
        // Check if email already exists
        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already registered. 
                      Please login instead.';
        } else {
            // Hash password before storing
            $hashedPassword = password_hash(
                $password, 
                PASSWORD_DEFAULT
            );
            
            // Determine account status
            // Company needs admin approval → pending
            // Student goes active immediately
            $status = ($role === 'company') 
                      ? 'pending' 
                      : 'active';
            
            // Insert new user
            $stmt = $conn->prepare(
                "INSERT INTO users 
                 (name, email, password, role, status) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssss", 
                $name, $email, $hashedPassword, 
                $role, $status
            );
            
            if ($stmt->execute()) {
                $newUserId = $conn->insert_id;
                /*
                Learn: insert_id gives you the ID
                of the row just inserted.
                Useful for creating related records.
                */
                
                // Create profile record based on role
                if ($role === 'student') {
                    $stmt2 = $conn->prepare(
                        "INSERT INTO student_profiles 
                         (user_id) VALUES (?)"
                    );
                    $stmt2->bind_param("i", $newUserId);
                    $stmt2->execute();
                    
                    setFlash('success', 
                        'Account created! Please login.');
                    redirect('login.php');
                    
                } elseif ($role === 'company') {
                    $stmt2 = $conn->prepare(
                        "INSERT INTO company_profiles 
                         (user_id, company_name, status) 
                         VALUES (?, ?, 'pending')"
                    );
                    // Use name as temporary company name
                    $stmt2->bind_param(
                        "is", $newUserId, $name
                    );
                    $stmt2->execute();
                    
                    setFlash('warning', 
                        'Account created! Please wait 
                         for admin approval before login.');
                    redirect('login.php');
                }
            } else {
                $error = 'Registration failed. 
                          Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
include '../includes/header.php';
?>

<!-- ============================================
     REGISTER PAGE HTML
     ============================================ -->
<!-- REGISTER PAGE -->
<div class="auth-wrapper">
    <div class="auth-card">
        
        <!-- Logo -->
        <div class="auth-logo">
            <h2>
                <i class="bi bi-link-45deg"></i>
                Intern<span>Link</span>
            </h2>
            <p>Internship & Vocational Training Portal</p>
        </div>

        <h5 class="auth-title">Create Account</h5>
        <p class="auth-subtitle">
            Join InternLink and start your journey
        </p>

        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex 
                    align-items-center gap-2">
            <i class="bi bi-x-circle-fill"></i>
            <?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form method="POST" id="registerForm" novalidate>
            
            <!-- Full Name -->
            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <div class="input-group-icon">
                    <i class="bi bi-person"></i>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars(
                            $_POST['name'] ?? '') ?>"
                        required
                    >
                </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">
                    Email Address *
                </label>
                <div class="input-group-icon">
                    <i class="bi bi-envelope"></i>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars(
                            $_POST['email'] ?? '') ?>"
                        required
                    >
                </div>
            </div>

            <!-- Role Selection -->
            <div class="mb-3">
                <label class="form-label">I am a *</label>
                <div class="role-selector">
                    <div class="role-card <?= 
                        ($_POST['role'] ?? '') === 'student' 
                        ? 'active' : '' ?>" 
                        data-role="student">
                        <i class="bi bi-mortarboard"></i>
                        <span>Student</span>
                    </div>
                    <div class="role-card <?= 
                        ($_POST['role'] ?? '') === 'company' 
                        ? 'active' : '' ?>"
                        data-role="company">
                        <i class="bi bi-building"></i>
                        <span>Company</span>
                    </div>
                </div>
                <input type="hidden" name="role" 
                       id="selectedRole" 
                       value="<?= $_POST['role'] ?? '' ?>">
            </div>

            <!-- Password -->   
            <div class="mb-3">
                <label class="form-label">Password *</label>
                <div class="input-group-icon">
                    <i class="bi bi-lock"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="form-control"
                        placeholder="Min. 8 characters"
                        required
                    >
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label class="form-label">
                    Confirm Password *
                </label>
                <div class="input-group-icon">
                    <i class="bi bi-lock-fill"></i>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        id="confirmPassword"
                        class="form-control"
                        placeholder="Repeat your password"
                        required
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-auth">
                <i class="bi bi-person-plus me-2"></i>
                Create Account
            </button>

        </form>

        <!-- Login Link -->
        <p class="text-center mt-3 mb-0" 
           style="font-size:0.9rem; color:var(--muted)">
            Already have an account? 
            <a href="login.php" 
               style="color:var(--primary); 
                      font-weight:600">
                Login here
            </a>
        </p>

    </div>
</div>

<?php include '../includes/footer.php'; ?>