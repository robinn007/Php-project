/**
 * @file directives.js
 * @description Defines custom directives for the Student Management System.
 * Includes flash message handling, email/phone validation, and contenteditable binding.
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
 * @ngdoc directive
 * @name renderHtml
 * @description Renders HTML content with applied filters.
 * @restrict A
 */
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