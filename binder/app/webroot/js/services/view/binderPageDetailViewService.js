/**
 * Created on 2015/02/26.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.BinderPageDetailViewClass = function(sharedStoreService, searchService) {

    this._sharedStore = sharedStoreService;
    this._search = searchService;
    this._imageViewer = null;
    this._srcBinderPage = null;
    this._tmpBinderPage = null;
    this._originalPage = null;
    this._selectedPages = null;

    this._originalPageMeta = null;
    this._binderPageMeta = null;

    this._isOpenColorPicker = true;
};

UNIVERSALVIEWER.Service.Viewer.BinderPageDetailViewClass.carouselImageNum = 3;

UNIVERSALVIEWER.Service.Viewer.BinderPageDetailViewClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    initialize: function($timeout, $q, $filter, $modal, binderPage, originalPage, viewerId, viewerName, prefixUrl, overlayId, overlayClass)
    {
        var self = this;

        // タイムラインの戻り先を指定
        if (self.sharedStore.returnView == 'home.timeline') {
            angular.forEach(self.sharedStore.binderPages, function(page, i) {
                if (binderPage.id == page.id) {
                    self.sharedStore.timelineStartSlide = i + 1;
                }
            });
        }

        // バインダーページを編集用にコピーする
        self.srcBinderPage = binderPage;
        self.tmpBinderPage = binderPage.clone();

        // 参照用オリジナルページをセット
        self.originalPage = originalPage;

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
                self.search.tagResource.loading.complete();
                // 401エラーの場合はログイン画面へ
                if (error.status == 401) {
                    $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                }
            }
        );

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

        // カラーピッカーの設定
        this.isOpenColorPicker = true;
        angular.element("#colorPicker").colorpicker({
            defaultPalette: 'theme',
            color: "#ff0000"
        });
        angular.element("#colorPicker").on("change.color", function(event, color){
            self.imageViewer.edit.stroke = color;
        });

        // 画像ビューアの設定（アノテーション付）
        self.imageViewer = new UNIVERSALVIEWER.Common.ImageViewer(binderPage);
        self.imageViewer.initialize(
            viewerId,
            viewerName,
            prefixUrl,
            self.tmpBinderPage.deepZoomImage,
            overlayId,
            overlayClass,
            self.tmpBinderPage.url,
            self.tmpBinderPage.width,
            self.tmpBinderPage.height,
            self.sharedStore.imageViewerMessage,
            self.tmpBinderPage.annotations,
            function(annotation) {
                var modalInstance = $modal.open({
                    templateUrl: 'modal.html',
                    controller: function ($scope, $modalInstance) {

                        $scope.annotate = {
                            svgId: annotation.svgId,
                            title: annotation.title,
                            text: annotation.text,
                            url: annotation.url
                        };

                        $scope.ok = function () {
                            $modalInstance.close($scope.annotate);
                        };

                        $scope.cancel = function () {
                            $modalInstance.dismiss('cancel');
                        };
                    }
                });
                modalInstance.result.then(function (annotate) {
                    // 成功した時は、編集内容を反映する
                    self.imageViewer.editAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, annotate.svgId, annotate.title, annotate.text, annotate.url);
                }, function () {
                    // 失敗もしくはキャンセルしたときは何もしない
                });
            }
        );

        // カルーセル初期化
        self.initCarousel($timeout, '.owl-carousel');

    },

    // ####################################################
    // イベント関連
    // ####################################################

    /**
     * 移動・拡縮ボタンイベントを設定する
     * edit.mode = 0;
     *
     * @param parentSelector
     * @param selector
     */
    setEditMoveButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.mode = UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE;
        });
    },

    /**
     * 図形：丸ボタンイベントを設定する
     * edit.mode = 1;
     *
     * @param parentSelector
     * @param selector
     */
    setEditCircleButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.mode = UNIVERSALVIEWER.Common.ImageViewer.mode.ELLIPSE;
        });
    },

    /**
     * 図形：四角ボタンイベントを設定する
     * edit.mode = 2;
     *
     * @param parentSelector
     * @param selector
     */
    setEditRectButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.mode = UNIVERSALVIEWER.Common.ImageViewer.mode.RECT;
        });
    },

    /**
     * 図形：矢印ボタンイベントを設定する
     * edit.mode = 3;
     *
     * @param scope
     * @param parentSelector
     * @param selector
     */
    setEditArrowButton: function(scope, parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.mode = UNIVERSALVIEWER.Common.ImageViewer.mode.ARROW;
        });
    },

    /**
     * 図形：線ボタンイベントを設定する
     * edit.mode = 4;
     *
     * @param parentSelector
     * @param selector
     */
    setEditLineButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.mode = UNIVERSALVIEWER.Common.ImageViewer.mode.LINE;
        });
    },

    /**
     * 注釈ボタンイベントを設定する
     * edit.mode = 5;
     *
     * @param parentSelector
     * @param selector
     */
    setEditAnnotateButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.mode = UNIVERSALVIEWER.Common.ImageViewer.mode.ANNOTATE;
        });
    },

    /**
     * 「線種：実線」選択ボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setLineStyleSolidButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.lineStyle = UNIVERSALVIEWER.Common.ImageViewer.lineStyle.SOLID;
        });
    },

    /**
     * 「線種：点線」選択ボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setLineStyleDottedButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.lineStyle = UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED;
        });
    },

    /**
     * 「線幅：太い」選択ボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setLineWidthBoldButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.strokeWidth = UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.BOLD;
        });
    },

    /**
     * 「線幅：標準」選択ボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setLineWidthNormalButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.strokeWidth = UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NORMAL;
        });
    },

    /**
     * 「線幅：細い」選択ボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setLineWidthNarrowButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.strokeWidth = UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NARROW;
        });
    },

    /**
     * 「パターン１」選択ボタンイベントを設定する
     * カラー：赤, 線種：実線, 太さ：標準
     *
     * @param parentSelector
     * @param selector
     */
    setAnnotationPattern1Button: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.stroke = 'red';
            self.imageViewer.edit.lineStyle = UNIVERSALVIEWER.Common.ImageViewer.lineStyle.SOLID;
            self.imageViewer.edit.strokeWidth = UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NORMAL;
        });
    },

    /**
     * 「パターン２」選択ボタンイベントを設定する
     * カラー：青, 線種：点線, 太さ：細い
     *
     * @param parentSelector
     * @param selector
     */
    setAnnotationPattern2Button: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.stroke = 'blue';
            self.imageViewer.edit.lineStyle = UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED;
            self.imageViewer.edit.strokeWidth = UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NARROW;
        });
    },

    /**
     * 「パターン３」選択ボタンイベントを設定する
     * カラー：黄色, 線種：実線, 太さ：太い
     *
     * @param parentSelector
     * @param selector
     */
    setAnnotationPattern3Button: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.edit.stroke = 'yellow';
            self.imageViewer.edit.lineStyle = UNIVERSALVIEWER.Common.ImageViewer.lineStyle.SOLID;
            self.imageViewer.edit.strokeWidth = UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.BOLD;
        });
    },

    /**
     * UNDOボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setUndoButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.undo();
        });
    },

    /**
     * REDOボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setRedoButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.redo();
        });
    },

    /**
     * 全削除ボタンイベントを設定する
     *
     * @param parentSelector
     * @param selector
     */
    setDeleteAllButton: function(parentSelector, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            self.imageViewer.removeAnnotations(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW);
        });
    },

    /**
     * 表示の切り替え
     *
     * @param parentSelector
     * @param selector
     */
    setOverlayToggleButton: function(parentSelector, selector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            angular.element("#overlay").toggle();
            self.imageViewer.edit.isViewOverlay = !self.imageViewer.edit.isViewOverlay;
        });
    },

    /**
     * コメント編集ボタンイベント
     *
     * @param $modal
     * @param parentSelector
     * @param selector
     */
    setEditAnnotationButton: function($modal, parentSelector, selector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parentSelector, selector, 'click', function(event) {
            var annotationId = angular.element(event.target).parents(".annotationTextInfo").data("annotation-id");
            var annotation = null;
            angular.forEach(self.imageViewer.annotation.annotations, function(record) {
                if (record.svgId == annotationId) {
                    annotation = record;
                }
            });
            var modalInstance = $modal.open({
                templateUrl: 'modal.html',
                controller: function ($scope, $modalInstance) {

                    $scope.annotate = {
                        svgId: annotation.svgId,
                        title: annotation.title,
                        text: annotation.text,
                        url: annotation.url
                    };

                    $scope.ok = function () {
                        $modalInstance.close($scope.annotate);
                    };

                    $scope.cancel = function () {
                        $modalInstance.dismiss('cancel');
                    };
                }
            });
            modalInstance.result.then(function (annotate) {
                // 成功した時は、編集内容を反映する
                self.imageViewer.editAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, annotate.svgId, annotate.title, annotate.text, annotate.url);
            }, function () {
                // 失敗もしくはキャンセルしたときは何もしない
            });
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

            // TODO:現在編集しているバインダーページの保存チェック

            // 編集するバインダーページ変更
            var newBinderPage = null;
            switch (self.sharedStore.returnView) {
                case 'home.binderPage':
                case 'home.timeline':
                    var binderPageId = angular.element(event.currentTarget).data('binder-page-id');
                    angular.forEach(self.selectedPages, function (page, i) {
                        if (page.id == binderPageId) {
                            newBinderPage = self.selectedPages[i];
                        }
                    });
                    break;
                default:
                    var originalPageId = angular.element(event.currentTarget).data('original-page-id');
                    angular.forEach(self.selectedPages, function (page, i) {
                        if (page.originalPageId == originalPageId) {
                            newBinderPage = self.selectedPages[i];
                        }
                    });
                    break;
            }


            self.sharedStore.binderPage = newBinderPage;
            $state.go("home.binderPage.detail", $stateParams, {reload:true});
        });
    },

    /**
     * バインダーページ保存ボタンイベントの設定
     *
     * @param $scope
     * @param $window
     * @param $timeout
     * @param selector
     * @param carousel
     */
    setSaveBinderPageButton: function($scope, $window, $timeout, selector, carousel)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, 'click', function(event) {

            // tmpBinderPage に imageViewer の annotation.annotations をセットする
            self.tmpBinderPage.annotations = self.imageViewer.annotation.annotations;

            // データベースに保存
            var binderPageResource = self.search.binderPageResource.saveBinderPage(self.tmpBinderPage);
            binderPageResource.$promise.then(
                function(data) {
                    // 保存成功
                    self.search.binderPageResource.loading.complete();
                    // バインダーの初期化
                    var resBinderPage = new UNIVERSALVIEWER.Class.BinderPageClass();
                    resBinderPage.initFromDb(data.results.BinderPage[0]);
                    // データベースから取得したデータをtmpBinderPageにセットする
                    self.tmpBinderPage = resBinderPage;
                    // tmpBinderPage を srcBinderPage と同期を取る
                    self.srcBinderPage = self.tmpBinderPage.clone();
                    // バインダーページ一覧も更新
                    var selected = angular.copy(self.selectedPages);
                    angular.forEach(self.selectedPages, function (page, i) {
                        if (self.srcBinderPage.id == page.id) {
                            selected[i] = self.srcBinderPage;
                        }
                    });
                    self.selectedPages = selected;
                    // 共有オブジェクトも更新確定
                    switch (self.sharedStore.returnView) {
                        case 'home.binderPage':
                        case 'home.timeline':
                            self.sharedStore.binderPages = angular.copy(self.selectedPages);
                            break;
                        default:
                            self.sharedStore.binder.binderPages = angular.copy(self.selectedPages);
                            break;
                    }

                    $scope.$apply;
                    // ダーティー状態クリア
                    $scope.formMeta.$setPristine();
                    self.imageViewer.clearDirty();
                    // カルーセル更新
                    self.resetCarousel($timeout, carousel);
                },
                function(error) {
                    // 保存失敗
                    self.search.binderPageResource.loading.complete();
                    // 401エラーの場合はログイン画面へ
                    if (error.status == 401) {
                        $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                    }
                }
            );
        });
    },

    // ####################################################
    // データチェック関連
    // ####################################################

    setIsNone: function(scope) {
        var self = this;
        scope.isNone = function() {
            return self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.NONE;
        }
    },
    setIsMove: function(scope) {
        var self = this;
        scope.isMove = function() {
            return (
                self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.NONE ||
                self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE
            );
        }
    },
    setIsEllipse: function(scope) {
        var self = this;
        scope.isEllipse = function() {
            return self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.ELLIPSE;
        }
    },
    setIsRect: function(scope) {
        var self = this;
        scope.isRect = function() {
            return self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.RECT;
        }
    },
    setIsArrow: function(scope) {
        var self = this;
        scope.isArrow = function() {
            return self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.ARROW;
        }
    },
    setIsLine: function(scope) {
        var self = this;
        scope.isLine = function() {
            return self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.LINE;
        }
    },
    setIsAnnotate: function(scope) {
        var self = this;
        scope.isAnnotate = function() {
            return self.imageViewer.edit.mode == UNIVERSALVIEWER.Common.ImageViewer.mode.ANNOTATE;
        }
    },
    setIsSolid: function(scope) {
        var self = this;
        scope.isSolid = function() {
            return self.imageViewer.edit.lineStyle == UNIVERSALVIEWER.Common.ImageViewer.lineStyle.SOLID;
        }
    },
    setIsDotted: function(scope) {
        var self = this;
        scope.isDotted = function() {
            return self.imageViewer.edit.lineStyle == UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED;
        }
    },
    setIsBold: function(scope) {
        var self = this;
        scope.isBold = function() {
            return self.imageViewer.edit.strokeWidth == UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.BOLD;
        }
    },
    setIsNormal: function(scope) {
        var self = this;
        scope.isNormal = function() {
            return self.imageViewer.edit.strokeWidth == UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NORMAL;
        }
    },
    setIsNarrow: function(scope) {
        var self = this;
        scope.isNarrow = function() {
            return self.imageViewer.edit.strokeWidth == UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NARROW;
        }
    },


    // ####################################################
    // 内部処理系
    // ####################################################

    /**
     * カルーセルを初期化する
     *
     * @param $timeout
     * @param selector
     */
    initCarousel: function($timeout, selector)
    {
        var self = this;
        var idx = 0;
        switch (self.sharedStore.returnView) {
            case 'home.binderPage':
            case 'home.timeline':
                self.selectedPages = self.sharedStore.binderPages;
                angular.forEach(self.selectedPages, function(page, i) {
                    if (page.id == self.tmpBinderPage.id) {
                        idx = (i > 0) ? i - 1: 0;
                    }
                });
                break;
            default:
                self.selectedPages = self.sharedStore.binder.binderPages;
                angular.forEach(self.selectedPages, function(page, i) {
                    if (page.originalPageId == self.tmpBinderPage.originalPageId) {
                        idx = (i > 0) ? i - 1: 0;
                    }
                });
                break;
        }
        $timeout(function () {
            angular.element(selector)
                .owlCarousel({
                    items: UNIVERSALVIEWER.Service.Viewer.BinderPageDetailViewClass.carouselImageNum,
                    responsive: true,
                    lazyLoad: true,
                    navigation: true
                });
            angular.element(selector).data('owlCarousel').jumpTo(idx);
        }, 1000);
    },

    /**
     * カルーセルの再設定・再描画を行う
     *
     * @param $timeout
     * @param selector
     */
    resetCarousel: function($timeout, selector)
    {
        var self = this;
        angular.element(selector).data('owlCarousel').destroy();
        var idx = 0;
        switch (self.sharedStore.returnView) {
            case 'home.binderPage':
            case 'home.timeline':
                angular.forEach(self.selectedPages, function(page, i) {
                    if (page.id == self.tmpBinderPage.id) {
                        idx = (i > 0) ? i - 1: 0;
                    }
                });
                break;
            default:
                angular.forEach(self.selectedPages, function(page, i) {
                    if (page.originalPageId == self.tmpBinderPage.originalPageId) {
                        idx = (i > 0) ? i - 1: 0;
                    }
                });
                break;
        }
        $timeout(function () {
            angular.element(selector)
                .owlCarousel({
                    items: UNIVERSALVIEWER.Service.Viewer.BinderPageDetailViewClass.carouselImageNum,
                    responsive: true,
                    lazyLoad: true,
                    navigation: true
                });
            angular.element(selector).data('owlCarousel').jumpTo(idx);
        }, 1000);
    },

    // ####################################################
    // テンプレートで使う関数
    // ####################################################

    /**
     * カルーセルにページ番号を出すかどうか
     */
    isDisplayPageNo: function() {
        var self = this;
        return (!(self.sharedStore.returnView == 'home.binderPage' || self.sharedStore.returnView == 'home.timeline'));
    },

    /**
     * 画面で表示されているカルーセルが現在編集しているものかどうか
     *
     * @param page
     * @returns {boolean}
     */
    isCurrentCarousel: function(page) {
        var self = this;
        var isCurrent = false;
        switch (self.sharedStore.returnView) {
            case 'home.binderPage':
            case 'home.timeline':
                if (page.id == self.tmpBinderPage.id) {
                    isCurrent = true;
                }
                break;
            default:
                if (page.originalPageId == self.tmpBinderPage.originalPageId) {
                    isCurrent = true;
                }
                break;
        }
        return isCurrent;
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; },
    get imageViewer() { return this._imageViewer; },
    set imageViewer(imageViewer) { this._imageViewer = imageViewer; },
    get srcBinderPage() { return this._srcBinderPage; },
    set srcBinderPage(srcBinderPage) { this._srcBinderPage = srcBinderPage; },
    get tmpBinderPage() { return this._tmpBinderPage; },
    set tmpBinderPage(tmpBinderPage) { this._tmpBinderPage = tmpBinderPage; },
    get originalPage() { return this._originalPage; },
    set originalPage(originalPage) { this._originalPage = originalPage; },
    get selectedPages() { return this._selectedPages; },
    set selectedPages(selectedPages) { this._selectedPages = selectedPages; },
    get originalPageMeta() { return this._originalPageMeta; },
    set originalPageMeta(originalPageMeta) { this._originalPageMeta = originalPageMeta; },
    get binderPageMeta() { return this._binderPageMeta; },
    set binderPageMeta(binderPageMeta) { this._binderPageMeta = binderPageMeta; },
    get isOpenColorPicker() { return this._isOpenColorPicker },
    set isOpenColorPicker(isOpenColorPicker) { this._isOpenColorPicker = isOpenColorPicker; }


};
