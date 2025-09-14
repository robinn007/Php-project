/**
 * @file stateFilter.js
 * @description Directive for a state dropdown filter with proper model synchronization.
 */

angular.module('myApp').directive('stateFilter', ['$timeout', function($timeout) {
    return {
        restrict: 'E',
        scope: {
            selectedState: '=',
            onStateChange: '&'
        },
        template: 
            '<div class="form-group state-filter">' +
                '<label for="stateFilter">Filter by State</label>' +
                '<select id="stateFilter" class="form-control">' +
                    '<option value="">All States</option>' +
                    '<option ng-repeat="state in states" value="{{ state }}">{{ state }}</option>' +
                '</select>' +
            '</div>',
        link: function(scope, element, attrs) {
            // Define states (same as in StudentFormController and StudentController)
            scope.states = [
                'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh',
                'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa', 'Gujarat', 'Haryana',
                'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep',
                'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry',
                'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand',
                'West Bengal'
            ].sort();

            var selectElement = element.find('select')[0];
            console.log('State filter directive initialized');

            // Function to update select value
            function updateSelectValue(value) {
                if (selectElement) {
                    selectElement.value = value || '';
                    console.log('Updated select element value to:', selectElement.value);
                }
            }

            // Initialize select value after a timeout to ensure DOM is ready
            $timeout(function() {
                updateSelectValue(scope.selectedState);
                console.log('Initial selectedState:', scope.selectedState);
            }, 100);

            // Handle change event directly on the DOM element
            selectElement.addEventListener('change', function() {
                var newValue = this.value;
                console.log('Select changed to:', newValue);
                
                scope.$apply(function() {
                    scope.selectedState = newValue;
                    scope.onStateChange({ state: newValue || '' });
                });
            });

            // Watch for external changes to selectedState
            scope.$watch('selectedState', function(newValue, oldValue) {
                if (newValue !== oldValue) {
                    console.log('selectedState changed from:', oldValue, 'to:', newValue);
                    $timeout(function() {
                        updateSelectValue(newValue);
                    }, 0);
                }
            });

            // Also watch with a longer timeout to catch late updates
            scope.$watch('selectedState', function(newValue) {
                $timeout(function() {
                    if (selectElement.value !== (newValue || '')) {
                        console.log('Correcting select value from:', selectElement.value, 'to:', newValue);
                        updateSelectValue(newValue);
                    }
                }, 200);
            });
        }
    };
}]);