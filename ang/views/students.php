<!-- Include header with breadcrumb (note: 'Ascending' may be a typo) -->
<ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=true; breadcrumbText='Ascending'"></ng-include>
<!-- Include flash message partial for user feedback -->
<ng-include src="'views/partials/flash-message.php'"></ng-include>
<!-- Button to navigate to add student page -->
<a href="#/students/add" class="btn btn-add">Add New Student</a>
<!-- Include reusable student table with edit and delete options -->
<ng-include src="'views/partials/table-students.php'" ng-init="tableTitle='Students'; showEdit=true; showDelete=true" style="margin-top: 100px; background-color: aqua;"></ng-include>