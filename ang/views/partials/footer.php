</div>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-section">
          <h4>Student Management System edit new 1.2
          </h4>
          <p>Efficiently manage student records with our comprehensive platform.</p>
        </div>
        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li ng-show="isLoggedIn"><a href="/ci/ang/students/dashboard">Dashboard</a></li>
            <li ng-show="isLoggedIn"><a href="/ci/ang/students/add">Add Student</a></li>
            <li ng-show="isLoggedIn"><a href="/ci/ang/test-db">Database Status</a></li>
            <li ng-show="!isLoggedIn"><a href="/ci/ang/login">Login</a></li>
            <li ng-show="!isLoggedIn"><a href="/ci/ang/signup">Sign Up</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Support</h4>
          <ul class="footer-links">
            <li><a href="/ci/migrate">Run Migrations</a></li>
            <li><a href="/ci/students/setup_database">Setup Database</a></li>
            <li ng-show="isLoggedIn"><a href="/ci/ang/students/dashboard">Dashboard</a></li>
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

  <!-- JavaScript Assets -->
  <script src="assets/angular-1.3.0/angular.js"></script>
  <script src="assets/angular-1.3.0/angular-route.min.js"></script>
  <script src="assets/angular-1.3.0/angular-cookies.js"></script>
  <script src="assets/angular-1.3.0/angular-sanitize.min.js"></script>
  
  <!-- Application Scripts -->
  <script src="js/app.js"></script>
  <script src="js/services.js"></script>
  <script src="js/directives.js"></script>
  <script src="js/routes.js"></script>
  
  <!-- Controllers -->
  <script src="js/controllers/NavController.js"></script>
  <script src="js/controllers/HomeController.js"></script>
  <script src="js/controllers/StudentController.js"></script>
  <script src="js/controllers/StudentFormController.js"></script>
  <script src="js/controllers/DeletedStudentsController.js"></script>
  <script src="js/controllers/TestDbController.js"></script>
  <script src="js/controllers/AuthController.js"></script>
  <script src="js/controllers/DashboardController.js"></script>
  
  <!-- <script src="js/filters/email-contact-filters.js"></script> -->
</body>
</html>