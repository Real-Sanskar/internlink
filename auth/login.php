<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    
    } else {
        // Find user by email
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, 
                    role, status 
             FROM users 
             WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if (!$user) {
            // User not found
            $error = 'Invalid email or password.';
            /*
            Learn: We give vague error on purpose.
            Don't say "email not found" because that
            tells hackers which emails exist.
            */
        
        } elseif (!password_verify($password, 
                                    $user['password'])) {
            // Wrong password
            $error = 'Invalid email or password.';
        
        } elseif ($user['status'] === 'banned') {
            $error = 'Your account has been banned. 
                      Contact support.';
        
        } elseif ($user['status'] === 'pending') {
            $error = 'Your account is pending approval. 
                      Please wait for admin review.';
        
        } else {
            // ✅ Login successful!
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            /*
            Learn: We store minimal info in session.
            Don't store password or sensitive data.
            We can always query DB for more info.
            */

            // Redirect based on role
            setFlash('success', 
                'Welcome back, ' . $user['name'] . '!');
            redirectToDashboard();
        }
    }
}

$pageTitle = 'Login';
include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        
        <!-- Logo -->
        <div class="auth-logo">
            <h2>
                <i class="bi bi-link-45deg"></i>
                Inter<span>Link</span>
            </h2>
            <p>Internship & Vocational Training Portal</p>
        </div>

        <h5 class="auth-title">Welcome Back!</h5>
        <p class="auth-subtitle">
            Login to your InterLink account
        </p>

        <!-- Flash Message -->
        <?php showFlash(); ?>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex 
                    align-items-center gap-2">
            <i class="bi bi-x-circle-fill"></i>
            <?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" id="loginForm">
            
            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">
                    Email Address
                </label>
                <div class="input-group-icon">
                    <i class="bi bi-envelope"></i>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars(
                            $_POST['email'] ?? ''
                        ) ?>"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label">
                    Password
                </label>
                <div class="input-group-icon 
                            position-relative">
                    <i class="bi bi-lock"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="loginPassword"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-auth">
                <i class="bi bi-box-arrow-in-right me-2">
                </i>
                Login
            </button>
        </form>

        <!-- Register Link -->
        <p class="text-center mt-3 mb-0" 
           style="font-size:0.9rem; color:var(--muted)">
            Don't have an account? 
            <a href="register.php" 
               style="color:var(--primary); 
                      font-weight:600">
                Register here
            </a>
        </p>

        <!-- Demo accounts info (for project demo) -->
        <div class="mt-4 p-3" 
             style="background:var(--light); 
                    border-radius:8px; 
                    font-size:0.8rem;">
            <strong>Demo Accounts:</strong><br>
            👨‍💼 Admin: admin@interlink.com / admin123<br>
            👨‍🎓 Student: student@interlink.com / student123<br>
            🏢 Company: company@interlink.com / company123
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>