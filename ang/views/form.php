<!-- User form (possibly for testing, not integrated with main app) -->
<h2>User Form</h2>

<!-- Form with AngularJS validation -->
<form name="userForm" ng-submit="submitForm()" novalidate>
  <label>Name:</label>
  <input type="text" ng-model="user.name" required>
  <!-- Show validation error if form submitted and name is empty -->
  <span ng-show="userForm.$submitted && !user.name">Name is required</span>
  <br><br>

  <label>Email:</label>
  <input type="email" name="email" ng-model="user.email" required>
  <!-- Show validation error if form submitted and email is invalid -->
  <span ng-show="userForm.$submitted && userForm.email.$invalid">Valid email required</span>
  <br><br>

  <button type="submit">Submit</button>
</form>

<hr>
<!-- Preview of form data -->
<h3>Preview:</h3>
<p>{{ user }}</p>