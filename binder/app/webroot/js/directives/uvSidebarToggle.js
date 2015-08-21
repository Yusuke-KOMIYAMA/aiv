/**
 * Created on 2015/01/06.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Directive = UNIVERSALVIEWER.Directive || {};
UNIVERSALVIEWER.Directive.Common = UNIVERSALVIEWER.Directive.Common || {};

UNIVERSALVIEWER.Directive.Common.app.directive('uvSidebarToggle', [function() {
    return {
        restrict: "A",
        link: function(scope, element, attrs) {

            // クリックするとサイドメニューを閉じる・開く
            element.bind("click", function () {

                /**
                 * show: 表示する id
                 * hidden: 閉じる id
                 * {
                 *  show: "acdBox01",
                 *  hidden: "acdBox02",
                 * }
                 */
                var data = {};
                eval("data = " + attrs.uvSidebarToggle);
console.log(data);
                var tgFlag = angular.element("#"+data.show).css("display");
                if (tgFlag == "none") {
                    angular.element("#"+data.show).slideDown();
                    angular.element("#"+data.hidden).slideUp();
                    angular.element("#"+data.show+"Flag > i").attr("class","fa fa-chevron-up fa-fw");
                    angular.element("#"+data.hidden+"Flag > i").attr("class","fa fa-chevron-down fa-fw");
                } else {
                    angular.element("#"+data.show).slideUp();
                    angular.element("#"+data.hidden).slideDown();
                    angular.element("#"+data.show+"Flag > i").attr("class","fa fa-chevron-down fa-fw");
                    angular.element("#"+data.hidden+"Flag > i").attr("class","fa fa-chevron-up fa-fw");
                }

            });
        }
    };
}]);
