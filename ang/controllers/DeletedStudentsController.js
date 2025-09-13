/**
 * @file DeletedStudentsController.js
 * @description Manages the deleted students archive view, including restore and permanent delete.
 */
angular.module('myApp').controller('DeletedStudentsController', ['$scope', 'AjaxHelper', function($scope, AjaxHelper) {
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

    AjaxHelper.ajaxRequest('GET', '/students/deleted')
        .then(function(response) {
            if (response.data.success) {
                $scope.students = response.data.students || [];
                if ($scope.students.length === 0) {
                    setFlash('No deleted students found.', 'info');
                } else {
                    setFlash('Loaded ' + $scope.students.length + ' deleted students.', 'success');
                }
            } else {
                setFlash(response.flashMessage, response.flashType);
            }
        })
        .catch(function(error) {
            setFlash(error.flashMessage, error.flashType);
        })
        ['finally'](function() {
            $scope.isLoading = false;
        });

    $scope.restoreStudent = function(id) {
        if (!id) { return; }
        if (confirm('Are you sure you want to restore this student?')) {
            AjaxHelper.ajaxRequest('POST', '/students/restore/' + id, { action: 'restore', id: id })
                .then(function(response) {
                    if (response.data.success) {
                        removeById(id);
                        setFlash(response.flashMessage, response.flashType);
                    } else {
                        setFlash(response.flashMessage, response.flashType);
                    }
                })
                .catch(function(error) {
                    setFlash(error.flashMessage, error.flashType);
                });
        }
    };

    $scope.permanentDelete = function(id) {
        if (!id) { return; }
        if (confirm('Are you sure you want to permanently delete this student? This action cannot be undone!')) {
            AjaxHelper.ajaxRequest('POST', '/students/permanent_delete/' + id, { action: 'permanent_delete', id: id })
                .then(function(response) {
                    if (response.data.success) {
                        removeById(id);
                        setFlash(response.flashMessage, response.flashType);
                    } else {
                        setFlash(response.flashMessage, response.flashType);
                    }
                })
                .catch(function(error) {
                    setFlash(error.flashMessage, error.flashType);
                });
        }
    };
}]);