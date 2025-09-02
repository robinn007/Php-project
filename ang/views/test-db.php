<!-- Include header without breadcrumb -->
<ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=false"></ng-include>
<!-- Include flash message partial for user feedback -->
<ng-include src="'views/partials/flash-message.php'"></ng-include>
<!-- Display database status message -->
<div ng-bind-html="message"></div>
<!-- Link to students list -->
<p><a href="#/students">Go to Students List</a></p>