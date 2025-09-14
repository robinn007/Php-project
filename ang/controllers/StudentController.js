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
    $scope.selectedState = '';
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
    $scope.selectedState = searchParams.state || '';
    console.log('Controller: Setting selectedState to:', $scope.selectedState); // ADD THIS
    console.log('Initialized filters from URL:', { search: $scope.searchText, state: $scope.selectedState });
}

    $scope.loadStudents = function(searchText, selectedState) {
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
        if (selectedState) {
            params.state = selectedState;
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
        if ($scope.selectedState) {
            params.state = $scope.selectedState;
        }
        console.log('Updating URL with params:', params);
        $location.search(params);
    }

    // Search handler
    $scope.handleSearch = function(searchText) {
        $scope.searchText = searchText || '';
        console.log('Search triggered with:', $scope.searchText);
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedState);
    };

    // State filter handler
    $scope.handleStateChange = function(state) {
        $scope.selectedState = state || '';
        console.log('State filter changed to:', $scope.selectedState);
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedState);
    };

    // Watch for URL parameter changes
    $scope.$on('$locationChangeSuccess', function() {
        initFilters();
        $scope.loadStudents($scope.searchText, $scope.selectedState);
    });

    // Initial load
    initFilters();
    $scope.loadStudents($scope.searchText, $scope.selectedState);

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
        $scope.loadStudents($scope.searchText, $scope.selectedState);
    });
}]);