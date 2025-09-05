<?php
/**
 * Main Index File for Student Management System
 * Uses partial files for header and footer for better maintainability
 */

// Include the header partial
include_once 'views/partials/navbar.php';
?>

<!-- Main Content Area - This is where ng-view will render -->
<div ng-view></div>

<?php
// Include the footer partial
include_once 'views/partials/footer.php';
?>