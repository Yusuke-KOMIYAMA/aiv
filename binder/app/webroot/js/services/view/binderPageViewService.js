/**
 * Created on 2015/01/21.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.BinderPageViewClass = function(sharedStoreService, searchService) {

    this._sharedStore = sharedStoreService;
    this._search = searchService;
};

UNIVERSALVIEWER.Service.Viewer.BinderPageViewClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    initialize: function()
    {
        // Overlay を fade in する
        angular.element("#binderPagesArea").on("mouseenter", ".pageImagePanel", function(event) {
            angular.element(this).children(".overlay").fadeIn();
        });

        // Overlay を fade out する
        angular.element("#binderPagesArea").on("mouseleave", ".pageImagePanel", function(event) {
            angular.element(this).children(".overlay").fadeOut();
        });
    },

    // ####################################################
    // イベント関連
    // ####################################################

    /**
     * バインダーページ編集ボタンを押した時のイベントを設定する
     *
     * @param $state
     * @param parent
     * @param selector
     */
    setClickEditButton: function($state, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event)
        {
            var binderPageIndex = angular.element(event.target).parents(".pagePanel").data("index");
            var binderPage = self.sharedStore.binderPages[binderPageIndex];
            self.sharedStore.binderPage = binderPage;
            self.sharedStore.setReturnView("home.binderPage");
            $state.go('home.binderPage.detail');
        });
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; }

};
