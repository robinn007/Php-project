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
  function ($scope, $location, $rootScope, AuthService, AjaxHelper, $cookies) {
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

    // Replace the logout function in your ci/ang/controllers/NavController.js

    $scope.logout = function () {
      console.log("=== LOGOUT STARTED ===");
      console.log("Current user:", $scope.currentUser);
      console.log("Current auth state:", $scope.isLoggedIn);
      console.log("Available cookies:", document.cookie);

      // Test the logout endpoint first
      console.log("Testing logout endpoint...");
      AjaxHelper.ajaxRequest("POST", "/auth/test_logout", {})
        .then(function (testResponse) {
          console.log("Test logout response:", testResponse);

          // Now try the actual logout
          console.log("Proceeding with actual logout...");
          return performActualLogout();
        })
        .catch(function (testError) {
          console.error("Test logout failed:", testError);
          console.log("Attempting logout anyway...");
          return performActualLogout();
        });

      function performActualLogout() {
        // Prepare data for logout request
        var data = {
          timestamp: new Date().toISOString(),
          user: $scope.currentUser,
        };

        // Add CSRF token if available
        var csrfToken = $cookies.csrf_token;
        if (csrfToken) {
          var csrfTokenName =
            document
              .querySelector('meta[name="csrf-token-name"]')
              ?.getAttribute("content") || "ci_csrf_token";
          data[csrfTokenName] = csrfToken;
          console.log("CSRF token found and added");
        } else {
          console.log("No CSRF token found");
        }

        console.log("Logout request data:", data);
        console.log("Making logout request to /auth/logout");

        return AjaxHelper.ajaxRequest("POST", "/auth/logout", data)
          .then(function (response) {
            console.log("=== LOGOUT SUCCESS ===");
            console.log("Server response:", response);

            // Clear authentication data locally
            AuthService.logout();
            updateAuthState();

            // Broadcast logout event
            $rootScope.$broadcast("userLoggedOut");

            // Show success message
            if (response.flashMessage) {
              $rootScope.$emit("flashMessage", {
                message: response.flashMessage,
                type: response.flashType || "success",
              });
            }

            // Redirect to login page
            console.log("Redirecting to login page");
            $location.path("/login");
            $scope.$applyAsync();

            console.log("=== LOGOUT COMPLETE ===");
          })
          .catch(function (error) {
            console.error("=== LOGOUT ERROR ===");
            console.error("Full error object:", error);
            console.error("Error status:", error.status);
            console.error("Error message:", error.message);
            console.error("Error data:", error.data);
            console.error("Error responseData:", error.responseData);

            // Log the complete error structure
            console.error("Error keys:", Object.keys(error));
            for (let key in error) {
              console.error(`Error.${key}:`, error[key]);
            }

            // Force logout locally regardless of server error
            console.log("Forcing local logout due to server error");
            AuthService.logout();
            updateAuthState();
            $rootScope.$broadcast("userLoggedOut");

            // Determine appropriate message
            var message = "You have been logged out";
            var messageType = "info";

            if (error.status === 500) {
              message = "Logged out (server error occurred)";
              messageType = "warning";
            } else if (error.status === 0) {
              message = "Logged out (network error)";
              messageType = "warning";
            } else if (error.flashMessage) {
              message = error.flashMessage;
              messageType = error.flashType || "info";
            }

            $rootScope.$emit("flashMessage", {
              message: message,
              type: messageType,
            });

            // Redirect regardless
            console.log("Redirecting to login page after error");
            $location.path("/login");
            $scope.$applyAsync();

            console.log("=== LOGOUT ERROR HANDLED ===");
          });
      }
    };
  },
]);
