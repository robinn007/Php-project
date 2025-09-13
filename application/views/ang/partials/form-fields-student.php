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

    <!-- Email Field -->
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

    <!-- Phone Field -->
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

    <!-- State Field with improved layout -->
    <div class="form-group">
      <label for="state">State *</label>
      <select 
        id="state" 
        name="state" 
        ng-model="student.state" 
        required
        class="form-control"
        ng-class="{ 'error': studentForm.state.$invalid && studentForm.state.$touched }"
      >
        <option value="">Select a State</option>
        <option ng-repeat="state in states" value="{{ state }}" ng-selected="student.state === state">{{ state }}</option>
      </select>
      <div ng-show="studentForm.state.$invalid && studentForm.state.$touched" class="error-message">
        <span ng-show="studentForm.state.$error.required">State is required.</span>
      </div>
      
      <!-- Available States List for better UX -->
      <!-- <div class="available-states-info">
        <small class="states-helper-text">Available states and union territories:</small>
        <div class="states-grid">
          <span ng-repeat="state in states" class="state-item" ng-class="{'selected-state': student.state === state}">
            {{ state }}
          </span>
        </div>
      </div> -->
    </div>
    
    <!-- Form Actions -->
    <!-- <div class="form-actions">
      <button type="submit" class="btn btn-primary" ng-disabled="studentForm.$invalid">
        {{ action === 'edit' ? 'Update Student' : 'Add Student' }}
      </button>
      <button type="button" class="btn btn-secondary" ng-click="goToStudents()">
        Cancel
      </button>
    </div> -->
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