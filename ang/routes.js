/**
 * @file routes.js
 * @description Configures routes and HTTP interceptors for the Student Management System.
 * Separated from app.js for better code organization and maintainability.
 */
app.config([
  "$routeProvider",
  "$httpProvider",
  "$locationProvider",
  function ($routeProvider, $httpProvider, $locationProvider) {
    console.log("Routes configuration initialized");

    $locationProvider.html5Mode({
      enabled: true,
      requireBase: true,
    });

    $routeProvider
      .when("/login", {
        templateUrl: "/view/login",
        controller: "AuthController",
        //  requireLogin: false,
      })
      .when("/signup", {
        templateUrl: "/view/signup",
        controller: "AuthController",
            requireLogin: false,
      })
      .when("/students/dashboard", {
        templateUrl: "/view/dashboard",
        controller: "DashboardController",
        requireLogin: true,
      })
      .when("/students", {
        templateUrl: "/view/students",
        controller: "StudentController",
        requireLogin: true,
      })
      .when("/students/add", {
        templateUrl: "/view/student-form",
        controller: "StudentFormController",
        requireLogin: true,
      })
      .when("/students/edit/:id", {
        templateUrl: "/view/student-form",
        controller: "StudentFormController",
        requireLogin: true,
      })
      .when("/students/deleted", {
        templateUrl: "/view/deleted-students",
        controller: "DeletedStudentsController",
        requireLogin: true,
      })
      .when("/clicks", {
        templateUrl: "/view/clicks",
        controller: "ClicksController",
        requireLogin: true,
      })
      .when("/test-db", {
        templateUrl: "/view/test-db",
        controller: "TestDbController",
        requireLogin: true,
      })
      .when("/about", {
        templateUrl: "/view/about",
        controller: "HomeController",
      })
      .otherwise({
        redirectTo: "/students",
      });

    $httpProvider.interceptors.push(function ($cookies) {
      return {
        request: function (config) {
          var token = $cookies.csrf_token || "";
          if (token) {
            config.headers["X-CSRF-Token"] = token;
            console.log(
              "CSRF token attached to request:",
              token.substring(0, 10) + "..."
            );
          }
          return config;
        },
        response: function (response) {
          if (response.data && response.data.csrf_token) {
            $cookies.csrf_token = response.data.csrf_token;
            console.log("CSRF token updated from response");
          }
          return response;
        },
      };
    });
  },
]);
