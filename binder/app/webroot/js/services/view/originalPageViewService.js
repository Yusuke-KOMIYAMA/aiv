/**
 * Created on 2015/01/21.
 */
var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.OriginalPageViewClass = function(sharedStoreService, searchService) {

    this._sharedStore = sharedStoreService;
    this._search = searchService;
    this._originalPageLayout = 'middle';
    this._binderPageLayout = 'middle';
    this._dirty = false;
    this._dstIndex = 1;

};

UNIVERSALVIEWER.Service.Viewer.OriginalPageViewClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    initialize: function(binder)
    {
        var self = this;

        /**
         * データ初期化
         */
        self.sharedStore.binder = binder;

        // オリジナルページ Overlay を fade in する
        angular.element("#originalPagesArea").on("mouseenter", ".pageImagePanel", function(event) {
            angular.element(this).children(".overlay").fadeIn();
        });

        // オリジナルページ Overlay を fade out する
        angular.element("#originalPagesArea").on("mouseleave", ".pageImagePanel", function(event) {
            angular.element(this).children(".overlay").fadeOut();
        });

        // バインダーページ Overlay を fade in する
        angular.element("#binderPagesArea").on("mouseenter", ".pageImagePanel", function(event) {
            angular.element(this).children(".overlay").fadeIn();
        });

        // バインダーページ Overlay を fade out する
        angular.element("#binderPagesArea").on("mouseleave", ".pageImagePanel", function(event) {
            angular.element(this).children(".overlay").fadeOut();
        });

        self.dirty = false;
    },

    // ####################################################
    // イベント処理関連
    // ####################################################

    // ----------------------------------------------------
    //  オリジナルページ関連
    // ----------------------------------------------------

    /**
     * オリジナルページをクリックしたときのイベント設定
     * @param parent
     * @param selector
     */
    setSelectOriginalPage: function(parent, selector)
    {
        var self = this;

        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event)
        {
            self.dirty = true;
            var id = angular.element(event.target).parents(".pagePanel").data("original-page-id");
            self._selectOriginalPage(id);
        });
    },

    /**
     * すべてのオリジナルページを選択するボタンをクリックしたときのイベント設定
     * @param selector
     */
    setOriginalPages: function(selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function(event) {
            self.dirty = true;
            self._selectOriginalPages();
        });
    },

    /**
     * オリジナルページ編集ボタンを押した時のイベントを設定する
     *
     * @param $state
     * @param parent
     * @param selector
     */
    setClickEditButton: function($state, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event) {
            event.stopPropagation();

            var originalPageId = angular.element(event.target).parents(".pagePanel").data("original-page-id");
            var originalPage = null;
            angular.forEach(self.sharedStore.originalPages, function(page, i) {
                if (page.id == originalPageId) {
                    originalPage = page;
                }
            });

            self.sharedStore.originalPage = originalPage;

            $state.go('home.originalPage.detail');
        });
    },

    /**
     * オリジナルページを削除する
     *
     * @param parent
     * @param selector
     */
    setUnsetOriginalPage: function(parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event) {
            event.stopPropagation();
            var originalPageId = angular.element(event.target).parents(".pagePanel").data("original-page-id");
            var originalPage = null;
            var index = -1;
            angular.forEach(self.sharedStore.originalPages, function(page, i) {
                if (page.id == originalPageId) {
                    originalPage = page;
                    index = i;
                    return;
                }
            });
            if (window.confirm(self.sharedStore.deleteOriginalPageMessage)) {
                originalPageResource = self.search.originalPageResource.deleteOriginalPage(originalPage);
                originalPageResource.$promise.then(
                    function() {
                        // 削除成功
                        self.search.originalPageResource.loading.complete();
                        // クライアント側のリストからも削除する
                        self.sharedStore.originalPages.splice(index, 1);
                    },
                    function(error) {
                        // 削除失敗
                        self.search.originalPageResource.loading.complete();
                        // 401エラーの場合はログイン画面へ
                        if (error.status == 401) {
                            $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                        }
                    }
                );
            }
        });
    },

    // ----------------------------------------------------
    //  バインダーページ関連
    // ----------------------------------------------------

    /**
     * 選択したバインダーページを削除する
     * @param parent
     * @param selector
     */
    setUnsetBinderPage: function(parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event) {
            self.dirty = true;
            var originalPageId = angular.element(event.target).parents(".pagePanel").data("original-page-id");
            self._unselectBinderPage(originalPageId);
        });
    },

    /**
     * すべてのバインダーページを削除する
     * @param selector
     */
    setUnsetBinderPages: function(selector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function(event) {
            self.dirty = true;
            self._unselectBinderPages();
        });
    },

    /**
     * バインダーページ編集ボタンを押した時のイベントを設定する
     *
     * @param $state
     * @param parent
     * @param selector
     */
    setClickBinderPageEditButton: function($state, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event)
        {
            event.stopPropagation();

            // バインダーが保存されていない場合
            if (self.sharedStore.binder.id == 0) {
                alert(self.sharedStore.notSaveBinderMessage);
                return;
            }
            var originalPageId = angular.element(event.target).parents(".pagePanel").data("original-page-id");
            var binderPage = null;
            angular.forEach(self.sharedStore.binder.binderPages, function(page, i) {
                if (page.originalPageId == originalPageId) {
                    binderPage = page;
                }
            });
            self.sharedStore.binderPage = binderPage;
            self.sharedStore.setReturnView("home.originalPage");
            $state.go('home.binderPage.detail');
        });
    },

    /**
     * 入力した番号へ選択したバインダーページを移動するイベントの設定
     * @param selector
     */
    setMoveBinderPage: function(selector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function (event) {
            self.dirty = true;
            self._moveBinderPage(self.dstIndex - 1);
        });
    },

    /**
     * 入力した番号へ選択したバインダーページを移動するイベントの設定
     * @param selector
     */
    setMoveBinderPageReturn: function(selector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "keyup", function (event) {
            if (event.keyCode == 13) {
                self.dirty = true;
                self._moveBinderPage(self.dstIndex - 1);
            }
        });
    },

    // ----------------------------------------------------
    //  バインダー関連
    // ----------------------------------------------------

    /**
     * バインダー情報編集のモーダルを表示するイベントの設定
     *
     * @param $timeout
     * @param $q
     * @param $modal
     * @param size
     * @param selector
     */
    setEditBinderModal: function($timeout, $q, $modal, size, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function(event) {
            var modalInstance = $modal.open({
                templateUrl: 'editBinderModal.html',
                size: size,
                resolve: {
                    binder: function () {
                        return self.sharedStore.binder;
                    }
                },
                controller: function ($scope, $modalInstance, binder) {

                    // タグの生成
                    $scope.loadTags = function(query) {
                        var deferred = $q.defer();
                        deferred.resolve(self.sharedStore.allTags);
                        return deferred.promise;
                    };

                    // 編集用バインダーオブジェクトの生成
                    $scope.binder = binder.clone();

                    $scope.interval = 0;

                    // カバー画像の選択
                    $scope.coverId = 1;
                    var slides = $scope.slides = [];
                    angular.forEach($scope.binder.binderPages, function(binderPage, i) {
                        var active = false;
                        if (binderPage.imageId == $scope.binder.coverId) {
                            active = true;
                            $scope.coverId = i;
                        }
                        slides.push(
                            {
                                id: i,
                                active: active,
                                text: 'Page '+binderPage.pageNo,
                                type: "chart",
                                url: binderPage.thumbUrl
                            }
                        );
                    });

                    // カバー画像Carouselの監視
                    $scope.$watch(
                        function () {
                            return slides.filter(function (s) { return s.active; })[0];
                        },
                        function(newVal, oldVal) {
                            if (newVal != undefined) {
                                $scope.coverId = newVal.id;
                            }
                        }
                    );
                    // カバー画像inputの監視
                    $scope.$watch(
                        function () {
                            return $scope.coverId;
                        },
                        function(newVal, oldVal) {
                            if (newVal != undefined) {
                                $scope.slides[newVal].active = true;
                            }
                        }
                    );

                    // 保存する場合の処理
                    $scope.ok = function () {
                        // カバーの保存
                        angular.forEach($scope.binder.binderPages, function(binderPage, i) {
                            if ($scope.coverId == i) {
                                $scope.binder.coverId = binderPage.imageId;
                            }
                        });

                        // 編集用バインダーを渡す
                        $modalInstance.close($scope.binder);
                    };
                    // キャンセルする場合の処理
                    $scope.cancel = function () {
                        $modalInstance.dismiss('cancel');
                    };

                }
            });
            modalInstance.result.then(function (binder) {
                self.sharedStore.binder = binder;
                self.dirty = true;
            }, function () {
                console.log('Modal dismissed at: ' + new Date());
            });
        });

    },

    /**
     * バインダー保存ボタンを設定する
     * @param selector
     */
    setSaveBinderButton: function($window, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function (event) {

            // 保存する直前に、カバーがなかったら先頭のBinderPage.imageIdを保存しておく
            if (self.sharedStore.binder.coverId == 0) {
                angular.forEach(self.sharedStore.binder.binderPages, function(page, i) {
                    if (i == 0) {
                        self.sharedStore.binder.coverId = page.imageId;
                    }
                });
            }
            // 保存する前に、設定したカバーが削除されているか確認する
            else {
                // 削除されているかチェックする
                var delFlag = true;
                angular.forEach(self.sharedStore.binder.binderPages, function(page, i) {
                    if (self.sharedStore.binder.coverId == page.imageId) {
                        delFlag = false;
                    }
                });
                // 削除されていたら、0 もしくは先頭のbinderPage.imageIdを設定する
                if (delFlag) {
                    self.sharedStore.binder.coverId = 0;
                    angular.forEach(self.sharedStore.binder.binderPages, function(page, i) {
                        if (i == 0) {
                            self.sharedStore.binder.coverId = page.imageId;
                        }
                    });
                }
            }

            var binderResource = self.search.binderResource.saveBinder(self.sharedStore.binder);
            binderResource.$promise.then(
                function(data) {
                    // バインダー保存成功
                    self.dirty = false;
                    self.search.binderResource.loading.complete();
                    var binder = new UNIVERSALVIEWER.Class.BinderClass();
                    binder.initFromDb(data.results.Binder[0]);
                    self.sharedStore.binder = binder;
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
        });
    },

    // ----------------------------------------------------
    //  共通関連
    // ----------------------------------------------------

    /**
     * リストスタイルを「小」へ変更するイベントの設定
     * @param selector
     * @param dstSelector
     */
    setSmall: function(selector, dstSelector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function(event) {
            angular.element(dstSelector).removeClass("middle large");
            angular.element(dstSelector).addClass("small");
        });
    },

    /**
     * リストスタイルを「中」へ変更するイベントの設定
     * @param selector
     * @param dstSelector
     */
    setMiddle: function(selector, dstSelector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function(event) {
            angular.element(dstSelector).removeClass("small large");
            angular.element(dstSelector).addClass("middle");
        });
    },

    /**
     * リストスタイルを「大」へ変更するイベントの設定
     * @param selector
     * @param dstSelector
     */
    setLarge: function(selector, dstSelector) {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoad(selector, "click", function(event) {
            angular.element(dstSelector).removeClass("small middle");
            angular.element(dstSelector).addClass("large");
        });
    },

    // ####################################################
    // 内部処理関連
    // ####################################################

    /**
     * 選択したオリジナルページをバインダーにセットする
     *
     * @param id
     * @private
     */
    _selectOriginalPage: function(id)
    {
        var self = this;
        var originalPage = null;

        // 選択したオリジナルページを取得
        angular.forEach(self.sharedStore.originalPages, function(page, i) {
            if (page.id == id) {
                originalPage = page;
            }
        });

        // オリジナルページを選択状態にする
        originalPage.isSelect = true;

        // バインダーページに同じ originalPageId がなかったら追加する
        var flag = true;
        var maxPageNo = 1;
        angular.forEach(self.sharedStore.binder.binderPages, function(page, i) {
            if (originalPage.id == page.originalPageId) {
                flag = false;
            }
            if (maxPageNo > page.pageNo) {
                maxPageNo = page.pageNo;
            }
        });

        // オリジナルページをコピーしたバインダーページをバインダーにセット
        if (flag == true) {
            var binderPage = new UNIVERSALVIEWER.Class.BinderPageClass();
            binderPage.initialize(
                0,
                originalPage.userId,
                self.sharedStore.binder.id,
                originalPage.id,
                maxPageNo + 1,
                originalPage.title,
                originalPage.text,
                originalPage.creator,
                originalPage.confirmor,
                originalPage.creationDate,
                originalPage.tags,
                originalPage.url,
                originalPage.thumbUrl,
                originalPage.deepZoomImage,
                originalPage.imageId,
                originalPage.imageRotate,
                originalPage.extension,
                originalPage.fileSize,
                originalPage.width,
                originalPage.height,
                []
            );
            self.sharedStore.binder.binderPages.push(binderPage);
        }
    },

    /**
     * 検索結果の全てのオリジナルページをバインダーにセットする
     *
     * @private
     */
    _selectOriginalPages: function()
    {
        var self = this;
        angular.forEach(self.sharedStore.originalPages, function(page, i) {
            self._selectOriginalPage(page.id);
        });
    },

    /**
     * 選択したバインダーページを削除する
     * @param originalPageId
     * @private
     */
    _unselectBinderPage: function(originalPageId)
    {
        var self = this;
        angular.forEach(self.sharedStore.binder.binderPages, function(page, i) {
            if (page.originalPageId == originalPageId) {
                self.sharedStore.binder.binderPages.splice(i, 1);
            }
        });
        angular.forEach(self.sharedStore.originalPages, function(page, i) {
            if (page.id == originalPageId) {
                page.isSelect = false;
            }
        });
    },

    /**
     * 全てのバインダーページを削除する
     * @private
     */
    _unselectBinderPages: function()
    {
        var self = this;
        self.sharedStore.binder.binderPages = [];
        angular.forEach(self.sharedStore.originalPages, function(page, i) {
            if (page.isSelect == true) {
                page.isSelect = false;
            }
        })
    },

    /**
     * 入力した番号へ選択したバインダーページを移動する
     * @param dstIndex
     */
    _moveBinderPage: function(dstIndex)
    {
        var self = this;
        var start = -1, end = -1;
        angular.element("#binderPagesArea").children().each(function(i) {
            var $s = angular.element(this);
            if ($s.hasClass("ui-sortable-selected")) {
                if (start < 0) {
                    start = i;
                }
                end = i + 1;
            }
        });
        // 選択されたバインダーページを取得
        var selectedElements = self.sharedStore.binder.binderPages.slice(start, end);
        // 選択されたバインダーページをリストから削除
        self.sharedStore.binder.binderPages.splice(start, end - start);
        // 選択された分を削除したリストの指定番号の位置へ選択分を挿入
        if (self.sharedStore.binder.binderPages.length < dstIndex) {
            self.sharedStore.binder.binderPages = self.sharedStore.binder.binderPages.concat(selectedElements);
        }
        else {

            self.sharedStore.binder.binderPages = self.sharedStore.binder.binderPages.slice(0, dstIndex)
                .concat(
                    selectedElements.concat(
                        self.sharedStore.binder.binderPages.slice(dstIndex)
                    )
                );
        }
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; },
    get originalPageLayout() { return this._originalPageLayout; },
    set originalPageLayout(originalPageLayout) { this._originalPageLayout = originalPageLayout; },
    get binderPageLayout() { return this._binderPageLayout; },
    set binderPageLayout(binderPageLayout) { this._binderPageLayout = binderPageLayout; },
    get dstIndex() { return this._dstIndex; },
    set dstIndex(dstIndex) { this._dstIndex = dstIndex; },
    get opTags() { return this._opTags; },
    set opTags(opTags) { this._opTags = opTags; },
    get bpTags() { return this._bpTags; },
    set bpTags(bpTags) { this._bpTags = bpTags; },
    get dirty() { return this._dirty; },
    set dirty(dirty) { this._dirty = dirty; }

};

