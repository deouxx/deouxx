    </div><!-- .admin-main-wrapper -->

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Mobile Sidebar Toggle
        const toggleBtn = document.getElementById('sidebar-toggle-btn');
        const sidebar = document.getElementById('admin-sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('open');
            });

            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                    sidebar.classList.remove('open');
                }
            });
        }

        // Auto-dismiss alert
        const alerts = document.querySelectorAll('.admin-flash-alert');
        alerts.forEach(al => {
            setTimeout(() => {
                al.style.transition = 'opacity 0.4s ease';
                al.style.opacity = '0';
                setTimeout(() => al.remove(), 400);
            }, 4000);
        });
    });

    // Helper functions for modals
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    }
    </script>
</body>
</html>
