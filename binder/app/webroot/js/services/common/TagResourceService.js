/**
 * Created on 2015/02/25.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};

// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Service.Common.TagResourceServiceClass = function($resource, cfpLoadingBar) {

    this._resource = $resource(UNIVERSALVIEWER.Config.rootUrl + "api/tag/:id.json", {id: '@id'}, {
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
UNIVERSALVIEWER.Service.Common.TagResourceServiceClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * 「バインダーページ」に紐づくタグを取得する
     */
    searchTags: function ()
    {
        var self = this;
        var params = {};
        return self.resource.query(params);
    },

    get resource() { return this._resource; },
    get loading() { return this._loading; }
};

