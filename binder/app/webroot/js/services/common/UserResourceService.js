'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};


// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Service.Common.UserResourceServiceClass = function(resource, cfpLoadingBar) {
    this._resource = resource(UNIVERSALVIEWER.Config.rootUrl + "api/user/:id.json", {id: '@id'}, {
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

UNIVERSALVIEWER.Service.Common.UserResourceServiceClass.prototype = {

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * データベースからユーザー一覧を取得する
     *
     * @returns {*|T|IHttpPromise<T>}
     */
    searchUsers: function ()
    {
        var self = this;
        self.loading.start();
        return self.resource.get({});
    },

    // ####################################################
    //  getter/setter
    // ####################################################

    get resource() { return this._resource; },
    get loading() { return this._loading; }

};
