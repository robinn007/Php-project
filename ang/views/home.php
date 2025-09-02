<!-- Home page container -->
<div class="home-container">
  <!-- Include header without breadcrumb -->
  <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=false"></ng-include>
  <!-- Include flash message partial for user feedback -->
  <ng-include src="'views/partials/flash-message.php'"></ng-include>
  <!-- Include action buttons for navigation -->
  <ng-include src="'views/partials/action-buttons.php'" ng-init="showViewStudents=true; showAddStudent=true; showTestDb=true"></ng-include>
  <!-- Static feature list -->
  <div class="features">
    <h3>Features</h3>
    <ul>
      <li>Manage student records efficiently</li>
      <li>Add, edit, and delete student information</li>
      <li>View deleted students and restore them</li>
      <li>User authentication and session management</li>
    </ul>
  </div>
</div>