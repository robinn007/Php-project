<div class="auth-container">
  <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=false; title='Sign Up'"></ng-include>
  <ng-include src="'views/partials/flash-message.php'"></ng-include>
  <form name="signupForm" ng-submit="submitForm()" novalidate>
    <ng-include src="'views/partials/form-fields-auth.php'" ng-init="isSignup=true"></ng-include>
    <button type="submit" class="btn btn-primary" ng-disabled="signupForm.$invalid">Sign Up</button>
  </form>
  <p>Already have an account? <a href="ci/ang/login">Login</a></p>
</div>