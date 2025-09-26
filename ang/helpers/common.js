/**
 * @file common.js
 * @description Utility module for handling AJAX calls across the Student Management System.
 */
angular.module('myApp').factory('AjaxHelper', ['$http', '$q', function($http, $q) {
    console.log('AjaxHelper initialized');

    // Custom function to serialize object to application/x-www-form-urlencoded
    function serializeData(data) {
        if (!data) return '';
        var pairs = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                var value = data[key];
                if (value !== null && value !== undefined) {
                    pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                }
            }
        }
        return pairs.join('&');
    }

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
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': method === 'POST' || method === 'PUT' ? 'application/x-www-form-urlencoded; charset=UTF-8' : 'application/json'
            }
        }, config || {});

        if (currentCanceller) {
            requestConfig.timeout = currentCanceller.promise;
        }

        if (method === 'GET' && data) {
            requestConfig.params = data;
            console.log('AjaxHelper: GET params:', data);
        } else if (data && (method === 'POST' || method === 'PUT')) {
            // Serialize data to URL-encoded format using custom serializeData function
            requestConfig.data = serializeData(data);
            console.log('AjaxHelper: POST data (URL-encoded):', requestConfig.data);
        }

        console.log('AjaxHelper: Request headers:', requestConfig.headers);

        $http(requestConfig).then(
            function(response) {
                console.log('AjaxHelper: Response from', url, ':', response.data);

                if (currentCanceller && requestCancellers[requestKey] === currentCanceller) {
                    delete requestCancellers[requestKey];
                }

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
            },
            function(error) {
                if (currentCanceller && requestCancellers[requestKey] === currentCanceller) {
                    delete requestCancellers[requestKey];
                }

                console.error('AjaxHelper: Error in', method, 'request to', url, ':', {
                    status: error.status,
                    statusText: error.statusText,
                    data: error.data || 'No data',
                    headers: error.headers ? error.headers() : 'No headers'
                });

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
                var flashMessage = error.data && error.data.flashMessage ? error.data.flashMessage : 'Error: ' + errorMessage;
                var flashType = error.data && error.data.flashType ? error.data.flashType : 'error';

                deferred.reject({
                    message: errorMessage,
                    status: error.status || 0,
                    flashMessage: flashMessage,
                    flashType: flashType,
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