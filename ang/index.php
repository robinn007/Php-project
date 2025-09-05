<?php
// Optional: Add PHP logic for session handling or CSRF token generation
$csrf_token_name = 'csrf_token_name'; // Replace with actual CodeIgniter CSRF token name
$csrf_token = ''; // Replace with actual CSRF token from session or config
?>
<!DOCTYPE html>
<html ng-app="myApp">
<head>
  <meta charset="UTF-8">
  <title>Student Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="/ci/ang/">
  <link rel="stylesheet" href="css/style.css">
  <meta name="csrf-token-name" content="<?php echo htmlspecialchars($csrf_token_name); ?>">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
</head>
<body>
  <nav class="navbar" ng-controller="NavController">
    <div class="navbar-container">
      <a href="/ci/ang/students" class="navbar-brand">Home</a>
      <ul class="navbar-nav">
        <li ng-show="isLoggedIn">
          <a href="/ci/ang/students/dashboard" class="nav-link" ng-class="{ active: currentPath == '/students/dashboard' }">Dashboard</a>
        </li>
        <li ng-show="isLoggedIn">
          <a href="/ci/ang/students/add" class="nav-link" ng-class="{ active: currentPath == '/students/add' }">Add Student</a>
        </li>
        <li ng-show="isLoggedIn">
          <a href="/ci/ang/students/deleted" class="nav-link" ng-class="{ active: currentPath == '/students/deleted' }">Deleted Students1</a>
        </li>
        <li ng-show="isLoggedIn">
          <a href="/ci/ang/test-db" class="nav-link" ng-class="{ active: currentPath == '/test-db' }">Test DB</a>
        </li>
        <li ng-show="!isLoggedIn">
          <a href="/ci/ang/login" class="nav-link" ng-class="{ active: currentPath == '/login' }">Login</a>
        </li>
        <li ng-show="!isLoggedIn">
          <a href="/ci/ang/signup" class="nav-link" ng-class="{ active: currentPath == '/signup' }">Sign Up</a>
        </li>
      </ul>
      <div class="user-info" ng-show="isLoggedIn">
        <span class="user-name">Welcome, {{ currentUser }}</span>
        <a href="/ci/ang/logout" class="btn-logout" ng-click="logout()">Logout</a>
      </div>
    </div>
  </nav>

  <main class="main-content">
    <div class="container">
      <div ng-view></div>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="footer-section">
          <h4>Student Management System edit 4.4</h4>
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

  <script src="assets/angular-1.3.0/angular.js"></script>
  <script src="assets/angular-1.3.0/angular-route.min.js"></script>
  <script src="assets/angular-1.3.0/angular-cookies.js"></script>
  <script src="assets/angular-1.3.0/angular-sanitize.min.js"></script>
  <script src="js/app.js"></script>
  <script src="js/services.js"></script>
  <script src="js/directives.js"></script>
  <script src="js/routes.js"></script>
  <script src="js/controllers/NavController.js"></script>
  <script src="js/controllers/HomeController.js"></script>
  <script src="js/controllers/StudentController.js"></script>
  <script src="js/controllers/StudentFormController.js"></script>
  <script src="js/controllers/DeletedStudentsController.js"></script>
  <script src="js/controllers/TestDbController.js"></script>
  <script src="js/controllers/AuthController.js"></script>
  <script src="js/controllers/DashboardController.js"></script>
   <!-- <script src="js/filters/email-contact-filters.js"></script>  -->
</body>
</html>