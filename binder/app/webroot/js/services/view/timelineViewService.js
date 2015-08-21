/**
 * Created on 2015/03/24.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Viewer = UNIVERSALVIEWER.Service.Viewer || {};

UNIVERSALVIEWER.Service.Viewer.TimelineViewClass = function(sharedStoreService, searchService) {

    this._sharedStore = sharedStoreService;
    this._search = searchService;

};
UNIVERSALVIEWER.Service.Viewer.TimelineViewClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * 初期化
     */
    initialize: function()
    {
        var self = this;

        // TimelineJS 設定
        var timeline = createStoryJS({
            type: "timeline",
            width: "100%",
            height: "100%",
            source: self.sharedStore.timelines,
            start_at_slide: self.sharedStore.timelineStartSlide,
            embed_id: "timeline-embed"
        });
    },

    // ####################################################
    // イベント関連
    // ####################################################

    setDetailLink: function($state, parent, selector)
    {
        var self = this;
        UNIVERSALVIEWER.Common.Utils.onLoadWithChild(parent, selector, "click", function(event) {
            var binderPageId = angular.element(event.target).data("binder-page-id");
            var binderPage = null;
            angular.forEach(self.sharedStore.binderPages, function(page, i) {
                if (page.id == binderPageId) {
                    binderPage = page;
                }
            });
            self.sharedStore.binderPage = binderPage;
            self.sharedStore.setReturnView("home.timeline");
            $state.go('home.binderPage.detail');
        });

    },

    // ####################################################
    // getter/setter
    // ####################################################

    get sharedStore() { return this._sharedStore; },
    get search() { return this._search; }

};
