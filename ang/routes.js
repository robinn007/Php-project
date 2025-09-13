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

    $locationProvider.html5Mode({
        enabled: true,
        requireBase: true
    });

    $routeProvider
        .when('/login', {
            templateUrl: '/view/login',
            controller: 'AuthController'
        })
        .when('/signup', {
            templateUrl: '/view/signup',
            controller: 'AuthController'
        })
        .when('/students/dashboard', {
            templateUrl: '/view/dashboard',
            controller: 'DashboardController',
            requireAuth: true
        })
        .when('/students', {
            templateUrl: '/view/students',
            controller: 'StudentController',
            requireAuth: true
        })
        .when('/students/add', {
            templateUrl: '/view/student-form',
            controller: 'StudentFormController',
            requireAuth: true
        })
        .when('/students/edit/:id', {
            templateUrl: '/view/student-form',
            controller: 'StudentFormController',
            requireAuth: true
        })
        .when('/students/deleted', {
            templateUrl: '/view/deleted-students',
            controller: 'DeletedStudentsController',
            requireAuth: true
        })
        .when('/test-db', {
            templateUrl: '/view/test-db',
            controller: 'TestDbController',
            requireAuth: true
        })
        .when('/about', {
            templateUrl: '/view/about',
            controller: 'HomeController'
        })
        .otherwise({
            redirectTo: '/students'
        });

    $httpProvider.interceptors.push(function($cookies) {
        return {
            request: function(config) {
                var token = $cookies.csrf_token || '';
                if (token) {
                    config.headers['X-CSRF-Token'] = token;
                    console.log('CSRF token attached to request:', token.substring(0, 10) + '...');
                }
                return config;
            },
            response: function(response) {
                if (response.data && response.data.csrf_token) {
                    $cookies.csrf_token = response.data.csrf_token;
                    console.log('CSRF token updated from response');
                }
                return response;
            }
        };
    });
}]);