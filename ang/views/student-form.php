<div class="form-container">
  <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=true; breadcrumbText=title"></ng-include>
  <ng-include src="'views/partials/flash-message.php'"></ng-include>
  
  <div class="form-header">
    <h1>{{ action === 'edit' ? 'Edit Student' : 'Add New Student' }}</h1>
  </div>
  
  <form name="studentForm" ng-submit="submitForm()" novalidate>
    <ng-include src="'views/partials/form-fields-student.php'"></ng-include>
    <div class="form-actions">
      <button type="submit" class="btn btn-submit">
        {{ action === 'edit' ? 'Update Student' : 'Add Student' }}
      </button>
      <a href="/ci/ang/students" class="btn btn-cancel">Cancel..</a>
    </div>
  </form>
  
  <div class="form-footer">
    <p><em>Fields marked with <span class="required">*</span> are required.</em></p>
  </div>
</div>