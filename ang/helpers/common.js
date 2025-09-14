/**
 * @file common.js
 * @description Utility module for handling AJAX calls across the Student Management System.
 */
angular.module('myApp').factory('AjaxHelper', ['$http', '$cookies', '$q', function($http, $cookies, $q) {
    console.log('AjaxHelper initialized');

    // Store cancellers by URL to avoid cancelling unrelated requests
    var requestCancellers = {};

    var ajaxRequest = function(method, url, data, config) {
        var deferred = $q.defer();
        
        // Create a unique key for this request type
        var requestKey = method + ':' + url;
        
        // Only cancel previous requests to the same endpoint for GET requests
        if (method === 'GET' && requestCancellers[requestKey]) {
            console.log('AjaxHelper: Cancelling previous GET request to', url);
            requestCancellers[requestKey].resolve();
            delete requestCancellers[requestKey];
        }

        // Create a new canceller for GET requests only
        var currentCanceller = null;
        if (method === 'GET') {
            currentCanceller = $q.defer();
            requestCancellers[requestKey] = currentCanceller;
        }

        console.log('AjaxHelper: Initiating', method, 'request to', url, 'with data:', data);

        // Get CSRF token name from meta tag
        var csrfTokenName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'ci_csrf_token';
        var csrfToken = $cookies.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        console.log('AjaxHelper: CSRF token name:', csrfTokenName, 'CSRF token:', csrfToken?.substring(0, 10) + '...');

        // Prepare request configuration
        var requestConfig = angular.extend({
            method: method,
            url: url,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Content-Type': 'application/json'
            }
        }, config || {});

        // Add timeout promise for cancellation only for GET requests
        if (currentCanceller) {
            requestConfig.timeout = currentCanceller.promise;
        }

        // Handle GET requests with query parameters
        if (method === 'GET' && data) {
            requestConfig.params = data;
            console.log('AjaxHelper: GET params:', data);
        }
        // Add CSRF token to POST data for POST/PUT requests
        else if (data && (method === 'POST' || method === 'PUT')) {
            data[csrfTokenName] = csrfToken;
            console.log('AjaxHelper: POST data with CSRF:', data);
            requestConfig.data = data;
        }

        // Log request headers for debugging
        console.log('AjaxHelper: Request headers:', requestConfig.headers);

        // Perform the AJAX request
        $http(requestConfig).then(
            function(response) {
                console.log('AjaxHelper: Response from', url, ':', response.data);

                // Clear the canceller since the request completed
                if (currentCanceller && requestCancellers[requestKey] === currentCanceller) {
                    delete requestCancellers[requestKey];
                }

                // Check if response is HTML (indicating an error)
                if (typeof response.data === 'string' && response.data.trim().startsWith('<!DOCTYPE')) {
                    console.error('AjaxHelper: HTML Response (Status:', response.status, '):', response.data);
                    deferred.reject({
                        message: 'Server returned an HTML error page',
                        status: response.status,
                        flashMessage: 'Server error: Unexpected response from server',
                        flashType: 'error',
                        responseData: response.data
                    });
                    return;
                }

                // Check if response contains data
                if (!response.data) {
                    console.error('AjaxHelper: Empty response from', url, ', status:', response.status);
                    deferred.reject({
                        message: 'No data received from server',
                        status: response.status,
                        flashMessage: 'Error: Empty response from server',
                        flashType: 'error'
                    });
                    return;
                }

                // Process successful response
                if (response.data.success) {
                    // Update CSRF token if provided in response
                    if (response.data.csrf_token) {
                        $cookies.csrf_token = response.data.csrf_token;
                        console.log('AjaxHelper: CSRF token updated:', response.data.csrf_token.substring(0, 10) + '...');
                    }
                    
                    deferred.resolve({
                        data: response.data,
                        flashMessage: response.data.message || 'Operation successful',
                        flashType: 'success'
                    });
                } else {
                    console.error('AjaxHelper: Server reported failure:', response.data);
                    deferred.reject({
                        message: response.data.message || 'Operation failed',
                        status: response.status,
                        flashMessage: response.data.message || 'Operation failed: Unknown error',
                        flashType: 'error'
                    });
                }
            },
            function(error) {
                // Clear the canceller if the request was cancelled or failed
                if (currentCanceller && requestCancellers[requestKey] === currentCanceller) {
                    delete requestCancellers[requestKey];
                }

                console.error('AjaxHelper: Error in', method, 'request to', url, ':', {
                    status: error.status,
                    statusText: error.statusText,
                    data: error.data || 'No data',
                    headers: error.headers ? error.headers() : 'No headers'
                });

                // Handle cancellation errors more gracefully
                if (error.status === -1 && (error.xhrStatus === 'abort' || !error.statusText)) {
                    console.log('AjaxHelper: Request to', url, 'was cancelled');
                    deferred.reject({
                        message: 'cancelled',
                        status: -1,
                        flashMessage: 'Request cancelled',
                        flashType: 'info'
                    });
                    return;
                }

                var errorMessage = error.statusText || 'Network or server error';
                if (error.data && error.data.message) {
                    errorMessage = error.data.message;
                }
                deferred.reject({
                    message: errorMessage,
                    status: error.status || 0,
                    flashMessage: 'Error: ' + errorMessage,
                    flashType: 'error',
                    responseData: error.data || 'No data'
                });
            }
        );

        return deferred.promise;
    };

    return {
        ajaxRequest: ajaxRequest
    };
}]);