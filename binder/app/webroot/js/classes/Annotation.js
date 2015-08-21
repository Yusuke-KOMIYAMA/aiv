/**
 * Created on 2015/02/09.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Class = UNIVERSALVIEWER.Class || {};

// ####################################################
// コンストラクタ
// ####################################################
UNIVERSALVIEWER.Class.AnnotationClass = function()
{
    this._id = 0;
    this._binderPageId = 0;
    this._figureType = 0;
    this._svgId = 0;
    this._x = 0;
    this._y = 0;
    this._x2 = 0;
    this._y2 = 0;
    this._rx = 0;
    this._ry = 0;
    this._width = 0;
    this._height = 0;
    this._stroke = 0;
    this._strokeWidth = 0;
    this._lineStyle = 0;
    this._annotationTextId = 0;
    this._title = '';
    this._text = '';
    this._url = '';

};

// ####################################################
// Const
// ####################################################
/**
 * 注釈の図形種別
 *
 * ELLIPSE: 丸
 * RECT: 矩形
 * ARROW: 矢印
 * LINE: 線
 * ANNOTATION: 注釈
 */
UNIVERSALVIEWER.Class.AnnotationClass.FIGURETYPE = {
    ELLIPSE: 1,
    RECT: 2,
    ARROW: 3,
    LINE: 4,
    ANNOTATION: 5
};
UNIVERSALVIEWER.Class.AnnotationClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    /**
     * 「AnnotationClass」インスタンスの初期化
     *
     * @param id
     * @param binderPageId
     * @param figureType
     * @param svgId
     * @param x
     * @param y
     * @param x2
     * @param y2
     * @param rx
     * @param ry
     * @param width
     * @param height
     * @param stroke
     * @param strokeWidth
     * @param lineStyle
     * @param annotationTextId
     * @param title
     * @param text
     * @param url
     */
    initialize: function(id, binderPageId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle, annotationTextId, title, text, url)
    {
        var self = this;

        self.id = id;
        self.binderPageId = binderPageId;
        self.figureType = figureType;
        self.svgId = svgId;
        self.x = x;
        self.y = y;
        self.x2 = x2;
        self.y2 = y2;
        self.rx = rx;
        self.ry = ry;
        self.width = width;
        self.height = height;
        self.stroke = stroke;
        self.strokeWidth = strokeWidth;
        self.lineStyle = lineStyle;
        self.annotationTextId = annotationTextId;
        self.title = title;
        self.text = text;
        self.url = url;
    },

    /**
     * 「AnnotationClass」インスタンスの更新
     *
     * @param id
     * @param binderPageId
     * @param figureType
     * @param svgId
     * @param x
     * @param y
     * @param x2
     * @param y2
     * @param rx
     * @param ry
     * @param width
     * @param height
     * @param stroke
     * @param strokeWidth
     * @param lineStyle
     * @param annotationTextId
     * @param title
     * @param text
     * @param url
     */
    update: function(id, binderPageId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle, annotationTextId, title, text, url)
    {
        var self = this;

        self.id = id;
        self.binderPageId = binderPageId;
        self.figureType = figureType;
        self.svgId = svgId;
        self.x = x;
        self.y = y;
        self.x2 = x2;
        self.y2 = y2;
        self.rx = rx;
        self.ry = ry;
        self.width = width;
        self.height = height;
        self.stroke = stroke;
        self.strokeWidth = strokeWidth;
        self.lineStyle = lineStyle;
        self.annotationTextId = annotationTextId;
        self.title = title;
        self.text = text;
        self.url = url;
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
        var annotationTextId = 0, title = '', text = '', url = '';

        // 文字注釈がある場合
        if (UNIVERSALVIEWER.Class.AnnotationClass.FIGURETYPE.ANNOTATION == record.figureType) {
            annotationTextId = record.AnnotationText.id;
            title = record.AnnotationText.title;
            text = record.AnnotationText.text;
            url = record.AnnotationText.url;
        }

        // オブジェクトへデータを反映
        self.initialize(
            record.id, record.binderPageId,
            record.figureType, record.svgId,
            record.x, record.y, record.x2, record.y2, record.rx, record.ry, record.width, record.height,
            record.stroke, record.strokeWidth, record.lineStyle,
            annotationTextId, title, text, url
        );
    },

    /**
     * サーバーへ送信するJSON対応のオブジェクトを生成する
     *
     * @param record
     * @returns {{id: number, binderPageId: number, figureType: number, svgId: number, x: number, y: number, x2: number, y2: number, rx: number, ry: number, width: number, height: number, stroke: string, strokeWidth: number, lineStyle: number, AnnotationText: {}}}
     */
    createJson: function()
    {
        var self = this;

        // 文字注釈がある場合
        var annotationText = [];
console.log("Annotation::createJson.1");
console.log(self.figureType);
console.log(self.annotationTextId);
console.log(self.id);
console.log(self.title);
console.log(self.text);
console.log(self.url);
        if (UNIVERSALVIEWER.Class.AnnotationClass.FIGURETYPE.ANNOTATION == self.figureType) {
            annotationText.push({
                AnnotationText:{
                    id: self.annotationTextId,
                    annotationId: self.id,
                    title: self.title,
                    text: self.text,
                    url: self.url
                }
            });
console.log(annotationText);
        }
        return {
            Annotation: {
                id: self.id,
                parentId: self.binderPageId,
                figureType: self.figureType,
                svgId: self.svgId,
                x: self.x,
                y: self.y,
                x2: self.x2,
                y2: self.y2,
                rx: self.rx,
                ry: self.ry,
                width: self.width,
                height: self.height,
                stroke: self.stroke,
                strokeWidth: self.strokeWidth,
                lineStyle: self.lineStyle,
                AnnotationTexts: annotationText
            }
        };
    },

    // ####################################################
    // クライアント関連
    // ####################################################

    // ####################################################
    // getter/setter
    // ####################################################

    get id() { return this._id; },
    set id(id) { this._id = Number(id); },
    get binderPageId() { return this._binderPageId; },
    set binderPageId(binderPageId) { this._binderPageId = Number(binderPageId); },
    get figureType() { return this._figureType; },
    set figureType(figureType) { this._figureType = Number(figureType); },
    get svgId() { return this._svgId; },
    set svgId(svgId) { this._svgId = Number(svgId); },
    get x() { return this._x; },
    set x(x) { this._x = Number(x); },
    get y() { return this._y; },
    set y(y) { this._y = Number(y); },
    get x2() { return this._x2; },
    set x2(x2) { this._x2 = Number(x2); },
    get y2() { return this._y2; },
    set y2(y2) { this._y2 = Number(y2); },
    get rx() { return this._rx; },
    set rx(rx) { this._rx = Number(rx); },
    get ry() { return this._ry; },
    set ry(ry) { this._ry = Number(ry); },
    get width() { return this._width; },
    set width(width) { this._width = Number(width); },
    get height() { return this._height; },
    set height(height) { this._height = Number(height); },
    get stroke() { return this._stroke; },
    set stroke(stroke) { this._stroke = stroke; },
    get strokeWidth() { return this._strokeWidth; },
    set strokeWidth(strokeWidth) { this._strokeWidth = Number(strokeWidth); },
    get lineStyle() { return this._lineStyle; },
    set lineStyle(lineStyle) { this._lineStyle = Number(lineStyle); },
    get annotationTextId() { return this._annotationTextId; },
    set annotationTextId(annotationTextId) { this._annotationTextId = Number(annotationTextId); },
    get title() { return this._title; },
    set title(title) { this._title = title; },
    get text() { return this._text; },
    set text(text) { this._text = text; },
    get url() { return this._url; },
    set url(url) { this._url = url; }

};

