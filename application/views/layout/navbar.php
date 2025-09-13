<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<nav class="navbar" ng-controller="NavController">
    <div class="navbar-container">
        <a href="/students" class="navbar-brand">Home</a>
        <ul class="navbar-nav">
            <li ng-show="isLoggedIn">
                <a href="/students/dashboard" class="nav-link" ng-class="{ active: currentPath == '/students/dashboard' }">Dashboard</a>
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