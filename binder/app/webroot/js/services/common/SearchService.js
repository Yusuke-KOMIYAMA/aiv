/**
 * Created on 2015/02/17.
 */
var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};

UNIVERSALVIEWER.Service.Common.SearchServiceClass = function(originalPageResourceService, binderResourceService, binderPageResourceService, tagResourceService, userResourceService) {

    // バインダー検索関連
    this._binderResource = binderResourceService;

    // バインダーページ検索関連
    this._binderPageResource = binderPageResourceService;

    // オリジナルページ検索関連
    this._originalPageResource = originalPageResourceService;

    // タグ検索関連
    this._tagResource = tagResourceService;

    this._userResource = userResourceService;

};
UNIVERSALVIEWER.Service.Common.SearchServiceClass.prototype = {

    // ####################################################
    // getter/setter
    // ####################################################

    get originalPageResource() { return this._originalPageResource; },
    get binderResource() { return this._binderResource; },
    get binderPageResource() { return this._binderPageResource; },
    get tagResource() { return this._tagResource; },
    get userResource() { return this._userResource; }

};
