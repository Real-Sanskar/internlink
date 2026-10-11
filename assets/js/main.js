// =============================================
// INTERLINK - MAIN JAVASCRIPT FILE
// =============================================




// ---- ROLE CARD SELECTION ----
/*
Learn: When user clicks a role card,
we add 'active' class and update hidden input.
This is more user-friendly than a dropdown.
*/
document.addEventListener('DOMContentLoaded', function() {
    
    const roleCards    = document.querySelectorAll('.role-card');
    const roleInput    = document.getElementById('selectedRole');
    
    if (roleCards.length > 0) {
        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove active from all cards
                roleCards.forEach(c => 
                    c.classList.remove('active')
                );
                // Add active to clicked card
                this.classList.add('active');
                // Update hidden input
                if (roleInput) {
                    roleInput.value = this.dataset.role;
                }
            });
        });
    }

    // ---- CLIENT SIDE FORM VALIDATION ----
    /*
    Learn: We validate on both:
    1. JavaScript (client side) → instant feedback
    2. PHP (server side) → security
    Never rely on JS validation alone!
    */
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            
            const name     = document.querySelector('[name="name"]');
            const email    = document.querySelector('[name="email"]');
            const password = document.getElementById('password');
            const confirm  = document.getElementById('confirmPassword');
            const role     = document.getElementById('selectedRole');
            
            let valid = true;

            // Clear previous errors
            clearErrors();

            // Validate name
            if (!name.value.trim()) {
                showError(name, 'Full name is required');
                valid = false;
            }

            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                showError(email, 'Enter a valid email');
                valid = false;
            }

            // Validate role
            if (!role || !role.value) {
                alert('Please select a role (Student or Company)');
                valid = false;
            }

            // Validate password
            if (password.value.length < 8) {
                showError(password, 
                    'Password must be at least 8 characters');
                valid = false;
            }

            // Validate confirm password
            if (password.value !== confirm.value) {
                showError(confirm, 'Passwords do not match');
                valid = false;
            }

            // Stop form if invalid
            if (!valid) {
                e.preventDefault();
            }
        });
    }

    // ---- AUTO HIDE ALERTS ----
    /*
    Learn: Bootstrap alerts auto-dismiss after 5 seconds
    */
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });



    // Close sidebar when clicking overlay
    const overlay = document.getElementById('sidebarOverlay');

    if (overlay) {
        overlay.addEventListener('click', function () {
            toggleSidebar();
        });
    }

    // Close sidebar when a menu link is clicked (mobile UX)
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            // Only close on mobile
            if (sidebar && sidebar.classList.contains('active') 
                && window.innerWidth < 992) {
                toggleSidebar();
            }
        });
    });
});

// ---- HELPER: Show Error ----
function showError(input, message) {
    input.classList.add('is-invalid');
    
    // Remove existing error first
    const existingError = input.parentNode.parentNode
                          .querySelector('.error-text');
    if (existingError) existingError.remove();
    
    // Create error message element
    let errorDiv = document.createElement('div');
    errorDiv.className = 'error-text';
    errorDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>' 
                         + message;
    
    // Add AFTER the input-group-icon div (not inside it)
    input.closest('.mb-3').appendChild(errorDiv);
}

// ---- HELPER: Clear Errors ----
function clearErrors() {
    document.querySelectorAll('.is-invalid')
        .forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback')
        .forEach(el => el.remove());
}

// ---- BOOKMARK TOGGLE (AJAX) ----
/*
Learn: AJAX lets us send request to server
WITHOUT reloading the page.
fetch() is modern way to do AJAX in JS.
*/
function toggleBookmark(listingId, btn) {
    fetch('../student/ajax/bookmark.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ listing_id: listingId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('bookmarked');
            const icon = btn.querySelector('i');
            if (btn.classList.contains('bookmarked')) {
                icon.className = 'bi bi-bookmark-fill';
            } else {
                icon.className = 'bi bi-bookmark';
            }
        }
    })
    .catch(error => console.log('Error:', error));
}


// =============================================
// SIDEBAR TOGGLE (Mobile)
// =============================================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    sidebar.classList.toggle('active');

    if (overlay) {
        overlay.classList.toggle('active');
    }

    // Prevent body scroll when sidebar is open
    if (sidebar.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}