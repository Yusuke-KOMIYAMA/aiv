/**
 * BinderPageResourceServiceClass
 *
 * 「バインダーページ」RESTful API 対応クラス
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};


// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Service.Common.BinderPageResourceServiceClass = function($resource, cfpLoadingBar) {

    this._resource = $resource(UNIVERSALVIEWER.Config.rootUrl + "api/binderPage/:id.json", {id: '@id'}, {
        get: {
            method: "GET",
            withCredentials: true
        },
        query: {
            method: "PUT",
            withCredentials: true
        },
        save: {
            method: "POST",
            withCredentials: true
        },
        delete: {
            method: "DELETE",
            withCredentials: true
        }
    });
    this._loading = cfpLoadingBar;
    this._binderId = -1;
    this._binderPages = [];
};
UNIVERSALVIEWER.Service.Common.BinderPageResourceServiceClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * バインダーページリストをリセットする
     */
    reset: function () {
        var self = this;
        self.binderId = -1;
        self.binderPages = [];
    },

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * タイムライン用バインダーページリストを取得する
     *
     * @returns {*}
     */
    searchBinderPagesForTimeline: function() {
        var self = this;
        self.loading.start();
        return self.resource.query({
            "timeline": "true"
        });
    },

    /**
     * 条件を指定して「バインダーページを取得する」
     *
     * @param text
     * @param isTitle
     * @param isText
     * @param isTag
     * @param isAnnotation
     */
    searchBinderPages: function (text, isTitle, isText, isTag, isAnnotation)
    {
        var self = this;

        // 検索条件の生成
        var params = {};
        if (text.trim()) {
            params["text"] = text;
        }
        if (isTitle) {
            params["isTitle"] = isTitle;
        }
        if (isText) {
            params["isText"] = isText;
        }
        if (isTag) {
            params["isTag"] = isTag;
        }
        if (isAnnotation) {
            params["isAnnotation"] = isAnnotation;
        }
        self.loading.start();
        return self.resource.query(params);
    },

    /**
     * バインダーをタグで検索する
     *
     * @param tag
     * @returns {*}
     */
    searchBinderPagesByTag: function(tag)
    {
        var self = this;

        // 検索条件の生成
        var params = {};
        if (tag.trim()) {
            params["tag"] = tag;
        }
        self.loading.start();
        return self.resource.query(params);
    },

    saveBinderPage: function(binderPage)
    {
        var self = this;
        self.loading.start();
        return this.resource.save({
            BinderPages: [
                binderPage.createJson()
            ]
        });
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    /**
     * クライアントに保持されているバインダーページリストからIDを指定してオリジナルページを取得する
     *
     * @param id
     * @returns {*} null: なし, BinderPageオブジェクト
     */
    getBinderPage: function (id) {
        var result = null;

        angular.forEach(this.binderPages, function (page) {
            if (page.id == id) {
                result = page;
                return;
            }
        });
        return result;
    },

    /**
     *  Indexによるバインダーページの取得
     *
     * @param index
     * @returns {*}, null: なし, BinderPageオブジェクト
     */
    getBinderPageByIndex: function (index) {
        var self = this;
        if (self.binderPages.length <= index) {
            return null;
        }
        return self.binderPages[index];
    },

    /**
     * Indexを指定してバインダーページを削除する
     *
     * @param index
     * @return boolean true: 成功, false: 失敗
     */
    removeBinderPageByIndex: function (index) {
        var self = this;
        if (self.binderPages.length <= index) {
            return false;
        }
        self.binderPages.splice(index, 1);
        return true;
    },

    /**
     * BinderPageList内のBinderPageに該当IDあるかどうか
     * @param id
     * @returns {boolean}
     */
    pagesIdExists: function (id) {
        var self = this;
        var flag = false;
        angular.forEach(self.binderPages, function (record) {
            if (record.id == id) {
                flag = true;
                return;
            }
        });
        return flag;
    },

    /**
     * BinderPageList内のBinderPageに該当OriginalPageIDがあるかどうか
     *
     * @param originalPageId
     * @returns {boolean}
     */
    pagesOriginalPageIdExists: function (originalPageId) {
        var self = this;
        var flag = false;
        angular.forEach(self.binderPages, function (record) {
            if (record.originalPageId == originalPageId) {
                flag = true;
                return;
            }
        });
        return flag;
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get resource() { return this._resource; },
    get binderId() { return this._binderId; },
    set binderId(binderId) { this._binderId = binderId; },
    get binderPages() { return this._binderPages; },
    set binderPages(binderPages) { this._binderPages = binderPages; },
    get loading() { return this._loading; }

};

