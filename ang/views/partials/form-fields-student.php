<div class="student-form-container">
  <h1>{{ title }}</h1>
  
  <!-- Flash Message -->
  <div ng-show="flashMessage" class="flash-message flash-{{ flashType }}">
    {{ flashMessage }}
  </div>

  <form name="studentForm" ng-submit="submitForm()" novalidate>
    
    <!-- Name Field -->
    <div class="form-group">
      <label for="name">Name *</label>
      <input 
        type="text" 
        id="name" 
        name="name" 
        ng-model="student.name" 
        required 
        maxlength="100"
        class="form-control"
        ng-class="{ 'error': studentForm.name.$invalid && studentForm.name.$touched }"
      >
      <div ng-show="studentForm.name.$invalid && studentForm.name.$touched" class="error-message">
        <span ng-show="studentForm.name.$error.required">Name is required.</span>
        <span ng-show="studentForm.name.$error.maxlength">Name must be less than 100 characters.</span>
      </div>
    </div>

    <!-- Email Field -->
    <div class="form-group">
      <label for="email">Email *</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        ng-model="student.email" 
        required 
        maxlength="150"
        class="form-control"
        ng-class="{ 'error': studentForm.email.$invalid && studentForm.email.$touched }"
      >
      <div ng-show="studentForm.email.$invalid && studentForm.email.$touched" class="error-message">
        <span ng-show="studentForm.email.$error.required">Email is required.</span>
        <span ng-show="studentForm.email.$error.email">Please enter a valid email address.</span>
        <span ng-show="studentForm.email.$error.maxlength">Email must be less than 150 characters.</span>
      </div>
    </div>

    <!-- Phone Field -->
    <div class="form-group">
      <label for="phone">Phone</label>
      <input 
        type="tel" 
        id="phone" 
        name="phone" 
        ng-model="student.phone" 
        required
        maxlength="20"
        class="form-control"
        ng-class="{ 'error': studentForm.phone.$invalid && studentForm.phone.$touched }"
      >
      <div ng-show="studentForm.phone.$invalid && studentForm.phone.$touched" class="error-message">
        <span ng-show="studentForm.phone.$error.maxlength">Phone must be less than 20 characters.</span>
      </div>
    </div>

    <!-- Address Field (Contenteditable) -->
    <div class="form-group">
      <label for="address">Address</label>
      <div 
        id="address"
        name="address"
        contenteditable="true"
        ng-model="student.address"
        class="form-control contenteditable-field"
        placeholder="Enter student address..."
        contenteditable-model
      ></div>
    </div>

  </form>
</div>