/**
 * @file StudentController.js
 * @description Controller for managing student list view including fetching and deleting students.
 */
angular.module('myApp').controller('StudentController', ['$scope', 'AjaxHelper', '$sce', '$filter', '$location', function($scope, AjaxHelper, $sce, $filter, $location) {
    $scope.title = "Students Dashboard......";
    $scope.students = [];
    $scope.flashMessage = 'Loading students...';
    $scope.flashType = 'info';
    $scope.searchText = '';
    $scope.selectedStates = [];
    var lastRequestPromise = null; // Track the last AJAX request promise

    // Define and sort states alphabetically
    $scope.states = [
        'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh',
        'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa', 'Gujarat', 'Haryana',
        'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry',
        'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand',
        'West Bengal'
    ].sort();

    console.log('StudentController initialized');

    // Initialize filters from URL parameters
    function initFilters() {
        var searchParams = $location.search();
        $scope.searchText = searchParams.search || '';
        
        // Handle selectedStates from URL
        if (searchParams.states) {
            try {
                $scope.selectedStates = JSON.parse(searchParams.states);
                if (!Array.isArray($scope.selectedStates)) {
                    $scope.selectedStates = [$scope.selectedStates];
                }
            } catch (e) {
                console.error('Error parsing states from URL:', e);
                $scope.selectedStates = angular.copy($scope.states); // Default to all states
            }
        } else {
            $scope.selectedStates = angular.copy($scope.states); // Default to all states
        }
        
        console.log('Initialized filters from URL:', { 
            search: $scope.searchText, 
            states: $scope.selectedStates.length + ' states selected'
        });
    }

    $scope.loadStudents = function(searchText, selectedStates) {
        // Cancel any previous request
        if (lastRequestPromise) {
            console.log('StudentController: Cancelling previous loadStudents request');
            // The cancellation is handled in AjaxHelper
        }

        $scope.flashMessage = 'Loading students...';
        $scope.flashType = 'info';
        var params = {};
        
        if (searchText) {
            params.search = searchText;
        }
        
        // Handle multiple states
        if (selectedStates && selectedStates.length > 0) {
            // If all states are selected, don't send state filter (equivalent to no filter)
            if (selectedStates.length < $scope.states.length) {
                params.states = JSON.stringify(selectedStates);
            }
        }
        
        console.log('Loading students with params:', params);

        // Store the new request promise
        lastRequestPromise = AjaxHelper.ajaxRequest('GET', '/students/manage', params)
            .then(function(response) {
                console.log('getStudents response:', response);
                $scope.flashMessage = response.flashMessage;
                $scope.flashType = response.flashType;
                if (response.data.success) {
                    $scope.students = response.data.students || [];
                }
                lastRequestPromise = null; // Clear the promise after completion
            })
            .catch(function(error) {
                // Ignore cancellation errors
                if (error.message === 'cancelled') {
                    console.log('StudentController: Request was cancelled, ignoring error');
                    return;
                }
                console.error('Error loading students:', error);
                $scope.flashMessage = error.flashMessage;
                $scope.flashType = error.flashType;
                lastRequestPromise = null; // Clear the promise after error
            });
    };

    // Update URL parameters
    function updateUrlParams() {
        var params = {};
        if ($scope.searchText) {
            params.search = $scope.searchText;
        }
        if ($scope.selectedStates && $scope.selectedStates.length > 0 && $scope.selectedStates.length < $scope.states.length) {
            params.states = JSON.stringify($scope.selectedStates);
        }
        console.log('Updating URL with params:', params);
        $location.search(params);
    }

    // Search handler
    $scope.handleSearch = function(searchText) {
        $scope.searchText = searchText || '';
        console.log('Search triggered with:', $scope.searchText);
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    };

    // State filter handler
    $scope.handleStateChange = function(states) {
        $scope.selectedStates = states || [];
        console.log('State filter changed to:', $scope.selectedStates.length + ' states selected');
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    };

    // Watch for URL parameter changes
    $scope.$on('$locationChangeSuccess', function() {
        initFilters();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    });

    // Initial load
    initFilters();
    $scope.loadStudents($scope.searchText, $scope.selectedStates);

    $scope.deleteStudent = function(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            AjaxHelper.ajaxRequest('POST', '/students/manage', { action: 'delete', id: id })
                .then(function(response) {
                    if (response.data.success) {
                        $scope.students = $scope.students.filter(function(student) {
                            return student.id !== id;
                        });
                        $scope.flashMessage = response.flashMessage;
                        $scope.flashType = response.flashType;
                    } else {
                        $scope.flashMessage = response.flashMessage;
                        $scope.flashType = response.flashType;
                    }
                })
                .catch(function(error) {
                    $scope.flashMessage = error.flashMessage;
                    $scope.flashType = error.flashType;
                });
        }
    };

    $scope.$on('studentUpdated', function() {
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    });
}]);