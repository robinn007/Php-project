/**
 * @file stateFilter.js
 * @description Directive for a multi-select state dropdown filter with search, checkboxes and preselection.
 */

angular.module("myApp").directive("stateFilter", [
  "$timeout",
  function ($timeout) {
    return {
      restrict: "E",
      scope: {
        selectedStates: "=",
        onStateChange: "&",
      },
      template:
        '<div class="form-group">' +
        "<label>Filter by State</label>" +
        '<div class="multiselect-dropdown">' +
        '<div class="multiselect-button" ng-click="toggleDropdown()">' +
        "<span>{{ getSelectedText() }}</span>" +
        '<span class="caret">▼</span>' +
        "</div>" +
        '<div class="multiselect-options" ng-show="isDropdownOpen">' +
        '<div class="search-container">' +
        '<input type="text" ' +
        'placeholder="Search states..." ' +
        'ng-model="searchText" ' +
        'ng-change="onSearchChange()" ' +
        'class="search-input" ' +
        'ng-click="$event.stopPropagation()">' +
        "</div>" +
        '<div class="option-item">' +
        "<label>" +
        '<input type="checkbox" ' +
        'ng-model="selectAll" ' +
        'ng-change="toggleSelectAll()" ' +
        'ng-click="$event.stopPropagation()"> ' +
        "All States" +
        "</label>" +
        "</div>" +
        '<div class="option-item" ng-repeat="state in filteredStates">' +
        "<label>" +
        '<input type="checkbox" ' +
        'ng-model="selectedStateMap[state]" ' +
        'ng-change="updateSelectedStates()" ' +
        'ng-click="$event.stopPropagation()"> ' +
        "{{ state }}" +
        "</label>" +
        "</div>" +
        '<div class="apply-container">' +
        '<button type="button" ' +
        'class="btn btn-primary btn-sm" ' +
        'ng-click="applyFilter()" ' +
        'ng-disabled="!hasChanges()">' +
        "Apply Filter" +
        "</button>" +
        "</div>" +
        "</div>" +
        "</div>" +
        "</div>",
      link: function (scope, element, attrs) {
        // Define states (same as in controllers)
        scope.allStates = [
          "Andaman and Nicobar Islands",
          "Andhra Pradesh",
          "Arunachal Pradesh",
          "Assam",
          "Bihar",
          "Chandigarh",
          "Chhattisgarh",
          "Dadra and Nagar Haveli and Daman and Diu",
          "Delhi",
          "Goa",
          "Gujarat",
          "Haryana",
          "Himachal Pradesh",
          "Jammu and Kashmir",
          "Jharkhand",
          "Karnataka",
          "Kerala",
          "Ladakh",
          "Lakshadweep",
          "Madhya Pradesh",
          "Maharashtra",
          "Manipur",
          "Meghalaya",
          "Mizoram",
          "Nagaland",
          "Odisha",
          "Puducherry",
          "Punjab",
          "Rajasthan",
          "Sikkim",
          "Tamil Nadu",
          "Telangana",
          "Tripura",
          "Uttar Pradesh",
          "Uttarakhand",
          "West Bengal",
        ].sort();

        // Initialize scope variables
        scope.isDropdownOpen = false;
        scope.selectedStateMap = {};
        scope.selectAll = true;
        scope.searchText = "";
        scope.filteredStates = angular.copy(scope.allStates);
        scope.appliedStates = []; // Track the last applied state
        scope.pendingChanges = false;

        // Add CSS styles
        var style = document.createElement("style");
        style.textContent = `
                .multiselect-dropdown {
                    position: relative;
                    width: 100%;
                }
                .multiselect-button {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 12px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    background: white;
                    cursor: pointer;
                    min-height: 34px;
                }
                .multiselect-button:hover {
                    border-color: #66afe9;
                    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, .6);
                }

                .multiselect-options {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    border: 1px solid #ccc;
                    border-top: none;
                    border-radius: 0 0 4px 4px;
                    max-height: 300px;
                    overflow-y: auto;
                    z-index: 1000;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .search-container {
                    padding: 8px;
                    border-bottom: 1px solid #eee;
                    background: #f9f9f9;
                }
                .search-input {
                    width: 100%;
                    padding: 6px 8px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                    font-size: 14px;
                }
                .search-input:focus {
                    outline: none;
                    border-color: #66afe9;
                    box-shadow: 0 0 5px rgba(102, 175, 233, .5);
                }
                .option-item {
                    padding: 6px 12px;
                    border-bottom: 1px solid #f0f0f0;
                }
                .option-item:hover {
                    background-color: #f5f5f5;
                }
                .option-item label {
                    margin: 0;
                    font-weight: normal;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                }
                .option-item input[type="checkbox"] {
                    margin-right: 8px;
                    margin-top: 0;
                }
                .apply-container {
                    padding: 8px 12px;
                    border-top: 1px solid #eee;
                    background: #f9f9f9;
                }
                .apply-container button {
                    width: 100%;
                }
            `;
        document.head.appendChild(style);

        // Initialize with all states selected by default
        function initializeStates() {
          if (!scope.selectedStates || !Array.isArray(scope.selectedStates)) {
            scope.selectedStates = angular.copy(scope.allStates);
          }

          // Create a map for easier checkbox management
          scope.allStates.forEach(function (state) {
            scope.selectedStateMap[state] =
              scope.selectedStates.indexOf(state) !== -1;
          });

          // Store initial applied states - FIXED: Initialize as empty to show changes
          scope.appliedStates = [];
          updateSelectAllState();
          
          // FIXED: Set pendingChanges to true if states are selected but not applied
          if (scope.selectedStates.length > 0) {
            scope.pendingChanges = true;
          }
          
          console.log(
            "State filter initialized with selected states:",
            scope.selectedStates
          );
          console.log("Pending changes:", scope.pendingChanges);
        }

        // Toggle dropdown visibility
        scope.toggleDropdown = function () {
          scope.isDropdownOpen = !scope.isDropdownOpen;
          if (scope.isDropdownOpen) {
            // Focus search input when dropdown opens
            $timeout(function () {
              var searchInput = element[0].querySelector(".search-input");
              if (searchInput) {
                searchInput.focus();
              }
            }, 100);
          }
        };

        // Handle search text changes
        scope.onSearchChange = function () {
          if (scope.searchText && scope.searchText.trim()) {
            var searchTerm = scope.searchText.toLowerCase().trim();
            scope.filteredStates = scope.allStates.filter(function (state) {
              return state.toLowerCase().indexOf(searchTerm) !== -1;
            });
          } else {
            scope.filteredStates = angular.copy(scope.allStates);
          }
        };

        // Get display text for selected states
        scope.getSelectedText = function () {
          // FIXED: Use selectedStates instead of appliedStates to show current selection
          var statesToShow = scope.selectedStates || [];
          
          if (statesToShow.length === 0) {
            return "No states selected";
          }
          if (statesToShow.length === scope.allStates.length) {
            return "All States";
          }
          if (statesToShow.length === 1) {
            return statesToShow[0];
          }
          return statesToShow.length + " states selected";
        };

        // Update selected states array when checkboxes change
        scope.updateSelectedStates = function () {
          var newSelectedStates = [];
          scope.allStates.forEach(function (state) {
            if (scope.selectedStateMap[state]) {
              newSelectedStates.push(state);
            }
          });

          scope.selectedStates = newSelectedStates;
          updateSelectAllState();

          // FIXED: Check if current selection differs from applied states
          scope.pendingChanges = !arraysEqual(scope.selectedStates, scope.appliedStates);

          console.log("Pending selected states:", scope.selectedStates);
          console.log("Applied states:", scope.appliedStates);
          console.log("Has changes:", scope.pendingChanges);
        };

        // Toggle select all functionality
        scope.toggleSelectAll = function () {
          if (scope.selectAll) {
            scope.allStates.forEach(function (state) {
              scope.selectedStateMap[state] = true;
            });
          } else {
            scope.allStates.forEach(function (state) {
              scope.selectedStateMap[state] = false;
            });
          }
          scope.updateSelectedStates();
        };

        // Apply the filter (trigger the callback)
        scope.applyFilter = function () {
          scope.appliedStates = angular.copy(scope.selectedStates);
          scope.pendingChanges = false;
          scope.isDropdownOpen = false;
          scope.onStateChange({ states: scope.selectedStates });
          console.log("Filter applied with states:", scope.selectedStates);
        };

        // FIXED: Check if there are pending changes
        scope.hasChanges = function () {
          // Always allow applying if states are selected but never been applied
          if (scope.appliedStates.length === 0 && scope.selectedStates.length > 0) {
            return true;
          }
          // Otherwise check for actual changes
          return scope.pendingChanges || !arraysEqual(scope.selectedStates, scope.appliedStates);
        };

        // Update select all checkbox state
        function updateSelectAllState() {
          var allStatesSelected = scope.allStates.every(function (state) {
            return scope.selectedStateMap[state];
          });
          scope.selectAll = allStatesSelected;
        }

        // Helper function to compare arrays - FIXED
        function arraysEqual(arr1, arr2) {
          if (!arr1 && !arr2) return true;
          if (!arr1 || !arr2) return false;
          if (arr1.length !== arr2.length) return false;
          var sorted1 = arr1.slice().sort();
          var sorted2 = arr2.slice().sort();
          return sorted1.every(function (val, index) {
            return val === sorted2[index];
          });
        }

        // Close dropdown when clicking outside
        function handleOutsideClick(event) {
          var dropdownElement = element[0].querySelector(
            ".multiselect-dropdown"
          );
          if (dropdownElement && !dropdownElement.contains(event.target)) {
            scope.$apply(function () {
              scope.isDropdownOpen = false;
            });
          }
        }

        // Watch for changes in selectedStates from parent
        scope.$watchCollection("selectedStates", function (newValue, oldValue) {
          if (newValue !== oldValue && newValue) {
            scope.allStates.forEach(function (state) {
              scope.selectedStateMap[state] = newValue.indexOf(state) !== -1;
            });
            // Don't update appliedStates here - let user apply manually
            updateSelectAllState();
            // Check if there are changes compared to applied states
            scope.pendingChanges = !arraysEqual(newValue, scope.appliedStates);
            console.log("External selectedStates change detected:", newValue);
          }
        });

        // Initialize after a timeout
        $timeout(function () {
          initializeStates();
        }, 100);

        // Add click outside listener
        $timeout(function () {
          document.addEventListener("click", handleOutsideClick);
        }, 0);

        // Cleanup on scope destroy
        scope.$on("$destroy", function () {
          document.removeEventListener("click", handleOutsideClick);
        });
      },
    };
  },
]);