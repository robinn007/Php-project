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
 */
app.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
    console.log('Routes configuration initialized');
    
    // Define application routes
    $routeProvider
        .when('/login', {
            templateUrl: 'views/login.php',
            controller: 'AuthController'
        })
        .when('/signup', {
            templateUrl: 'views/signup.php',
            controller: 'AuthController'
        })
        .when('/dashboard', {
            templateUrl: 'views/dashboard.php',
            controller: 'DashboardController',
            requireAuth: true   // Requires user to be logged in
        })
        .when('/students', {
            templateUrl: 'views/students.php',
            controller: 'StudentController',
            requireAuth: true  // Requires user to be logged in
        })
        .when('/students/add', {
            templateUrl: 'views/student-form.php',
            controller: 'StudentFormController',
            requireAuth: true  // Requires user to be logged in
        })
        .when('/students/edit/:id', {
            templateUrl: 'views/student-form.php',
            controller: 'StudentFormController',
            requireAuth: true  // Requires user to be logged in
        })
        .when('/students/deleted', {
            templateUrl: 'views/deleted-students.php',
            controller: 'DeletedStudentsController',
            requireAuth: true  // Requires user to be logged in
        })
        .when('/test-db', {
            templateUrl: 'views/test-db.php',
            controller: 'TestDbController',
            requireAuth: true  // Requires user to be logged in
        })
        .otherwise({
            redirectTo: function($cookies) {
                return $cookies.user_id ? '/dashboard' : '/login';
            }
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