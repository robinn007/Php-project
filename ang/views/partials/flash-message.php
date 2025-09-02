<!-- <div ng-show="flashMessage" class="flash-message" ng-class="{ 'success': flashType == 'success', 'error': flashType == 'error', 'info': flashType == 'info' }" flash-message>
  <strong>{{ flashType | capitalize }}:</strong> {{ flashMessage }}
</div> -->

<!-- Flash message display with dynamic styling -->
<div ng-show="flashMessage" style="padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #fff;" 
     ng-style="{'background-color': flashType === 'success' ? '#28a745' : flashType === 'error' ? '#dc3545' : '#17a2b8'}">
  <!-- Display message with capitalize filter -->
  {{flashMessage | capitalizeFilter}}
</div>