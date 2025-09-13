<!-- <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=true; breadcrumbText='Ascending'"></ng-include>
<ng-include src="'views/partials/flash-message.php'"></ng-include>
<a href="/ci/ang/students/add" class="btn btn-add">Add New Student</a>
<ng-include src="'views/partials/table-students.php'" ng-init="tableTitle='Students'; showEdit=true; showDelete=true" style="margin-top: 100px; background-color: aqua;"></ng-include> -->

<ng-include src="'/partials/header'" ng-init="showBreadcrumb=true"></ng-include>
<ng-include src="'/partials/flash-message'"></ng-include>
<a href="/students/add" class="btn btn-add">Add New Student</a>
<ng-include src="'/partials/table-students'" ng-init="tableTitle='Students'; showEdit=true; showDelete=true" style="margin-top: 100px; background-color: aqua;"></ng-include>