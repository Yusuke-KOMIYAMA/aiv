/**
 * 「バインダー」の「バインダーページ」一覧のDOMの描画が完了したことを検知する
 * Controller内で、
 *   $scope.$on('uvBinderPagesRendered', function(event) { // 処理 });
 * を実行して、描画が終わった時の処理を記述する。
 *
 * Created on 2015/01/06.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Directive = UNIVERSALVIEWER.Directive || {};
UNIVERSALVIEWER.Directive.Common = UNIVERSALVIEWER.Directive.Common || {};

UNIVERSALVIEWER.Directive.Common.app.directive('uvBinderPagesRendered', ['$rootScope', '$timeout', function($rootScope, $timeout) {
    return {
        restrict: "A",
        link: function(scope, element, attrs) {
            if (scope.$last === true) {
                $timeout(function () {
                    scope.$emit('uvBinderPagesRendered');
                });
            }
        }
    };
}]);
