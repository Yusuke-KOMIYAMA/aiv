/**
 * Created on 2015/02/17.
 */
var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};

UNIVERSALVIEWER.Service.Common.app = angular.module('common.services', ['ngResource','ngAnimate','angular-loading-bar']);
UNIVERSALVIEWER.Service.Common.app.config(['cfpLoadingBarProvider', function(cfpLoadingBarProvider) {

    // ローディングバー設定
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.latencyThreshold = 500;

}]);

UNIVERSALVIEWER.Service.Common.app.service('originalPageResourceService', ['$resource', 'cfpLoadingBar', UNIVERSALVIEWER.Service.Common.OriginalPageResourceServiceClass]);
UNIVERSALVIEWER.Service.Common.app.service('binderResourceService', ['$resource', 'cfpLoadingBar', UNIVERSALVIEWER.Service.Common.BinderResourceServiceClass]);
UNIVERSALVIEWER.Service.Common.app.service('binderPageResourceService', ['$resource', 'cfpLoadingBar', UNIVERSALVIEWER.Service.Common.BinderPageResourceServiceClass]);
UNIVERSALVIEWER.Service.Common.app.service('tagResourceService', ['$resource', 'cfpLoadingBar', UNIVERSALVIEWER.Service.Common.TagResourceServiceClass]);
UNIVERSALVIEWER.Service.Common.app.service('userResourceService', ['$resource', 'cfpLoadingBar', UNIVERSALVIEWER.Service.Common.UserResourceServiceClass]);
UNIVERSALVIEWER.Service.Common.app.service('searchService', ['originalPageResourceService', 'binderResourceService', 'binderPageResourceService', 'tagResourceService', 'userResourceService', UNIVERSALVIEWER.Service.Common.SearchServiceClass]);
UNIVERSALVIEWER.Service.Common.app.service('sharedStoreService', [UNIVERSALVIEWER.Service.Common.SharedStoreServiceClass]);
