/**
 * Created on 2015/01/06.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Directive = UNIVERSALVIEWER.Directive || {};
UNIVERSALVIEWER.Directive.Common = UNIVERSALVIEWER.Directive.Common || {};

UNIVERSALVIEWER.Directive.Common.app.directive('uvToggle', [function() {
    return {
        restrict: "A",
        link: function(scope, element, attrs) {

            // クリックするとサイドメニューを閉じる・開く
            element.bind("click", function () {

                /**
                 * target: 閉じた状態を保持するID
                 * class: targetに追加するクラス名
                 * removeCls: (オプション) 同時に削除するクラス名
                 * {
                 *  target: "wrapper",
                 *  class: "closeSearchResult",
                 *  removeCls: "closeBinder"
                 * }
                 */
                var data = {};
                eval("data = " + attrs.uvToggle);

                /*
                var attr = angular.element("body").attr("data-toggle" + "-" + data.name);
                 */

                if (angular.element("#"+data.target).hasClass(data.class)) {
                    angular.element("#"+data.target).removeClass(data.class);
                }
                else {
                    angular.element("#"+data.target).addClass(data.class);
                    if (typeof data.removeCls !== 'undefined' && data.removeCls !== false) {
                        angular.element("#"+data.target).removeClass(data.removeCls);
                    }
                }

            });
        }
    };
}]);
