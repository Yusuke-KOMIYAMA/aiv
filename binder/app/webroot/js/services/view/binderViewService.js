/**
 * File: binderViewService.js
 *
 * バインダーリスト
 */
var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.BinderViewClass = function(sharedStoreService, searchService)
{
    this._sharedStore = sharedStoreService;
    this._search = searchService;
};

UNIVERSALVIEWER.Service.Viewer.BinderViewClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * 画面データ初期化
     */
    initialize: function($window)
    {
        var self = this;

        // バインダーを読み込む
        var bindersResource = self.search.binderResource.searchBinders();
        // サーバーから読み込んだバインダーデータからオブジェクトを生成
        var binders = [];
        bindersResource.$promise.then(
            function(data) {
                // 読込成功
                self.search.binderResource.loading.complete();
                // バインダーを格納する
                angular.forEach(data.results.Binder, function (record) {
                    // バインダーの初期化
                    var binder = new UNIVERSALVIEWER.Class.BinderClass();
                    binder.initFromDb(record);
                    binders.push(binder);
                    self.search.binderResource.loading.complete();
                });
            },
            function(error) {
                // 読込失敗
                self.search.binderResource.loading.complete();
                // 401エラーの場合はログイン画面へ
                if (error.status == 401) {
                    $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                }
            }
        );

        // バインダーを画面クラスへセットする
        self.sharedStore.binders = binders;

    },

    /**
     * バインダーパネルのホバーイベント設定
     * @param parent
     * @param selector
     */
    setOverlay: function(parent, selector)
    {
        // Overlay を fade in する
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "mouseenter", function(event) {
            angular.element(this).children(".overlay").fadeIn();
        });
        // Overlay を fade out する
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "mouseleave", function(event) {
            angular.element(this).children(".overlay").fadeOut();
        });
    },

    // ####################################################
    // イベント関連
    // ####################################################

    /**
     * バインダー編集アイコンのクリックイベント
     *
     * @param $state
     * @param parent
     * @param selector
     */
    setBinderEdit: function($state, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, 'click', function(event) {
            // binderId を取得
            var id = angular.element(event.target).parents(".pagePanel").data("binder-id");
            // 選択したバインダーオブジェクトをセット
            angular.forEach(self.sharedStore.binders, function(record) {
                if (id == record.id) {
                    self.sharedStore.binder = record;
                }
            });
            // オリジナルページ検索結果へ移動する
            $state.go("home.originalPage");
        });
    },

    /**
     * バインダー削除ボタンのクリックイベント
     *
     * @param $modal
     * @param $window
     * @param parent
     * @param selector
     */
    setBinderDelete: function($modal, $window, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, 'click', function(event) {

            event.stopPropagation();
            event.preventDefault();

            var id = angular.element(event.target).parents(".pagePanel").data("binder-id");
            var index = -1;
            angular.forEach(self.sharedStore.binders, function(record, i) {
                if (id == record.id) {
                    index = i;
                }
            });

            // 削除確認モーダルダイアログを表示する
            var modalInstance = $modal.open({
                templateUrl: 'confirmDeleteModal.html',
                controller: function ($scope, $modalInstance) {
                    $scope.binder = self.sharedStore.binders[index];
                    $scope.ok = function () {
                        $modalInstance.close();
                    };
                    $scope.cancel = function () {
                        $modalInstance.dismiss('cancel');
                    };
                }
            });
            modalInstance.result.then(
                function () {
                    // 成功時はサーバーに問い合わせて、バインダーを削除する
                    var deleteBinderResource = self.search.binderResource.deleteBinder(self.sharedStore.binders[index]);
                    deleteBinderResource.$promise.then(
                        function() {
                            // 削除が成功したので、画面側のバインダーリストからも削除する
                            self.search.binderResource.loading.complete();
                            self.sharedStore.binders.splice(index, 1);
                        }, function(error) {
                            // 削除失敗
                            self.search.binderResource.loading.complete();
                            // 401エラーの場合はログイン画面へ
                            if (error.status == 401) {
console.log("401");
                                $window.location.href = UNIVERSALVIEWER.Config.loginUrl;
                            }
                        }
                    )
                },
                function () {
                }
            );
        });
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; }

};
