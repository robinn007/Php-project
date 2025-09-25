<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// At the top of index.php
if (function_exists('spl_autoload_register')) {
    spl_autoload_register(function($class) {
        log_message('debug', 'Autoload attempted for class: ' . $class);
        if ($class === 'PHPExcel') {
            log_message('error', 'PHPExcel autoload prevented');
            return false;
        }
    }, true, true);
}
?>
<!DOCTYPE html>
<html ng-app="myApp">
<head>
    <meta charset="UTF-8">
    <title>Student Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <link rel="stylesheet" href="/assets/css/style.css">
    <meta name="csrf-token-name" content="<?php echo htmlspecialchars($this->security->get_csrf_token_name()); ?>">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($this->security->get_csrf_hash()); ?>">
</head>
<body>
    <ng-include src="'/layout/navbar'"></ng-include>
    <main class="main-content">
        <div class="container">
            <div ng-view></div>
        </div>
    </main>
    <ng-include src="'/layout/footer'"></ng-include>

    <!-- JavaScript Assets -->
    <script src="/assets/js/angular-1.3.0/angular.js"></script>
    <script src="/assets/js/angular-1.3.0/angular-route.min.js"></script>
    <script src="/assets/js/angular-1.3.0/angular-cookies.js"></script>
    <script src="/assets/js/angular-1.3.0/angular-sanitize.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.0/angular-cookies.js"></script> -->

    <!-- Application Scripts -->
    <script src="/ang/app.js"></script>
    <script src="/ang/helpers/services.js"></script>
    <script src="/ang/helpers/common.js"></script>
    <script src="/ang/directives/directives.js"></script>
    <script src="/ang/directives/searchFilter.js"></script>
    <script src="/ang/directives/stateFilter.js"></script>
    <script src="/ang/routes.js"></script>
    <script src="/ang/filters/filters.js"></script>

    
    <!-- Controllers -->
<script src="/ang/controllers/NavController.js"></script>
<script src="/ang/controllers/HomeController.js"></script>
<script src="/ang/controllers/StudentController.js"></script>
<script src="/ang/controllers/StudentFormController.js"></script>
<script src="/ang/controllers/DeletedStudentsController.js"></script>
<script src="/ang/controllers/TestDbController.js"></script>
<script src="/ang/controllers/AuthController.js"></script>
<script src="/ang/controllers/DashboardController.js"></script>
<script src="/ang/controllers/ClicksController.js"></script>
<script src="/ang/controllers/ChatController.js"></script>

    <!-- Add Socket.IO Client -->
    <script src="http://localhost:3000/socket.io/socket.io.js"></script>
<script src="/ang/services/SocketService.js"></script>
</body>
</html>