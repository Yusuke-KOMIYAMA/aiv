/**
 * Created by yabana on 2015/04/06.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Directive = UNIVERSALVIEWER.Directive || {};
UNIVERSALVIEWER.Directive.Common = UNIVERSALVIEWER.Directive.Common || {};

UNIVERSALVIEWER.Directive.Common.app.directive('uvDisableAnimate', ['$animate', function($animate) {
    return {
        link: function ($scope, $element, $attrs) {
            $scope.$watch( function() {
                return $scope.$eval($attrs.setNgAnimate, $scope);
            }, function(valnew, valold){
                $animate.enabled(!!valnew, $element);
            });
        }
    }
}]);
