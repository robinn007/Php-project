<div class="auth-container">
    <ng-include src="'/partials/header'" ng-init="showBreadcrumb=false; title='Login'"></ng-include>
    <ng-include src="'/partials/flash-message'"></ng-include>
    <form name="loginForm" ng-submit="submitForm()" novalidate>
        <ng-include src="'/partials/form-fields-auth'" ng-init="isSignup=false"></ng-include>
        <button type="submit" class="btn btn-primary" ng-disabled="loginForm.$invalid">Login</button>
    </form>
    <p>Don't have an account? <a href="/signup">Sign Up</a></p>
</div>