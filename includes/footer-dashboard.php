            </div><!-- End dashboard-content -->
        </main><!-- End main-content -->
    </div><!-- End app-container -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Mobile sidebar toggle -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');

            // Add mobile menu button if on mobile
            if (window.innerWidth <= 768) {
                const menuBtn = document.createElement('button');
                menuBtn.className = 'mobile-menu-btn';
                menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                menuBtn.style.cssText = 'position:fixed;top:20px;left:20px;z-index:1001;width:44px;height:44px;border:none;background:var(--bg-card);border-radius:var(--radius-lg);box-shadow:var(--shadow-md);cursor:pointer;';
                document.body.appendChild(menuBtn);

                menuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });

                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                });
            }
        });
    </script>
</body>
</html>
