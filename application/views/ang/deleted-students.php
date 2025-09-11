<!-- Include header with breadcrumb for deleted students -->
<ng-include src="'/ci/partials/header'" ng-init="showBreadcrumb=true; breadcrumbText='Deleted Students'"></ng-include>
<!-- Include flash message partial for user feedback -->
<ng-include src="'/ci/partials/flash-message'"></ng-include>
<!-- Include reusable student table with restore and permanent delete options -->
<ng-include src="'/ci/partials/table-students'" ng-init="tableTitle='Deleted Students'; showRestore=true; showPermanentDelete=true"></ng-include>