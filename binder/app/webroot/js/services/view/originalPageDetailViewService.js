/**
 * Created on 2015/02/26.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.OriginalPageDetailViewClass = function(sharedStoreService, searchService) {

    this._sharedStore = sharedStoreService;
    this._search = searchService;
    this._srcOriginalPage = null;
    this._tmpOriginalPage = null;
    this._selectedPages = null;
};

UNIVERSALVIEWER.Service.Viewer.OriginalPageDetailViewClass.carouselImageNum = 3;

UNIVERSALVIEWER.Service.Viewer.OriginalPageDetailViewClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * オリジナルページ編集画面関連データの初期化
     *
     * @param $timeout
     * @param $q
     * @param $filter
     * @param originalPage
     * @param viewerId
     * @param prefixUrl
     */
    initialize: function($timeout, $q, $filter, originalPage, viewerId, prefixUrl)
    {
        var self = this;

        // オリジナルページを編集用にコピーする
        self.srcOriginalPage = originalPage;
        self.tmpOriginalPage = originalPage.clone();

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

        // Openseadragon 初期化
        OpenSeadragon({
            id: viewerId,
            prefixUrl: prefixUrl,
            tileSources: self.tmpOriginalPage.deepZoomImage
        });

        // タグのサジェスト
        self.suggestTags = function(query) {
            var deferred = $q.defer();
            deferred.resolve(
                $filter("filter")(self.sharedStore.allTags, {
                    text: query
                })
            );
            return deferred.promise;
        };

        // カルーセル設定
        self.selectedPages = self.sharedStore.originalPages;
        self.initCarousel($timeout, '.owl-carousel');

    },

    // ####################################################
    // イベント関連
    // ####################################################

    /**
     * 保存ボタンイベントの設定
     * オリジナルページを保存する
     *
     * @param $scope
     * @param $window
     * @timeout
     * @param selector
     * @carousel
     */
    setSaveButton: function($scope, $window, $timeout, selector, carousel)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, 'click', function(event) {

            // tmpOriginalPage を srcOriginalPage,originalPages と同期を取る
            self.srcOriginalPage = self.tmpOriginalPage.clone();
            angular.forEach(self.selectedPages, function(page, i) {
                if (self.srcOriginalPage.id == page.id) {
                    self.selectedPages[i].import(self.srcOriginalPage);
                }
            });

            // データベースに保存
            var originalPageResource = self.search.originalPageResource.saveOriginalPage(self.srcOriginalPage);
            originalPageResource.$promise.then(
                function() {
                    // 保存成功
                    self.search.originalPageResource.loading.complete();
                    self.resetCarousel($timeout, carousel);
                    $scope.$apply;
                    $scope.originalPageMeta.$setPristine();
                },
                function(error) {
                    // 保存失敗
                    self.search.originalPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }
                }
            );
        });
    },

    /**
     * カルーセルのクリックイベント
     *
     * @param $state
     * @param $stateParams
     * @param parent
     * @param selector
     */
    setCarousel: function($state, $stateParams, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event) {

            var originalPageId = angular.element(event.currentTarget).data('original-page-id');

            // TODO: 現在編集しているオリジナルページの保存チェック

            // オリジナルページの選択
            var newOriginalPage = UNIVERSALVIEWER.Class.OriginalPageClass();
            angular.forEach(self.selectedPages, function(page, i) {
                if (page.id == originalPageId) {
                    newOriginalPage = page;
                }
            });
            self.sharedStore.originalPage = newOriginalPage;

            $state.go("home.originalPage.detail", $stateParams, {reload:true});
        });

    },

    // ####################################################
    // 内部処理系
    // ####################################################

    /**
     * カルーセルの初期化
     *
     * @param $timeout
     * @param selector
     */
    initCarousel: function($timeout, selector)
    {
        var self = this;
        var idx = 0;
        angular.forEach(self.selectedPages, function(page, i) {
            if (page.id == self.tmpOriginalPage.id) {
                idx = (i > 0) ? i - 1: 0;
            }
        });
        $timeout(function () {
            angular.element(selector)
                .owlCarousel({
                    items: UNIVERSALVIEWER.Service.Viewer.OriginalPageDetailViewClass.carouselImageNum,
                    responsive: true,
                    lazyLoad: true,
                    navigation: true
                });
            angular.element(selector).data('owlCarousel').jumpTo(idx);
        }, 300);
    },

    /**
     * カルーセルのリセット
     *
     * @param $timeout
     * @param selector
     */
    resetCarousel: function($timeout, selector)
    {
        var self = this;
        angular.element(selector).data('owlCarousel').destroy();
        var idx = 0;
        angular.forEach(self.selectedPages, function(page, i) {
            if (page.id == self.tmpOriginalPage.id) {
                idx = (i > 0) ? i - 1: 0;
            }
        });
        $timeout(function () {
            angular.element(selector)
                .owlCarousel({
                    items: UNIVERSALVIEWER.Service.Viewer.OriginalPageDetailViewClass.carouselImageNum,
                    responsive: true,
                    lazyLoad: true,
                    navigation: true
                });
            angular.element(selector).data('owlCarousel').jumpTo(idx);
        }, 100);
    },

    // ####################################################
    // テンプレートで使う関数
    // ####################################################

    /**
     * 画面で表示されているカルーセルが現在編集しているものかどうか
     *
     * @param page
     * @returns {boolean}
     */
    isCurrentCarousel: function(page) {
        var self = this;
        var isCurrent = false;
        if (page.id == self.tmpOriginalPage.id) {
            isCurrent = true;
        }
        return isCurrent;
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; },
    get srcOriginalPage() { return this._srcOriginalPage; },
    set srcOriginalPage(srcOriginalPage) { this._srcOriginalPage = srcOriginalPage; },
    get tmpOriginalPage() { return this._tmpOriginalPage; },
    set tmpOriginalPage(tmpOriginalPage) { this._tmpOriginalPage = tmpOriginalPage; },
    get selectedPages() { return this._selectedPages; },
    set selectedPages(selectedPages) { this._selectedPages = selectedPages; }

};
