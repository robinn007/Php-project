angular.module('myApp').controller('ClicksController', ['$scope', 'AjaxHelper', 'AuthService', '$location', function($scope, AjaxHelper, AuthService, $location) {
    'use strict';

    $scope.title = 'Clicks Dashboard';
    $scope.clicks = [];
    $scope.isLoading = true;
    $scope.isExporting = false;
    $scope.flashMessage = 'Loading clicks...';
    $scope.flashType = 'info';
    
    $scope.currentPage = 1;
    $scope.itemsPerPage = 50;
    $scope.totalCount = 0;
    $scope.totalPages = 0;
    $scope.hasNext = false;
    $scope.hasPrev = false;
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
                    
                    // Ensure pagination data is properly set
                    if (response.data.pagination) {
                        $scope.currentPage = parseInt(response.data.pagination.current_page) || 1;
                        $scope.totalPages = parseInt(response.data.pagination.total_pages) || 0;
                        $scope.totalCount = parseInt(response.data.pagination.total_count) || 0;
                        $scope.hasNext = Boolean(response.data.pagination.has_next);
                        $scope.hasPrev = Boolean(response.data.pagination.has_prev);
                    } else {
                        // Fallback if pagination data is missing
                        $scope.currentPage = 1;
                        $scope.totalPages = $scope.clicks.length > 0 ? 1 : 0;
                        $scope.totalCount = $scope.clicks.length;
                        $scope.hasNext = false;
                        $scope.hasPrev = false;
                    }
                    
                    console.log('Pagination data:', {
                        currentPage: $scope.currentPage,
                        totalPages: $scope.totalPages,
                        totalCount: $scope.totalCount,
                        hasNext: $scope.hasNext,
                        hasPrev: $scope.hasPrev
                    });
                    
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
                    } else if (typeof error.data === 'string' && error.data.includes('<b>Fatal error</b>')) {
                        $scope.flashMessage = 'Server error: Unexpected response format';
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

    $scope.exportClicks = function() {
        $scope.isExporting = true;
        
        var exportParams = {
            export: 'csv'
        };
        
        if ($scope.searchQuery && $scope.searchQuery.trim()) {
            exportParams.search = $scope.searchQuery.trim();
        }
        
        console.log('Exporting clicks with params:', exportParams);
        
        AjaxHelper.ajaxRequest('GET', '/clicks/export', exportParams)
            .then(function(response) {
                if (response.data.success && response.data.csv_data) {
                    var blob = new Blob([response.data.csv_data], { type: 'text/csv;charset=utf-8;' });
                    var link = document.createElement('a');
                    var url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    
                    var now = new Date();
                    var dateStr = now.getFullYear() + '-' + 
                                 ('0' + (now.getMonth() + 1)).slice(-2) + '-' + 
                                 ('0' + now.getDate()).slice(-2) + '_' +
                                 ('0' + now.getHours()).slice(-2) + '-' +
                                 ('0' + now.getMinutes()).slice(-2);
                    
                    var filename = 'clicks_export_' + dateStr + '.csv';
                    if ($scope.searchQuery && $scope.searchQuery.trim()) {
                        filename = 'clicks_filtered_export_' + dateStr + '.csv';
                    }
                    
                    link.setAttribute('download', filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                    
                    $scope.flashMessage = 'Export completed successfully!';
                    $scope.flashType = 'success';
                    
                    console.log('Export completed:', filename);
                } else {
                    console.error('Export failed:', response);
                    $scope.flashMessage = response.data.message || 'Export failed: Unknown error';
                    $scope.flashType = 'error';
                }
            })
            .catch(function(error) {
                console.error('Export error:', error);
                $scope.flashMessage = error.data && error.data.message ? error.data.message : 'Export failed: Unknown error';
                $scope.flashType = 'error';
            })
            .finally(function() {
                $scope.isExporting = false;
            });
    };

    $scope.goToPage = function(page) {
        if (page >= 1 && page <= $scope.totalPages && page !== $scope.currentPage) {
            console.log('Going to page:', page);
            $scope.currentPage = page;
            loadClicksData();
        }
    };

    $scope.nextPage = function() {
        if ($scope.hasNext) {
            console.log('Going to next page:', $scope.currentPage + 1);
            $scope.currentPage++;
            loadClicksData();
        }
    };

    $scope.prevPage = function() {
        if ($scope.hasPrev) {
            console.log('Going to previous page:', $scope.currentPage - 1);
            $scope.currentPage--;
            loadClicksData();
        }
    };

    $scope.search = function() {
        console.log('Search triggered with query:', $scope.searchQuery);
        $scope.currentPage = 1;
        loadClicksData();
    };

    $scope.clearSearch = function() {
        console.log('Clearing search');
        $scope.searchQuery = '';
        $scope.currentPage = 1;
        loadClicksData();
    };

    $scope.changeItemsPerPage = function() {
        console.log('Items per page changed to:', $scope.itemsPerPage);
        $scope.currentPage = 1;
        loadClicksData();
    };

    $scope.getPageNumbers = function() {
        var pages = [];
        var start = Math.max(1, $scope.currentPage - 2);
        var end = Math.min($scope.totalPages, $scope.currentPage + 2);
        
        for (var i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    };

    // Initialize
    loadClicksData();
}]);