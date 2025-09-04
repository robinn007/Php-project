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


app.directive('ckEditor', ['$timeout', function($timeout) {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      console.log('ckEditor directive initialized');
      
      // Initialize CKEditor
      var ck = CKEDITOR.replace(element[0], {
        height: 250,
        allowedContent: true,
        toolbar: [
          { name: 'document', items: ['Source', '-', 'Preview', 'Print'] },
          { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
          { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
          { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe'] },
          { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
          { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
          { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
          { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
          { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
          { name: 'colors', items: ['TextColor', 'BGColor'] },
          { name: 'about', items: ['About'] }
        ]
      });

      // Update Angular model when CKEditor content changes
      ck.on('change', function() {
        $timeout(function() {
          var data = ck.getData();
          ngModel.$setViewValue(data);
          console.log('CKEditor content updated:', data);
        });
      });

      // Initialize CKEditor with model data
      ngModel.$render = function() {
        var value = ngModel.$viewValue || '';
        ck.setData(value);
        console.log('CKEditor rendered with:', value);
      };

      // Validate content length
      function validateContent(value) {
        var maxLength = parseInt(attrs.maxlength) || 2000;
        var minLength = parseInt(attrs.minlength) || 0;
        
        // Handle undefined, null, or non-string values
        if (value == null || typeof value !== 'string') {
          ngModel.$setValidity('required', !attrs.required);
          ngModel.$setValidity('minlength', true);
          ngModel.$setValidity('maxlength', true);
          return value; // Return as-is to avoid further processing
        }

        // Strip HTML tags to count plain text length
        var plainText = value.replace(/<[^>]+>/g, '');
        var length = plainText.length;

        ngModel.$setValidity('required', length > 0 || !attrs.required);
        ngModel.$setValidity('minlength', length >= minLength);
        ngModel.$setValidity('maxlength', length <= maxLength);

        return value;
      }

      // Add validation for parsers and formatters
      ngModel.$parsers.push(validateContent);
      ngModel.$formatters.push(validateContent);

      // Clean up CKEditor instance on scope destroy
      scope.$on('$destroy', function() {
        if (ck) {
          ck.destroy();
        }
      });
    }
  };
}]);

/**
 * @ngdoc filter
 * @name plainText
 * @description Strips HTML tags from a string to return plain text.
 * @param {string} input - Input string containing HTML
 * @returns {string} Plain text without HTML tags
 */
app.filter('plainText', function() {
  return function(input) {
    if (!input || typeof input !== 'string') return input;
    return input.replace(/<[^>]+>/g, '');
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



// New: Contenteditable directive for two-way data binding
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


