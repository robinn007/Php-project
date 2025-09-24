angular.module('myApp').controller('ClicksController', ['$scope', 'AjaxHelper', 'AuthService', '$location', '$timeout', function($scope, AjaxHelper, AuthService, $location, $timeout) {
    'use strict';
    
    $scope.title = 'Clicks Dashboard';
    $scope.clicks = [];
    $scope.isLoading = true;
    $scope.isExporting = false;
    $scope.flashMessage = 'Loading clicks...';
    $scope.flashType = 'info';
    $scope.exportFormat = 'csv'; // Default format

    // Initialize from URL parameters or defaults
    var urlParams = $location.search();
    $scope.currentPage = parseInt(urlParams.page) || 1;
    $scope.itemsPerPage = parseInt(urlParams.limit) || 50; // Get from URL or default to 50
    $scope.searchQuery = urlParams.search || '';

    $scope.totalCount = 0;
    $scope.totalPages = 0;
    $scope.hasNext = false;
    $scope.hasPrev = false;

    if (!AuthService.isLoggedIn()) {
        $scope.flashMessage = 'Please log in to view the clicks dashboard.';
        $scope.flashType = 'error';
        $location.path('/login');
        return;
    }

    // Debounce search to prevent rapid requests
    var searchTimeout;
    $scope.search = function() {
        if (searchTimeout) {
            $timeout.cancel(searchTimeout);
        }
        searchTimeout = $timeout(function() {
            $scope.currentPage = 1;
            loadClicksData();
        }, 1000);
    };

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
            limit: $scope.itemsPerPage // Make sure this is sent to backend
        };
        
        if ($scope.searchQuery && $scope.searchQuery.trim()) {
            params.search = $scope.searchQuery.trim();
        }
        
        console.log('Loading clicks with params:', params); // Debug log
        
        AjaxHelper.ajaxRequest('GET', '/clicks', params)
            .then(function(response) {
                if (!response || !response.data) {
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
                        $scope.currentPage = 1;
                        $scope.totalPages = $scope.clicks.length > 0 ? 1 : 0;
                        $scope.totalCount = $scope.clicks.length;
                        $scope.hasNext = false;
                        $scope.hasPrev = false;
                    }
                    
                    updateUrlParams();
                    
                    if ($scope.clicks.length === 0) {
                        $scope.flashMessage = $scope.searchQuery ? 'No clicks found matching your search.' : 'No clicks found.';
                        $scope.flashType = 'info';
                    } else {
                        $scope.flashMessage = 'Showing ' + $scope.clicks.length + ' of ' + $scope.totalCount + ' clicks (Page ' + $scope.currentPage + ' of ' + $scope.totalPages + ')';
                        $scope.flashType = 'success';
                    }
                } else {
                    $scope.flashMessage = response.data.message || 'Failed to load clicks data';
                    $scope.flashType = 'error';
                }
            })
            .catch(function(error) {
                if (error && error.data) {
                    $scope.flashMessage = error.data.message || 'Error: Invalid response format';
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
            export: $scope.exportFormat
        };

        if ($scope.searchQuery && $scope.searchQuery.trim()) {
            exportParams.search = $scope.searchQuery.trim();
        }

        AjaxHelper.ajaxRequest('GET', '/clicks/export', exportParams)
            .then(function(response) {
                if (response.data && response.data.success && response.data.file_data) {
                    var blob, fileName;
                    var now = new Date();
                    var dateStr = now.getFullYear() + '-' + 
                                 ('0' + (now.getMonth() + 1)).slice(-2) + '-' + 
                                 ('0' + now.getDate()).slice(-2) + '_' +
                                 ('0' + now.getHours()).slice(-2) + '-' +
                                 ('0' + now.getMinutes()).slice(-2);

                    if ($scope.exportFormat === 'xlsx') {
                        var binaryString = atob(response.data.file_data);
                        var bytes = new Uint8Array(binaryString.length);
                        for (var i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        blob = new Blob([bytes], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                        fileName = 'clicks_export_' + dateStr + '.xlsx';
                        if ($scope.searchQuery && $scope.searchQuery.trim()) {
                            fileName = 'clicks_filtered_export_' + dateStr + '.xlsx';
                        }
                    } else if ($scope.exportFormat === 'xls') {
                        var binaryString = atob(response.data.file_data);
                        var bytes = new Uint8Array(binaryString.length);
                        for (var i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        blob = new Blob([bytes], { type: 'application/vnd.ms-excel' });
                        fileName = 'clicks_export_' + dateStr + '.xls';
                        if ($scope.searchQuery && $scope.searchQuery.trim()) {
                            fileName = 'clicks_filtered_export_' + dateStr + '.xls';
                        }
                    } else {
                        blob = new Blob([response.data.file_data], { type: 'text/csv;charset=utf-8;' });
                        fileName = 'clicks_export_' + dateStr + '.csv';
                        if ($scope.searchQuery && $scope.searchQuery.trim()) {
                            fileName = 'clicks_filtered_export_' + dateStr + '.csv';
                        }
                    }

                    var link = document.createElement('a');
                    var url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', fileName);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);

                    var formatText = $scope.exportFormat.toUpperCase();
                    $scope.flashMessage = formatText + ' export completed successfully!';
                    $scope.flashType = 'success';
                } else {
                    $scope.flashMessage = response.data && response.data.message ? response.data.message : 'Export failed: Invalid response';
                    $scope.flashType = 'error';
                }
            })
            .catch(function(error) {
                $scope.flashMessage = error.data && error.data.message ? error.data.message : 'Export failed: Server error';
                $scope.flashType = 'error';
            })
            .finally(function() {
                $scope.isExporting = false;
            });
    };

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

    $scope.clearSearch = function() {
        $scope.searchQuery = '';
        $scope.currentPage = 1;
        loadClicksData();
    };

    $scope.changeItemsPerPage = function() {
        console.log('Items per page changed to:', $scope.itemsPerPage); // Debug log
        $scope.currentPage = 1; // Reset to first page
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

    // Watch for URL parameter changes
    $scope.$on('$locationChangeSuccess', function() {
        var urlParams = $location.search();
        var newPage = parseInt(urlParams.page) || 1;
        var newLimit = parseInt(urlParams.limit) || 50;
        var newSearch = urlParams.search || '';
        
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