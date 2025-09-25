angular.module('myApp').controller('StudentController', ['$scope', 'AjaxHelper', '$sce', '$filter', '$location', function($scope, AjaxHelper, $sce, $filter, $location) {
    $scope.title = "Students Dashboard......";
    $scope.students = [];
    $scope.flashMessage = 'Loading students...';
    $scope.flashType = 'info';
    $scope.searchText = '';
    $scope.selectedStates = [];
    var lastRequestPromise = null;

    $scope.states = [
        'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh',
        'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa', 'Gujarat', 'Haryana',
        'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry',
        'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand',
        'West Bengal'
    ].sort();

    console.log('StudentController initialized');

    function initFilters() {
        var searchParams = $location.search();
        $scope.searchText = searchParams.search || '';
        if (searchParams.states) {
            try {
                $scope.selectedStates = JSON.parse(searchParams.states);
                if (!Array.isArray($scope.selectedStates)) {
                    $scope.selectedStates = [$scope.selectedStates];
                }
            } catch (e) {
                console.error('Error parsing states from URL:', e);
                $scope.selectedStates = angular.copy($scope.states);
            }
        } else {
            $scope.selectedStates = angular.copy($scope.states);
        }
        console.log('Initialized filters:', { search: $scope.searchText, states: $scope.selectedStates.length + ' states' });
    }

    $scope.loadStudents = function(searchText, selectedStates) {
        if (lastRequestPromise) {
            console.log('Cancelling previous loadStudents request');
        }

        $scope.flashMessage = 'Loading students...';
        $scope.flashType = 'info';
        var params = {};
        if (searchText) {
            params.search = searchText;
        }
        if (selectedStates && selectedStates.length > 0 && selectedStates.length < $scope.states.length) {
            params.states = JSON.stringify(selectedStates);
        }
        console.log('Loading students with params:', params);

        lastRequestPromise = AjaxHelper.ajaxRequest('GET', '/students/manage', params)
            .then(function(response) {
                console.log('getStudents response:', response);
                $scope.flashMessage = response.flashMessage;
                $scope.flashType = response.flashType;
                if (response.data.success) {
                    $scope.students = response.data.students || [];
                    $scope.students.forEach(function(student) {
                        student.statusDisplay = student.status === 'online' ? 'Online' : 'Offline';
                        console.log('Student status for ' + student.email + ': ' + student.statusDisplay);
                    });
                    $scope.$apply(); // Ensure AngularJS updates the view
                } else {
                    console.error('Failed to load students:', response.data.message);
                }
                lastRequestPromise = null;
            })
            .catch(function(error) {
                if (error.message === 'cancelled') {
                    console.log('Request cancelled');
                    return;
                }
                console.error('Error loading students:', error);
                $scope.flashMessage = error.flashMessage || 'Failed to load students';
                $scope.flashType = error.flashType || 'error';
                $scope.$apply();
                lastRequestPromise = null;
            });
    };

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

    $scope.handleSearch = function(searchText) {
        $scope.searchText = searchText || '';
        console.log('Search triggered with:', $scope.searchText);
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    };

    $scope.handleStateChange = function(states) {
        $scope.selectedStates = states || [];
        console.log('State filter changed to:', $scope.selectedStates.length + ' states');
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    };

    $scope.$on('$locationChangeSuccess', function() {
        initFilters();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    });

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
                    $scope.$apply();
                })
                .catch(function(error) {
                    $scope.flashMessage = error.flashMessage || 'Failed to delete student';
                    $scope.flashType = error.flashType || 'error';
                    $scope.$apply();
                });
        }
    };

    $scope.$on('studentUpdated', function() {
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    });
}]);