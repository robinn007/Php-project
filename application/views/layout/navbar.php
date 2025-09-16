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
                <a href="/clicks" class="nav-link" ng-class="{ active: currentPath == '/clicks' }">Clicks</a>
            </li>

            <li ng-show="isLoggedIn">
                <a href="/test-db" class="nav-link" ng-class="{ active: currentPath == '/test-db' }">Test DB...</a>
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
            <a href="#" class="btn-logout" ng-click="logout()">Logout</a>
        </div>
        <div flash-message ng-class="flashType">{{ flashMessage }}</div>
    </div>
</nav>



<!-- -- Add indexes for frequently queried columns
CREATE INDEX idx_clicks_link ON clicks (link(50));
CREATE INDEX idx_clicks_pid ON clicks (pid);
CREATE INDEX idx_clicks_campaignId ON clicks (campaignId);
CREATE INDEX idx_clicks_timestamp ON clicks (timestamp);

-- Optional: Add full-text index for search if using MySQL with InnoDB/MyISAM
CREATE FULLTEXT INDEX idx_clicks_link_fulltext ON clicks (link); -->