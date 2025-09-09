/**
 * @file common.js
 * @description Utility module for handling AJAX calls across the Student Management System.
 * Provides a centralized AJAX helper function for making HTTP requests with CSRF token handling,
 * response processing, and error handling.
 */

angular.module('myApp').factory('AjaxHelper', ['$http', '$cookies', '$q', function($http, $cookies, $q) {
  console.log('AjaxHelper initialized');

  /**
   * @function ajaxRequest
   * @description Performs an AJAX request with centralized CSRF token handling, response processing, and error handling.
   * @param {string} method - HTTP method ('GET', 'POST', etc.)
   * @param {string} url - API endpoint URL
   * @param {Object} [data] - Data to send with the request (for POST/PUT)
   * @param {Object} [config] - Additional $http config options
   * @returns {Promise} Resolves with processed response data or rejects with an error object
   */
  var ajaxRequest = function(method, url, data, config) {
    var deferred = $q.defer();
    console.log('AjaxHelper: Initiating', method, 'request to', url, 'with data:', data);

    // Prepare request configuration
    var requestConfig = angular.extend({
      method: method,
      url: url,
      headers: {
        'X-CSRF-Token': $cookies.csrf_token || ''
      }
    }, config || {});

    // Add data for POST/PUT requests
    if (data && (method === 'POST' || method === 'PUT')) {
      requestConfig.data = data;
    }

    // Perform the AJAX request
    $http(requestConfig).then(
      function(response) {
        console.log('AjaxHelper: Response from', url, ':', response.data);
        
        // Check if response contains data
        if (!response.data) {
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
          // Handle server-reported failure
          deferred.reject({
            message: response.data.message || 'Operation failed',
            status: response.status,
            flashMessage: response.data.message || 'Operation failed: Unknown error',
            flashType: 'error'
          });
        }
      },
      function(error) {
        console.error('AjaxHelper: Error in', method, 'request to', url, ':', error);
        // Handle HTTP errors
        var errorMessage = error.statusText || 'Network or server error';
        if (error.data && error.data.message) {
          errorMessage = error.data.message;
        }
        deferred.reject({
          message: errorMessage,
          status: error.status || 0,
          flashMessage: 'Error: ' + errorMessage,
          flashType: 'error'
        });
      }
    );

    return deferred.promise;
  };

  return {
    ajaxRequest: ajaxRequest
  };
}]);