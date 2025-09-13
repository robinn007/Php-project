<div class="form-container">
    <ng-include src="'/partials/header'" ng-init="showBreadcrumb=true; breadcrumbText=title"></ng-include>
    <ng-include src="'/partials/flash-message'"></ng-include>
    
    <div class="form-header">
        <h1>{{ action === 'edit' ? 'Edit Student' : 'Add New Student' }}</h1>
    </div>
    
    <form name="studentForm" ng-submit="submitForm()" novalidate>
        <ng-include src="'/partials/form-fields-student'"></ng-include>
        <div class="form-actions">
            <button type="submit" class="btn btn-submit">
                {{ action === 'edit' ? 'Update Student' : 'Add Student' }}
            </button>
            <a href="/students" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
    
    <div class="form-footer">
        <p><em>Fields marked with <span class="required">*</span> are required.</em></p>
    </div>
</div>