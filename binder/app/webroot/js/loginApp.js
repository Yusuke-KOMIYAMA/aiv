var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Module = UNIVERSALVIEWER.Module || {};
UNIVERSALVIEWER.Module.loginApp = UNIVERSALVIEWER.Module.loginApp || {};
UNIVERSALVIEWER.Module.loginApp.app = angular.module('loginApp', ['ui.bootstrap', 'pascalprecht.translate']);
UNIVERSALVIEWER.Module.loginApp.app.config(['$translateProvider', function($translateProvider) {

    /**
     * 多言語対応のための設定
     */
    $translateProvider.preferredLanguage('en-US');
    $translateProvider.fallbackLanguage('en-US');
    $translateProvider.useStaticFilesLoader({
        prefix: UNIVERSALVIEWER.Config.rootUrl + 'app/webroot/assets/i18n/locale-',
        suffix: '.json'
    });

}]);
UNIVERSALVIEWER.Module.loginApp.app.controller('loginCtrl', ['$scope', '$translate', function ($scope, $translate) {

    // 言語設定
    $translate.use(angular.element("#langId").text());

    // ローカルログインフォームを閉じておく
    $scope.isCollapsed = true;

}]);
