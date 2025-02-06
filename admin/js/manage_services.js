document.addEventListener('DOMContentLoaded', function() {
    // ... existing code from first script block ...
    const tabs = document.querySelectorAll('.nav-link');
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

    // ... rest of the first script block code ...
});

// Sidebar toggle functions from second script block
function toggleSidebar() {
    const sidebar = document.getElementById('sidebarMenu');
    sidebar.classList.toggle('show');
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebarMenu');
    const toggle = document.querySelector('.mobile-toggle');
    if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
        sidebar.classList.remove('show');
    }
});

// Mobile toggle button setup from third script block
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.querySelector('.mobile-toggle');
    if (toggleButton) {
        toggleButton.setAttribute('onclick', 'toggleSidebar()');
    }
}); 