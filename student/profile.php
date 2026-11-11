<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requireRole('student');

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current profile
$stmt = $conn->prepare(
    "SELECT sp.*, u.name, u.email 
     FROM student_profiles sp
     JOIN users u ON sp.user_id = u.id
     WHERE sp.user_id = ?"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    setFlash('danger', 'Profile not found.');
    redirect('index.php');
}

// =============================================
// HANDLE FORM SUBMIT
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $phone         = sanitize($_POST['phone'] ?? '');
    $address       = sanitize($_POST['address'] ?? '');
    $date_of_birth = sanitize($_POST['date_of_birth'] ?? '');
    $education     = sanitize($_POST['education'] ?? '');
    $field_of_study= sanitize($_POST['field_of_study'] ?? '');
    $skills        = sanitize($_POST['skills'] ?? '');
    $about         = sanitize($_POST['about'] ?? '');
    $linkedin_url  = sanitize($_POST['linkedin_url'] ?? '');
    $name          = sanitize($_POST['name'] ?? '');

    // Keep existing file paths by default
    $profile_photo = $profile['profile_photo'];
    $resume_file   = $profile['resume_file'];

    // ---- Validate name ----
    if (empty($name)) {
        $error = 'Name is required.';
    }

    // ---- Validate LinkedIn URL if provided ----
    if (empty($error) && !empty($linkedin_url)) {
        if (!filter_var($linkedin_url, FILTER_VALIDATE_URL)) {
            $error = 'Please enter a valid LinkedIn URL.';
        }
    }

    // ---- Handle Profile Photo Upload ----
    if (empty($error) && isset($_FILES['profile_photo']) 
        && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {

        $photo = $_FILES['profile_photo'];
        $allowedPhotos = ['image/jpeg', 'image/png', 'image/webp'];
        $maxPhotoSize = 2 * 1024 * 1024; // 2MB

        if ($photo['error'] !== UPLOAD_ERR_OK) {
            $error = 'Photo upload failed. Please try again.';
        } elseif (!in_array($photo['type'], $allowedPhotos)) {
            $error = 'Photo must be JPG, PNG, or WEBP.';
        } elseif ($photo['size'] > $maxPhotoSize) {
            $error = 'Photo size must be less than 2MB.';
        } else {
            $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $newPhotoName = 'photo_' . $userId . '_' . time() . '.' . strtolower($ext);
            $photoPath = __DIR__ . '/../assets/uploads/photos/' . $newPhotoName;

            if (move_uploaded_file($photo['tmp_name'], $photoPath)) {
                // Delete old photo if exists
                if (!empty($profile['profile_photo'])) {
                    $old = __DIR__ . '/../assets/uploads/photos/' . $profile['profile_photo'];
                    if (file_exists($old)) unlink($old);
                }
                $profile_photo = $newPhotoName;
            } else {
                $error = 'Failed to save profile photo.';
            }
        }
    }

    // ---- Handle Resume Upload ----
    if (empty($error) && isset($_FILES['resume_file']) 
        && $_FILES['resume_file']['error'] !== UPLOAD_ERR_NO_FILE) {

        $resume = $_FILES['resume_file'];
        $allowedResumes = ['application/pdf'];
        $maxResumeSize = 5 * 1024 * 1024; // 5MB

        if ($resume['error'] !== UPLOAD_ERR_OK) {
            $error = 'Resume upload failed. Please try again.';
        } elseif (!in_array($resume['type'], $allowedResumes)) {
            $error = 'Resume must be a PDF file.';
        } elseif ($resume['size'] > $maxResumeSize) {
            $error = 'Resume size must be less than 5MB.';
        } else {
            $newResumeName = 'resume_' . $userId . '_' . time() . '.pdf';
            $resumePath = __DIR__ . '/../assets/uploads/resumes/' . $newResumeName;

            if (move_uploaded_file($resume['tmp_name'], $resumePath)) {
                // Delete old resume if exists
                if (!empty($profile['resume_file'])) {
                    $old = __DIR__ . '/../assets/uploads/resumes/' . $profile['resume_file'];
                    if (file_exists($old)) unlink($old);
                }
                $resume_file = $newResumeName;
            } else {
                $error = 'Failed to save resume.';
            }
        }
    }

    // ---- Save to Database ----
    if (empty($error)) {

        // Update name in users table
        $stmt = $conn->prepare(
            "UPDATE users SET name = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $name, $userId);
        $stmt->execute();
        $stmt->close();

        // Update session name
        $_SESSION['name'] = $name;

        // Empty date becomes NULL
        $dob = !empty($date_of_birth) ? $date_of_birth : null;

        // Update student profile
        $stmt = $conn->prepare("
            UPDATE student_profiles SET
                phone = ?,
                address = ?,
                date_of_birth = ?,
                education = ?,
                field_of_study = ?,
                skills = ?,
                about = ?,
                linkedin_url = ?,
                profile_photo = ?,
                resume_file = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param(
            "ssssssssssi",
            $phone,
            $address,
            $dob,
            $education,
            $field_of_study,
            $skills,
            $about,
            $linkedin_url,
            $profile_photo,
            $resume_file,
            $userId
        );

        if ($stmt->execute()) {
            $stmt->close();

            // Re-fetch profile for completion calc
            $stmt2 = $conn->prepare(
                "SELECT * FROM student_profiles WHERE user_id = ?"
            );
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $updatedProfile = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();

            // Update completion percent
            $percent = calcProfileCompletion($updatedProfile);
            $stmt3 = $conn->prepare(
                "UPDATE student_profiles 
                 SET completion_percent = ? WHERE user_id = ?"
            );
            $stmt3->bind_param("ii", $percent, $userId);
            $stmt3->execute();
            $stmt3->close();

            setFlash('success', 'Profile updated successfully!');
            redirect('profile.php');
        } else {
            $error = 'Failed to update profile. Please try again.';
            $stmt->close();
        }
    }

    // Refresh profile data after failed attempt (keep form values)
    if (!empty($error)) {
        $profile['phone'] = $phone;
        $profile['address'] = $address;
        $profile['date_of_birth'] = $date_of_birth;
        $profile['education'] = $education;
        $profile['field_of_study'] = $field_of_study;
        $profile['skills'] = $skills;
        $profile['about'] = $about;
        $profile['linkedin_url'] = $linkedin_url;
        $profile['name'] = $name;
    }
}

// Calculate completion for display
$completion = calcProfileCompletion($profile);

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/../includes/topbar.php'; ?>

        <div class="page-content">

            <?php showFlash(); ?>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-x-circle-fill me-2"></i>
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Profile Completion -->
            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>Profile Completion</strong>
                        <small class="text-muted d-block">
                            Complete your profile to get better opportunities
                        </small>
                    </div>
                    <span class="fw-700" style="color:var(--primary); font-size:1.2rem;">
                        <?= $completion ?>%
                    </span>
                </div>
                <div class="completion-bar">
                    <div class="completion-fill" style="width:<?= $completion ?>%"></div>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="profileForm">
            <!--
                Learn: enctype="multipart/form-data" is REQUIRED
                for file uploads. Without it, $_FILES will be empty.
            -->

                <div class="row g-4">

                    <!-- LEFT COLUMN -->
                    <div class="col-lg-4">

                        <!-- Photo Card -->
                        <div class="section-card text-center">
                            <div class="profile-photo-wrapper mb-3">
                                <?php if (!empty($profile['profile_photo'])): ?>
                                    <img 
                                        src="../assets/uploads/photos/<?= htmlspecialchars($profile['profile_photo']) ?>"
                                        alt="Profile Photo"
                                        class="profile-photo-img"
                                        id="photoPreview"
                                    >
                                <?php else: ?>
                                    <div class="profile-photo-placeholder" id="photoPreview">
                                        <?= strtoupper(substr($profile['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h5 class="mb-1"><?= htmlspecialchars($profile['name']) ?></h5>
                            <p class="text-muted mb-3" style="font-size:0.85rem;">
                                <?= htmlspecialchars($profile['email']) ?>
                            </p>

                            <label class="btn btn-sm btn-outline-primary mb-2" style="cursor:pointer;">
                                <i class="bi bi-camera me-1"></i>
                                Change Photo
                                <input 
                                    type="file" 
                                    name="profile_photo" 
                                    id="photoInput"
                                    accept="image/jpeg,image/png,image/webp"
                                    hidden
                                >
                            </label>
                            <small class="text-muted d-block">
                                JPG, PNG or WEBP. Max 2MB.
                            </small>
                        </div>

                        <!-- Resume Card -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Resume
                            </div>

                            <?php if (!empty($profile['resume_file'])): ?>
                            <div class="resume-current mb-3">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                <div>
                                    <strong style="font-size:0.85rem;">Resume uploaded</strong>
                                    <br>
                                    <a href="../assets/uploads/resumes/<?= htmlspecialchars($profile['resume_file']) ?>" 
                                       target="_blank"
                                       style="font-size:0.8rem; color:var(--primary);">
                                        View Resume
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <p class="text-muted mb-3" style="font-size:0.85rem;">
                                No resume uploaded yet
                            </p>
                            <?php endif; ?>

                            <label class="btn btn-sm btn-outline-primary w-100" style="cursor:pointer;">
                                <i class="bi bi-upload me-1"></i>
                                <?= !empty($profile['resume_file']) ? 'Replace Resume' : 'Upload Resume' ?>
                                <input 
                                    type="file" 
                                    name="resume_file"
                                    accept="application/pdf"
                                    hidden
                                    id="resumeInput"
                                >
                            </label>
                            <small class="text-muted d-block mt-2" id="resumeFileName">
                                PDF only. Max 5MB.
                            </small>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-lg-8">

                        <!-- Personal Info -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-person"></i>
                                Personal Information
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control"
                                           value="<?= htmlspecialchars($profile['name']) ?>"
                                           required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control"
                                           value="<?= htmlspecialchars($profile['email']) ?>"
                                           disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control"
                                           placeholder="e.g. 9876543210"
                                           value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control"
                                           value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2"
                                              placeholder="Your city, state"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" class="form-control"
                                           placeholder="https://linkedin.com/in/yourprofile"
                                           value="<?= htmlspecialchars($profile['linkedin_url'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Education -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-mortarboard"></i>
                                Education
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Education Level</label>
                                    <select name="education" class="form-select">
                                        <option value="">Select education</option>
                                        <?php
                                        $eduOptions = [
                                            'High School',
                                            'Diploma',
                                            'Undergraduate',
                                            'Graduate',
                                            'Post Graduate',
                                            'PhD'
                                        ];
                                        foreach ($eduOptions as $opt):
                                        ?>
                                        <option value="<?= $opt ?>"
                                            <?= ($profile['education'] ?? '') === $opt ? 'selected' : '' ?>>
                                            <?= $opt ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Field of Study</label>
                                    <input type="text" name="field_of_study" class="form-control"
                                           placeholder="e.g. Computer Science"
                                           value="<?= htmlspecialchars($profile['field_of_study'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Skills & About -->
                        <div class="section-card">
                            <div class="section-title">
                                <i class="bi bi-stars"></i>
                                Skills & About
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Skills</label>
                                    <input type="text" name="skills" class="form-control"
                                           placeholder="e.g. HTML, CSS, JavaScript, PHP, MySQL"
                                           value="<?= htmlspecialchars($profile['skills'] ?? '') ?>">
                                    <small class="text-muted">
                                        Separate skills with commas
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">About Me</label>
                                    <textarea name="about" class="form-control" rows="4"
                                              placeholder="Write a short bio about yourself..."><?= htmlspecialchars($profile['about'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2 justify-content-end mb-4">
                            <a href="index.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="bi bi-check-lg me-1"></i>
                                Save Profile
                            </button>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </main>
</div>

<?php
$extraJS = <<<'JS'
<script>
// Photo preview when selecting file
document.getElementById('photoInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validate size client-side
    if (file.size > 2 * 1024 * 1024) {
        alert('Photo must be less than 2MB');
        e.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(ev) {
        const preview = document.getElementById('photoPreview');
        if (preview.tagName === 'IMG') {
            preview.src = ev.target.result;
        } else {
            // Replace placeholder div with img
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.className = 'profile-photo-img';
            img.id = 'photoPreview';
            preview.replaceWith(img);
        }
    };
    reader.readAsDataURL(file);
});

// Show selected resume filename
document.getElementById('resumeInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const label = document.getElementById('resumeFileName');
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('Resume must be less than 5MB');
            e.target.value = '';
            return;
        }
        if (file.type !== 'application/pdf') {
            alert('Resume must be a PDF file');
            e.target.value = '';
            return;
        }
        label.textContent = 'Selected: ' + file.name;
        label.style.color = 'var(--primary)';
    }
});
</script>
JS;

include __DIR__ . '/../includes/footer.php';
?>