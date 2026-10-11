/* ============================================
   INTERNLINK — LANDING PAGE JAVASCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

    // =============================================
    // COUNTER ANIMATION
    // =============================================
    /*
    Learn: Intersection Observer watches when
    an element comes into view (scrolled to).
    We use it to start counter animation only
    when user scrolls to stats section.
    */

    const counters = document.querySelectorAll('.counter-value');

    const animateCounter = (el) => {
        const target = parseInt(el.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps
        let current = 0;

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                el.textContent = target;
                clearInterval(timer);
            } else {
                el.textContent = Math.floor(current);
            }
        }, 16);
    };

    // Only animate when scrolled into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));

    // =============================================
    // NAVBAR SCROLL EFFECT
    // =============================================
    /*
    Learn: When user scrolls down, navbar gets
    a shadow and slightly shrinks. This is a
    common professional UI pattern.
    */

    const navbar = document.querySelector('.landing-navbar');

    window.addEventListener('scroll', function () {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    // =============================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // =============================================
    /*
    Learn: When clicking #features or #how-it-works,
    page smoothly scrolls instead of jumping.
    */

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(
                this.getAttribute('href')
            );
            if (target) {
                const offset = 80; // navbar height
                const top = target.getBoundingClientRect().top 
                            + window.scrollY - offset;
                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });
            }
        });
    });

    // =============================================
    // SCROLL REVEAL ANIMATION
    // =============================================
    /*
    Learn: Elements fade in as you scroll down.
    Makes the page feel dynamic and professional.
    */

    const revealElements = document.querySelectorAll(
        '.step-card, .feature-card, .listing-card, ' +
        '.counter-card, .cta-card'
    );

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    revealElements.forEach(el => {
        el.classList.add('reveal');
        revealObserver.observe(el);
    });
});