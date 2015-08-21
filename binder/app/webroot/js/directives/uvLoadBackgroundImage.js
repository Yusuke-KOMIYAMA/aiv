/**
 * Created on 2015/01/16.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Directive = UNIVERSALVIEWER.Directive || {};
UNIVERSALVIEWER.Directive.Common = UNIVERSALVIEWER.Directive.Common || {};

UNIVERSALVIEWER.Directive.Common.app.directive('uvLoadBackgroundImage', [function() {
    return function (scope, element, attrs) {
        var url = attrs.uvLoadBackgroundImage;
        element.css({
            'background-image': 'url(' + url + ')'
        });
    }
}]);

