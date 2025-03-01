       
            </div>
        </section>
    </div>

    
    <script>
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const body = document.body;
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        const sidebar = document.getElementById('sidebar');
        const navLinks = document.getElementById('nav-links');

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
        });

        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
        }

        sidebarToggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 800) {
                navLinks.classList.toggle('open');
                sidebar.classList.toggle("sidebar-collapsed");
            } else {
                sidebar.classList.toggle("sidebar-collapsed");
            }

        });
    </script>
</body>

</html>