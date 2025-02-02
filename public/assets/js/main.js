document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    // Create overlay element
    const overlay = document.createElement('div');
    overlay.className = 'content-overlay';
    document.body.appendChild(overlay);

    // Sidebar toggle
    sidebarCollapse.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        sidebar.classList.toggle('active');
        
        // Only toggle content class on desktop
        if (window.innerWidth > 768) {
            content.classList.toggle('active');
        }
        
        // Only toggle overlay on mobile
        if (window.innerWidth <= 768) {
            overlay.classList.toggle('active');
        }
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickInsideToggle = sidebarCollapse.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickInsideToggle) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            overlay.classList.remove('active');
        }
    });

    // Handle submenu toggles
    const submenuToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const submenuIcon = this.querySelector('.fa-chevron-down');
            if (submenuIcon) {
                submenuIcon.style.transform = this.getAttribute('aria-expanded') === 'true' 
                    ? 'rotate(0deg)' 
                    : 'rotate(180deg)';
            }
        });
    });
});