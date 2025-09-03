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
      <a href="/students" class="navbar-brand">Home</a>
      <ul class="navbar-nav">
        <li ng-show="isLoggedIn">
          <a href="/ci/ang/dashboard" class="nav-link" ng-class="{ active: currentPath == '/dashboard' }">Dashboard</a>
        </li>
        <li ng-show="isLoggedIn">
          <a href="/students/add" class="nav-link" ng-class="{ active: currentPath == '/students/add' }">Add Student</a>
        </li>
        <li ng-show="isLoggedIn">
          <a href="/students/deleted" class="nav-link" ng-class="{ active: currentPath == '/students/deleted' }">Deleted Students</a>
        </li>
        <li ng-show="isLoggedIn">
          <a href="/test-db" class="nav-link" ng-class="{ active: currentPath == '/test-db' }">Test DB</a>
        </li>
        <li ng-show="!isLoggedIn">
          <a href="/login" class="nav-link" ng-class="{ active: currentPath == '/login' }">Login</a>
        </li>
        <li ng-show="!isLoggedIn">
          <a href="/signup" class="nav-link" ng-class="{ active: currentPath == '/signup' }">Sign Up</a>
        </li>
      </ul>
      <div class="user-info" ng-show="isLoggedIn">
        <span class="user-name">Welcome, {{ currentUser }}</span>
        <a href="/logout" class="btn-logout" ng-click="logout()">Logout</a>
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
            <li ng-show="isLoggedIn"><a href="/dashboard">Dashboard</a></li>
            <li ng-show="isLoggedIn"><a href="/students/add">Add Student</a></li>
            <li ng-show="isLoggedIn"><a href="/test-db">Database Status</a></li>
            <li ng-show="!isLoggedIn"><a href="/login">Login</a></li>
            <li ng-show="!isLoggedIn"><a href="/signup">Sign Up</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Support</h4>
          <ul class="footer-links">
            <li><a href="/ci/migrate">Run Migrations</a></li>
            <li><a href="/ci/students/setup_database">Setup Database</a></li>
            <li ng-show="isLoggedIn"><a href="/dashboard">Dashboard</a></li>
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
  <script src="js/app.js"></script>
  <script src="js/services.js"></script>
  <script src="js/controllers.js"></script>
  <script src="js/directives.js"></script>
  <script src="js/dashboard.js"></script>
  <script src="js/routes.js"></script>
</body>
</html>