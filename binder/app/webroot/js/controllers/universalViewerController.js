/**
 * Created on 2015/01/18.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Controller = UNIVERSALVIEWER.Controller || {};
UNIVERSALVIEWER.Controller.UniversalViewer = UNIVERSALVIEWER.Controller.UniversalViewer || {};

UNIVERSALVIEWER.Controller.UniversalViewer.app = angular.module('universalViewer.controllers', ['universalViewer.services']);

UNIVERSALVIEWER.Controller.UniversalViewer.app.controller('universalViewerController', ['$scope', '$translate', '$timeout', '$resource', '$stateParams', '$filter', 'homeViewService', 'sharedStoreService', function($scope, $translate, $timeout, $resource, $stateParams, $filter, homeViewService, sharedStoreService) {

    // とりあえず、デフォルト
    $translate.use(angular.element("#langId").text());

    $scope.title = "";
    $scope.className = sharedStoreService.className;

    // 自動生成時のバインダーのタイトル・本文（翻訳）
    $scope.$watch(
        function() { return $filter('translate')('binder.default.title'); },
        function(newval) {
            sharedStoreService.defaultBinderTitle = newval;
        }
    );
    $scope.$watch(
        function() { return $filter('translate')('binder.default.text'); },
        function(newval) {
            sharedStoreService.defaultBinderText = newval;
        }
    );

    // メッセージ（翻訳）
    $scope.$watch(
        function() { return $filter('translate')('message.confirm_move_page'); },
        function(newval) {
            sharedStoreService.confirmMovePageMessage = newval;
        }
    );
    $scope.$watch(
        function() { return $filter('translate')('originalPage.message.not_save_binder'); },
        function(newval) {
            sharedStoreService.notSaveBinderMessage = newval;
        }
    );
    $scope.$watch(
        function() { return $filter('translate')('originalPage.message.delete_original_page'); },
        function(newval) {
            sharedStoreService.deleteOriginalPageMessage = newval;
        }
    );

    $scope.$watch(
        function() { return $filter('translate')('imageViewer.popup.close'); },
        function(newval) {
            sharedStoreService.imageViewerMessage.close = newval;
        }
    );
    $scope.$watch(
        function() { return $filter('translate')('imageViewer.popup.edit'); },
        function(newval) {
            sharedStoreService.imageViewerMessage.edit = newval;
        }
    );
    $scope.$watch(
        function() { return $filter('translate')('imageViewer.popup.delete'); },
        function(newval) {
            sharedStoreService.imageViewerMessage.delete = newval;
        }
    );
    $scope.$watch(
        function() { return $filter('translate')('imageViewer.popup.confirm_delete'); },
        function(newval) {
            sharedStoreService.imageViewerMessage.confirmDelete = newval;
        }
    );

    // タイトル、クラス名のチェック
    $scope.$watch(
        function() { return $filter('translate')(sharedStoreService.title); },
        function(newval) {
            $scope.title = newval;
        }
    );
    $scope.$watch(
        function() { return sharedStoreService.className; },
        function(newval) {
            $scope.className = newval;
        },
        true
    );

    /**
     * AngularJS の $timeout を走らせて、再描画処理を都度行なうようにする
     */
    var watch = function() {
        $timeout(function() {
            watch();
        }, 50);
    };

    watch();

}]);
