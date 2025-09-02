<!-- Student form container -->
<div class="form-container">
  <!-- Include header with dynamic breadcrumb -->
  <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=true; breadcrumbText=title"></ng-include>
  <!-- Include flash message partial for user feedback -->
  <ng-include src="'views/partials/flash-message.php'"></ng-include>
  
  <!-- Form header with dynamic title -->
  <div class="form-header">
    <h1>{{ action === 'edit' ? 'Edit Student' : 'Add New Student' }}</h1>
  </div>
  
  <!-- Student form with validation -->
  <form name="studentForm" ng-submit="submitForm()" novalidate>
    <!-- Include reusable student form fields -->
    <ng-include src="'views/partials/form-fields-student.php'"></ng-include>
    <!-- Form actions -->
    <div class="form-actions">
      <!-- Submit button with dynamic text -->
      <button type="submit" class="btn btn-submit">
        {{ action === 'edit' ? 'Update Student' : 'Add Student' }}
      </button>
      <!-- Cancel button linking to students list -->
      <a href="#/students" class="btn btn-cancel">Cancel</a>
    </div>
  </form>
  
  <!-- Form footer with required field note -->
  <div class="form-footer">
    <p><em>Fields marked with <span class="required">*</span> are required.</em></p>
  </div>
</div>