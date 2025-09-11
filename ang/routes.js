/**
 * @file routes.js
 * @description Configures routes and HTTP interceptors for the Student Management System.
 * Separated from app.js for better code organization and maintainability.
 */

/**
 * @ngdoc config
 * @name routeConfig
 * @description Configures application routes and HTTP interceptors.
 * @param {Object} $routeProvider - Angular route provider
 * @param {Object} $httpProvider - Angular HTTP provider
 * @param {Object} $locationProvider - Angular location provider
 */
app.config(['$routeProvider', '$httpProvider', '$locationProvider', function($routeProvider, $httpProvider, $locationProvider) {
    console.log('Routes configuration initialized');
    
    // Enable HTML5 mode to remove hash (#) from URLs
    $locationProvider.html5Mode({
        enabled: true,
        requireBase: true // Requires <base> tag in index.php
    });

    // Define application routes
    $routeProvider
     .when('/login', {
            templateUrl: '/ci/view/login',
            controller: 'AuthController'
        })
          .when('/signup', {
            templateUrl: '/ci/view/signup',
            controller: 'AuthController'
        })
        .when('/students/dashboard', {
            templateUrl: '/ci/view/dashboard',
            controller: 'DashboardController',
         //   requireAuth: true // Requires user to be logged in
        })
        .when('/students', {
          templateUrl: '/ci/view/students',
            controller: 'StudentController',
         //   requireAuth: true // Requires user to be logged in
        })
        .when('/students/add', {
                templateUrl: '/ci/view/student-form',
            controller: 'StudentFormController',
           // requireAuth: true // Requires user to be logged in
        })
        .when('/students/edit/:id', {
              templateUrl: '/ci/view/student-form',
            controller: 'StudentFormController',
           // requireAuth: true // Requires user to be logged in
        })
        .when('/students/deleted', {
            templateUrl: '/ci/view/deleted-students',
            controller: 'DeletedStudentsController',
            //requireAuth: true // Requires user to be logged in
        })
        .when('/test-db', {
       templateUrl: '/ci/view/test-db',
            controller: 'TestDbController',
         //   requireAuth: true // Requires user to be logged in
        })
        .when('/about', {
            templateUrl: '/ci/view/about',
           // controller: 'HomeController'
        })
        .otherwise({
             redirectTo: '/students'
        });

    /**
     * @description HTTP interceptor to attach CSRF token to requests and update it from responses.
     * @param {Object} $cookies - Angular cookies service
     * @returns {Object} Interceptor object with request and response handlers
     */
    // HTTP Interceptor for CSRF token
    $httpProvider.interceptors.push(function($cookies) {
        return {
            /**
             * @function request
             * @description Attaches CSRF token to outgoing HTTP requests.
             * @param {Object} config - HTTP request configuration
             * @returns {Object} Modified request configuration
             */
            request: function(config) {
                // Attach CSRF token to request headers
                var token = $cookies.csrf_token || '';
                if (token) {
                    config.headers['X-CSRF-Token'] = token;
                    console.log('CSRF token attached to request:', token.substring(0, 10) + '...');
                }
                return config;
            },
            /**
             * @function response
             * @description Updates CSRF token from server responses.
             * @param {Object} response - HTTP response object
             * @returns {Object} Unmodified response object
             */
            response: function(response) {
                // Update CSRF token from response if provided
                if (response.data && response.data.csrf_token) {
                    $cookies.csrf_token = response.data.csrf_token;
                    console.log('CSRF token updated from response');
                }
                return response;
            }
        };
    });
}]);