app.directive('flashMessage', ['$timeout', function($timeout) {
  console.log('flash-message directive initialized'); // Debug
  return {
    restrict: 'A',
    link: function(scope, element) {
      // Initial state: hide element if no message
      if (!scope.flashMessage) {
        element.css('display', 'none');
      }

      scope.$watch('flashMessage', function(newVal) {
        if (newVal) {
          element.css('display', 'block'); // Show element
          $timeout(function() {
            scope.flashMessage = '';
            scope.flashType = '';
            scope.$apply(); // Ensure scope updates
            element.css('display', 'none'); // Hide after 5 seconds
          }, 5000);
        } else {
          element.css('display', 'none'); // Hide if no message
        }
      });
    }
  };
}]);