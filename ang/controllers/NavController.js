/**
 * @file NavController.js
 * @description Controller for managing navigation and user authentication state and logout.
 */
angular.module("myApp").controller("NavController", [
  "$scope",
  "$location",
  "$rootScope",
  "AuthService",
  "AjaxHelper",
  "$cookies",
  "SocketService",
  function ($scope, $location, $rootScope, AuthService, AjaxHelper, $cookies, SocketService) {
    // Initialize authentication state
    function updateAuthState() {
      $scope.isLoggedIn = AuthService.isLoggedIn();
      $scope.currentUser = AuthService.getCurrentUser();
      $scope.currentPath = $location.path();
      console.log("NavController - Auth state updated:", {
        isLoggedIn: $scope.isLoggedIn,
        currentUser: $scope.currentUser,
        currentPath: $scope.currentPath,
        cookies: {
          user_id: document.cookie.indexOf("user_id") !== -1,
          username: document.cookie.indexOf("username") !== -1,
        },
      });
    }

    // Navigation items
    $scope.navItems = [
      { path: "/students", label: "Students" },
      { path: "/students/add", label: "Add Student" },
      { path: "/students/deleted", label: "Deleted Students" },
      { path: "/chat", label: "Chat" },
      { path: "/clicks", label: "Clicks" },
      { path: "/test-db", label: "Test DB" }
    ];

    // Initial state
    updateAuthState();

    console.log(
      "NavController initialized. isLoggedIn:",
      $scope.isLoggedIn,
      "currentUser:",
      $scope.currentUser,
      "currentPath:",
      $scope.currentPath
    );

    // Watch for route changes
    $scope.$on("$routeChangeSuccess", function () {
      updateAuthState();
      console.log(
        "NavController - Route changed:",
        $scope.currentPath,
        "isLoggedIn:",
        $scope.isLoggedIn
      );
    });

    // Watch for login events
    $rootScope.$on("userLoggedIn", function () {
      console.log("NavController - Received userLoggedIn event");
      updateAuthState();
    });

    // Watch for logout events
    $rootScope.$on("userLoggedOut", function () {
      console.log("NavController - Received userLoggedOut event");
      updateAuthState();
    });

    // Watch for cookie changes
    $scope.$watch(
      function () {
        return AuthService.isLoggedIn();
      },
      function (newValue, oldValue) {
        if (newValue !== oldValue) {
          console.log(
            "NavController - Login state changed from",
            oldValue,
            "to",
            newValue
          );
          updateAuthState();
        }
      }
    );

    $scope.logout = function () {
      console.log("=== LOGOUT STARTED ===");
      console.log("Current user:", $scope.currentUser);
      console.log("Current auth state:", $scope.isLoggedIn);
      console.log("Available cookies:", document.cookie);

      // Clear local auth data
      SocketService.emit('user_logout', { email: $cookies.email || '' });
      AuthService.logout();
      
      // Make logout request to server
      var data = {
        timestamp: new Date().toISOString(),
        user: $scope.currentUser,
      };

      var csrfToken = $cookies.csrf_token;
      if (csrfToken) {
        var csrfTokenName =
          document
            .querySelector('meta[name="csrf-token-name"]')
            ?.getAttribute("content") || "ci_csrf_token";
        data[csrfTokenName] = csrfToken;
        console.log("CSRF token included in logout request");
      } else {
        console.warn("No CSRF token found, proceeding with logout");
      }

      AjaxHelper.ajaxRequest("POST", "/auth/logout", data)
        .then(function (response) {
          console.log("=== LOGOUT SUCCESS ===");
          console.log("Server response:", response);

          // Clear authentication data again
          AuthService.logout();
          updateAuthState();

          // Broadcast logout event
          $rootScope.$broadcast("userLoggedOut");

          // Show success message
          $rootScope.$emit("flashMessage", {
            message: response.flashMessage || 'You have been logged out successfully',
            type: response.flashType || "success",
          });

          // Redirect to login with logout parameter
          $location.path("/login").search({ logout: 'true' });
          $scope.$applyAsync();

          console.log("=== LOGOUT COMPLETE ===");
        })
        .catch(function (error) {
          console.error("=== LOGOUT ERROR ===");
          console.error("Full error object:", error);

          // Force logout locally on error
          AuthService.logout();
          updateAuthState();
          $rootScope.$broadcast("userLoggedOut");

          // Show message
          $rootScope.$emit("flashMessage", {
            message: error.flashMessage || 'You have been logged out',
            type: error.flashType || "info",
          });

          // Redirect to login with logout parameter
          $location.path("/login").search({ logout: 'true' });
          $scope.$applyAsync();

          console.log("=== LOGOUT ERROR HANDLED ===");
        });
    };
  },
]);