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

/**
 * @ngdoc directive
 * @name validPhone
 * @description Validates phone number input fields with enhanced rules in E.164 format.
 * @restrict A
 * @requires ngModel
 */
app.directive('validPhone', function() {
  console.log('validPhone directive initialized'); // Debug log
  var PHONE_REGEXP = /^\+[1-9]\d{0,2}\d{7,14}$/;
  var BLOCKED_NUMBERS = [
    '1234567890', '0123456789', '0000000000', '1111111111', '2222222222',
    '3333333333', '4444444444', '5555555555', '6666666666', '7777777777',
    '8888888888', '9999999999'
  ];

  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      console.log('validPhone directive linked for element:', element); // Debug log
      function validate(value) {
        if (!value) {
          ngModel.$setValidity('validPhone', true);
          ngModel.$setValidity('phoneFormat', true);
          ngModel.$setValidity('phoneE164', true);
          ngModel.$setValidity('phoneLength', true);
          ngModel.$setValidity('phoneLeadingZero', true);
          ngModel.$setValidity('phoneConsecutiveDigits', true);
          ngModel.$setValidity('phoneDummy', true);
          element.removeClass('phone-invalid');
          return value;
        }

        var cleanedValue = value.replace(/[\s\-\(\)\.]/g, '');
        var validChars = /^\+\d+$/.test(cleanedValue);
        var e164Match = cleanedValue.match(/^\+(\d{1,3})(\d+)$/);
        var isValidE164 = e164Match && PHONE_REGEXP.test(cleanedValue);
        var subscriberNumber = e164Match ? e164Match[2] : '';
        var noLeadingZero = subscriberNumber && !subscriberNumber.startsWith('0');
        var digitsOnly = cleanedValue.replace(/\+/, '');
        var isValidLength = digitsOnly.length >= 8 && digitsOnly.length <= 15;
        var noConsecutiveDigits = !/(\d)\1{5,}/.test(digitsOnly);
        var isNotDummy = !BLOCKED_NUMBERS.includes(digitsOnly);

        ngModel.$setValidity('phoneFormat', validChars);
        ngModel.$setValidity('phoneE164', isValidE164);
        ngModel.$setValidity('phoneLeadingZero', noLeadingZero);
        ngModel.$setValidity('phoneLength', isValidLength);
        ngModel.$setValidity('phoneConsecutiveDigits', noConsecutiveDigits);
        ngModel.$setValidity('phoneDummy', isNotDummy);

        var valid = validChars && isValidE164 && noLeadingZero && isValidLength && noConsecutiveDigits && isNotDummy;
        ngModel.$setValidity('validPhone', valid);

        if (!valid) {
          element.addClass('phone-invalid');
        } else {
          element.removeClass('phone-invalid');
        }

        return valid ? cleanedValue : undefined;
      }

      ngModel.$parsers.unshift(validate);
      ngModel.$formatters.unshift(validate);

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
 * @description Validates phone number input fields with enhanced rules in E.164 format.
 * @restrict A
 * @requires ngModel
 */
app.directive('validPhone', function() {
  // E.164 format: +[country code][subscriber number], e.g., +12025550123
  var PHONE_REGEXP = /^\+[1-9]\d{0,2}\d{7,14}$/;
  
  // Dummy/test numbers to block
  var BLOCKED_NUMBERS = [
    '1234567890',
    '0123456789',
    '0000000000',
    '1111111111',
    '2222222222',
    '3333333333',
    '4444444444',
    '5555555555',
    '6666666666',
    '7777777777',
    '8888888888',
    '9999999999'
  ];

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
        // Allow empty value (optional field)
        if (!value) {
          ngModel.$setValidity('validPhone', true);
          ngModel.$setValidity('phoneFormat', true);
          ngModel.$setValidity('phoneLength', true);
          ngModel.$setValidity('phoneLeadingZero', true);
          ngModel.$setValidity('phoneConsecutiveDigits', true);
          ngModel.$setValidity('phoneDummy', true);
          element.removeClass('phone-invalid');
          return value;
        }

        // 8. Trim spaces, dashes, parentheses, and dots
        var cleanedValue = value.replace(/[\s\-\(\)\.]/g, '');

        // 1. Check if only digits after +
        var validChars = /^\+\d+$/.test(cleanedValue);
        ngModel.$setValidity('phoneFormat', validChars);

        // 4. Check E.164 format: starts with + followed by 1-3 digit country code
        var e164Match = cleanedValue.match(/^\+(\d{1,3})(\d+)$/);
        var isValidE164 = e164Match && PHONE_REGEXP.test(cleanedValue);
        ngModel.$setValidity('phoneE164', isValidE164);

        // Extract subscriber number (after country code)
        var subscriberNumber = e164Match ? e164Match[2] : '';

        // 2. No leading zeros in subscriber number
        var noLeadingZero = subscriberNumber && !subscriberNumber.startsWith('0');
        ngModel.$setValidity('phoneLeadingZero', noLeadingZero);

        // 3 & 6. Length restriction (8-15 digits, max 15 digits)
        var digitsOnly = cleanedValue.replace(/\+/, '');
        var isValidLength = digitsOnly.length >= 8 && digitsOnly.length <= 15;
        ngModel.$setValidity('phoneLength', isValidLength);

        // 5. No 6 or more consecutive same digits
        var noConsecutiveDigits = !/(\d)\1{5,}/.test(digitsOnly);
        ngModel.$setValidity('phoneConsecutiveDigits', noConsecutiveDigits);

        // 7. Block dummy/test numbers
        var isNotDummy = !BLOCKED_NUMBERS.includes(digitsOnly);
        ngModel.$setValidity('phoneDummy', isNotDummy);

        // Overall validation
        var valid = validChars && isValidE164 && noLeadingZero && isValidLength && noConsecutiveDigits && isNotDummy;

        ngModel.$setValidity('validPhone', valid);

        // Update CSS class based on validation state
        if (!valid) {
          element.addClass('phone-invalid');
        } else {
          element.removeClass('phone-invalid');
        }

        return valid ? cleanedValue : undefined;
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
    template: `
      <div ng-show="showError" class="email-error-message">
        <span ng-show="form[field].$error.required">Email is required.</span>
        <span ng-show="form[field].$error.emailMaxlength">Email must be less than 100 characters.</span>
        <span ng-show="form[field].$error.emailSpaces">Email cannot contain spaces.</span>
        <span ng-show="form[field].$error.emailDoubleAt">Email must contain exactly one @ symbol.</span>
        <span ng-show="form[field].$error.emailTld">Domain must end with a valid TLD (e.g., .com).</span>
        <span ng-show="form[field].$error.emailLeadingTrailingDots">Email local part cannot start or end with a dot.</span>
        <span ng-show="form[field].$error.emailConsecutiveDots">Email local part cannot contain consecutive dots.</span>
        <span ng-show="form[field].$error.emailLocalChars">Email local part can only contain letters, digits, dots, hyphens, underscores, or +.</span>
        <span ng-show="form[field].$error.emailLeadingChars">Email local part or domain cannot start with a dot or hyphen.</span>
        <span ng-show="form[field].$error.emailDomainTypo">{{ emailSuggestion }}</span>
        <span ng-show="form[field].$error.validEmail && !form[field].$error.emailMaxlength && !form[field].$error.emailSpaces && 
                      !form[field].$error.emailDoubleAt && !form[field].$error.emailTld && !form[field].$error.emailLeadingTrailingDots && 
                      !form[field].$error.emailConsecutiveDots && !form[field].$error.emailLocalChars && !form[field].$error.emailLeadingChars && 
                      !form[field].$error.emailDomainTypo">Please enter a valid email address (example: user@domain.com).</span>
      </div>
    `,
    scope: {
      form: '=',
      field: '@',
      emailSuggestion: '='
    },
    link: function(scope, element, attrs) {
      scope.$watch(function() {
        var field = scope.form[scope.field];
        return field && field.$invalid && (field.$touched || field.$dirty);
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
    template: `
      <div ng-show="showError" class="phone-error-message">
        <span ng-show="form[field].$error.required">Phone number is required.</span>
        <span ng-show="form[field].$error.phoneFormat">Phone number can only contain digits and a leading +.</span>
        <span ng-show="form[field].$error.phoneE164">Phone number must start with + followed by a 1-3 digit country code (e.g., +12025550123).</span>
        <span ng-show="form[field].$error.phoneLength">Phone number must have 8-15 digits.</span>
        <span ng-show="form[field].$error.phoneLeadingZero">Phone number cannot start with a zero after the country code.</span>
        <span ng-show="form[field].$error.phoneConsecutiveDigits">Phone number cannot have 6 or more consecutive identical digits.</span>
        <span ng-show="form[field].$error.phoneDummy">This phone number is not allowed (e.g., 1234567890 or 9999999999).</span>
        <span ng-show="form[field].$error.validPhone && !form[field].$error.phoneFormat && !form[field].$error.phoneE164 && 
                      !form[field].$error.phoneLength && !form[field].$error.phoneLeadingZero && !form[field].$error.phoneConsecutiveDigits && 
                      !form[field].$error.phoneDummy">Please enter a valid phone number (e.g., +12025550123).</span>
      </div>
    `,
    scope: {
      form: '=',
      field: '@'
    },
    link: function(scope, element, attrs) {
      scope.$watch(function() {
        var field = scope.form[scope.field];
        return field && field.$invalid && (field.$touched || field.$dirty);
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

app.directive('emailLink', ['$filter', function($filter) {
  return {
    restrict: 'E',
    scope: {
      email: '@',           // Email address (required)
      name: '@',            // Student/Person name (optional)
      subject: '@',         // Custom subject (optional)
      body: '@',            // Custom body (optional)
      displayText: '@',     // Custom display text (optional, can be masked)
      cssClass: '@',        // Custom CSS class (optional)
      target: '@'           // Link target (optional, defaults to '_blank')
    },
    template: '<a ng-href="{{ mailtoUrl }}" target="{{ linkTarget }}" ng-class="{{ linkClass }}">{{ linkText }}</a>',
    link: function(scope) {
      // Set default values
      scope.linkTarget = scope.target || '_blank';
      scope.linkClass = scope.cssClass || '';

      // Use displayText if provided, otherwise use email (unmasked by default)
      scope.linkText = scope.displayText || scope.email;

      // Build mailto URL using unmasked email
      function buildMailtoUrl() {
        if (!scope.email) {
          scope.mailtoUrl = '#';
          return;
        }

        // Use the cleaned, unmasked email for mailto
        var cleanEmail = $filter('emailFilter')(scope.email, 'clean');
        let url = 'mailto:' + cleanEmail;
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
}]);

/**
 * @file phone-link.directive.js
 * @description Directive for creating clickable phone links with tel: functionality
 */
app.directive('phoneLink', ['$filter', function($filter) {
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
          // Use phoneFilter to clean the phone number
          scope.cleanPhone = $filter('phoneFilter')(newPhone, 'clean');
        }
      });
    }
  };
}]);

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
 * @ngdoc filter
 * @name emailFilter
 * @description Processes email strings with various operations including validation and typo correction.
 */
app.filter('emailFilter', function() {
  // Common domain typos and their corrections
  var DOMAIN_SUGGESTIONS = {
    'gmial.com': 'gmail.com',
    'gamil.com': 'gmail.com',
    'hotnail.com': 'hotmail.com',
    'hotmal.com': 'hotmail.com',
    'yaho.com': 'yahoo.com',
    'yahho.com': 'yahoo.com',
    'yhaoo.com': 'yahoo.com'
  };

  return function(input, operation) {
    console.log('emailFilter - Input:', input, 'Operation:', operation);

    // Return empty string for null/undefined input
    if (!input) {
      console.log('emailFilter - No input provided, returning empty string');
      return '';
    }

    // Ensure input is a string, convert to lowercase, and trim
    if (typeof input !== 'string') {
      console.log('emailFilter - Input is not a string:', typeof input);
      input = String(input);
    }
    var cleanedInput = input.toLowerCase().trim();
    var result = cleanedInput;

    switch (operation) {
      case 'validate':
        const emailRegex = /^[a-zA-Z0-9][a-zA-Z0-9._%+-]{0,62}[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]{0,61}\.[a-zA-Z]{2,}$/;
        console.log("my email", emailRegex)
        const hasSpaces = /\s/.test(cleanedInput);
        const atCount = (cleanedInput.match(/@/g) || []).length;
        const isValidLength = cleanedInput.length <= 100;
        const parts = cleanedInput.split('@');
        const isValidParts = parts.length === 2;
        const localPart = isValidParts ? parts[0] : '';
        const domain = isValidParts ? parts[1] : '';
        const tldValid = isValidParts && domain.match(/\.[a-zA-Z]{2,}$/);
        const noLeadingTrailingDots = isValidParts && !localPart.startsWith('.') && !localPart.endsWith('.');
        const noConsecutiveDots = isValidParts && !localPart.includes('..');
        const validLocalChars = isValidParts && /^[a-zA-Z0-9][a-zA-Z0-9._%+-]*[a-zA-Z0-9]$/.test(localPart);
        const validLeadingChars = isValidParts && !localPart.startsWith('-') && !domain.startsWith('.') && !domain.startsWith('-');
        const hasNoDomainTypo = isValidParts && !DOMAIN_SUGGESTIONS[domain];

        if (cleanedInput.length === 0) {
          result = 'Email is required';
        } else if (hasSpaces) {
          result = 'Email cannot contain spaces';
        } else if (atCount !== 1) {
          result = 'Email must contain exactly one @ symbol';
        } else if (!isValidLength) {
          result = 'Email must be less than 100 characters';
        } else if (!tldValid) {
          result = 'Domain must end with a valid TLD (e.g., .com)';
        } else if (!noLeadingTrailingDots) {
          result = 'Email local part cannot start or end with a dot';
        } else if (!noConsecutiveDots) {
          result = 'Email local part cannot contain consecutive dots';
        } else if (!validLocalChars) {
          result = 'Email local part can only contain letters, digits, dots, hyphens, underscores, or +';
        } else if (!validLeadingChars) {
          result = 'Email local part or domain cannot start with a dot or hyphen';
        } else if (!hasNoDomainTypo) {
          result = `Invalid domain; did you mean ${DOMAIN_SUGGESTIONS[domain]}?`;
        } else {
          result = emailRegex.test(cleanedInput) ? 'Valid email' : 'Invalid email';
        }
        break;

      case 'domain':
        const domainParts = cleanedInput.split('@');
        result = domainParts.length > 1 ? domainParts[1] : 'No domain found';
        break;

      case 'username':
        const usernameParts = cleanedInput.split('@');
        result = usernameParts.length > 0 ? usernameParts[0] : cleanedInput;
        break;

      case 'mask':
        const maskParts = cleanedInput.split('@');
        if (maskParts.length > 1) {
          const username = maskParts[0];
          const domain = maskParts[1];
          if (username.length > 4) {
            result = username.substring(0, 4) + '***@' + domain;
          } else if (username.length > 2) {
            result = username.substring(0, 2) + '*@' + domain;
          } else {
            result = username + '@' + domain;
          }
        } else {
          result = cleanedInput;
        }
        break;

      case 'suggest':
        const partsSuggest = cleanedInput.split('@');
        if (partsSuggest.length === 2 && DOMAIN_SUGGESTIONS[partsSuggest[1]]) {
          result = partsSuggest[0] + '@' + DOMAIN_SUGGESTIONS[partsSuggest[1]];
        } else {
          result = cleanedInput;
        }
        break;

      case 'clean':
        result = cleanedInput;
        break;

      default:
        console.log('emailFilter - Unknown operation:', operation, '- returning cleaned input');
        result = cleanedInput;
    }

    console.log('emailFilter - Final Result:', result);
    return result;
  };
});

/**
 * @ngdoc filter
 * @name phoneFilter
 * @description Processes phone numbers with various operations including validation and formatting.
 */
app.filter('phoneFilter', function() {
  // Dummy/test numbers to block
  var BLOCKED_NUMBERS = [
    '1234567890',
    '0123456789',
    '0000000000',
    '1111111111',
    '2222222222',
    '3333333333',
    '4444444444',
    '5555555555',
    '6666666666',
    '7777777777',
    '8888888888',
    '9999999999'
  ];

  return function(input, operation) {
    console.log('phoneFilter - Input:', input, 'Operation:', operation);

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

    // Clean input by removing spaces, dashes, parentheses, and dots
    var cleanedInput = input.replace(/[\s\-\(\)\.]/g, '');
    var result = cleanedInput;

    switch (operation) {
      case 'clean':
        result = cleanedInput;
        break;

      case 'format':
        // Format based on country code
        var digitsOnly = cleanedInput.replace(/\+/, '');
        if (cleanedInput.startsWith('+1') && digitsOnly.length === 11) {
          // USA: +1XXXXXXXXXX → +1 (XXX) XXX-XXXX
          result = cleanedInput.replace(/(\+1)(\d{3})(\d{3})(\d{4})/, '$1 ($2) $3-$4');
        } else if (cleanedInput.startsWith('+91') && digitsOnly.length === 12) {
          // India: +91XXXXXXXXXX → +91 XXXXX-XXXXX
          result = cleanedInput.replace(/(\+91)(\d{5})(\d{5})/, '$1 $2-$3');
        } else {
          // Default E.164 format: keep as is
          result = cleanedInput;
        }
        break;

      case 'validate':
        // Validate E.164 format
        var validChars = /^\+\d+$/.test(cleanedInput);
        var e164Match = cleanedInput.match(/^\+(\d{1,3})(\d+)$/);
        var isValidE164 = e164Match && /^\+[1-9]\d{0,2}\d{7,14}$/.test(cleanedInput);
        var subscriberNumber = e164Match ? e164Match[2] : '';
        var noLeadingZero = subscriberNumber && !subscriberNumber.startsWith('0');
        var digitsOnly = cleanedInput.replace(/\+/, '');
        var isValidLength = digitsOnly.length >= 8 && digitsOnly.length <= 15;
        var noConsecutiveDigits = !/(\d)\1{5,}/.test(digitsOnly);
        var isNotDummy = !BLOCKED_NUMBERS.includes(digitsOnly);

        if (!validChars) {
          result = 'Phone number can only contain digits and a leading +';
        } else if (!isValidE164) {
          result = 'Phone number must start with + followed by a 1-3 digit country code';
        } else if (!noLeadingZero) {
          result = 'Phone number cannot start with a zero after the country code';
        } else if (!isValidLength) {
          result = 'Phone number must have 8-15 digits';
        } else if (!noConsecutiveDigits) {
          result = 'Phone number cannot have 6 or more consecutive identical digits';
        } else if (!isNotDummy) {
          result = 'This phone number is not allowed (e.g., 1234567890 or 9999999999)';
        } else {
          result = 'Valid phone';
        }
        break;

      case 'mask':
        var digitsOnly = cleanedInput.replace(/\+/, '');
        if (cleanedInput.startsWith('+91') && digitsOnly.length === 12) {
          // India: +91XXXXX-XXXXX → +91XXXXX-***XX
          result = cleanedInput.substring(0, 8) + '***' + cleanedInput.substring(11);
        } else if (cleanedInput.startsWith('+1') && digitsOnly.length === 11) {
          // USA: +1XXX-XXX-XXXX → +1XXX-XXX-**XX
          result = cleanedInput.substring(0, 8) + '**' + cleanedInput.substring(10);
        } else if (digitsOnly.length >= 8) {
          // Generic: Mask last 3 digits
          result = cleanedInput.substring(0, cleanedInput.length - 3) + '***';
        } else {
          result = cleanedInput;
        }
        break;

      case 'digits':
        result = cleanedInput.replace(/\+/, '');
        break;

      default:
        console.log('phoneFilter - Unknown operation:', operation, '- returning cleaned input');
        result = cleanedInput;
    }

    console.log('phoneFilter - Final Result:', result);
    return result;
  };
});

// Address Filter - TEXT CLEANING AND FORMATTING
// Updated Address Filter - Add this new case to your existing addressFilter in directives.js

app.filter('addressFilter', function() {
  return function(input, operation) {
    console.log('addressFilter - Input:', input, 'Operation:', operation);
    
    if (!input) {
      console.log('addressFilter - No input provided, returning empty string');
      return '';
    }
    
    if (typeof input !== 'string') {
      console.log('addressFilter - Input is not a string:', typeof input);
      input = String(input);
    }

    var result = input;
    
    switch (operation) {
      case 'clean':
        // Remove HTML tags, extra whitespace, and normalize line breaks
        result = input
          .replace(/<[^>]*>/g, '') // Remove HTML tags
          .replace(/&nbsp;/g, ' ') // Replace &nbsp; with regular space
          .replace(/&amp;/g, '&')  // Replace &amp; with &
          .replace(/&lt;/g, '<')   // Replace &lt; with <
          .replace(/&gt;/g, '>')   // Replace &gt; with >
          .replace(/\s+/g, ' ')    // Replace multiple spaces with single space
          .trim();                 // Remove leading/trailing whitespace
        break;

      case 'shortWithFormatting':
        // NEW CASE: Truncate but preserve HTML formatting tags
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = input;
        var textContent = tempDiv.textContent || tempDiv.innerText || '';
        
        if (textContent.length > 50) {
          // If text is too long, we need to truncate while preserving HTML
          var truncated = '';
          var charCount = 0;
          var maxChars = 47; // 
          
          // Simple approach: iterate through the HTML and count text characters
          var tempContainer = document.createElement('div');
          tempContainer.innerHTML = input;
          
          function truncateHTML(node, remainingChars) {
            if (remainingChars <= 0) return '';
            
            if (node.nodeType === 3) { // Text node
              var text = node.textContent;
              if (text.length <= remainingChars) {
                return text;
              } else {
                return text.substring(0, remainingChars);
              }
            } else if (node.nodeType === 1) { // Element node
              var tag = node.tagName.toLowerCase();
              var result = '<' + tag;
              
              // Copy attributes if any
              for (var i = 0; i < node.attributes.length; i++) {
                var attr = node.attributes[i];
                result += ' ' + attr.name + '="' + attr.value + '"';
              }
              result += '>';
              
              var usedChars = 0;
              for (var j = 0; j < node.childNodes.length; j++) {
                if (remainingChars - usedChars <= 0) break;
                var childResult = truncateHTML(node.childNodes[j], remainingChars - usedChars);
                result += childResult;
                
                // Count text characters added
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = childResult;
                usedChars += (tempDiv.textContent || tempDiv.innerText || '').length;
              }
              
              result += '</' + tag + '>';
              return result;
            }
            return '';
          }
          
          try {
            result = truncateHTML(tempContainer, maxChars) + '...';
          } catch (e) {
            // Fallback: use the original 'short' method if HTML parsing fails
            result = input
              .replace(/<[^>]*>/g, '')
              .replace(/&nbsp;/g, ' ')
              .replace(/\s+/g, ' ')
              .trim();
            
            if (result.length > 50) {
              result = result.substring(0, 47) + '...';
            }
          }
        } else {
          // If content is short enough, return as-is
          result = input;
        }
        break;
        
      case 'short':
        // Keep the original 'short' case for backwards compatibility
        var cleaned = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .replace(/\s+/g, ' ')
          .trim();
        
        if (cleaned.length > 50) {
          result = cleaned.substring(0, 47) + '...';
        } else {
          result = cleaned;
        }
        break;

      case 'displayFormatted':
        // NEW CASE: Clean up contenteditable artifacts but preserve formatting
        result = input
          .replace(/<div><br><\/div>/gi, '<br>') // Replace empty divs with br
          .replace(/<div>/gi, '<br>')            // Replace div starts with br
          .replace(/<\/div>/gi, '')              // Remove div ends
          .replace(/<p><br><\/p>/gi, '<br>')     // Replace empty paragraphs
          .replace(/^<br>/, '')                  // Remove leading br
          .replace(/(<br>\s*){2,}/gi, '<br><br>') // Limit consecutive breaks
          .trim();
        break;
        
      case 'oneline':
        result = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .replace(/[\r\n]+/g, ', ')
          .replace(/\s+/g, ' ')
          .replace(/,\s*,/g, ',')
          .replace(/,\s*$/, '')
          .trim();
        break;
   
      case 'format':
        result = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .replace(/\s+/g, ' ')
          .trim()
          .split(/[\r\n,]+/)
          .map(function(line) {
            return line.trim()
              .split(' ')
              .map(function(word) {
                if (word.length <= 2 && word.toUpperCase() === word) {
                  return word;
                }
                return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
              })
              .join(' ');
          })
          .filter(function(line) { return line.length > 0; })
          .join(', ');
        break;
        
      case 'validate':
        var cleaned = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .replace(/\s+/g, ' ')
          .trim();
        
        if (cleaned.length < 5) {
          result = 'Too short (minimum 5 characters)';
        } else if (cleaned.length > 500) {
          result = 'Too long (maximum 500 characters)';
        } else {
          var hasLetters = /[a-zA-Z]/.test(cleaned);
          var hasNumbers = /[0-9]/.test(cleaned);
          
          if (hasLetters && hasNumbers) {
            result = 'Valid address';
          } else if (hasLetters) {
            result = 'Valid address (no street number)';
          } else {
            result = 'Invalid address (needs letters)';
          }
        }
        break;
   
      case 'postal':
        var postalRegex = /\b(\d{5}(-\d{4})?|\d{6}|[A-Z]\d[A-Z]\s*\d[A-Z]\d)\b/g;
        var matches = input.match(postalRegex);
        
        if (matches && matches.length > 0) {
          result = matches[0];
        } else {
          result = 'No postal code found';
        }
        break;
        
      case 'count':
        var cleaned = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .replace(/\s+/g, ' ')
          .trim();
        
        result = cleaned.length + ' characters';
        break;
        
      case 'lines':
        var lines = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .split(/[\r\n]+/)
          .filter(function(line) { return line.trim().length > 0; });
        
        result = lines.length + ' lines';
        break;
        
      case 'html':
        result = input
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#x27;')
          .replace(/[\r\n]+/g, '<br>')
          .replace(/\s+/g, ' ')
          .trim();
        break;
        
      default:
        console.log('addressFilter - Unknown operation:', operation, '- returning original input');
        result = input;
    }
    
    console.log('addressFilter - Final Result:', result);
    return result;
  };
});

