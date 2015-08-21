/**
 * OriginalPageClass
 * 「オリジナルページ」クラス
 *
 * Created on 2015/01/05.
 *
 * @member id オリジナルページID
 * @member userId ユーザーID
 * @member originalPageHeaderId オリジナルページヘッダーID
 * @member creator 作成者
 * @member confirmor 確認者
 * @member creationDate 作成日
 * @member title タイトル
 * @member text 本文
 * @member orgTitle 初期登録時タイトル
 * @member orgText 初期登録時本文
 * @member orgCreator 初期登録時作成者
 * @member orgConfirmor 初期登録時確認者
 * @member orgCreationDate 初期登録時作成日
 * @member url 画像のURL
 * @member thumbUrl サムネイル画像のURL
 * @member imageId 画像ID
 * @member deepZoomImage 画像タイルデータ
 * @member imageRotate 画像回転
 * @member width 画像幅
 * @member height 画像高さ
 * @member tags タグ
 * @member informationSource 登録元ソース
 * @member isSelect 選択されているかどうか（バインダーページ生成時のオリジナルページ選択済フラグ）
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Class = UNIVERSALVIEWER.Class || {};

// ####################################################
// コンストラクタ
// ####################################################

UNIVERSALVIEWER.Class.OriginalPageClass = function()
{
    // メンバー変数
    this._id = 0;
    this._userId = "";
    this._originalPageHeaderId = "";
    this._pageNo = 0;
    this._title = "";
    this._text = "";
    this._creator = "";
    this._confirmor = "";
    this._creationDate = "";
    this._orgTitle = "";
    this._orgText = "";
    this._orgCreator = "";
    this._orgConfirmor = "";
    this._orgCreationDate = "";
    this._tags = [];
    this._url = "";
    this._thumbUrl = "";
    this._deepZoomImage = "";
    this._imageId = "";
    this._imageRotate = "";
    this._extension = "";
    this._fileSize = 0;
    this._width = 0;
    this._height = 0;
    this._informationSource = "";
    this._isSelect = false;
};
UNIVERSALVIEWER.Class.OriginalPageClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * 「OriginalPage」データの初期化
     *
     * @param id
     * @param userId
     * @param originalPageHeaderId
     * @param pageNo
     * @param title
     * @param text
     * @param creator
     * @param confirmor
     * @param creationDate
     * @param orgTitle
     * @param orgText
     * @param orgCreator
     * @param orgConfirmor
     * @param orgCreationDate
     * @param tags
     * @param url
     * @param thumbUrl
     * @param deepZoomImage
     * @param imageId
     * @param imageRotate
     * @param extension
     * @param fileSize
     * @param width
     * @param height
     * @param informationSource
     * @param isSelect
     */
    initialize: function(id, userId, originalPageHeaderId, pageNo, title, text, creator, confirmor, creationDate, orgTitle, orgText, orgCreator, orgConfirmor, orgCreationDate, tags, url, thumbUrl, deepZoomImage, imageId, imageRotate, extension, fileSize, width, height, informationSource, isSelect)
    {
        var self = this;

        // 「オリジナルページ」のメンバー変数をセット
        self.id = id;
        self.userId = userId;
        self.creator = creator;
        self.confirmor = confirmor;
        self.creationDate = creationDate;
        self.pageNo = pageNo;
        self.title = title;
        self.text = text;
        self.originalPageHeaderId = originalPageHeaderId;
        self.orgTitle = orgTitle;
        self.orgText = orgText;
        self.orgCreator = orgCreator;
        self.orgConfirmor = orgConfirmor;
        self.orgCreationDate = orgCreationDate;
        self.url = url;
        self.thumbUrl = thumbUrl;
        self.deepZoomImage = deepZoomImage;
        self.imageId = imageId;
        self.imageRotate = imageRotate;
        self.extension = extension;
        self.fileSize = fileSize;
        self.width = width;
        self.height = height;
        self.tags = tags;
        self.informationSource = informationSource;
        self.isSelect = isSelect;
    },

    // ####################################################
    // データベース関連
    // ####################################################

    /**
     * サーバーにアクセスして取得したJSONから初期化
     *
     * @param record
     * @param isSelect
     */
    initFromDb: function(record, isSelect)
    {
        var self = this;

        // 「タグ」の処理
        var tags = record.Tag;

        // 初期化準備
        var reg=/(.*)(?:\.([^.]+$))/;
        var extAry = record.Image.localFileName.match(reg);
        var ext = '';
        if (extAry && extAry.length >= 2) {
            ext = extAry[2];
        }

        // 「OriginalPage」の初期化
        self.initialize(
            record.id,
            record.userId,
            record.originalPageHeaderId,
            record.pageNo,
            record.title,
            record.text,
            record.creator,
            record.confirmor,
            record.creationDate,
            record.orgTitle,
            record.orgText,
            record.orgCreator,
            record.orgConfirmor,
            record.orgCreationDate,
            tags,
            record.Image.localFileName,
            record.Image.localFileName,
            record.Image.deepZoomImage,
            record.imageId,
            record.imageRotate,
            ext,
            record.Image.fileSize,
            record.Image.sizeX,
            record.Image.sizeY,
            record.informationSource,
            isSelect
        );
    },

    initFromResource: function(data)
    {
        var self = this;
        data.$promise.then(function(record) {
            self.initFromDb(record.results.OriginalPage[0], false);
        }, function(err) {
        });
    },

    /**
     * サーバーへ送信するJSON対応のオブジェクトを生成する
     */
    createJson: function()
    {
        var self = this;

        var tags = [];
        if (self.tags.length > 0) {
            angular.forEach(self.tags, function(tag) {
                tags.push({Tag:{text:tag.text}});
            });
        }

        // オリジナルページ本体
        return {
            OriginalPage:{
                id: self.id,
                userId: self.userId,
                originalPageHeaderId: self.originalPageHeaderId,
                title: self.title,
                creationDate: self.creationDate,
                creator: self.creator,
                text: self.text,
                confirmor: self.confirmor,
                imageRotate: self.imageRotate,
                pageNo: self.pageNo,
                Tags: tags
            }
        };
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    /**
     * オリジナルページをコピーする
     */
    clone: function()
    {
        var self = this;

        return angular.copy(self);
    },

    /**
     * 別のオリジナルページのデータのみを読み込む
     *
     * @param originalPage
     */
    import: function(originalPage)
    {
        var self = this;
        self.id = originalPage.id;
        self.userId = originalPage.userId;
        self.creator = originalPage.creator;
        self.confirmor = originalPage.confirmor;
        self.creationDate = originalPage.creationDate;
        self.pageNo = originalPage.pageNo;
        self.title = originalPage.title;
        self.text = originalPage.text;
        self.originalPageHeaderId = originalPage.originalPageHeaderId;
        self.orgTitle = originalPage.orgTitle;
        self.orgText = originalPage.orgText;
        self.orgCreator = originalPage.orgCreator;
        self.orgConfirmor = originalPage.orgConfirmor;
        self.orgCreationDate = originalPage.orgCreationDate;
        self.url = originalPage.url;
        self.thumbUrl = originalPage.thumbUrl;
        self.deepZoomImage = originalPage.deepZoomImage;
        self.imageId = originalPage.imageId;
        self.imageRotate = originalPage.imageRotate;
        self.extension = originalPage.extension;
        self.fileSize = originalPage.fileSize;
        self.width = originalPage.width;
        self.height = originalPage.height;
        self.tags = angular.copy(originalPage.tags);
        self.informationSource = originalPage.informationSource;
        self.isSelect = originalPage.isSelect;
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get id() { return this._id },
    set id(id) { this._id = id; },
    get userId() { return this._userId; },
    set userId(userId) { this._userId = userId; },
    get pageNo() { return this._pageNo; },
    set pageNo(pageNo) { this._pageNo = pageNo; },
    get title() { return this._title; },
    set title(title) { this._title = title; },
    get text() { return this._text; },
    set text(text) { this._text = text; },
    get creator(){ return this._creator; },
    set creator(creator) { this._creator = creator; },
    get confirmor() { return this._confirmor; },
    set confirmor(confirmor) { this._confirmor = confirmor; },
    get creationDate() { return this._creationDate; },
    set creationDate(creationDate) { this._creationDate = creationDate; },
    get originalPageHeaderId() { return this._originalPageHeaderId; },
    set originalPageHeaderId(originalPageHeaderId) { this._originalPageHeaderId = Number(originalPageHeaderId); },
    get orgTitle() { return this._orgTitle; },
    set orgTitle(orgTitle) { this._orgTitle = orgTitle; },
    get orgText() { return this._orgText; },
    set orgText(orgText) { this._orgText = orgText; },
    get orgCreator() { return this._orgCreator; },
    set orgCreator(orgCreator) { this._orgCreator = orgCreator; },
    get orgConfirmor() { return this._orgConfirmor; },
    set orgConfirmor(orgConfirmor) { this._orgConfirmor = orgConfirmor; },
    get orgCreationDate() { return this._orgCreationDate; },
    set orgCreationDate(orgCreationDate) { this._orgCreationDate = orgCreationDate; },
    get tags() { return this._tags; },
    set tags(tags) { this._tags = tags; },
    get url() { return UNIVERSALVIEWER.Config.imageDir + this._url; },
    set url(url) { this._url = url.replace(UNIVERSALVIEWER.Config.imageDir,""); },
    get thumbUrl() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.smallThumb + this._thumbUrl; },
    set thumbUrl(thumbUrl) { this._thumbUrl = thumbUrl.replace(UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.smallThumb,""); },
    get thumbUrlMiddle() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.middleThumb + this._thumbUrl; },
    get thumbUrlLarge() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.largeThumb + this._thumbUrl; },
    get deepZoomImage() { return UNIVERSALVIEWER.Config.imageDir + this._deepZoomImage; },
    set deepZoomImage(deepZoomImage) { this._deepZoomImage = deepZoomImage.replace(UNIVERSALVIEWER.Config.imageDir,""); },
    get imageId() { return this._imageId; },
    set imageId(imageId) { this._imageId = imageId; },
    get imageRotate() { return this._imageRotate; },
    set imageRotate(imageRotate) { this._imageRotate = imageRotate; },
    get extension() { return this._extension; },
    set extension(extension) { this._extension = extension; },
    get fileSize() { return this._fileSize; },
    set fileSize(fileSize) { this._fileSize = fileSize; },
    get width() { return this._width; },
    set width(width) { this._width = width; },
    get height() { return this._height; },
    set height(height) { this._height = height; },
    get informationSource() { return this._informationSource; },
    set informationSource(informationSource) { this._informationSource = informationSource; },
    get isSelect() { return this._isSelect; },
    set isSelect(isSelect) { this._isSelect = isSelect; }

};


