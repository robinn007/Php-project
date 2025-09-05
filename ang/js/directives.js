/**
 * @file directives.js
 * @description Defines custom directives and filters for the Student Management System.
 * Includes flash message handling, email/phone validation, and text formatting.
 */

// Initialize the app variable if not already defined
var app = angular.module('myApp');

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
 */
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
 */
app.directive('validEmail', function() {
  var EMAIL_REGEXP = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
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

/**
 * @file email-link.directive.js
 * @description Directive for creating clickable email links with customizable mailto functionality
 */
app.directive('emailLink', function() {
  return {
    restrict: 'E',
    scope: {
      email: '@',           // Email address (required)
      name: '@',            // Student/Person name (optional)
      subject: '@',         // Custom subject (optional)
      body: '@',            // Custom body (optional)
      displayText: '@',     // Custom display text (optional, defaults to email)
      cssClass: '@',        // Custom CSS class (optional)
      target: '@'           // Link target (optional, defaults to '_blank')
    },
    template: '<a ng-href="{{ mailtoUrl }}" target="{{ linkTarget }}" ng-class="{{ linkClass }}">{{ linkText }}</a>',
    link: function(scope) {
      // Set default values
      scope.linkTarget = scope.target || '_blank';
      scope.linkText = scope.displayText || scope.email;
      scope.linkClass = scope.cssClass || '';
      
      // Build mailto URL
      function buildMailtoUrl() {
        if (!scope.email) {
          scope.mailtoUrl = '#';
          return;
        }
        
        let url = 'mailto:' + scope.email;
        let params = [];
        
        // Add subject
        let subject = scope.subject;
        if (!subject && scope.name) {
          subject = 'Hello ' + scope.name;
        }
        if (subject) {
          params.push('subject=' + encodeURIComponent(subject));
        }
        
        // Add body
        let body = scope.body;
        if (!body && scope.name) {
          body = 'Dear ' + scope.name + ',';
        }
        if (body) {
          params.push('body=' + encodeURIComponent(body));
        }
        
        // Combine URL with parameters
        if (params.length > 0) {
          url += '?' + params.join('&');
        }
        
        scope.mailtoUrl = url;
      }

      // Watch for changes in email, name, subject, or body
      scope.$watchGroup(['email', 'name', 'subject', 'body'], function() {
        buildMailtoUrl();
      });
      
      // Initial build
      buildMailtoUrl();
    }
  };
});

/**
 * @file phone-link.directive.js
 * @description Directive for creating clickable phone links with tel: functionality
 */
app.directive('phoneLink', function() {
  return {
    restrict: 'E',
    scope: {
      phone: '@',           // Phone number
      displayText: '@',     // Custom display text (optional)
      emptyText: '@',       // Text when no phone (optional, defaults to 'N/A')
      cssClass: '@'         // Custom CSS class (optional)
    },
    template: '<a ng-if="phone && phone.trim()" ng-href="tel:{{ cleanPhone }}" ng-class="cssClass">{{ displayText || phone }}</a>' +
              '<span ng-if="!phone || !phone.trim()" ng-class="cssClass">{{ emptyText || "N/A" }}</span>',
    link: function(scope, element, attrs) {
      // Watch for phone changes and clean it for tel: URL
      scope.$watch('phone', function(newPhone) {
        if (newPhone && newPhone.trim()) {
          // Remove spaces, dashes, parentheses, dots for clean tel: URL
          scope.cleanPhone = newPhone.replace(/[\s\-\(\)\.]/g, '');
        }
      });
    }
  };
});

// Contenteditable directive for two-way data binding
app.directive('contenteditableModel', ['$sce', function($sce) {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      
      // View -> Model
      function read() {
        var html = element.html();
        // When we clear the content editable the browser leaves a <br> behind
        // If strip-br attribute is provided then we strip this out
        if (attrs.stripBr && html === '<br>') {
          html = '';
        }
        ngModel.$setViewValue(html);
      }

      // Model -> View
      ngModel.$render = function() {
        var value = ngModel.$viewValue || '';
        element.html($sce.trustAsHtml(value));
      };

      // Listen for change events to enable binding
      element.on('blur keyup change', function() {
        scope.$evalAsync(read);
      });

      // Handle paste events
      element.on('paste', function(e) {
        setTimeout(function() {
          scope.$evalAsync(read);
        }, 0);
      });

      // Initialize
      read();
    }
  };
}]);

/**
 * @file filters.js
 * @description Email and Phone Filters for the Student Management System
 * FIXED VERSION - These are the ONLY filter definitions in this file
 */

// Email Filter - CORRECTED AND WORKING VERSION
app.filter('emailFilter', function() {
  return function(input, operation) {
    console.log('emailFilter - Input:', input, 'Operation:', operation); // Debug log
    
    // Return empty string for null/undefined input
    if (!input) {
      console.log('emailFilter - No input provided, returning empty string');
      return '';
    }
    
    // Ensure input is a string
    if (typeof input !== 'string') {
      console.log('emailFilter - Input is not a string:', typeof input);
      input = String(input);
    }

    var result = input;
    
    switch (operation) {
      case 'validate':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        result = emailRegex.test(input) ? 'Valid email' : 'Invalid email';
        break;
        
      case 'domain':
        const domainParts = input.split('@');
        if (domainParts.length > 1) {
          result = domainParts[1];
        } else {
          result = 'No domain found';
        }
        break;
        
      case 'username':
        const usernameParts = input.split('@');
        if (usernameParts.length > 0) {
          result = usernameParts[0];
        } else {
          result = input;
        }
        break;
        
      case 'mask':
        const maskParts = input.split('@');
        if (maskParts.length > 1) {
          const username = maskParts[0];
          const domain = maskParts[1];
          if (username.length > 4) {
            result = username.substring(0, 4) + '***@' + domain;
          } else if (username.length > 2) {
            result = username.substring(0, 2) + '*@' + domain;
          } else {
            result = username + '@' + domain; // Don't mask very short usernames
          }
        } else {
          result = input; // Return original if no @ symbol
        }
        break;
        
      default:
        console.log('emailFilter - Unknown operation:', operation, '- returning original input');
        result = input;
    }
    
    console.log('emailFilter - Final Result:', result);
    return result;
  };
});

// Phone Filter - CORRECTED AND WORKING VERSION
app.filter('phoneFilter', function() {
  return function(input, operation) {
    console.log('phoneFilter - Input:', input, 'Operation:', operation); // Debug log
    
    // Return empty string for null/undefined input
    if (!input) {
      console.log('phoneFilter - No input provided, returning empty string');
      return '';
    }
    
    // Ensure input is a string
    if (typeof input !== 'string') {
      console.log('phoneFilter - Input is not a string:', typeof input);
      input = String(input);
    }

    var result = input;
    var cleanPhone = input.replace(/[^\d+]/g, ''); // Remove everything except digits and +
    
    switch (operation) {
      case 'clean':
        result = cleanPhone;
        break;
        
      case 'format':
        var digitsOnly = cleanPhone.replace(/[^\d]/g, ''); // Remove + for digit counting
        
        if (cleanPhone.startsWith('+91') && digitsOnly.length === 12) {
          // India: +91XXXXXXXXXX → +91-XXXXX-XXXXX
          result = cleanPhone.replace(/(\+91)(\d{5})(\d{5})/, '$1-$2-$3');
        } else if (cleanPhone.startsWith('+1') && digitsOnly.length === 11) {
          // USA: +1XXXXXXXXXX → +1-(XXX)-XXX-XXXX
          result = cleanPhone.replace(/(\+1)(\d{3})(\d{3})(\d{4})/, '$1-($2)-$3-$4');
        } else if (digitsOnly.length === 10 && /^\d{10}$/.test(digitsOnly)) {
          // Generic 10-digit: XXXXXXXXXX → (XXX) XXX-XXXX
          result = digitsOnly.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (digitsOnly.length === 10) {
          // Alternative 10-digit format
          result = digitsOnly.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
        } else {
          result = input; // Return original if no pattern matches
        }
        break;
        
      case 'validate':
        var digits = input.replace(/[^\d]/g, '');
        if (digits.length >= 10 && digits.length <= 15) {
          result = 'Valid phone';
        } else {
          result = 'Invalid phone (need 10-15 digits)';
        }
        break;
        
      case 'mask':
        var digitsOnly = cleanPhone.replace(/[^\d]/g, '');
        
        if (cleanPhone.startsWith('+91') && digitsOnly.length === 12) {
          // India: Show +91XXXXX-XXXXX → +91XXXXX-***XX
          result = cleanPhone.substring(0, 8) + '***' + cleanPhone.substring(11);
        } else if (cleanPhone.startsWith('+1') && digitsOnly.length === 11) {
          // USA: Show +1XXX-XXX-XXXX → +1XXX-XXX-**XX
          result = cleanPhone.substring(0, 8) + '**' + cleanPhone.substring(10);
        } else if (digitsOnly.length === 10) {
          // 10-digit: Show XXXXXX-XXXX → XXXXXX-**XX
          result = digitsOnly.substring(0, 4) + '****' + digitsOnly.substring(8);
        } else if (digitsOnly.length >= 7) {
          // Generic: Mask last 3 digits
          result = input.substring(0, input.length - 3) + '***';
        } else {
          result = input; // Don't mask very short numbers
        }
        break;
        
      case 'digits':
        result = input.replace(/[^\d]/g, '');
        break;
        
      default:
        console.log('phoneFilter - Unknown operation:', operation, '- returning original input');
        result = input;
    }
    
    console.log('phoneFilter - Final Result:', result);
    return result;
  };
});