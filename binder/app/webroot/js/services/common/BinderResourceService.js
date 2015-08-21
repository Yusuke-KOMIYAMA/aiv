/**
 * BinderResourceServiceClass
 * Created on 2015/01/19.
 *
 * 「バインダー」RESTful API 対応クラス
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};


// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Service.Common.BinderResourceServiceClass = function(resource, cfpLoadingBar) {

    this._resource = resource(UNIVERSALVIEWER.Config.rootUrl + "api/binder/:id.json", {id: '@id'}, {
        get: {
            method: "GET",
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
UNIVERSALVIEWER.Service.Common.BinderResourceServiceClass.prototype = {

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * データベースからバインダー一覧を取得する
     *
     * @returns {*|T|IHttpPromise<T>}
     */
    searchBinders: function ()
    {
        var self = this;
        self.loading.start();
        return self.resource.get({});
    },

    /**
     * バインダーを保存する
     *
     * @param binder
     * @returns {*|T|IHttpPromise<T>}
     */
    saveBinder: function(binder)
    {
        var self = this;
        self.loading.start();
        return this.resource.save({
            Binders: [
                binder.createJson()
            ]
        });
    },

    /**
     * バインダーを削除する
     *
     * @param binder
     * @returns {*|T|IHttpPromise<T>}
     */
    deleteBinder: function(binder)
    {
        var self = this;
        self.loading.start();
        return this.resource.delete({
            id:binder.id
        });
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    /**
     * バインダーを取得する
     *
     * @param id
     * @returns {*}
     */
    getBinder: function (id)
    {
        var result = null;
        angular.forEach(this.binders, function(binder) {
            if (binder.id == id) {
                result = binder;
                return;
            }
        });
        return result;
    },


    get resource() { return this._resource; },
    get binders() { return this._binders; },
    set binders(binders) { this._binders = binders; },
    get loading() { return this._loading; }
};
