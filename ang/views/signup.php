<!-- Signup page container -->
<div class="auth-container">
  <!-- Include header with custom title and no breadcrumb -->
  <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=false; title='Sign Up'"></ng-include>
  <!-- Include flash message partial for user feedback -->
  <ng-include src="'views/partials/flash-message.php'"></ng-include>
  <!-- Signup form with validation -->
  <form name="signupForm" ng-submit="submitForm()" novalidate>
    <!-- Include reusable authentication form fields with signup-specific fields -->
    <ng-include src="'views/partials/form-fields-auth.php'" ng-init="isSignup=true"></ng-include>
    <!-- Submit button, disabled if form is invalid -->
    <button type="submit" class="btn btn-primary" ng-disabled="signupForm.$invalid">Sign Up</button>
  </form>
  <!-- Link to login page -->
  <p>Already have an account? <a href="#/login">Login</a></p>
</div>