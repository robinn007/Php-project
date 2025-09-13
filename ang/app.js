/**
 * @file app.js
 * @description Main AngularJS application module for the Student Management System.
 * Initializes the application and enforces authentication on route changes.
 */

var app = angular.module('myApp', ['ngRoute', 'ngCookies', 'ngSanitize']);

/**
 * @description Initializes the application and enforces authentication on route changes.
 * @param {Object} $rootScope - Angular root scope
 * @param {Object} $cookies - Angular cookies service
 * @param {Object} $location - Angular location service
 * @param {Object} AjaxHelper - AJAX helper service
 * @param {Object} AuthService - Authentication service
 */
app.run(['$rootScope', '$cookies', '$location', 'AjaxHelper', 'AuthService', function($rootScope, $cookies, $location, AjaxHelper, AuthService) {
    console.log('App run block initialized');

    // Fetch CSRF token on app start
    AjaxHelper.ajaxRequest('GET', '/auth/get_csrf')
        .then(function(response) {
            if (response.data.csrf_token) {
                $cookies.csrf_token = response.data.csrf_token;
                console.log('Initial CSRF token set:', $cookies.csrf_token?.substring(0, 10) + '...');
            } else {
                console.error('No CSRF token in response:', response.data);
            }
        })
        .catch(function(error) {
            console.error('Failed to fetch initial CSRF token:', error);
            // Fallback to meta tag CSRF token
            var metaCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (metaCsrfToken) {
                $cookies.csrf_token = metaCsrfToken;
                console.log('Fallback CSRF token set from meta tag:', metaCsrfToken.substring(0, 10) + '...');
            }
        });

    $rootScope.$on('$routeChangeStart', function(event, next) {
        console.log('Route change to:', next?.originalPath);
        if (next?.requireLogin && !AuthService.isLoggedIn()) {
            console.log('Redirecting to login: User not authenticated');
            $location.path('/login');
        }
    });
}]);