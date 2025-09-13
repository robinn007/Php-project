<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Student Management System edit new 1.4</h4>
                <p>Efficiently manage student records with our comprehensive platform.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li ng-show="isLoggedIn"><a href="/students/dashboard">Dashboard</a></li>
                    <li ng-show="isLoggedIn"><a href="/students/add">Add Student</a></li>
                    <li ng-show="isLoggedIn"><a href="/test-db">Database Status</a></li>
                    <li ng-show="!isLoggedIn"><a href="/login">Login</a></li>
                    <li ng-show="!isLoggedIn"><a href="/signup">Sign Up</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <ul class="footer-links">
                    <li><a href="/migrate">Run Migrations</a></li>
                    <li><a href="/students/setup_database">Setup Database</a></li>
                    <li ng-show="isLoggedIn"><a href="/students/dashboard">Dashboard</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-copyright">
                <p>&copy; 2025 Student Management System. Built with CodeIgniter 2 and AngularJS 1.3.</p>
            </div>
        </div>
    </div>
</footer>