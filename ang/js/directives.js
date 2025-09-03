  /**
   * @file directive.js
   * @description Defines custom directives and filters for the Student Management System.
   * Includes flash message handling, email/phone validation, and text formatting.
   */

  /**
   * @ngdoc directive
   * @name flashMessage
   * @description Displays temporary flash messages that auto-hide after 5 seconds.
   * @restrict A
   * @param {Object} $timeout - Angular timeout service
   */


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

  /**
   * @ngdoc filter
   * @name capitalizeFilter
   * @description Capitalizes the first letter of a string and lowercases the rest.
   * @param {string} input - Input string to transform
   * @returns {string} Transformed string
   * */

  app.filter('capitalizeFilter', function() {
    return function(input) {
      if (!input || typeof input !== 'string') return input;
      return input.charAt(0).toUpperCase() + input.slice(1).toLowerCase();
    };
  });

  /**
   * @ngdoc directive
   * @name validEmail
   * @description Validates email input fields using a regex pattern.
   * @restrict A
   * @requires ngModel
   * */

  // Email validation directive
  app.directive('validEmail', function() {
    var EMAIL_REGEXP = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function(scope, element, attrs, ngModel) {
        // Only apply validation if the element has a value
        /**
         * @function validate
         * @description Validates the email input and updates validity state.
         * @param {string} value - Input value to validate
         * @returns {string|undefined} Valid email or undefined if invalid
         */
        function validate(value) {
          var valid = !value || EMAIL_REGEXP.test(value);
          ngModel.$setValidity('validEmail', valid);
          
          // Add/remove CSS class based on validation state
          if (value && !valid) {
            element.addClass('email-invalid');
          } else {
            element.removeClass('email-invalid');
          }
          
          return valid ? value : undefined;
        }

        // For DOM -> model validation
        ngModel.$parsers.unshift(validate);
        // For model -> DOM validation
        ngModel.$formatters.unshift(validate);
        
        // Watch for changes in the model to update styling
        scope.$watch(function() {
          return ngModel.$viewValue;
        }, function(newValue) {
          validate(newValue);
        });
      }
    };
  });

  /**
   * @ngdoc directive
   * @name validPhone
   * @description Validates phone number input fields with various formats.
   * @restrict A
   * @requires ngModel
   */

  // Phone number validation directive
  app.directive('validPhone', function() {
    // Supports various phone number formats:
    // +1-555-123-4567, (555) 123-4567, 555.123.4567, 5551234567, +91 9876543210, etc.
    var PHONE_REGEXP = /^[\+]?[\s]?[(]?[\d\s\-\(\)\.]{10,15}$/;
    
    return {
      restrict: 'A',
      require: 'ngModel',
      link: function(scope, element, attrs, ngModel) {
          /**
         * @function validate
         * @description Validates the phone number input and updates validity state.
         * @param {string} value - Input value to validate
         * @returns {string|undefined} Valid phone number or undefined if invalid
         */
        function validate(value) {
          // If no value, it's valid (optional field)
          if (!value) {
            ngModel.$setValidity('validPhone', true);
            element.removeClass('phone-invalid');
            return value;
          }
          
          // Clean the phone number for validation (remove spaces, dashes, parentheses, dots)
          var cleanPhone = value.replace(/[\s\-\(\)\.]/g, '');
          
          // Check if it matches phone pattern and has appropriate length
          var isValidFormat = PHONE_REGEXP.test(value);
          var isValidLength = cleanPhone.length >= 10 && cleanPhone.length <= 15;
          var hasOnlyValidChars = /^[\+\d\s\-\(\)\.]+$/.test(value);
          
          var valid = isValidFormat && isValidLength && hasOnlyValidChars;
          
          ngModel.$setValidity('validPhone', valid);
          
          // Add/remove CSS class based on validation state
          if (!valid) {
            element.addClass('phone-invalid');
          } else {
            element.removeClass('phone-invalid');
          }
          
          return valid ? value : undefined;
        }

        // For DOM -> model validation
        ngModel.$parsers.unshift(validate);
        // For model -> DOM validation
        ngModel.$formatters.unshift(validate);
        
        // Watch for changes in the model to update styling
        scope.$watch(function() {
          return ngModel.$viewValue;
        }, function(newValue) {
          validate(newValue);
        });
      }
    };
  });

  /**
   * @ngdoc directive
   * @name emailValidationMessage
   * @description Displays real-time email validation error messages.
   * @restrict E
   * @param {Object} form - Form controller
   * @param {string} field - Field name to validate
   */
  // Additional directive for real-time email validation feedback
  app.directive('emailValidationMessage', function() {
    return {
      restrict: 'E',
      template: '<span ng-show="showError" class="email-error-message">Please enter a valid email address (example: user@domain.com)</span>',
      scope: {
        form: '=',
        field: '@'
      },
      link: function(scope, element, attrs) {
          /**
         * @description Watches form field for validation errors to show/hide error message.
         */
        scope.$watch(function() {
          var field = scope.form[scope.field];
          return field && field.$invalid && field.$error.validEmail && (field.$touched || field.$dirty);
        }, function(newValue) {
          scope.showError = newValue;
        });
      }
    };
  });

  /**
   * @ngdoc directive
   * @name phoneValidationMessage
   * @description Displays real-time phone validation error messages.
   * @restrict E
   * @param {Object} form - Form controller
   * @param {string} field - Field name to validate
   */

  // Additional directive for real-time phone validation feedback
  app.directive('phoneValidationMessage', function() {
    return {
      restrict: 'E',
      template: '<span ng-show="showError" class="phone-error-message">Please enter a valid phone number (example: +91-555-123-4567, (985) 123-4567, or 5551234567)</span>',
      scope: {
        form: '=',
        field: '@'
      },
      link: function(scope, element, attrs) {
        /**
         * @description Watches form field for validation errors to show/hide error message.
         */
        scope.$watch(function() {
          var field = scope.form[scope.field];
          return field && field.$invalid && field.$error.validPhone && (field.$touched || field.$dirty);
        }, function(newValue) {
          scope.showError = newValue;
        });
      }
    };
  });

  // Content editable directive for address field for angular
app.directive('contentEditable', function() {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      // Set contenteditable attribute
      element.attr('contenteditable', 'true');
      
      // Add placeholder functionality
      var placeholder = attrs.placeholder || '';
      
      function setPlaceholder() {
        if (!element.text().trim() || element.text() === placeholder) {
          element.text(placeholder);
          element.addClass('placeholder-text');
        }
      }
      
      function removePlaceholder() {
        if (element.hasClass('placeholder-text')) {
          element.text('');
          element.removeClass('placeholder-text');
        }
      }
      
      // Initialize placeholder
      if (!ngModel.$viewValue) {
        setPlaceholder();
      }
      
      // Handle focus events
      element.on('focus', function() {
        removePlaceholder();
      });
      
      element.on('blur', function() {
        var content = element.text().trim();
        if (!content) {
          setPlaceholder();
          ngModel.$setViewValue('');
        } else {
          ngModel.$setViewValue(content);
        }
        ngModel.$setTouched();
        scope.$apply();
      });
      
      // Handle input events - AngularJS 1.3.0 compatible
      element.on('input', function() {
        var content = element.text();
        if (!element.hasClass('placeholder-text')) {
          ngModel.$setViewValue(content);
          // AngularJS 1.3.0 compatible way to set dirty
          ngModel.$dirty = true;
          ngModel.$pristine = false;
        }
        scope.$apply();
      });
      
      // Handle paste events to clean up formatting
      element.on('paste', function(e) {
        e.preventDefault();
        var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
        var pastedText = clipboardData.getData('text/plain');
        
        // Insert plain text only
        if (document.execCommand) {
          document.execCommand('insertText', false, pastedText);
        } else {
          // Fallback for browsers that don't support execCommand
          var selection = window.getSelection();
          if (selection.rangeCount) {
            var range = selection.getRangeAt(0);
            range.deleteContents();
            range.insertNode(document.createTextNode(pastedText));
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
          }
        }
        
        // Update model - AngularJS 1.3.0 compatible
        var content = element.text();
        if (!element.hasClass('placeholder-text')) {
          ngModel.$setViewValue(content);
          ngModel.$dirty = true;
          ngModel.$pristine = false;
        }
        scope.$apply();
      });
      
      // Watch for model changes and update view
      ngModel.$render = function() {
        if (ngModel.$viewValue) {
          element.removeClass('placeholder-text');
          element.text(ngModel.$viewValue);
        } else {
          setPlaceholder();
        }
      };
      
      // Validation function for content length - AngularJS 1.3.0 compatible
      function validateContent(value) {
        var maxLength = parseInt(attrs.maxlength) || 500;
        var minLength = parseInt(attrs.minlength) || 0;
        
        if (!value) {
          ngModel.$setValidity('required', !attrs.required);
          ngModel.$setValidity('minlength', true);
          ngModel.$setValidity('maxlength', true);
          return value;
        }
        
        var trimmedValue = value.trim();
        var length = trimmedValue.length;
        
        ngModel.$setValidity('required', length > 0);
        ngModel.$setValidity('minlength', length >= minLength);
        ngModel.$setValidity('maxlength', length <= maxLength);
        
        return value;
      }
      
      // Add validation
      ngModel.$parsers.push(validateContent);
      ngModel.$formatters.push(validateContent);
    }
  };
});

/**
 * @file directives.js
 * @description Custom AngularJS directives for the Student Management System.
 */

/**
 * @ngdoc directive
 * @name tinyMce
 * @description Integrates TinyMCE rich text editor with AngularJS for two-way data binding.
 * @element textarea
 */
/**
 * @file directives.js
 * @description Custom AngularJS directives for the Student Management System.
 */

