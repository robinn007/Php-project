angular.module('myApp').controller('ClicksController', ['$scope', 'AjaxHelper', 'AuthService', '$location', function($scope, AjaxHelper, AuthService, $location) {
    'use strict';

    $scope.title = 'Clicks Dashboard';
    $scope.clicks = [];
    $scope.isLoading = true;
    $scope.flashMessage = 'Loading clicks...';
    $scope.flashType = 'info';
    
    // Pagination variables
    $scope.currentPage = 1;
    $scope.itemsPerPage = 100; // Start with 100 for better performance
    $scope.totalCount = 0;
    $scope.totalPages = 0;
    $scope.searchQuery = '';

    console.log('ClicksController initialized');

    if (!AuthService.isLoggedIn()) {
        console.log('User not logged in, redirecting to /login');
        $scope.flashMessage = 'Please log in to view the clicks dashboard.';
        $scope.flashType = 'error';
        $location.path('/login');
        return;
    }

    function loadClicksData() {
        $scope.isLoading = true;
        $scope.flashMessage = 'Loading clicks...';
        $scope.flashType = 'info';
        
        var params = {
            page: $scope.currentPage,
            limit: $scope.itemsPerPage
        };
        
        if ($scope.searchQuery && $scope.searchQuery.trim()) {
            params.search = $scope.searchQuery.trim();
        }
        
        console.log('Making AJAX request to /clicks with params:', params);
        
        AjaxHelper.ajaxRequest('GET', '/clicks', params)
            .then(function(response) {
                console.log('Received response:', response);
                
                if (!response || !response.data) {
                    console.error('Empty or invalid response received');
                    $scope.flashMessage = 'Error: Empty response from server';
                    $scope.flashType = 'error';
                    return;
                }
                
                if (response.data.success) {
                    $scope.clicks = response.data.clicks || [];
                    
                    // Handle pagination data
                    if (response.data.pagination) {
                        $scope.currentPage = response.data.pagination.current_page;
                        $scope.totalPages = response.data.pagination.total_pages;
                        $scope.totalCount = response.data.pagination.total_count;
                        $scope.hasNext = response.data.pagination.has_next;
                        $scope.hasPrev = response.data.pagination.has_prev;
                    }
                    
                    console.log('Clicks loaded:', $scope.clicks.length, 'of', $scope.totalCount, 'total');
                    
                    if ($scope.clicks.length === 0) {
                        $scope.flashMessage = $scope.searchQuery ? 'No clicks found matching your search.' : 'No clicks found.';
                        $scope.flashType = 'info';
                    } else {
                        $scope.flashMessage = 'Showing ' + $scope.clicks.length + ' of ' + $scope.totalCount + ' clicks (Page ' + $scope.currentPage + ' of ' + $scope.totalPages + ')';
                        $scope.flashType = 'success';
                    }
                } else {
                    console.log('Response indicates failure:', response.data);
                    $scope.flashMessage = response.data.message || 'Failed to load clicks data';
                    $scope.flashType = 'error';
                }
            })
            .catch(function(error) {
                console.error('AJAX error:', error);
                
                if (error && error.data) {
                    if (error.data.message) {
                        $scope.flashMessage = error.data.message;
                    } else if (typeof error.data === 'string') {
                        $scope.flashMessage = 'Server error: ' + error.data;
                    } else {
                        $scope.flashMessage = 'Error: Invalid response format';
                    }
                } else if (error && error.status) {
                    $scope.flashMessage = 'HTTP Error ' + error.status + ': ' + (error.statusText || 'Connection failed');
                } else {
                    $scope.flashMessage = 'Error: Unable to connect to server';
                }
                
                $scope.flashType = 'error';
            })
            .finally(function() {
                $scope.isLoading = false;
            });
    }

    // Pagination functions
    $scope.goToPage = function(page) {
        if (page >= 1 && page <= $scope.totalPages && page !== $scope.currentPage) {
            $scope.currentPage = page;
            loadClicksData();
        }
    };

    $scope.nextPage = function() {
        if ($scope.hasNext) {
            $scope.currentPage++;
            loadClicksData();
        }
    };

    $scope.prevPage = function() {
        if ($scope.hasPrev) {
            $scope.currentPage--;
            loadClicksData();
        }
    };

    // Search functionality
    $scope.search = function() {
        $scope.currentPage = 1; // Reset to first page when searching
        loadClicksData();
    };

    $scope.clearSearch = function() {
        $scope.searchQuery = '';
        $scope.currentPage = 1;
        loadClicksData();
    };

    // Change items per page
    $scope.changeItemsPerPage = function() {
        $scope.currentPage = 1;
        loadClicksData();
    };

    // Generate page numbers for pagination
    $scope.getPageNumbers = function() {
        var pages = [];
        var start = Math.max(1, $scope.currentPage - 2);
        var end = Math.min($scope.totalPages, $scope.currentPage + 2);
        
        for (var i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    };

    // Initial load
    loadClicksData();
}]);