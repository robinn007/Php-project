<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
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
                    <li ng-show="isLoggedIn"><a href="/ci/ang/students/dashboard">Dashboard</a></li>
                    <li ng-show="isLoggedIn"><a href="/ci/ang/students/add">Add Student</a></li>
                    <li ng-show="isLoggedIn"><a href="/ci/ang/test-db">Database Status</a></li>
                    <li ng-show="!isLoggedIn"><a href="/ci/ang/login">Login</a></li>
                    <li ng-show="!isLoggedIn"><a href="/ci/ang/signup">Sign Up</a></li>
                      <!-- <li ng-show="!isLoggedIn"><a href="/ci/ang/logout">Logout</a></li> -->
                      <!-- <div class="user-info" ng-show="!isLoggedIn"> -->
            <!-- <span class="user-name">Welcome, {{ currentUser }}</span> -->
            <!-- <a href="/ci/ang/logout" class="btn-logout" ng-click="logout()">Logout</a>
        </div>  -->
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