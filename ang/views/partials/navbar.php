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
          <a href="/ci/ang/students/deleted" class="nav-link" ng-class="{ active: currentPath == '/students/deleted' }">Deleted Students</a>
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