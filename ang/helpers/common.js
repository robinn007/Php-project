/**
 * @file common.js
 * @description Utility module for handling AJAX calls across the Student Management System.
 */
angular.module('myApp').factory('AjaxHelper', ['$http', '$q', '$timeout', function($http, $q, $timeout) {
    console.log('AjaxHelper initialized');

    var requestCancellers = {};

    var ajaxRequest = function(method, url, data, config) {
        var deferred = $q.defer();
        
        var requestKey = method + ':' + url;
        
        if (method === 'GET' && requestCancellers[requestKey]) {
            console.log('AjaxHelper: Cancelling previous GET request to', url);
            requestCancellers[requestKey].resolve();
            delete requestCancellers[requestKey];
        }

        var currentCanceller = null;
        if (method === 'GET') {
            currentCanceller = $q.defer();
            requestCancellers[requestKey] = currentCanceller;
        }

        console.log('AjaxHelper: Initiating', method, 'request to', url, 'with data:', data);

        var requestConfig = angular.extend({
            method: method,
            url: url,
            timeout: 30000, // 30-second timeout
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        }, config || {});

        // Use JSON for /auth/create_group, URL-encoded for others
        if (url === '/auth/create_group' && (method === 'POST' || method === 'PUT')) {
            requestConfig.headers['Content-Type'] = 'application/json';
            requestConfig.data = data; // Send raw JSON data
            console.log('AjaxHelper: POST data (JSON):', JSON.stringify(data));
        } else if (method === 'POST' || method === 'PUT') {
            requestConfig.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            requestConfig.data = serializeData(data);
            console.log('AjaxHelper: POST data (URL-encoded):', requestConfig.data);
        } else if (method === 'GET' && data) {
            requestConfig.params = data;
            console.log('AjaxHelper: GET params:', data);
        }

        if (currentCanceller) {
            requestConfig.timeout = currentCanceller.promise;
        }

        console.log('AjaxHelper: Request headers:', requestConfig.headers);

        // Add a small delay for critical requests to ensure system is ready
        var requestDelay = 0;
        if (url === '/students' || url === '/auth/check_auth') {
            requestDelay = 100; // 100ms delay for critical endpoints
        }

        $timeout(function() {
            $http(requestConfig).then(
                function(response) {
                    console.log('AjaxHelper: Response from', url, ':', response.data);

                    if (currentCanceller && requestCancellers[requestKey] === currentCanceller) {
                        delete requestCancellers[requestKey];
                    }

                    // Handle HTML error responses
                    if (typeof response.data === 'string' && response.data.trim().startsWith('<!DOCTYPE')) {
                        console.error('AjaxHelper: HTML Response (Status:', response.status, '):', response.data.substring(0, 200) + '...');
                        deferred.reject({
                            message: 'Server returned an HTML error page',
                            status: response.status,
                            flashMessage: 'Server error: Unexpected response from server',
                            flashType: 'error',
                            responseData: response.data
                        });
                        return;
                    }

                    // Handle empty responses
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

                    // Handle success/failure based on response structure
                    if (response.data.hasOwnProperty('success')) {
                        if (response.data.success) {
                            deferred.resolve({
                                data: response.data,
                                flashMessage: response.data.flashMessage || response.data.message || 'Operation successful',
                                flashType: response.data.flashType || 'success'
                            });
                        } else {
                            console.error('AjaxHelper: Server reported failure:', response.data);
                            deferred.reject({
                                message: response.data.message || 'Operation failed',
                                status: response.status,
                                flashMessage: response.data.flashMessage || response.data.message || 'Operation failed: Unknown error',
                                flashType: response.data.flashType || 'error'
                            });
                        }
                    } else {
                        // For responses without success property, assume success if we got data
                        deferred.resolve({
                            data: response.data,
                            flashMessage: 'Operation completed successfully',
                            flashType: 'success'
                        });
                    }
                },
                function(error) {
                    if (currentCanceller && requestCancellers[requestKey] === currentCanceller) {
                        delete requestCancellers[requestKey];
                    }

                    console.error('AjaxHelper: Error in', method, 'request to', url, ':', {
                        status: error.status,
                        statusText: error.statusText,
                        data: error.data || 'No data',
                        headers: error.headers ? error.headers() : 'No headers',
                        config: error.config
                    });

                    // Handle cancelled requests
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

                    // Handle network errors (status 0)
                    if (error.status === 0) {
                        console.warn('AjaxHelper: Network error or CORS issue for', url);
                        deferred.reject({
                            message: 'Network or server error',
                            status: 0,
                            flashMessage: 'Error: Network or server error',
                            flashType: 'error',
                            responseData: error.data || 'No data'
                        });
                        return;
                    }

                    // Handle other errors
                    var errorMessage = error.statusText || 'Network or server error';
                    if (error.data && error.data.message) {
                        errorMessage = error.data.message;
                    }
                    
                    var flashMessage = error.data && error.data.flashMessage ? 
                        error.data.flashMessage : 'Error: ' + errorMessage;
                    var flashType = error.data && error.data.flashType ? 
                        error.data.flashType : 'error';

                    deferred.reject({
                        message: errorMessage,
                        status: error.status || 0,
                        flashMessage: flashMessage,
                        flashType: flashType,
                        responseData: error.data || 'No data'
                    });
                }
            );
        }, requestDelay);

        return deferred.promise;
    };

    // Custom function to serialize object to application/x-www-form-urlencoded
    function serializeData(data) {
        if (!data) return '';
        var pairs = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                var value = data[key];
                if (value !== null && value !== undefined) {
                    if (Array.isArray(value)) {
                        value.forEach(function(item, index) {
                            pairs.push(encodeURIComponent(key + '[]') + '=' + encodeURIComponent(item));
                        });
                    } else {
                        pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                    }
                }
            }
        }
        return pairs.join('&');
    }

    return {
        ajaxRequest: ajaxRequest
    };
}]);