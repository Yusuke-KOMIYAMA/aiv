/**
 * 「バインダーページ」クラス
 * BinderPageClass
 *
 * Created on 2015/01/05.
 *
 * @member id バインダーページNo
 * @member userId ユーザーNo
 * @member binderId バインダーNo
 * @member originalPageId オリジナルページNo
 * @member order 並び順
 * @member title タイトル
 * @member content 本文
 * @member creator 作成者
 * @member confirmor 確認者
 * @member creationDate 作成日
 * @member url 画像URL
 * @member thumbUrl 画像サムネイルURL
 * @member imageId 画像No
 * @member imageRotate 画像回転
 * @member width 画像幅
 * @member height 画像高さ
 * @param annotations 注釈
 * @member tags タグ配列
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Class = UNIVERSALVIEWER.Class || {};

// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Class.BinderPageClass = function()
{
    // メンバー変数
    this._id = 0;
    this._userId = "";
    this._binderId = 0;
    this._originalPageId = 0;
    this._pageNo = 0;
    this._creator = "";
    this._confirmor = "";
    this._creationDate = "";
    this._title = "";
    this._text = "";
    this._url = "";
    this._thumbUrl = "";
    this._deepZoomImage = "";
    this._imageId = "";
    this._imageRotate = "";
    this._extension = "";
    this._fileSize = "";
    this._width = 0;
    this._height = 0;
    this._annotations = [];
    this._tags = [];
};

UNIVERSALVIEWER.Class.BinderPageClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * バインダーページ初期化
     *
     * @param id
     * @param userId
     * @param binderId
     * @param originalPageId
     * @param pageNo
     * @param title
     * @param text
     * @param creator
     * @param confirmor
     * @param creationDate
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
     * @param annotations
     */
    initialize: function(id, userId, binderId, originalPageId, pageNo, title, text, creator, confirmor, creationDate, tags, url, thumbUrl, deepZoomImage, imageId, imageRotate, extension, fileSize, width, height, annotations)
    {
        // 「バインダーページ」のメンバー変数
        this.id = id;
        this.userId = userId;
        this.binderId = binderId;
        this.originalPageId = originalPageId;
        this.pageNo = pageNo;
        this.title = title;
        this.text = text;
        this.creator = creator;
        this.confirmor = confirmor;
        this.creationDate = creationDate;
        this.tags = tags;
        this.url = url;
        this.thumbUrl = thumbUrl;
        this.deepZoomImage = deepZoomImage;
        this.imageId = imageId;
        this.imageRotate = imageRotate;
        this.extension = extension;
        this.fileSize = fileSize;
        this.width = width;
        this.height = height;
        this.annotations = annotations;
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
        var annotations = [];

        // 「Annotation」リストの生成
        angular.forEach(record.Annotation, function(data) {

            var annotation = new UNIVERSALVIEWER.Class.AnnotationClass();
            annotation.initFromDb(data);
            annotations.push(annotation);
        });

        // 初期化準備
        var reg=/(.*)(?:\.([^.]+$))/;

        // 「BinderPage」の初期化
        self.initialize(
            record.id,
            record.userId,
            record.binderId,
            record.originalPageId,
            record.pageNo,
            record.title,
            record.text,
            record.creator,
            record.confirmor,
            record.creationDate,
            record.Tag,
            record.Image.localFileName,
            record.Image.localFileName,
            record.Image.deepZoomImage,
            record.imageId,
            record.imageRotate,
            record.Image.localFileName.match(reg)[2],
            record.Image.fileSize,
            record.Image.sizeX,
            record.Image.sizeY,
            annotations
        );
    },

    /**
     * サーバーへ送信するJSON対応のオブジェクトを生成する
     */
    createJson: function()
    {
        var self = this;

        // 注釈
        var annotations = [];
        angular.forEach(self.annotations, function(annotation) {
            annotations.push(annotation.createJson());
        });

        var tags = [];
        if (self.tags.length > 0) {
            angular.forEach(self.tags, function(tag) {
                tags.push({Tag:{text:tag.text}});
            });
        }

        // バインダーページ本体
        return {
            BinderPage:{
                id: self.id,
                userId: self.userId,
                binderId: self.binderId,
                originalPageId: self.originalPageId,
                pageNo: self.pageNo,
                title: self.title,
                text: self.text,
                creator: self.creator,
                confirmor: self.confirmor,
                creationDate: self.creationDate,
                imageId: self.imageId,
                imageRotate: self.imageRotate
            },
            Tags: tags,
            Annotations: annotations
        };
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    /**
     * バインダーページをコピーする
     */
    clone: function()
    {
        var self = this;

        return angular.copy(self);
    },

    /**
     * 別のバインダーページのデータのみを読み込む
     *
     * @param originalPage
     */
    import: function(binderPage)
    {
        var self = this;
        self.id = binderPage.id;
        self.userId = binderPage.userId;
        self.binderId = binderPage.binderId;
        self.originalPageId = binderPage.originalPageId;
        self.pageNo = binderPage.pageNo;
        self.title = binderPage.title;
        self.text = binderPage.text;
        self.creator = binderPage.creator;
        self.confirmor = binderPage.confirmor;
        self.creationDate = binderPage.creationDate;
        self.tags = angular.copy(binderPage.tags);
        self.url = binderPage.url;
        self.thumbUrl = binderPage.thumbUrl;
        self.deepZoomImage = binderPage.deepZoomImage;
        self.imageId = binderPage.imageId;
        self.imageRotate = binderPage.imageRotate;
        self.extension = binderPage.extension;
        self.fileSize = binderPage.fileSize;
        self.width = binderPage.width;
        self.height = binderPage.height;
        self.annotations = angular.copy(binderPage.annotations);

    },

    // ####################################################
    // getter/setter
    // ####################################################

    get id() { return this._id; },
    set id(id) { this._id = Number(id); },
    get userId() { return this._userId; },
    set userId(userId) { this._userId = Number(userId); },
    get binderId() { return this._binderId; },
    set binderId(binderId) { this._binderId = Number(binderId);},
    get originalPageId() { return this._originalPageId; },
    set originalPageId(originalPageId) { this._originalPageId = Number(originalPageId); },
    get pageNo() { return this._pageNo; },
    set pageNo(pageNo) { this._pageNo = Number(pageNo); },
    get creator() { return this._creator; },
    set creator(creator) { this._creator = creator; },
    get confirmor() { return this._confirmor; },
    set confirmor(confirmor) { this._confirmor = confirmor; },
    get creationDate() { return this._creationDate; },
    set creationDate(creationDate) { this._creationDate = creationDate; },
    get title() { return this._title; },
    set title(title) { this._title = title; },
    get text() { return this._text; },
    set text(text) { this._text = text; },
    get url() { return UNIVERSALVIEWER.Config.imageDir + this._url; },
    set url(url) { this._url = url.replace(UNIVERSALVIEWER.Config.imageDir,""); },
    get thumbUrl() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.smallThumb + this._thumbUrl; },
    get thumbUrlMiddle() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.middleThumb + this._thumbUrl; },
    get thumbUrlLarge() { return UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.largeThumb + this._thumbUrl; },
    set thumbUrl(thumbUrl) { this._thumbUrl = thumbUrl.replace(UNIVERSALVIEWER.Config.imageDir + UNIVERSALVIEWER.Config.smallThumb,""); },
    get deepZoomImage() { return UNIVERSALVIEWER.Config.imageDir + this._deepZoomImage; },
    set deepZoomImage(deepZoomImage) { this._deepZoomImage = deepZoomImage.replace(UNIVERSALVIEWER.Config.imageDir,""); },
    get imageId() { return this._imageId; },
    set imageId(imageId) { this._imageId = Number(imageId); },
    get imageRotate() { return this._imageRotate; },
    set imageRotate(imageRotate) { this._imageRotate = Number(imageRotate); },
    get extension() { return this._extension; },
    set extension(extension) { this._extension = extension; },
    get fileSize() { return this._fileSize; },
    set fileSize(fileSize) { this._fileSize = fileSize; },
    get width() { return this._width; },
    set width(width) { this._width = Number(width); },
    get height() { return this._height; },
    set height(height) { this._height = Number(height); },
    get annotations() { return this._annotations; },
    set annotations(annotations) { this._annotations = annotations },
    get tags() { return this._tags; },
    set tags(tags) { this._tags = tags; }


};
