<div class="auth-container">
  <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=false; title='Login'"></ng-include>
  <ng-include src="'views/partials/flash-message.php'"></ng-include>
  <form name="loginForm" ng-submit="submitForm()" novalidate>
    <ng-include src="'views/partials/form-fields-auth.php'" ng-init="isSignup=false"></ng-include>
    <button type="submit" class="btn btn-primary" ng-disabled="loginForm.$invalid">Login</button>
  </form>
  <p>Don't have an account? <a href="/ci/ang/signup">Sign Up</a></p>
</div>