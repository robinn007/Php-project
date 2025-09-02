/**
 * @file DeletedStudentsController.js
 * @description Manages the deleted students archive view, including restore and permanent delete.
 */
angular.module('myApp')
.controller('DeletedStudentsController', ['$scope', 'StudentService', function($scope, StudentService) {
  'use strict';

  $scope.title = 'Deleted Students Archive';
  $scope.students = [];
  $scope.isLoading = true;
  $scope.flashMessage = 'Loading deleted students...';
  $scope.flashType = 'info';

  function setFlash(msg, type) {
    $scope.flashMessage = msg;
    $scope.flashType = type || 'info';
  }

  function removeById(id) {
    var target = String(id);
    $scope.students = ($scope.students || []).filter(function(s) {
      return String(s.id) !== target;
    });
  }

  // Fetch deleted students
  StudentService.getDeletedStudents()
    .then(function(response) {
      if (response && response.data && response.data.success) {
        $scope.students = response.data.students || [];
        if ($scope.students.length === 0) {
          setFlash('No deleted students found.', 'info');
        } else {
          setFlash('Loaded ' + $scope.students.length + ' deleted students.', 'success');
        }
      } else {
        setFlash((response && response.data && response.data.message) || 'Failed to load deleted students.', 'error');
      }
    }, function(error) {
      setFlash('Error loading deleted students: ' +
        ((error && (error.statusText || error.message)) || 'Unknown error'), 'error');
    })['finally'](function() {
      $scope.isLoading = false;
    });

  /**
   * @function restoreStudent
   * @description Restores a soft-deleted student after confirmation.
   * @param {number} id - Student ID to restore
   */
  $scope.restoreStudent = function(id) {
    if (!id) { return; }
    if (confirm('Are you sure you want to restore this student?')) {
      StudentService.restoreStudent(id).then(function(response) {
        if (response && response.data && response.data.success) {
          removeById(id);
          setFlash(response.data.message || 'Student restored successfully.', 'success');
        } else {
          setFlash((response && response.data && response.data.message) || 'Failed to restore student.', 'error');
        }
      }, function(error) {
        setFlash('Error restoring student: ' +
          ((error && (error.statusText || error.message)) || 'Unknown error'), 'error');
      });
    }
  };

  /**
   * @function permanentDelete
   * @description Permanently deletes a student after confirmation.
   * @param {number} id - Student ID to permanently delete
   */
  $scope.permanentDelete = function(id) {
    if (!id) { return; }
    if (confirm('Are you sure you want to permanently delete this student? This action cannot be undone!')) {
      StudentService.permanentDelete(id).then(function(response) {
        if (response && response.data && response.data.success) {
          removeById(id);
          setFlash(response.data.message || 'Student permanently deleted successfully.', 'success');
        } else {
          setFlash((response && response.data && response.data.message) || 'Failed to delete student permanently.', 'error');
        }
      }, function(error) {
        setFlash('Error deleting student permanently: ' +
          ((error && (error.statusText || error.message)) || 'Unknown error'), 'error');
      });
    }
  };
}]);
