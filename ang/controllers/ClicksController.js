angular.module('myApp').controller('ClicksController', ['$scope', 'AjaxHelper', 'AuthService', '$location', function($scope, AjaxHelper, AuthService, $location) {
    'use strict';

    $scope.title = 'Clicks Dashboard';
    $scope.clicks = [];
    $scope.isLoading = true;
    $scope.isExporting = false;
    $scope.flashMessage = 'Loading clicks...';
    $scope.flashType = 'info';
    $scope.exportFormat = 'csv'; // Default export format
    
    // Initialize from URL parameters or defaults
    var urlParams = $location.search();
    $scope.currentPage = parseInt(urlParams.page) || 1;
    $scope.itemsPerPage = parseInt(urlParams.limit) || 50;
    $scope.searchQuery = urlParams.search || '';
    
    $scope.totalCount = 0;
    $scope.totalPages = 0;
    $scope.hasNext = false;
    $scope.hasPrev = false;

    console.log('ClicksController initialized');

    if (!AuthService.isLoggedIn()) {
        console.log('User not logged in, redirecting to /login');
        $scope.flashMessage = 'Please log in to view the clicks dashboard.';
        $scope.flashType = 'error';
        $location.path('/login');
        return;
    }

    // Function to update URL parameters
    function updateUrlParams() {
        var params = {
            page: $scope.currentPage,
            limit: $scope.itemsPerPage
        };
        
        if ($scope.searchQuery && $scope.searchQuery.trim()) {
            params.search = $scope.searchQuery.trim();
        }
        
        $location.search(params);
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
                    
                    // Update URL after successful load
                    updateUrlParams();
                    
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

    // Replace your exportClicks function in ClicksController.js with this:

$scope.exportClicks = function(format) {
    $scope.isExporting = true;
    format = format || $scope.exportFormat;

    var exportParams = {
        export: format
    };

    if ($scope.searchQuery && $scope.searchQuery.trim()) {
        exportParams.search = $scope.searchQuery.trim();
    }

    console.log('Making export request with params:', exportParams);

    // FIXED: Use the correct route - your export method is in Students controller
    AjaxHelper.ajaxRequest('GET', '/students/export', exportParams)
        .then(function(response) {
            console.log('Export response received:', response);
            
            if (response.data && response.data.success && response.data.file_data) {
                var blob, fileName;
                var now = new Date();
                var dateStr = now.getFullYear() + '-' + 
                             ('0' + (now.getMonth() + 1)).slice(-2) + '-' + 
                             ('0' + now.getDate()).slice(-2) + '_' +
                             ('0' + now.getHours()).slice(-2) + '-' +
                             ('0' + now.getMinutes()).slice(-2);

                if (response.data.file_type === 'csv') {
                    blob = new Blob([response.data.file_data], { type: 'text/csv;charset=utf-8;' });
                    fileName = 'clicks_export_' + dateStr + '.csv';
                    if ($scope.searchQuery && $scope.searchQuery.trim()) {
                        fileName = 'clicks_filtered_export_' + dateStr + '.csv';
                    }
                } else if (response.data.file_type === 'excel') {
                    var binary = atob(response.data.file_data);
                    var array = new Uint8Array(binary.length);
                    for (var i = 0; i < binary.length; i++) {
                        array[i] = binary.charCodeAt(i);
                    }
                    blob = new Blob([array], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    fileName = 'clicks_export_' + dateStr + '.xlsx';
                    if ($scope.searchQuery && $scope.searchQuery.trim()) {
                        fileName = 'clicks_filtered_export_' + dateStr + '.xlsx';
                    }
                } else {
                    console.error('Unsupported file type:', response.data.file_type);
                    $scope.flashMessage = 'Export failed: Unsupported file type';
                    $scope.flashType = 'error';
                    return;
                }

                // Create download link
                var link = document.createElement('a');
                var url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', fileName);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                $scope.flashMessage = response.data.message || (response.data.file_type.toUpperCase() + ' export completed successfully!');
                $scope.flashType = 'success';

            } else {
                console.error('Export failed: Invalid response structure');
                console.error('Response data:', response.data);
                $scope.flashMessage = response.data && response.data.message ? response.data.message : 'Export failed: Invalid response structure';
                $scope.flashType = 'error';
            }
        })
        .catch(function(error) {
            console.error('Export error:', error);
            console.error('Error details:', {
                status: error.status,
                statusText: error.statusText,
                data: error.data
            });
            
            var errorMessage = 'Export failed: ';
            if (error.data && error.data.message) {
                errorMessage += error.data.message;
            } else if (error.status) {
                errorMessage += 'HTTP ' + error.status + ' - ' + (error.statusText || 'Server error');
            } else {
                errorMessage += 'Unable to connect to server';
            }
            
            $scope.flashMessage = errorMessage;
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

    // Watch for URL parameter changes (browser back/forward navigation)
    $scope.$on('$locationChangeSuccess', function() {
        var urlParams = $location.search();
        var newPage = parseInt(urlParams.page) || 1;
        var newLimit = parseInt(urlParams.limit) || 50;
        var newSearch = urlParams.search || '';
        
        // Only reload if parameters actually changed
        if (newPage !== $scope.currentPage || 
            newLimit !== $scope.itemsPerPage || 
            newSearch !== $scope.searchQuery) {
            
            $scope.currentPage = newPage;
            $scope.itemsPerPage = newLimit;
            $scope.searchQuery = newSearch;
            
            loadClicksData();
        }
    });

    // Initialize
    loadClicksData();
}]);