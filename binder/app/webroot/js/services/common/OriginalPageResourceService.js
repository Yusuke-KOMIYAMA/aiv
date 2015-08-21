/**
 * OriginalPageResourceServiceClass
 *
 * 「オリジナルページ」RESTful API 対応クラス
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};


// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Service.Common.OriginalPageResourceServiceClass = function(resource, cfpLoadingBar) {

    this._resource = resource(UNIVERSALVIEWER.Config.rootUrl + "api/originalPage/:id.json", {id: '@id'}, {
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
};

UNIVERSALVIEWER.Service.Common.OriginalPageResourceServiceClass.prototype = {

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * データベースから条件を指定してオリジナルページを検索
     *
     * @param text
     * @param isTitle
     * @param isText
     * @param isTag
     * @param binder
     */
    searchOriginalPages: function(text, isTitle, isText, isTag, binder)
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
        // オリジナルページ一覧をサーバーから取得する
        self.loading.start();
        return self.resource.query(params);
    },

    /**
     * タグでオリジナルページを取得する
     *
     * @param tag
     * @returns {*}
     */
    searchOriginalPagesByTag: function(tag)
    {
        var self = this;
        // 検索条件の生成
        var params = {};
        if (tag.trim()) {
            params["tag"] = tag;
        }
        // オリジナルページ一覧をサーバーから取得する
        self.loading.start();
        return self.resource.query(params);
    },

    /**
     * DBからオリジナルページIDを指定してデータを取得する
     *
     * @param id
     * @returns {*}
     */
    searchOriginalPage: function(id)
    {
        var self = this;
        self.loading.start();
        return self.resource.get({id:id});
    },

    /**
     * DBからオリジナルページIDを指定してデータを取得する。削除したオリジナルページも対象とする
     *
     * @param id
     * @returns {*}
     */
    searchOriginalPageIncludeDeleted: function(id)
    {
        var self = this;
        self.loading.start();
        return self.resource.query({id:id});
    },

    /**
     * データベースへ指定したIDに対応するオリジナルページを保存する
     *
     * @param originalPage
     */
    saveOriginalPage: function(originalPage)
    {
        var self = this;
        self.loading.start();
        return self.resource.save({
            OriginalPages: [
                originalPage.createJson()
            ]
        });
    },

    /**
     * オリジナルページを削除する
     *
     * @param originalPage
     * @returns {*|T|IHttpPromise<T>}
     */
    deleteOriginalPage: function(originalPage)
    {
        var self = this;
        self.loading.start();
        return this.resource.delete({
            id:originalPage.id
        });
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    /**
     * クライアントに保持されているオリジナルページリストからIDを指定してオリジナルページを取得する
     *
     * @param id
     * @returns {*} null: なし, OriginalPageオブジェクト
     */
    getOriginalPage: function(id)
    {
        var result = null;

        angular.forEach(this.originalPages, function(page) {
            if (page.id == id) {
                result = page;
            }
        });
        return result;
    },

    /**
     * クライアントに保持されているオリジナルページリストからオリジナルページを取得する
     *
     * @param index
     * @returns {*} null: なし, OriginalPageオブジェクト
     */
    getOriginalPageByIndex: function(index)
    {
        if (this.originalPages.length <= index) {
            return null;
        }
        return this.originalPages[index];
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get resource() { return this._resource; },
    get originalPages() { return this._originalPages; },
    set originalPages(originalPages) { this._originalPages = originalPages; },
    get loading() { return this._loading; }

};

