<!-- About page template displaying static content -->
<ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=false"></ng-include>
<!-- Include flash message partial for user feedback -->
<ng-include src="'views/partials/flash-message.php'"></ng-include>
<!-- Display message from scope (set by HomeController) -->
<p>{{ message }}</p>