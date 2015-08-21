/**
 * Home画面サービス
 *
 * @file: homeViewService.js
 */
var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.HomeViewClass = function(sharedStoreService, searchService) {

    this._sharedStore = sharedStoreService;
    this._search = searchService;
    this._originalPageSearchCondition = {
        text: '',
        tags: '',
        isTitle: true,
        isText: true,
        isTag: true
    };
    this._users = [];
    this._binderPageSearchCondition = {
        text: '',
        tags: '',
        isTitle: true,
        isText: true,
        isTag: true,
        isAnnotation: true
    };
};

UNIVERSALVIEWER.Service.Viewer.HomeViewClass.prototype = {

    initialize: function()
    {
        var self = this;

        // すべての関連するタグのリストを取得
        var tagsResource = self.search.tagResource.searchTags();
        tagsResource.$promise.then(
            function (data) {
                // 読込成功
                self.search.tagResource.loading.complete();
                var allTags = [];
                angular.forEach(data.results.Tag, function (record) {
                    allTags.push({
                        id: record.id,
                        text: record.text
                    });
                });
                self.sharedStore.allTags = allTags;
            },
            function(error) {
                // 読込失敗
                self.search.tagResource.loading.complete();
                // 401エラーの場合はログイン画面へ
                if (error.status == 401) {
                    $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                }
            }
        );

    },

    /**
     * 画面遷移による「タイトル」と「クラス名」を指定する
     *
     * @param title
     * @param className
     */
    setViewText: function(title, className)
    {
        var self = this;
        self.sharedStore.title = title;
        self.sharedStore.className = className;
    },

    /**
     * オリジナルページ検索ボタンを押した時のイベントをセットする
     *
     * @param $state $state オブジェクト
     * @param $window
     * @param buttonSelector クリックイベントをセットする要素のセレクター
     */
    setSearchOriginalPage: function($state, $window, buttonSelector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(buttonSelector, 'click', function(event) {
            self._searchOriginalPageInner($state, $window, self);
        });
    },

    /**
     * Enterキーによるオリジナルページ検索イベントをセットする
     *
     * @param $state
     * @param $window
     * @param inputSelector
     */
    setSearchOriginalPageBindKey: function($state, $window, inputSelector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(inputSelector, 'keyup', function(event) {
            if (event.keyCode == 13) {
                self._searchOriginalPageInner($state, $window, self);
            }
        });
    },

    /**
     * オリジナルページ検索の内部処理
     *
     * @param $state
     * @param $window
     * @param self
     * @private
     */
    _searchOriginalPageInner: function($state, $window, self)
    {
        if (self.originalPageSearchCondition.text.trim()) {
            var binder = null;

            if (self.sharedStore.binder != null) {
                binder = self.sharedStore.binder;
            }

            // オリジナルページを検索
            self.sharedStore.originalPages = [];
            var originalPagesResource = self.search.originalPageResource.searchOriginalPages(
                self.originalPageSearchCondition.text,
                self.originalPageSearchCondition.isTitle,
                self.originalPageSearchCondition.isText,
                self.originalPageSearchCondition.isTag,
                binder
            );

            // オリジナルページへレスポンスを格納する
            originalPagesResource.$promise.then(
                function (data) {
                    // 読込成功
                    self.search.originalPageResource.loading.complete();
                    // オリジナルページのインスタンスを生成
                    var pages = [];
                    angular.forEach(data.results.OriginalPage, function (record) {
                        // チェックするバインダーが登録されている場合
                        var isSelect = false;
                        if (binder && binder instanceof UNIVERSALVIEWER.Class.BinderClass) {
                            // このOriginalPageがBinderに登録されているかどうか
                            isSelect = binder.pagesOriginalPageIdExists(record.id);
                        }
                        // オリジナルページを生成
                        var originalPage = new UNIVERSALVIEWER.Class.OriginalPageClass();
                        originalPage.initFromDb(record, isSelect);
                        // リストに登録する
                        pages.push(originalPage);
                    });
                    self.sharedStore.originalPages = pages;
                    // コンテンツエリアをオリジナルページ検索結果へ切り替え
                    $state.go('home.originalPage', {originalPageSearchCondition:self.originalPageSearchCondition});
                },
                function(error) {
                    // 読込失敗
                    self.search.originalPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }
                }
            );
        }
    },

    /**
     * オリジナルページタグを押した時のイベントをセットする
     *
     * @param $state
     * @param $window
     * @param parent
     * @param buttonSelector
     */
    setSearchOriginalPageByTag: function($state, $window, parent, buttonSelector)
    {
        var self = this;

        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, buttonSelector, 'click', function(event) {
            var binder = self.sharedStore.binder;

            // オリジナルページを検索
            var tag = angular.element(event.target).data("tag-text");
            self.sharedStore.originalPages = [];

            var originalPagesResource = self.search.originalPageResource.searchOriginalPagesByTag(tag);

            // 検索結果のレスポンスをShared Storeに保存
            originalPagesResource.$promise.then(
                function(data) {
                    // 読込成功
                    self.search.originalPageResource.loading.complete();
                    var pages = [];
                    // オリジナルページのインスタンスを生成
                    angular.forEach(data.results.OriginalPage, function (record) {
                        // チェックするバインダーが登録されている場合
                        var isSelect = false;
                        if (binder && binder instanceof UNIVERSALVIEWER.Class.BinderClass) {
                            // このOriginalPageがBinderに登録されているかどうか
                            isSelect = binder.pagesOriginalPageIdExists(record.id);
                        }
                        // オリジナルページを生成
                        var originalPage = new UNIVERSALVIEWER.Class.OriginalPageClass();
                        originalPage.initFromDb(record, isSelect);
                        // リストに登録する
                        pages.push(originalPage);
                    });
                    self.sharedStore.originalPages = pages;
                },
                function(error) {
                    // 読込失敗
                    self.search.originalPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }
                }
            );

            // コンテンツエリアをオリジナルページ検索結果へ切り替え
            $state.go('home.originalPage');
        });
    },

    /**
     * バインダーページ検索ボタンを押した時のイベントをセットする
     *
     * @param $state $state オブジェクト
     * @param $window
     * @param buttonSelector クリックイベントをセットする要素のセレクター
     */
    setSearchBinderPage: function($state, $window, buttonSelector)
    {
        var self = this;

        UNIVERSALVIEWER.Common.Utils.onLoad(buttonSelector, 'click', function(event) {
            self._searchBinderPageInner($state, $window, self);
        });
    },

    /**
     * Enterキーでバインダーページ検索をするイベントをセットする
     *
     * @param $state
     * @param $window
     * @param inputSelector
     */
    setSearchBinderPageBindKey: function($state, $window, inputSelector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(inputSelector, 'keyup', function(event) {
            if (event.keyCode == 13) {
                self._searchBinderPageInner($state, $window, self);
            }
        });
    },

    /**
     * バインダーページ検索内部処理
     *
     * @param $state
     * @param $window
     * @param self
     * @private
     */
    _searchBinderPageInner: function($state, $window, self)
    {
        self.sharedStore.binderPages = [];

        if (self.binderPageSearchCondition.text.trim()) {
            // バインダーページを検索
            var binderPagesResource = self.search.binderPageResource.searchBinderPages(
                self.binderPageSearchCondition.text,
                self.binderPageSearchCondition.isTitle,
                self.binderPageSearchCondition.isText,
                self.binderPageSearchCondition.isTag,
                self.binderPageSearchCondition.isAnnotation
            );
            // バインダーページのリザルトをShared Storeに保存
            binderPagesResource.$promise.then(
                function(data) {
                    // 読込成功
                    self.search.binderPageResource.loading.complete();
                    // バインダーページのインスタンスを生成
                    var pages = [];
                    angular.forEach(data.results.BinderPage, function (record) {
                        // バインダーページを生成
                        var binderPage = new UNIVERSALVIEWER.Class.BinderPageClass();
                        binderPage.initFromDb(record);
                        // リストに登録する
                        pages.push(binderPage);
                    });
                    self.sharedStore.binderPages = pages;
                },
                function(error) {
                    // 読込失敗
                    self.search.binderPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }

                }
            );

            // コンテンツエリアをバインダーページ検索結果へ切り替え
            $state.go('home.binderPage');
        }

    },

    /**
     * バインダーページタグを押した時のイベントをセットする
     *
     * @param $state $state オブジェクト
     * @param $window
     * @param parent
     * @param buttonSelector クリックイベントをセットする要素のセレクター
     */
    setSearchBinderPageByTag: function($state, $window, parent, buttonSelector)
    {
        var self = this;

        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, buttonSelector, 'click', function(event) {

            var tag = angular.element(event.target).data("tag-text");
            self.sharedStore.binderPages = [];
            // バインダーページを検索
            var binderPagesResource = self.search.binderPageResource.searchBinderPagesByTag(tag);
            // バインダーページのリザルトをShared Storeへ保存
            binderPagesResource.$promise.then(
                function(data) {
                    // 読込成功
                    self.search.binderPageResource.loading.complete();
                    // バインダーページのインスタンスを生成
                    var pages = [];
                    angular.forEach(data.results.BinderPage, function(record) {
                        // バインダーページを生成
                        var binderPage = new UNIVERSALVIEWER.Class.BinderPageClass();
                        binderPage.initFromDb(record);
                        // リストに登録する
                        pages.push(binderPage);
                    });
                    self.sharedStore.binderPages = pages;
                },
                function(error) {
                    // 読込失敗
                    self.search.binderPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }

                }
            );
            // コンテンツエリアをバインダーページ検索結果へ切り替え
            $state.go('home.binderPage');
        });
    },

    /**
     * タイムラインを押した時のイベントをセットする
     *
     * @param $state $state オブジェクト
     * @param parent
     * @param buttonSelector クリックイベントをセットする要素のセレクター
     */
    setTimeline: function($state, parent, buttonSelector)
    {
        var self = this;

        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, buttonSelector, 'click', function(event) {

            self.sharedStore.timelines = [];

            // タイムラインデータ読み込み
            var $binderPagesResource = self.search.binderPageResource.searchBinderPagesForTimeline();
            $binderPagesResource.$promise.then(
                function(data) {
                    // 読込成功
                    self.search.binderPageResource.loading.complete();
                    // バインダーページ一覧を生成
                    var pages = [];
                    var timelines = {};
                    var flag = true;
                    angular.forEach(data.results.BinderPage, function(record) {
                        var binderPage = new UNIVERSALVIEWER.Class.BinderPageClass();
                        binderPage.initFromDb(record);
                        pages.push(binderPage);
                        if (flag) {
                            flag = false;
                            timelines = {
                                timeline:{
                                    headline: "Your all binder pages.",
                                    type: "default",
                                    text: "This is all your binder pages.",
                                    asset:{
                                        media: binderPage.thumbUrlLarge,
                                        credit: "",
                                        caption: ""
                                    },
                                    date:[]
                                }
                            };
                        }
                        timelines.timeline.date.push({
                            startDate: UNIVERSALVIEWER.Common.Utils.timelineDateFormat(new Date(binderPage.creationDate)),
                            endDate: UNIVERSALVIEWER.Common.Utils.timelineDateFormat(new Date(binderPage.creationDate)),
                            headline: '<a class="detailLink" data-binder-page-id="' + binderPage.id + '">' + binderPage.title + '</a>',
                            text: binderPage.text,
                            asset:{
                                media: binderPage.thumbUrlLarge,
                                credit: "",
                                caption: ""
                            }
                        });
                    });
                    self.sharedStore.binderPages = pages;
                    self.sharedStore.timelines = timelines;
                    self.sharedStore.timelineStartSlide = 0;

                    // ローディング完了
                    self.search.binderPageResource.loading.complete();
                    // タイムラインへ移動
                    $state.go('home.timeline');
                },
                function(error) {
                    // 読込失敗
                    self.search.binderPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }
                }
            );
        });
    },

    /**
     * バインダー作成ボタンイベントの設定
     *
     * @param $scope
     * @param $state
     * @param $q
     * @param $filter
     * @param $modal
     * @param $window
     * @param size
     * @param parent
     * @param selector
     */
    setCreateBinderButton: function($scope, $state, $q, $filter, $modal, $window, size, parent, selector)
    {
        var self = this;

        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, 'click', function(event) {

            // 項目入力モーダルウィンドウを開く
            var modalInstance = $modal.open({
                templateUrl: 'editBinderModal.html',
                size: size,
                resolve: {
                    binder: function () {
                        return new UNIVERSALVIEWER.Class.BinderClass();
                    }
                },
                controller: function ($scope, $modalInstance, binder) {

                    $scope.loadTags = function(query) {
                        var deferred = $q.defer();
                        deferred.resolve(
                            $filter("filter")(self.sharedStore.allTags, {
                                text: query
                            })
                        );
                        return deferred.promise;
                    };

                    $scope.binder = binder.clone();
                    $scope.slides = [];
                    $scope.coverId = 1;

                    $scope.ok = function () {
                        $modalInstance.close($scope.binder);
                    };

                    $scope.cancel = function () {
                        $modalInstance.dismiss('cancel');
                    };
                }
            });
            // バインダー生成モーダルの結果を処理する
            modalInstance.result.then(
                function (binder) {
                    // バインダーを保存する
                    var binderRes = self.search.binderResource.saveBinder(binder);
                    binderRes.$promise.then(
                        function(data) {
                            // バインダー保存成功
                            self.search.binderResource.loading.complete();
                            // バインダーの初期化
                            var resBinder = new UNIVERSALVIEWER.Class.BinderClass();
                            resBinder.initFromDb(data.results.Binder[0]);
                            self.sharedStore.binder = resBinder;

                            $state.go("home.originalPage");
                        },
                        function(error) {
                            // バインダー保存失敗
                            self.search.binderResource.loading.complete();
                            // 401エラーの場合はログイン画面へ
                            if (error.status == 401) {
                                $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                            }
                        }
                    );
                }, function () {
                }
            );
        });

    },

    get title() { return this._title; },
    set title(title) { this._title = title; },
    get className() { return this._className; },
    set className(className) { this._className = className; },
    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; },
    get users() { return this._users; },
    set users(users) { this._users = users; },
    get allTags() { return this._allTags; },
    set allTags(allTags) { this._allTags = allTags; },
    get originalPageSearchCondition() { return this._originalPageSearchCondition; },
    set originalPageSearchCondition(originalPageSearchCondition) { this._originalPageSearchCondition = originalPageSearchCondition; },
    get binderPageSearchCondition() { return this._binderPageSearchCondition; },
    set binderPageSearchCondition(binderPageSearchCondition) { this._binderPageSearchCondition = binderPageSearchCondition; }

};

