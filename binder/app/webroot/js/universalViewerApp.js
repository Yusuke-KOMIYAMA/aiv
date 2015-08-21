/**
 * universalViewerApp.js
 *
 * ユニバーサルビューアー
 *
 * Libraries:
 *
 *   AngularJS
 *     ngAnimate
 *     ngTouch
 *     ngResource
 *     ngTagsInput
 *     angular translate
 *     UI Router
 *     UI Bootstrap
 *     UI Sortable
 *
 *   Bootstrap
 *
 *
 * @see https://angularjs.org/
 * @see https://github.com/angular/angular.js
 * @see https://github.com/angular/bower-angular-animate
 * @see https://github.com/angular/bower-angular-touch
 * @see https://github.com/angular/bower-angular-resource
 * @see https://github.com/mbenford/ngTagsInput
 * @see https://github.com/angular-translate/angular-translate
 * @see https://github.com/angular-ui/ui-router
 * @see https://github.com/chieffancypants/angular-loading-bar
 * @see https://github.com/angular-ui/ui-router
 * @see http://angular-ui.github.io/bootstrap/
 * @see https://github.com/angular-ui/ui-sortable
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Module = UNIVERSALVIEWER.Module || {};
UNIVERSALVIEWER.Module.UniversalViewer = UNIVERSALVIEWER.Module.UniversalViewer || {};

/**
 * AngularJS UniversalViewer Application Module 生成
 *
 * @type {ng.IModule}
 */
UNIVERSALVIEWER.Module.UniversalViewer.app
    = angular.module(
        'universalViewer',
        [
            'ui.bootstrap',
            'ui.router',
            'ui.sortable',
            'ui.sortable.multiselection',
            'ui.openseadragon',
            'pascalprecht.translate',
            'ngTouch',
            'ngResource',
            'ngTagsInput',
            'common.directives',
            'common.services',
            'universalViewer.services',
            'universalViewer.controllers'
        ]
    );

/**
 * モジュール設定
 */
UNIVERSALVIEWER.Module.UniversalViewer.app.config(['$translateProvider', '$stateProvider', '$urlRouterProvider', 'cfpLoadingBarProvider', function($translateProvider, $stateProvider, $urlRouterProvider, cfpLoadingBarProvider) {

    // ローディングバー設定
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = false;
    cfpLoadingBarProvider.latencyThreshold = 500;

    /**
     * 多言語対応のための設定
     */
    $translateProvider.preferredLanguage('en-US');
    $translateProvider.fallbackLanguage('en-US');
    $translateProvider.useStaticFilesLoader({
        prefix: UNIVERSALVIEWER.Config.rootUrl + 'app/webroot/assets/i18n/locale-',
        suffix: '.json'
    });

    /**
     * ルーティング設定
     */
    $urlRouterProvider

        .otherwise('/');

    /**
     * 画面遷移設定
     */
    $stateProvider

        /**
         * ホームページ（ログイン済）
         *
         * 右ペイン：デフォルト バインダーリスト
         */
        .state('home', {
            url: '^/',
            views: {
                "wrapper": {
                    templateUrl: '../app/webroot/views/index.html',
                    abstract: true,
                    controller: function ($scope, $state, $q, $filter, $modal, $window, $translate, homeViewService)
                    {
                        console.log("home");

                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        var VS = homeViewService;
                        VS.initialize();

                        // ------------------------------
                        // 画面へのデータセット
                        // ------------------------------

                        $scope.originalPageSearch = VS.originalPageSearchCondition;
                        $scope.binderPageSearch = VS.binderPageSearchCondition;
                        $scope.logoutUrl = UNIVERSALVIEWER.Config.logoutUrl;

                        // ユーザー一覧を取得する
                        var userId = angular.element("#userId").text();
                        var usersResource = VS.search.userResource.searchUsers();
                        usersResource.$promise.then(
                            // 読込成功（監督ユーザー）
                            function(data) {
                                VS.search.userResource.loading.complete();
                                var users = [];
                                angular.forEach(data.results, function (record, i) {
                                    users.push({
                                        num:record.User.id,
                                        name:record.User.userName
                                    });
                                    if (record.User.id == userId) {
                                        $scope.selectedUser = users[i].num;
                                    }
                                });
                                $scope.users = users;
                            },
                            // 読込失敗（通常ユーザー）
                            function(error) {
                                VS.search.userResource.loading.complete();
                                $scope.users = [];
                            }
                        );

                        // ------------------------------
                        // イベント設定
                        // ------------------------------

                        // オリジナルページ検索ボタンイベントを設定
                        VS.setSearchOriginalPage($state, $window, "#searchButton01");
                        // オリジナルページ検索 Enter key イベント設定
                        VS.setSearchOriginalPageBindKey($state, $window, "#originalPageSearchText");
                        // オリジナルページタグ検索設定
                        VS.setSearchOriginalPageByTag($state, $window, "#opSearchTags", ".opSearchTag");

                        // バインダーページ検索ボタンイベントを設定
                        VS.setSearchBinderPage($state, $window, "#searchButton02");
                        // バインダーページ検索 Enter key イベント設定
                        VS.setSearchBinderPageBindKey($state, $window, "#binderPageSearchText");
                        // バインダーページタグ検索設定
                        VS.setSearchBinderPageByTag($state, $window, "#bpSearchTags", ".bpSearchTag");
                        // バインダーページタイムライン検索設定
                        VS.setTimeline($state, "#sidebar", "#timelineButton");

                        // バインダー作成
                        VS.setCreateBinderButton($scope, $state, $q, $filter, $modal, $window, '', "#sidebar", "#createBinderButton");

                    }
                },
                "contents@home": {
                    templateUrl: '../app/webroot/views/binder/binder.html',
                    controller: function ($scope, $state, $modal, $window, homeViewService, binderViewService)
                    {
                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        // 画面タイトル・クラスセット
                        homeViewService.setViewText("home.title", "home");
                        // 画面オブジェクトセット
                        var VS = binderViewService;

                        // 画面初期化
                        VS.initialize($window);

                        // 他の検索結果情報をリセット
                        VS.sharedStore.binder = null;
                        VS.sharedStore.binderPages = [];
                        VS.sharedStore.originalPages = [];

                        // ------------------------------
                        // 画面へのデータセット
                        // ------------------------------

                        $scope.VS = VS;

                        // ------------------------------
                        // イベント設定
                        // ------------------------------

                        // バインダーパネルのホバーイベント
                        VS.setOverlay("#bindersArea", ".pageImagePanel");
                        // バインダーを押したときに編集画面へ
                        VS.setBinderEdit($state, "#bindersArea", ".pageImagePanel");
                        // バインダー削除アイコンを押した時のイベント設定
                        VS.setBinderDelete($modal, $window, "#bindersArea", ".deleteIcon")

                    }
                }
            }
        })


        /**
         * オリジナルページ一覧
         */
        .state('home.originalPage', {
            url: '^/',
            views: {
                "contents@home": {
                    templateUrl: '../app/webroot/views/originalPage/originalPage.html',
                    controller: function ($scope, $rootScope, $timeout, $state, $q, $filter, $modal, $window, $translate, uiSortableMultiSelectionMethods, homeViewService, originalPageViewService, sharedStoreService)
                    {
                        console.log("home.originalPage");

                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        // ページ遷移確認用フラグ
                        $rootScope.setAllowNavigation('home.originalPage');

                        // 画面タイトル・クラスセット
                        homeViewService.setViewText("originalPage.title", "originalPage");
                        // 画面オブジェクトセット
                        var VS = originalPageViewService;

                        // バインダーの取得
                        var binder = VS.sharedStore.binder;
                        // 「画像検索」の場合は、バインダーを仮に生成（DB未保存）
                        if (null == binder) {
                            binder = new UNIVERSALVIEWER.Class.BinderClass();
                            binder.title = sharedStoreService.defaultBinderTitle;
                            binder.text = sharedStoreService.defaultBinderText;
                        }

                        // 画面初期化
                        VS.initialize(binder);

                        // ------------------------------
                        // 画面へのデータセット
                        // ------------------------------

                        // 画面データセット
                        $scope.VS = VS;
                        // オリジナルページ総数
                        $scope.originalPagesLength = VS.sharedStore.originalPages.length;
                        // バインダーページ総数
                        $scope.binderPagesLength = VS.sharedStore.binder.binderPages.length;

                        // ------------------------------
                        // オリジナルページ関連イベント
                        // ------------------------------

                        // オリジナルページを全てバインダーに追加する
                        VS.setOriginalPages("#selectOriginalPagesButton");
                        // オリジナルページパネルを「小」にセットする
                        VS.setSmall("#smallOriginalPagesButton", "#originalPagesArea");
                        // オリジナルページパネルを「中」にセットする
                        VS.setMiddle("#middleOriginalPagesButton", "#originalPagesArea");
                        // オリジナルページパネルを「大」にセットする
                        VS.setLarge("#largeOriginalPagesButton", "#originalPagesArea");
                        // オリジナルページをクリックすると、バインダーに追加するイベントをセットする
                        VS.setSelectOriginalPage("#originalPagesArea", ".pageImagePanel");
                        // オリジナルページを削除する（確認つき）
                        VS.setUnsetOriginalPage("#originalPagesArea", ".deleteIcon");
                        // 編集ボタンイベント
                        VS.setClickEditButton($state, "#originalPagesArea", ".editIcon");

                        // ------------------------------
                        // バインダーページ関連イベント
                        // ------------------------------

                        // バインダーページを全てバインダーから削除する
                        VS.setUnsetBinderPages("#unselectBinderPagesButton");
                        // バインダーページを入力した数字へ移動する
                        VS.setMoveBinderPage("#moveBinderPageButton");
                        // バインダーページを入力した数字へ移動する
                        VS.setMoveBinderPageReturn("#moveBinderPageInput");
                        // バインダーページパネルを「小」にセットする
                        VS.setSmall("#smallBinderPagesButton", "#binderPagesArea");
                        // バインダーページパネルを「中」にセットする
                        VS.setMiddle("#middleBinderPagesButton", "#binderPagesArea");
                        // バインダーページパネルを「大」にセットする
                        VS.setLarge("#largeBinderPagesButton", "#binderPagesArea");
                        // バインダーページをバインダーから削除する
                        VS.setUnsetBinderPage("#binderPagesArea", ".deleteIcon");
                        // バインダーページを編集する
                        VS.setClickBinderPageEditButton($state, "#binderPagesArea", ".editIcon");

                        // ------------------------------
                        // バインダー関連イベント
                        // ------------------------------

                        // バインダーを編集する
                        VS.setEditBinderModal($timeout, $q, $modal, '', "#editBinderButton");
                        // バインダーを保存する
                        VS.setSaveBinderButton($window, "#saveBinderButton");

                        // セレクト・マルチソート用設定
                        $scope.sortableOptions = uiSortableMultiSelectionMethods.extendOptions({
                            start: function(e, ui) {
                                var width = 0;
                                angular.element(ui.helper[0]).children().each(function(i) {
                                    var w = Number(angular.element(this).width());
                                    var p = Number(angular.element(this).css("padding"));
                                    width += w + p * 2;
                                });
                                angular.element(ui.helper[0]).attr("style","position:absolute;z-index:1000;width:"+width+"px;");
                            },
                            'ui-floating': 'auto',
                            scroll: false,
                            overlap: 'horizontal'
                        });

                        // ------------------------------
                        // データ監視
                        // ------------------------------

                        // ダーティーチェック監視
                        $scope.$watch('VS.dirty', function (dirty) {
                            if (dirty) {
                                $rootScope.setPreventNavigation('home.originalPage');
                            }
                            else {
                                $rootScope.setAllowNavigation('home.originalPage');
                            }
                        });

                        // オリジナルページ総数監視
                        $scope.$watch(
                            'VS.sharedStore.originalPages.length',
                            function() {
                                $scope.originalPagesLength = VS.sharedStore.originalPages.length;
                            },
                            true
                        );

                        // バインダーページ総数監視
                        $scope.$watch(
                            'VS.sharedStore.binder.binderPages.length',
                            function() {
                                $scope.binderPagesLength = VS.sharedStore.binder.binderPages.length;
                            },
                            true
                        );

                        // バインダーページ並び順監視
                        // この $watch と ng-init@template にて並び順を自動的に更新する
                        $scope.$watch(
                            'VS.sharedStore.binder.binderPages',
                            function() {
                                angular.forEach(VS.sharedStore.binder.binderPages, function(page, i) {
                                    page.pageNo = i + 1;
                                })
                            },
                            true
                        );

                        var watch = function() {
                            $timeout(function() {
                                watch();
                            }, 50);
                        };

                        watch();
                    }
                }
            }
        })

        /**
         * オリジナルページ詳細
         */
        .state('home.originalPage.detail', {
            url: '^/',
            views: {
                "contents@home": {
                    templateUrl: '../app/webroot/views/originalPage/originalPage.detail.html',
                    resolve: {
                        originalPage: function ($state, sharedStoreService) {
                            return sharedStoreService.originalPage;
                        }
                    },
                    controller: function ($scope, $rootScope, $state, $stateParams, $q, $filter, $timeout, $window, homeViewService, originalPageDetailViewService, originalPage)
                    {
                        console.log("home.originalPage.detail");

                        // ------------------------------
                        // データチェック
                        // ------------------------------

                        // ページ遷移確認用フラグ
                        $rootScope.setAllowNavigation('home.originalPage.detail');

                        // 「オリジナルページ検索結果」のリストデータがなかったら home へ
                        if (homeViewService.sharedStore.originalPages == null || homeViewService.sharedStore.originalPages.length == 0) {
                            $state.go("home");
                            return false;
                        }
                        // 「選択されたオリジナルページ」データがなかったら home へ
                        if (homeViewService.sharedStore.originalPage == null) {
                            $state.go("home");
                            return false;
                        }

                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        // 画面タイトル・クラスセット
                        homeViewService.setViewText("originalPageDetail.title", "originalPageDetail");
                        // 画面オブジェクトセット
                        var VS = originalPageDetailViewService;
                        // 画面データセット
                        $scope.VS = VS;
                        // 画面初期化
                        VS.initialize(
                            $timeout,
                            $q,
                            $filter,
                            originalPage,
                            "openseadragon",
                            UNIVERSALVIEWER.Config.rootUrl + UNIVERSALVIEWER.Config.opensadragonPrefixUrl
                        );

                        // ------------------------------
                        // イベント設定
                        // ------------------------------

                        // カルーセルのクリックイベント
                        VS.setCarousel($state, $stateParams, "#owl-selected", ".item");
                        // 保存ボタンイベントをセットする
                        VS.setSaveButton($scope, $window, $timeout,  "#saveButton", ".owl-carousel");


                        // ------------------------------
                        // データ監視
                        // ------------------------------

                        // ダーティーチェック監視
                        $scope.$watch(
                            function() {
                                return ($scope.originalPageMeta && $scope.originalPageMeta.$dirty);
                            },
                            function (dirty) {
                                if (dirty) {
                                    $rootScope.setPreventNavigation('home.originalPage.detail');
                                }
                                else {
                                    $rootScope.setAllowNavigation('home.originalPage.detail');
                                }
                            }
                        );

                    }
                }
            }
        })

        /**
         * バインダーページ一覧
         */
        .state('home.binderPage', {
            url: '^/',
            views: {
                "contents": {
                    templateUrl: '../app/webroot/views/binderPage/binderPage.html',
                    controller: function ($scope, $state, homeViewService, binderPageViewService)
                    {
                        console.log("home.binderPage");

                        // ------------------------------
                        // データチェック
                        // ------------------------------

                        // バインダーページ検索結果が null の場合は home へ
                        if (binderPageViewService.sharedStore.binderPages == null) {
                            $state.go("home");
                            return;
                        }

                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        // 他の検索結果情報をリセット
                        homeViewService.sharedStore.binder = null;

                        // 画面タイトル・クラスセット
                        homeViewService.setViewText("binderPage.title", "binderPage");
                        // 画面オブジェクトセット
                        var VS = binderPageViewService;
                        // 画面初期化
                        VS.initialize();

                        // ------------------------------
                        // 画面へのデータセット
                        // ------------------------------

                        // 画面データセット
                        $scope.VS = VS;
                        // バインダーページ数の設定
                        $scope.binderPagesLength = VS.sharedStore.binderPages.length;

                        // ------------------------------
                        // イベント設定
                        // ------------------------------

                        // 編集ボタンイベント
                        binderPageViewService.setClickEditButton($state, "#binderPagesArea", ".editIcon");

                        // ------------------------------
                        // データ監視
                        // ------------------------------

                        // バインダーページ総数監視
                        $scope.$watch(
                            'VS.sharedStore.binderPages.length',
                            function() {
                                $scope.binderPagesLength = VS.sharedStore.binderPages.length;
                            },
                            true
                        );
                    }
                }
            }
        })

        /**
         * バインダーページ詳細
         */
        .state('home.binderPage.detail', {
            url: '^/',
            reloadToHome: true,
            views: {
                "contents@home": {
                    templateUrl: '../app/webroot/views/binderPage/binderPage.detail.html',
                    resolve: {
                        binderPage: function ($state, sharedStoreService) {
                            return sharedStoreService.binderPage;
                        },
                        originalPageData: function($state, searchService, sharedStoreService) {
                            if (sharedStoreService.binderPage == null) {
                                return null;
                            }
                            var originalPage = searchService.originalPageResource.searchOriginalPageIncludeDeleted(sharedStoreService.binderPage.originalPageId);
                            return originalPage.$promise;
                        }
                    },
                    controller: function ($rootScope, $scope, $state, $stateParams, $modal, $filter, $q, $timeout, $window, homeViewService, binderPageDetailViewService, binderPage, originalPageData)
                    {
                        console.log("home.binderPage.detail");

                        // 読込完了(resolve対応)
                        homeViewService.search.originalPageResource.loading.complete();

                        // ------------------------------
                        // データチェック
                        // ------------------------------

                        // バインダーページが null の場合 home へ
                        if (binderPage == null) {
                            $state.go("home");
                            return;
                        }
                        // オリジナルページが null の場合 home へ
                        if (originalPageData == null) {
                            $state.go("home");
                            return;
                        }

                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        // ページ遷移確認用フラグ
                        $rootScope.setAllowNavigation('home.binderPage.detail');

                        // 画面タイトル・クラスセット
                        homeViewService.setViewText("binderPageDetail.title", "binderPageDetail");
                        // 画面オブジェクトセット
                        var VS = binderPageDetailViewService;

                        // バインダーページの元になるオリジナルページ
                        var originalPage = new UNIVERSALVIEWER.Class.OriginalPageClass();
                        originalPage.initFromResource(originalPageData);

                        // 画面初期化
                        VS.initialize(
                            $timeout,
                            $q,
                            $filter,
                            $modal,
                            binderPage,
                            originalPage,
                            "openseadragon",
                            "seadragon",
                            UNIVERSALVIEWER.Config.rootUrl + UNIVERSALVIEWER.Config.opensadragonPrefixUrl,
                            "overlay",
                            "overlay"
                        );

                        // ------------------------------
                        // 画面へのデータセット
                        // ------------------------------

                        $scope.VS = VS;

                        // ------------------------------
                        // イベント設定
                        // ------------------------------

                        // 表示ボタンイベントを設定する
                        VS.setEditMoveButton("#editButtonGroup", "#moveButton");
                        // 丸ボタンイベントを設定する
                        VS.setEditCircleButton("#editButtonGroup", "#circleButton");
                        // 図形：四角ボタンイベントを設定する
                        VS.setEditRectButton("#editButtonGroup", "#rectButton");
                        // 図形：矢印ボタンイベントを設定する
                        VS.setEditArrowButton("#editButtonGroup", "#arrowButton");
                        // 図形：線ボタンイベントを設定する
                        VS.setEditLineButton("#editButtonGroup", "#lineButton");
                        // 注釈ボタンイベントを設定する
                        VS.setEditAnnotateButton("#editButtonGroup", "#annotationButton");
                        // 「線種：実線」選択ボタンイベントを設定する
                        VS.setLineStyleSolidButton("#editButtonGroup", "#solidButton");
                        // 「線種：点線」選択ボタンイベントを設定する
                        VS.setLineStyleDottedButton("#editButtonGroup", "#dottedButton");
                        // 「線幅：太い」選択ボタンイベントを設定する
                        VS.setLineWidthBoldButton("#editButtonGroup", "#strokeWidthBoldButton");
                        // 「線幅：標準」選択ボタンイベントを設定する
                        VS.setLineWidthNormalButton("#editButtonGroup", "#strokeWidthNormalButton");
                        // 「線幅：細い」選択ボタンイベントを設定する
                        VS.setLineWidthNarrowButton("#editButtonGroup", "#strokeWidthNarrowButton");
                        // 「パターン１」選択ボタンイベントを設定する
                        VS.setAnnotationPattern1Button("#editButtonGroup", "#annotationPattern1Button");
                        // 「パターン２」選択ボタンイベントを設定する
                        VS.setAnnotationPattern2Button("#editButtonGroup", "#annotationPattern2Button");
                        // 「パターン３」選択ボタンイベントを設定する
                        VS.setAnnotationPattern3Button("#editButtonGroup", "#annotationPattern3Button");
                        // 「UNDO」ボタンイベントを設定する
                        VS.setUndoButton("#editButtonGroup", "#undoButton");
                        // 「REDO」ボタンイベントを設定する
                        VS.setRedoButton("#editButtonGroup", "#redoButton");
                        // 「REDO」ボタンイベントを設定する
                        VS.setDeleteAllButton("#editButtonGroup", "#removeAllButton");
                        // 注釈の表示切替ボタンを設定する
                        VS.setOverlayToggleButton("#editButtonGroup", "#overlayViewButton");
                        // コメント編集ボタンを設定する
                        VS.setEditAnnotationButton($modal, "#rightMenu", ".editAnnotationButton");
                        // カルーセルのクリックイベントを設定する
                        VS.setCarousel($state, $stateParams, "#owl-selected", ".item");
                        // バインダーページ保存ボタンイベントを設定する
                        VS.setSaveBinderPageButton($scope, $window, $timeout, "#saveButton", '.owl-carousel');

                        // ------------------------------
                        // 画面チェック関数セット
                        // ------------------------------

                        // 画面でのチェック関数設定
                        VS.setIsNone($scope);
                        VS.setIsMove($scope);
                        VS.setIsEllipse($scope);
                        VS.setIsRect($scope);
                        VS.setIsArrow($scope);
                        VS.setIsLine($scope);
                        VS.setIsAnnotate($scope);
                        VS.setIsSolid($scope);
                        VS.setIsDotted($scope);
                        VS.setIsBold($scope);
                        VS.setIsNormal($scope);
                        VS.setIsNarrow($scope);

                        // ------------------------------
                        // データ監視
                        // ------------------------------

                        // ダーティーチェック監視
                        $scope.$watch(
                            function() {
                                return ($scope.formMeta && $scope.formMeta.$dirty == true || VS.imageViewer.isDirty());
                            },
                            function (dirty) {
                                if (dirty) {
                                    $rootScope.setPreventNavigation('home.binderPage.detail');
                                }
                                else {
                                    $rootScope.setAllowNavigation('home.binderPage.detail');
                                }
                            }
                        );
                    }
                }
            }
        })

        /**
         * タイムライン検索結果
         */
        .state('home.timeline', {
            url: '^/',
            views: {
                "contents": {
                    templateUrl: '../app/webroot/views/timeline.html',
                    controller: function ($scope, $state, homeViewService, timelineViewService)
                    {
                        console.log('home.timeline');

                        // ------------------------------
                        // 初期設定
                        // ------------------------------

                        // 他の検索結果情報をリセット
                        homeViewService.sharedStore.binder = null;

                        // 画面タイトル・クラスセット
                        homeViewService.setViewText("timeline.title", "timeline");
                        // 画面オブジェクトセット
                        var VS = timelineViewService;
                        // 画面初期化
                        VS.initialize();

                        // ------------------------------
                        // イベント設定
                        // ------------------------------

                        // バインダーページ詳細へのリンククリックイベント
                        VS.setDetailLink($state, "#timeline-embed", ".detailLink");

                    }
                }
            }
        })

    ;

}]);

UNIVERSALVIEWER.Module.UniversalViewer.app.run(['$rootScope', '$state', '$location', '$window', 'sharedStoreService', function ($rootScope, $state, $location, $window, sharedStoreService) {

    // 次に移動するかどうかを確認する場合は controller で true に設定すること
    $rootScope.preventNavigation = {
        'home.originalPage': false,
        'home.originalPage.detail': false,
        'home.binderPage.detail': false
    };
    $rootScope.preventNavigationUrl = null;

    $rootScope.setAllowNavigation = function(name) {
        $rootScope.preventNavigation[name] = false;
    };

    $rootScope.setPreventNavigation = function(name) {
        $rootScope.preventNavigation[name] = true;
        $rootScope.preventNavigationUrl = $location.absUrl();
    };

    $rootScope.checkAllowNavigation = function(name) {
        // ダーティーチェック
        switch (name) {
            case 'home.originalPage.detail':
            case 'home.binderPage.detail':
            case 'home.originalPage':
                if ($rootScope.preventNavigation[name]) {
                    return confirm(sharedStoreService.confirmMovePageMessage);
                }
                break;
            default:
                break;
        }
        return true;
    };

    // AngularJS アプリ内部でのページ遷移制御
    $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams)
    {
        if (!$rootScope.checkAllowNavigation(fromState.name)) {
            console.log("stop");
            event.preventDefault();
        }
    });

    // AngularJS アプリ外部へのページ遷移制御
    $window.onbeforeunload = function() {
        // Use the same data that we've set in our angular app
        if ($rootScope.preventNavigation[$state.current.name] && $location.absUrl() == $rootScope.preventNavigationUrl) {
            return sharedStoreService.confirmMovePageMessage;
        }
    }

}]);

