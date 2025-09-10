/**
 * @file filters.js
 * @description Defines custom filters for the Student Management System.
 * Includes text formatting, email/phone link generation, and validation.
 */

// Initialize the app variable if not already defined
var app = angular.module('myApp');

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

        function processHTMLForLinks(htmlContent, maxChars) {
          var result = htmlContent;

          var tempDiv = document.createElement('div');
          tempDiv.innerHTML = htmlContent;
          var textLength = (tempDiv.textContent || tempDiv.innerText || '').length;

          if (maxChars && textLength > maxChars) {
            result = truncateHTML(htmlContent, maxChars) + '...';
          }

          // Process emails - make ALL instances clickable
          var emailRegex = /\b([a-zA-Z0-9][a-zA-Z0-9._%+-]{0,62}[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]{0,61}\.[a-zA-Z]{2,})\b/g;
          var emailMatches = [];
          var emailMatch;
          
          while ((emailMatch = emailRegex.exec(result)) !== null) {
            var email = emailMatch[1];
            
            // Check if it's already inside an anchor tag
            var beforeMatch = result.substring(0, emailMatch.index);
            var afterMatch = result.substring(emailMatch.index + emailMatch[0].length);
            var isInsideAnchor = /<a[^>]*[^<]*$/.test(beforeMatch) && /^[^>]*<\/a>/.test(afterMatch);
            
            if (!isInsideAnchor) {
              var cleanEmail = $filter('emailFilter')(email, 'clean');
              var isValidEmail = $filter('emailFilter')(email, 'validate') === 'Valid email';
              
              if (isValidEmail) {
                emailMatches.push({
                  original: email,
                  clean: cleanEmail,
                  index: emailMatch.index,
                  length: emailMatch[0].length
                });
              }
            }
          }

          // Process phone numbers - make ALL instances clickable
          var phoneRegexPatterns = [
            /(\+\d{1,4}[\s.-]?\d{3,5}[\s.-]?\d{4,6}[\s.-]?\d{0,4})/g,  // International format
            /(\b\d{3}[\s.-]?\d{3}[\s.-]?\d{4}\b)/g,                     // US format
            /(\b\d{10,15}\b)/g,                                          // Simple long numbers
            /(\(\d{3}\)[\s.-]?\d{3}[\s.-]?\d{4})/g                      // (xxx) xxx-xxxx format
          ];

          var phoneMatches = [];
          phoneRegexPatterns.forEach(function(phoneRegex) {
            var phoneMatch;
            // Reset regex
            phoneRegex.lastIndex = 0;
            
            while ((phoneMatch = phoneRegex.exec(result)) !== null) {
              var phoneNumber = phoneMatch[1];
              var cleanPhone = phoneNumber.replace(/[\s.\-()]/g, '');
              
              // Check if it's already inside an anchor tag
              var beforeMatch = result.substring(0, phoneMatch.index);
              var afterMatch = result.substring(phoneMatch.index + phoneMatch[0].length);
              var isInsideAnchor = /<a[^>]*[^<]*$/.test(beforeMatch) && /^[^>]*<\/a>/.test(afterMatch);
              
              if (cleanPhone.length >= 10 && cleanPhone.length <= 15 && !isInsideAnchor) {
                var isValidPhone = $filter('phoneFilter')(phoneNumber, 'validate') === 'Valid phone';
                
                if (isValidPhone) {
                  phoneMatches.push({
                    original: phoneNumber,
                    clean: cleanPhone,
                    index: phoneMatch.index,
                    length: phoneMatch[0].length
                  });
                }
              }
            }
          });

          // Combine all matches and sort by index (descending)
          var allMatches = [];
          
          emailMatches.forEach(function(match) {
            allMatches.push({
              type: 'email',
              original: match.original,
              clean: match.clean,
              index: match.index,
              length: match.length
            });
          });
          
          phoneMatches.forEach(function(match) {
            allMatches.push({
              type: 'phone',
              original: match.original,
              clean: match.clean,
              index: match.index,
              length: match.length
            });
          });
          
          // Sort by index descending to avoid index shifting during replacement
          allMatches.sort(function(a, b) { return b.index - a.index; });

          // Process matches from end to beginning to avoid index shifting
          allMatches.forEach(function(match) {
            // Double-check that this position isn't already processed
            var currentSegment = result.substring(match.index, match.index + match.length);
            if (currentSegment === match.original) {
              
              if (match.type === 'email') {
                var emailLink = $filter('emailLinkFilter')(match.clean, {});
                var replacement = '<a href="' + emailLink + '" class="email-link">' + match.clean + '</a>';
                
                var beforeText = result.substring(0, match.index);
                var afterText = result.substring(match.index + match.length);
                result = beforeText + replacement + afterText;
                
              } else if (match.type === 'phone') {
                var formattedPhone = $filter('phoneFilter')(match.original, 'format');
                var phoneLink = $filter('phoneLinkFilter')(match.clean, {});
                var replacement = '<a href="' + phoneLink + '" class="phone-link">' + formattedPhone + '</a>';
                
                var beforeText = result.substring(0, match.index);
                var afterText = result.substring(match.index + match.length);
                result = beforeText + replacement + afterText;
              }
            }
          });

          // Clean up extra whitespace and formatting
          result = result.replace(/>\s+</g, '><')
                         .replace(/\s{3,}/g, ' ')
                         .replace(/\s+([,.;:])/g, '$1')
                         .trim();

          return result;
        }

        function truncateHTML(html, maxChars) {
          var tempContainer = document.createElement('div');
          tempContainer.innerHTML = html;
          var totalTextLength = (tempContainer.textContent || tempContainer.innerText || '').length;

          if (totalTextLength <= maxChars) {
            return html;
          }

          var charCount = 0;
          var openTags = [];

          function truncateNode(node, remainingChars) {
            if (remainingChars <= 0) {
              var closingTags = '';
              for (var k = openTags.length - 1; k >= 0; k--) {
                closingTags += '</' + openTags[k] + '>';
              }
              return closingTags;
            }

            if (node.nodeType === 3) {
              var text = node.textContent;
              if (text.length <= remainingChars) {
                charCount += text.length;
                return text;
              } else {
                charCount += remainingChars;
                var truncatedText = text.substring(0, remainingChars);
                var lastSpaceIndex = truncatedText.lastIndexOf(' ');
                if (lastSpaceIndex > 0 && lastSpaceIndex > remainingChars * 0.8) {
                  truncatedText = truncatedText.substring(0, lastSpaceIndex);
                }
                return truncatedText;
              }
            } else if (node.nodeType === 1) {
              var tagName = node.tagName.toLowerCase();
              var output = '<' + tagName;
              for (var i = 0; i < node.attributes.length; i++) {
                var attr = node.attributes[i];
                output += ' ' + attr.name + '="' + attr.value + '"';
              }
              output += '>';
              openTags.push(tagName);

              var charsUsed = 0;
              var hasContent = false;
              for (var j = 0; j < node.childNodes.length; j++) {
                var remainingForChild = remainingChars - charsUsed;
                if (remainingForChild <= 0) break;
                var childContent = truncateNode(node.childNodes[j], remainingForChild);
                if (childContent.trim()) {
                  hasContent = true;
                  output += childContent;
                  var tempEl = document.createElement('div');
                  tempEl.innerHTML = childContent;
                  var childTextLength = (tempEl.textContent || tempEl.innerText || '').length;
                  charsUsed += childTextLength;
                }
                if (charCount >= maxChars) break;
              }

              openTags.pop();
              if (hasContent || ['b', 'i', 'u', 'strong', 'em', 'span'].indexOf(tagName) !== -1) {
                output += '</' + tagName + '>';
              } else {
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
            var textContent = tempContainer.textContent || tempContainer.innerText || '';
            var truncatedText = textContent.substring(0, maxChars);
            return truncatedText;
          }
        }

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