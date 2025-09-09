<div class="student-form-container">
  <h1>{{ title }}</h1>
  
  <!-- Flash Message -->
  <div flash-message ng-show="flashMessage" class="flash-message flash-{{ flashType }}">
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

        <!-- Email Field valid-email -->
    <div class="form-group">
      <label for="email">Email *</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        ng-model="student.email" 
        required 
        maxlength="100"
        validEmail
        valid-email
        class="form-control"
        ng-class="{ 'error': studentForm.email.$invalid && studentForm.email.$touched }"
      >
      <email-validation-message form="studentForm" field="email" email-suggestion="emailSuggestion"></email-validation-message>
    </div>

    <!-- Phone Field   valid-phone -->
    <div class="form-group">
      <label for="phone">Phone *</label>
      <input 
        type="tel" 
        id="phone" 
        name="phone" 
        ng-model="student.phone" 
        required
        maxlength="18"
        validPhone
         valid-phone
        class="form-control"
        ng-class="{ 'error': studentForm.phone.$invalid && studentForm.phone.$touched }"
        placeholder="+12025550123"
      >
      <phone-validation-message form="studentForm" field="phone"></phone-validation-message>
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



 <!-- <div class="form-group">
      <label for="phone">Phone</label>
      <input 
        type="tel" 
        id="phone" 
        name="phone" 
        ng-model="student.phone" 
        required
        maxlength="20"
        valid-phone
        class="form-control"
        ng-class="{ 'error': studentForm.phone.$invalid && studentForm.phone.$touched }"
      >
      <phone-validation-message form="studentForm" field="phone"></phone-validation-message>
    </div> -->