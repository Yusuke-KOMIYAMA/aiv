/**
 * BinderClass
 *
 * 「バインダー」を保持するクラス
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Class = UNIVERSALVIEWER.Class || {};

// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Class.BinderClass = function()
{
    this._id = 0;
    this._userId = 0;
    this._title = '';
    this._text = '';
    this._tags = [];
    this._category = 0;
    this._coverId = 0;
    this._url = '';
    this._thumbUrl = '';
    this._binderPages = [];
};
UNIVERSALVIEWER.Class.BinderClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * BinderClassの初期化
     *
     * @param id
     * @param userId
     * @param title
     * @param text
     * @param category
     * @param coverId
     * @param url
     * @param thumbUrl
     * @param tags
     * @param binderPages
     */
    initialize: function(id, userId, title, text, category, coverId, url, thumbUrl, tags, binderPages)
    {
        var self = this;

        self.id = id;
        self.userId = userId;
        self.title = title;
        self.text = text;
        self.category = category;
        self.coverId = coverId;
        self.url = url;
        self.thumbUrl = thumbUrl;
        self.tags = tags;
        self.binderPages = binderPages;
    },

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * サーバーにアクセスして取得したJSONから初期化
     *
     * @param record
     */
    initFromDb: function(record)
    {
        var self = this;
        var binderPages = [];

        // バインダーページの初期化
        // record.BinderPage:[] を渡す
        angular.forEach(record.BinderPage, function(data) {
            var binderPage = new UNIVERSALVIEWER.Class.BinderPageClass();
            binderPage.initFromDb(data);
            binderPages.push(binderPage);
        });

        // バインダーページのソート
        binderPages.sort(
            function(a, b){
                var ano = a["pageNo"];
                var bno = b["pageNo"];
                if( ano < bno ) return -1;
                if( ano > bno ) return 1;
                return 0;
            }
        );

        // バインダーを初期化
        self.initialize(
            record.id,
            record.userId,
            record.title,
            record.text,
            record.category,
            record.coverId,
            record.Cover && record.Cover.localFileName ? record.Cover.localFileName : '',
            record.Cover && record.Cover.localFileName ? record.Cover.localFileName : '',
            record.Tag ? record.Tag : [],
            binderPages
        );
    },

    /**
     * サーバーへ送信するJSONの生成
     */
    createJson: function()
    {
        var self = this;

        var binderPages = [];
        if (self.binderPages.length > 0) {
            angular.forEach(self.binderPages, function(page) {
                binderPages.push(page.createJson());
            });
        }

        var tags = [];
        if (self.tags.length > 0) {
            angular.forEach(self.tags, function(tag) {
                tags.push({Tag:{text:tag.text}});
            });
        }

        // オリジナルページ本体
        return {
            Binder:{
                id: self.id,
                userId: self.userId,
                title: self.title,
                text: self.text,
                category: self.category,
                coverId: self.coverId,
                BinderPages: binderPages,
                Tags: tags
            }
        };
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    clone: function()
    {
        var self = this;

        return angular.copy(self);
    },

    /**
     * Binder内のBinderPageに該当IDあるかどうか
     * @param id
     * @returns {boolean}
     */
    pagesIdExists: function(id)
    {
        var self = this;
        var flag = false;
        angular.forEach(self.binderPages, function(record) {
            if (record.id == id) {
                flag = true;
                return;
            }
        });
        return flag;
    },

    /**
     * Binder内のBinderPageに該当OriginalPageIDがあるかどうか
     * @param originalPageId
     * @returns {boolean}
     */
    pagesOriginalPageIdExists: function(originalPageId)
    {
        var self = this;
        var flag = false;
        angular.forEach(self.binderPages, function(record) {
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

    get id() { return this._id; },
    set id(id) { this._id = Number(id); },
    get userId() { return this._userId; },
    set userId(userId) { this._userId = Number(userId); },
    get title() { return this._title; },
    set title(title) { this._title = title; },
    get text() { return this._text; },
    set text(text) { this._text = text; },
    get category() { return this._category; },
    set category(category) { this._category = category; },
    get coverId() { return this._coverId; },
    set coverId(coverId) { this._coverId = coverId; },
    get url() { return UNIVERSALVIEWER.Config.imageDir + this._url; },
    set url(url) { this._url = url.replace(UNIVERSALVIEWER.Config.imageDir,""); },
    get thumbUrl() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.smallThumb + this._thumbUrl; },
    set thumbUrl(thumbUrl) { this._thumbUrl = thumbUrl.replace(UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.smallThumb,""); },
    get tags() { return this._tags; },
    set tags(tags) { this._tags = tags; },
    get binderPages() { return this._binderPages; },
    set binderPages(binderPages) { this._binderPages = binderPages; }

};


