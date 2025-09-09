
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
  console.log('flash-message directive initialized');
  return {
    restrict: 'A',
    link: function(scope, element) {
      if (!scope.flashMessage) {
        element.css('display', 'none');
      }

      scope.$watch('flashMessage', function(newVal) {
        if (newVal) {
          element.css('display', 'block');
          $timeout(function() {
            scope.flashMessage = '';
            scope.flashType = '';
            scope.$apply();
            element.css('display', 'none');
          }, 5000);
        } else {
          element.css('display', 'none');
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
 * @ngdoc filter
 * @name emailLinkFilter
 * @description Returns a mailto URL for an email address with optional parameters.
 * @param {string} input - Email address
 * @param {Object} options - Optional parameters {name, subject, body}
 * @returns {string} Mailto URL (e.g., 'mailto:user@domain.com?subject=Hello')
 */
app.filter('emailLinkFilter', ['$filter', function($filter) {
  return function(input, options) {
    console.log('emailLinkFilter - Input:', input, 'Options:', options);

    // Return empty string for null/undefined input
    if (!input) {
      console.log('emailLinkFilter - No input provided, returning empty string');
      return '';
    }

    // Ensure input is a string and clean it
    if (typeof input !== 'string') {
      input = String(input);
    }
    var cleanEmail = $filter('emailFilter')(input, 'clean');
    if (!cleanEmail) {
      console.log('emailLinkFilter - Invalid email, returning empty string');
      return '';
    }

    // Default options
    var opts = angular.extend({
      name: '',
      subject: '',
      body: ''
    }, options || {});

    // Build mailto URL
    var url = 'mailto:' + encodeURIComponent(cleanEmail);
    var params = [];

    // Add subject
    var subject = opts.subject;
    if (!subject && opts.name) {
      subject = 'Hello ' + opts.name;
    }
    if (subject) {
      params.push('subject=' + encodeURIComponent(subject));
    }

    // Add body
    var body = opts.body;
    if (!body && opts.name) {
      body = 'Dear ' + opts.name + ',';
    }
    if (body) {
      params.push('body=' + encodeURIComponent(body));
    }

    // Combine URL with parameters
    if (params.length > 0) {
      url += '?' + params.join('&');
    }

    console.log('emailLinkFilter - Final Result:', url);
    return url;
  };
}]);


/**
 * @ngdoc filter
 * @name phoneLinkFilter
 * @description Returns a tel URL for a phone number or fallback text for invalid/empty inputs.
 * @param {string} input - Phone number
 * @param {Object} options - Optional parameters {emptyText}
 * @returns {string} Tel URL (e.g., 'tel:+12025550123') or fallback text
 */
app.filter('phoneLinkFilter', ['$filter', function($filter) {
  return function(input, options) {
    console.log('phoneLinkFilter - Input:', input, 'Options:', options);

    // Return empty text for null/undefined/empty input
    if (!input || !input.trim()) {
      console.log('phoneLinkFilter - No input provided, returning empty text');
      return options && options.emptyText ? options.emptyText : 'N/A';
    }

    // Ensure input is a string
    if (typeof input !== 'string') {
      input = String(input);
    }

    // Clean the phone number
    var cleanPhone = $filter('phoneFilter')(input, 'clean');
    if (!cleanPhone) {
      console.log('phoneLinkFilter - Invalid phone number, returning empty text');
      return options && options.emptyText ? options.emptyText : 'N/A';
    }

    // Build the tel URL
    var result = 'tel:' + encodeURIComponent(cleanPhone);

    console.log('phoneLinkFilter - Final Result:', result);
    return result;
  };
}]);

/**
 * @ngdoc directive
 * @name validEmail
 * @description Validates email input fields using a regex pattern.
 * @restrict A
 * @requires ngModel
 */
app.directive('validEmail', function() {
  var EMAIL_REGEXP = /^[a-zA-Z0-9][a-zA-Z0-9._%+-]{0,62}[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]{0,61}\.[a-zA-Z]{2,}$/;
  var DOMAIN_SUGGESTIONS = {
    'gmial.com': 'gmail.com',
    'gamil.com': 'gmail.com',
    'hotnail.com': 'hotmail.com',
    'hotmal.com': 'hotmail.com',
    'yaho.com': 'yahoo.com',
    'yahho.com': 'yahoo.com',
    'yhaoo.com': 'yahoo.com'
  };

  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      function validate(value) {
        if (!value) {
          ngModel.$setValidity('validEmail', true);
          ngModel.$setValidity('emailSpaces', true);
          ngModel.$setValidity('emailDoubleAt', true);
          ngModel.$setValidity('emailMaxlength', true);
          ngModel.$setValidity('emailTld', true);
          ngModel.$setValidity('emailLeadingTrailingDots', true);
          ngModel.$setValidity('emailConsecutiveDots', true);
          ngModel.$setValidity('emailLocalChars', true);
          ngModel.$setValidity('emailLeadingChars', true);
          ngModel.$setValidity('emailDomainTypo', true);
          return value;
        }

        var cleanedValue = value.toLowerCase().trim();
        var hasSpaces = /\s/.test(cleanedValue);
        var atCount = (cleanedValue.match(/@/g) || []).length;
        var isValidLength = cleanedValue.length <= 100;
        var parts = cleanedValue.split('@');
        var isValidParts = parts.length === 2;
        var localPart = isValidParts ? parts[0] : '';
        var domain = isValidParts ? parts[1] : '';
        var tldValid = isValidParts && domain.match(/\.[a-zA-Z]{2,}$/);
        var noLeadingTrailingDots = isValidParts && !localPart.startsWith('.') && !localPart.endsWith('.');
        var noConsecutiveDots = isValidParts && !localPart.includes('..');
        var validLocalChars = isValidParts && /^[a-zA-Z0-9][a-zA-Z0-9._%+-]*[a-zA-Z0-9]$/.test(localPart);
        var validLeadingChars = isValidParts && !localPart.startsWith('-') && !domain.startsWith('.') && !domain.startsWith('-');
        var hasNoDomainTypo = isValidParts && !DOMAIN_SUGGESTIONS[domain];


        ngModel.$setValidity('emailSpaces', !hasSpaces);
        ngModel.$setValidity('emailDoubleAt', atCount === 1);
        ngModel.$setValidity('emailMaxlength', isValidLength);
        ngModel.$setValidity('emailTld', !!tldValid);
        ngModel.$setValidity('emailLeadingTrailingDots', noLeadingTrailingDots);
        ngModel.$setValidity('emailConsecutiveDots', noConsecutiveDots);
        ngModel.$setValidity('emailLocalChars', validLocalChars);
        ngModel.$setValidity('emailLeadingChars', validLeadingChars);
        ngModel.$setValidity('emailDomainTypo', hasNoDomainTypo);

        var isValid = EMAIL_REGEXP.test(cleanedValue) && !hasSpaces && atCount === 1 && isValidLength &&
                      tldValid && noLeadingTrailingDots && noConsecutiveDots && validLocalChars && validLeadingChars && hasNoDomainTypo;

        ngModel.$setValidity('validEmail', isValid);

        if (!hasNoDomainTypo && isValidParts) {
          scope.emailSuggestion = parts[0] + '@' + DOMAIN_SUGGESTIONS[domain];
        } else {
          scope.emailSuggestion = '';
        }

        return isValid ? cleanedValue : undefined;
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
  var PHONE_REGEXP = /^\+[1-9]\d{0,2}\d{7,14}$/;
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
      function validate(value) {
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

        var cleanedValue = value.replace(/[\s\-\(\)\.]/g, '');
        var validChars = /^\+\d+$/.test(cleanedValue);
        ngModel.$setValidity('phoneFormat', validChars);

        var e164Match = cleanedValue.match(/^\+(\d{1,3})(\d+)$/);
        var isValidE164 = e164Match && PHONE_REGEXP.test(cleanedValue);
        ngModel.$setValidity('phoneE164', isValidE164);

        var subscriberNumber = e164Match ? e164Match[2] : '';
        var noLeadingZero = subscriberNumber && !subscriberNumber.startsWith('0');
        ngModel.$setValidity('phoneLeadingZero', noLeadingZero);

        var digitsOnly = cleanedValue.replace(/\+/, '');
        var isValidLength = digitsOnly.length >= 8 && digitsOnly.length <= 15;
        ngModel.$setValidity('phoneLength', isValidLength);

        var noConsecutiveDigits = !/(\d)\1{5,}/.test(digitsOnly);
        ngModel.$setValidity('phoneConsecutiveDigits', noConsecutiveDigits);

        var isNotDummy = !BLOCKED_NUMBERS.includes(digitsOnly);
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
        <span ng-show="form[field].$error.phoneLength">Phone number must have 10-15 digits.</span>
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
 * @ngdoc directive
 * @name contenteditableModel
 * @description Enables two-way data binding for contenteditable elements.
 * @restrict A
 * @requires ngModel
 */
app.directive('contenteditableModel', ['$sce', function($sce) {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      function read() {
        var html = element.html();
        if (attrs.stripBr && html === '<br>') {
          html = '';
        }
        ngModel.$setViewValue(html);
      }

      ngModel.$render = function() {
        var value = ngModel.$viewValue || '';
        element.html($sce.trustAsHtml(value));
      };

      element.on('blur keyup change', function() {
        scope.$evalAsync(read);
      });

      element.on('paste', function(e) {
        setTimeout(function() {
          scope.$evalAsync(read);
        }, 0);
      });

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

    if (!input) {
      console.log('emailFilter - No input provided, returning empty string');
      return '';
    }

    if (typeof input !== 'string') {
      console.log('emailFilter - Input is not a string:', typeof input);
      input = String(input);
    }
    var cleanedInput = input.toLowerCase().trim();
    var result = cleanedInput;

    switch (operation) {
      case 'validate':
        const emailRegex = /^[a-zA-Z0-9][a-zA-Z0-9._%+-]{0,62}[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]{0,61}\.[a-zA-Z]{2,}$/;
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

    if (!input) {
      console.log('phoneFilter - No input provided, returning empty string');
      return '';
    }

    if (typeof input !== 'string') {
      console.log('phoneFilter - Input is not a string:', typeof input);
      input = String(input);
    }

    var cleanedInput = input.replace(/[\s\-\(\)\.]/g, '');
    var result = cleanedInput;

    switch (operation) {
      case 'clean':
        result = cleanedInput;
        break;

      case 'format':
        var digitsOnly = cleanedInput.replace(/\+/, '');
        if (cleanedInput.startsWith('+1') && digitsOnly.length === 11) {
          result = cleanedInput.replace(/(\+1)(\d{3})(\d{3})(\d{4})/, '$1 ($2) $3-$4');
        } else if (cleanedInput.startsWith('+91') && digitsOnly.length === 12) {
          result = cleanedInput.replace(/(\+91)(\d{5})(\d{5})/, '$1 $2-$3');
        } else {
          result = cleanedInput;
        }
        break;

      case 'validate':
        var validChars = /^\+\d+$/.test(cleanedInput);
        var e164Match = cleanedInput.match(/^\+(\d{1,3})(\d+)$/);
        var isValidE164 = e164Match && /^\+[1-9]\d{0,2}\d{7,14}$/.test(cleanedInput);
        var subscriberNumber = e164Match ? e164Match[2] : '';
        var noLeadingZero = subscriberNumber && !subscriberNumber.startsWith('0');
        var digitsOnly = cleanedInput.replace(/\+/, '');
        var isValidLength = digitsOnly.length >= 10 && digitsOnly.length <= 15;
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
          result = cleanedInput.substring(0, 8) + '***' + cleanedInput.substring(11);
        } else if (cleanedInput.startsWith('+1') && digitsOnly.length === 11) {
          result = cleanedInput.substring(0, 8) + '**' + cleanedInput.substring(10);
        } else if (digitsOnly.length >= 8) {
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


// renderHTML
app.directive('renderHtml', ['$sce', '$filter', function($sce, $filter) {
  return {
    restrict: 'A',
    link: function(scope, element, attrs) {
      var filterName = attrs.renderHtml;
      var inputAttr = attrs.input;
      var operationAttr = attrs.operation;

      scope.$watch(inputAttr, function(newValue) {
        console.log('renderHtml - Input:', newValue, 'Filter:', filterName, 'Operation:', operationAttr);
        if (newValue && filterName && operationAttr) {
          try {
            var filteredValue = $filter(filterName)(newValue, operationAttr);
            console.log('renderHtml - Filtered Value:', filteredValue);
            element.html($sce.trustAsHtml(filteredValue));
          } catch (e) {
            console.error('Error applying filter in renderHtml directive:', e);
            element.text(newValue || '');
          }
        } else if (newValue) {
          element.html($sce.trustAsHtml(newValue));
        } else {
          element.html('');
        }
      });
    }
  };
}]);


// app.directive('safeHtml', ['$parse', function($parse) {
//   return {
//     restrict: 'A',
//     link: function(scope, element, attrs) {
//       scope.$watch(function() {
//         // Evaluate the expression inside safe-html
//         return scope.$eval(attrs.safeHtml);
//       }, function(newVal) {
//         if (newVal) {
//           // If expression has HTML → render it
//           element.html(newVal);
//         } else {
//           // If nothing or invalid → fall back to normal text
//           // This keeps plain {{ }} behavior safe
//           element.text(scope.$eval(attrs.safeHtml) || '');
//         }
//       });
//     }
//   };
// }]);


/**
 * @ngdoc filter
 * @name addressFilter
 * @description Processes address strings with various operations including cleaning, formatting, and character limiting.
 */
app.filter('addressFilter', ['$filter', function($filter) {
  return function(input, operation) {
    console.log('addressFilter - Input:', input, 'Operation:', operation);

    if (!input) {
      console.log('addressFilter - No input provided, returning empty string');
      return '';
    }

    if (typeof input !== 'string') {
      console.log('addressFilter - Input is not a string, converting:', typeof input);
      input = String(input);
    }

    var result = input;

    switch (operation) {
      case 'linkify':
        console.log('addressFilter - Processing linkify operation');
        
        // Updated regex patterns for better matching
        var emailRegex = /\b[a-zA-Z0-9][a-zA-Z0-9._%+-]{0,62}[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]{0,61}\.[a-zA-Z]{2,}\b/g;
        var phoneRegex = /\+\d{1,3}[\s-]?\d{3,4}[\s-]?\d{3,4}[\s-]?\d{3,4}|\+\d{10,15}|\b\d{10,15}\b/g;

        // Function to process HTML while preserving formatting and creating links
        function processHTMLForLinks(htmlContent, maxChars) {
          var processedEmails = new Set();
          var processedPhones = new Set();
          var result = htmlContent;

          // First, check if we need to truncate based on text content length
          var tempDiv = document.createElement('div');
          tempDiv.innerHTML = htmlContent;
          var textLength = (tempDiv.textContent || tempDiv.innerText || '').length;
          
          if (maxChars && textLength > maxChars) {
            // Truncate while preserving HTML structure
            result = truncateHTML(htmlContent, maxChars) + '...';
          }

          // Process emails in the HTML - avoid processing emails inside existing links
          result = result.replace(/(?<!href=["'][^"']*?)(?<!<a[^>]*>.*?)\b([a-zA-Z0-9][a-zA-Z0-9._%+-]{0,62}[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]{0,61}\.[a-zA-Z]{2,})\b(?![^<]*<\/a>)/g, function(match, email) {
            var emailLower = email.toLowerCase();
            if (processedEmails.has(emailLower)) {
              return ''; // Remove duplicates
            }
            processedEmails.add(emailLower);
            
            var cleanEmail = $filter('emailFilter')(email, 'clean');
            var isValidEmail = $filter('emailFilter')(email, 'validate') === 'Valid email';
            
            if (isValidEmail) {
              var emailLink = $filter('emailLinkFilter')(cleanEmail, {});
              return '<a href="' + emailLink + '" class="email-link">' + cleanEmail + '</a>';
            }
            return match;
          });

          // Process phone numbers in the HTML - avoid processing phones inside existing links
          result = result.replace(/(?<!href=["'][^"']*?)(?<!<a[^>]*>.*?)(\+\d{1,3}[\s-]?\d{3,4}[\s-]?\d{3,4}[\s-]?\d{3,4}|\+\d{10,15}|\b\d{10,15}\b)(?![^<]*<\/a>)/g, function(match, phone) {
            var cleanMatch = phone.replace(/[\s-]/g, '');
            if (processedPhones.has(cleanMatch)) {
              return ''; // Remove duplicates
            }
            processedPhones.add(cleanMatch);
            
            var cleanPhone = $filter('phoneFilter')(phone, 'clean');
            var isValidPhone = $filter('phoneFilter')(phone, 'validate') === 'Valid phone';
            
            if (isValidPhone) {
              var formattedPhone = $filter('phoneFilter')(phone, 'format');
              var phoneLink = $filter('phoneLinkFilter')(cleanPhone, {});
              return '<a href="' + phoneLink + '" class="phone-link">' + formattedPhone + '</a>';
            }
            return match;
          });

          // Clean up multiple spaces while preserving HTML
          result = result.replace(/>\s+</g, '><').replace(/\s+/g, ' ').trim();
          
          return result;
        }

        // Helper function to truncate HTML while preserving structure
        function truncateHTML(html, maxChars) {
          var tempContainer = document.createElement('div');
          tempContainer.innerHTML = html;
          var totalTextLength = (tempContainer.textContent || tempContainer.innerText || '').length;
          
          // If content is within limit, return as-is
          if (totalTextLength <= maxChars) {
            return html;
          }
          
          var charCount = 0;
          var openTags = []; // Stack to track open tags
          
          function truncateNode(node, remainingChars) {
            if (remainingChars <= 0) {
              // Close any remaining open tags
              var closingTags = '';
              for (var k = openTags.length - 1; k >= 0; k--) {
                closingTags += '</' + openTags[k] + '>';
              }
              return closingTags;
            }

            if (node.nodeType === 3) { // Text node
              var text = node.textContent;
              if (text.length <= remainingChars) {
                charCount += text.length;
                return text;
              } else {
                charCount += remainingChars;
                // Find last complete word within limit
                var truncatedText = text.substring(0, remainingChars);
                var lastSpaceIndex = truncatedText.lastIndexOf(' ');
                if (lastSpaceIndex > 0 && lastSpaceIndex > remainingChars * 0.8) {
                  truncatedText = truncatedText.substring(0, lastSpaceIndex);
                }
                return truncatedText;
              }
            } else if (node.nodeType === 1) { // Element node
              var tagName = node.tagName.toLowerCase();
              var output = '<' + tagName;

              // Copy attributes
              for (var i = 0; i < node.attributes.length; i++) {
                var attr = node.attributes[i];
                output += ' ' + attr.name + '="' + attr.value + '"';
              }
              output += '>';
              
              // Track opening tag
              openTags.push(tagName);

              // Process child nodes
              var charsUsed = 0;
              var hasContent = false;
              
              for (var j = 0; j < node.childNodes.length; j++) {
                var remainingForChild = remainingChars - charsUsed;
                if (remainingForChild <= 0) break;
                
                var childContent = truncateNode(node.childNodes[j], remainingForChild);
                if (childContent.trim()) {
                  hasContent = true;
                  output += childContent;
                  
                  // Calculate actual text length added
                  var tempEl = document.createElement('div');
                  tempEl.innerHTML = childContent;
                  var childTextLength = (tempEl.textContent || tempEl.innerText || '').length;
                  charsUsed += childTextLength;
                }
                
                if (charCount >= maxChars) break;
              }

              // Remove from stack and close tag only if we added content
              openTags.pop();
              if (hasContent || ['b', 'i', 'u', 'strong', 'em', 'span'].indexOf(tagName) !== -1) {
                output += '</' + tagName + '>';
              } else {
                // If no content was added, don't include the opening tag either
                return '';
              }
              
              return output;
            }
            return '';
          }

          try {
            var result = '';
            for (var i = 0; i < tempContainer.childNodes.length; i++) {
              if (charCount >= maxChars) break;
              var nodeResult = truncateNode(tempContainer.childNodes[i], maxChars - charCount);
              result += nodeResult;
            }
            return result;
          } catch (e) {
            console.error('Error truncating HTML:', e);
            // Fallback: try to preserve at least some formatting
            var textContent = tempContainer.textContent || tempContainer.innerText || '';
            var truncatedText = textContent.substring(0, maxChars);
            
            // Try to preserve bold/italic/underline for the truncated portion
            var simpleFormatted = html.replace(/<(?!\/?(b|i|u|strong|em|span)[>\s])[^>]*>/gi, '');
            if (simpleFormatted.length > maxChars) {
              // Find the last complete tag within the limit
              var truncated = simpleFormatted.substring(0, maxChars);
              var lastOpenTag = truncated.lastIndexOf('<');
              var lastCloseTag = truncated.lastIndexOf('>');
              
              if (lastOpenTag > lastCloseTag) {
                // There's an unclosed tag, remove it
                truncated = truncated.substring(0, lastOpenTag);
              }
              return truncated;
            }
            return simpleFormatted;
          }
        }

        // Process HTML content with 200 character limit
        result = processHTMLForLinks(input, 200);
        
        console.log('addressFilter - linkify result:', result);
        break;
        
      case 'clean':
        result = input
          .replace(/<[^>]*>/g, '')
          .replace(/&nbsp;/g, ' ')
          .replace(/&amp;/g, '&')
          .replace(/&lt;/g, '<')
          .replace(/&gt;/g, '>')
          .replace(/\s+/g, ' ')
          .trim();
        break;

      case 'limitChars':
        console.log('addressFilter - Processing limitChars operation');
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = input;
        var textContent = (tempDiv.textContent || tempDiv.innerText || '').trim();

        if (textContent.length <= 200) {
          result = input;
        } else {
          // Simple truncation preserving HTML structure
          var maxChars = 200;
          var truncated = '';
          var charCount = 0;
          
          try {
            function truncateNode(node, remainingChars) {
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
                var output = '<' + tag;

                // Copy attributes
                for (var i = 0; i < node.attributes.length; i++) {
                  var attr = node.attributes[i];
                  output += ' ' + attr.name + '="' + attr.value + '"';
                }
                output += '>';

                // Process children
                var charsUsed = 0;
                for (var j = 0; j < node.childNodes.length; j++) {
                  if (remainingChars - charsUsed <= 0) break;
                  var childContent = truncateNode(node.childNodes[j], remainingChars - charsUsed);
                  output += childContent;
                  
                  // Count characters in the child content
                  var tempEl = document.createElement('div');
                  tempEl.innerHTML = childContent;
                  charsUsed += (tempEl.textContent || tempEl.innerText || '').length;
                }

                output += '</' + tag + '>';
                return output;
              }
              return '';
            }

            tempDiv.innerHTML = input;
            truncated = truncateNode(tempDiv, maxChars);
            if (textContent.length > 200) {
              truncated += '...';
            }
            result = truncated;
          } catch (e) {
            console.error('addressFilter - Error in limitChars:', e);
            // Fallback
            var cleaned = $filter('addressFilter')(input, 'clean');
            result = cleaned.substring(0, 200) + (cleaned.length > 200 ? '...' : '');
          }
        }
        break;

      case 'shortWithFormatting':
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = input;
        var textContent = tempDiv.textContent || tempDiv.innerText || '';

        if (textContent.length > 50) {
          var maxChars = 47;
          var truncated = '';
          
          try {
            // Simple character-based truncation while preserving some HTML
            var plainText = textContent.substring(0, maxChars);
            
            // Try to preserve email and phone links within the truncated portion
            var emailMatches = plainText.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g) || [];
            var phoneMatches = plainText.match(/\+?\d{10,15}/g) || [];
            
            truncated = plainText;
            
            // Convert found emails to links
            emailMatches.forEach(function(email) {
              var cleanEmail = $filter('emailFilter')(email, 'clean');
              if ($filter('emailFilter')(email, 'validate') === 'Valid email') {
                var emailLink = $filter('emailLinkFilter')(cleanEmail, {});
                truncated = truncated.replace(email, '<a href="' + emailLink + '" class="email-link">' + cleanEmail + '</a>');
              }
            });
            
            // Convert found phones to links
            phoneMatches.forEach(function(phone) {
              var cleanPhone = $filter('phoneFilter')(phone, 'clean');
              if ($filter('phoneFilter')(phone, 'validate') === 'Valid phone') {
                var formattedPhone = $filter('phoneFilter')(phone, 'format');
                var phoneLink = $filter('phoneLinkFilter')(cleanPhone, {});
                truncated = truncated.replace(phone, '<a href="' + phoneLink + '" class="phone-link">' + formattedPhone + '</a>');
              }
            });
            
            result = truncated + '...';
          } catch (e) {
            console.error('Error in shortWithFormatting:', e);
            result = textContent.substring(0, 47) + '...';
          }
        } else {
          result = input;
        }
        break;

      case 'short':
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
        result = input
          .replace(/<div><br><\/div>/gi, '<br>')
          .replace(/<div>/gi, '<br>')
          .replace(/<\/div>/gi, '')
          .replace(/<p><br><\/p>/gi, '<br>')
          .replace(/^<br>/, '')
          .replace(/(<br>\s*){2,}/gi, '<br><br>')
          .trim();
        break;

      case 'oneline':
        result = input
          .replace(/<div><br><\/div>/gi, ' ')
          .replace(/<div>/gi, ' ')
          .replace(/<\/div>/gi, '')
          .replace(/<p><br><\/p>/gi, ' ')
          .replace(/<br\s*\/?>/gi, ' ')
          .replace(/^\s+/, '')
          .replace(/\s+$/, '')
          .replace(/\s{2,}/g, ' ')
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
}]);