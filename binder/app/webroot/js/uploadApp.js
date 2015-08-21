/**
 * ファイルアップロード用 AngularJS アプリケーション
 */
'use strict';

/**
 * @ngdoc module
 * @name uploadApp
 * @description
 *
 * uploadApp はファイルアップロードのメインアプリケーションです。
 * flow ライブラリを利用して、サーバーへファイルをアップロードします。
 */
var app = angular.module('uploadApp', ['flow', 'ngResource', 'ngTagsInput','ngAnimate','angular-loading-bar', 'pascalprecht.translate']);

/**
 * @ngdoc config
 * @description
 *
 * Angular Loading bar と flow の設定を行います。
 */
app.config(['cfpLoadingBarProvider', 'flowFactoryProvider', '$translateProvider', function (cfpLoadingBarProvider, flowFactoryProvider, $translateProvider) {

    // ローディングバー設定
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = false;
    cfpLoadingBarProvider.latencyThreshold = 500;

    // ngFlow 設定
    flowFactoryProvider.defaults = {
        target: UNIVERSALVIEWER.Config.rootUrl + 'media/upload/',
        permanentErrors: [404, 500, 501],
        maxChunkRetries: 1,
        chunkRetryInterval: 5000,
        simultaneousUploads: 4,
        withCredentials: true
    };
    flowFactoryProvider.on('catchAll', function (event) {
        console.log('catchAll', arguments);
    });

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

/**
 * @ngdoc filter
 * @name bytes
 * @description
 *
 * ファイルサイズ表示フィルター。byte 数を与えると、 kB, MB, GB, TB, PB へ単位変換を行います。
 */
app.filter('bytes', function() {
    return function(bytes, precision) {
        if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
        if (bytes == 0) return '-';
        if (typeof precision === 'undefined') precision = 1;
        var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'],
            number = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) +  ' ' + units[number];
    }
});

/**
 * @ngdoc service
 * @name tagResourceService
 * @description
 *
 * TagResourceサービス生成
 */
app.service('tagResourceService', ['$resource', 'cfpLoadingBar', UNIVERSALVIEWER.Service.Common.TagResourceServiceClass]);

/**
 * @ngdoc controller
 * @name uploadController
 * @description
 *
 * ファイルアップロードアプリケーションコントローラー
 */
app.controller('uploadController', ['$scope', '$window', '$q', '$filter', '$translate', 'cfpLoadingBar', 'tagResourceService', function($scope, $window, $q, $filter, $translate, cfpLoadingBar, tagResourceService) {

    // 言語設定
    $translate.use(angular.element("#langId").text());

    // すべての関連するタグのリストを取得
    var allTags = tagResourceService.searchTags();
    allTags.$promise.then(function (data) {
        var tags = [];
        angular.forEach(data.results.Tag, function (record) {
            tags.push({
                id: record.id,
                text: record.text
            });
        });
        allTags = tags;
    });
    // タグのサジェスト
    $scope.suggestTags = function(query) {
        var deferred = $q.defer();
        deferred.resolve(
            $filter("filter")(allTags, {
                text: query
            })
        );
        return deferred.promise;
    };

    /**
     * 全体設定
     */

    // フォームを無効にするフラグ
    $scope.isDisable = false;

    // 本日
    $scope.today = UNIVERSALVIEWER.Common.Utils.originalDateFormat(new Date());

    // ngFlow の設定項目
    $scope.config = {
        testChunks: false,
        isStart: false,
        isComplete: false,
        query: function (flowFile, flowChunk) {
            var tags = [];
            angular.forEach(flowFile.tag, function(tag) {
                tags.push(tag.text);
            });
            return {
                title: flowFile.title,
                text: flowFile.text,
                creator: flowFile.creator,
                confirmor: flowFile.confirmor,
                creationDate: flowFile.creationDate,
                tag: tags
            };
        }
    };

    // エラーがあるかどうか
    $scope.getFlowStatus = function($flow) {
        if (!$flow.opts.isStart) {
            return 0;
        }
        else if ($flow.opts.isStart && !$flow.opts.isComplete) {
            return 1;
        }
        var stat = 2;
        angular.forEach($flow.files, function(file) {
            if (file.error) {
                stat = 3;
            }
        });
        return stat;
    };

    // リロードボタン
    $scope.reloadButton = function() {
        $window.location.reload();
    };

    // アップロードできるファイルの制限チェック関数
    var maxFilesize = 100*1024*1024;
    $scope.isAllowFile = function($file) {
        return (!!{png:1,gif:1,jpg:1,jpeg:1,pdf:1}[$file.getExtension()]) && ($file.size < maxFilesize);
    };

    /**
     * イベント処理
     */

    // 初期化処理（ファイル単位）
    var getFilename = function(name) {
        var reg=/(.*)(?:\.([^.]+$))/;
        return name.match(reg)[1];
    };
    $scope.initFile = function(file, userName) {
        file.uploadStatus = 0; // 0:アップロード前、1:アップロード中, 2:アップロード成功, 3:アップロード失敗
        file.creator = userName;
        file.title = getFilename(file.name);
        file.text = getFilename(file.name);
        file.confirmor = userName;
        file.creationDate = $scope.today;
    };

    // アップロード開始イベント（全体・ファイル単位共）
    $scope.startUpload = function(file) {
        file.uploadStatus = 1;
    };
    $scope.$on('flow::uploadStart', function (event, $flow, flowFile) {

        // ファイルごとにイベントが発生するので、フラグで全体の状態を管理する
        if (!$flow.opts.isStart) {
            $flow.opts.isStart = true;
            cfpLoadingBar.start();
            $scope.isDisable = true;
        }

        return false;
    });

    // アップロード開始
    $scope.$on('flow::fileProgress', function(event, $flow, flowFile) {
        flowFile.uploadStatus = 1;
    });
    // アップロード成功時
    $scope.$on('flow::fileSuccess', function(event, $flow, flowFile) {
        flowFile.uploadStatus = 2;
    });
    // アップロード失敗時
    $scope.$on('flow::fileError', function(event, $flow, flowFile) {
        flowFile.uploadStatus = 3;
    });

    // アップロード完了
    $scope.$on('flow::complete', function (event, $flow, flowFile) {

        // 完了ファイル数をカウントして、すべて完了した場合は、全体を完了とする
        var completeNum = 0;
        angular.forEach($flow.files, function(file) {
            if (file.isComplete()) {
                completeNum++;
            }
        });
        if (completeNum == $flow.files.length) {
            cfpLoadingBar.complete();
            $flow.opts.isComplete = true;
        }
        return false;
    });

    // flow-file-added の式を有効にするために false を返す
    $scope.$on('flow::fileAdded', function (event, $flow, flowFile) {
        return false;
    });

}]);
