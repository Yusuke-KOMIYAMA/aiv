/**
 * Openseadragon, Snap.svg を利用した「注釈」機能付き「画像ビューア」
 *
 * Created on 2015/02/06.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Common = UNIVERSALVIEWER.Common || {};

// ==============================================
//  初期化関連
// ==============================================

/**
 * 注釈機能付き画像ビューア
 *
 * Require: jQuery, Openseadragon, Snap.svg
 *
 * @see https://openseadragon.github.io/
 * @see http://snapsvg.io/
 */
UNIVERSALVIEWER.Common.ImageViewer = function(parentPage)
{
    this.parent = parentPage;

    this.message = {
        "close":"",
        "edit":"",
        "delete":"",
        "confirmDelete":""
    };

    this.edit = {
        isViewOverlay: true,
        isViewModal: false,
        mode: UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE,
        stroke: 'red',
        fill: 'none',
        fillOpacity: 0,
        strokeWidth: UNIVERSALVIEWER.Common.ImageViewer.strokeWidth.NORMAL,
        rectR: 2,
        lineStyle: UNIVERSALVIEWER.Common.ImageViewer.lineStyle.SOLID,
        selectingId: 'selecting',
        annotateModalFunction: function(annotation){},
        isDirty: false,
        history: {
            undoStack: [],
            redoStack: []
        }
    };

    // Openseadragon settings
    this.openseadragon = {
        viewer: null,
        overlay: null,
        viewerId: '',
        overlayId: '',
        overlayClass: '',
        viewerName: '',
        prefixUrl: '',
        tileSource: ''
    };

    // Image information
    this.image = {
        url: parentPage.url,
        thumbUrl: parentPage.thumbUrl,
        width: parentPage.width,
        height: parentPage.height
    };

    // Annotation Load/Save Cache
    this.annotation = {
        currentId: 0,
        annotations: [],
        append: function(id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle, annotationTextId, title, text, url)
        {
            if (this.get(svgId) == null) {
                var annotation = new UNIVERSALVIEWER.Class.AnnotationClass();
                annotation.initialize(id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle, annotationTextId, title, text, url);
                this.annotations.push(annotation);
            }
        },
        update: function(id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle, annotationTextId, title, text, url)
        {
            var annotation = this.get(svgId);
            if (annotation != null) {
                annotation.update(id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle, annotationTextId, title, text, url);
            }
        },
        remove: function(svgId)
        {
            var annotations = this.annotations;
            angular.forEach(annotations, function(annotation, i) {
                if (annotation.svgId == svgId) {
                    annotations.splice(i, 1);
                }
            });
        },
        edit: function(svgId, title, text, url) {
            var annotations = this.annotations;
            angular.forEach(annotations, function(annotation, i) {
                if (annotation.svgId == svgId) {
                    annotation.title = title;
                    annotation.text = text;
                    annotation.url = url;
                }
            });
        },
        get: function(svgId) {
            var annotations = this.annotations;
            var selectedAnnotation = null;
            angular.forEach(annotations, function(annotation, i) {
                if (annotation.svgId == svgId) {
                    selectedAnnotation = annotation;
                    return;
                }
            });
            return selectedAnnotation;
        }
    };

    // サーバーから取得したアノテーション情報をここで展開する

    // Drawing calculation members
    this.graphics = {
        svg: {
            id: 'svg',
            figureClass: 'figure',
            paper: null,
            marker: null
        },
        isMoving: false,
        originalWidth: 0,   // Original image width
        originalHeight: 0,  // Original image height
        diviationHorizontal: 0, // Canvas <=> Overlay position diviation Left.
        diviationVertical: 0,   // Canvas <=> Overlay position diviation Top.
        currentWidth: 0,    // Overlay current width
        currentHeight: 0,   // Overlay current height
        zoomRatio: 0, // Zoom ratio
        startX: 0,
        startY: 0,
        currentX: 0,
        currentY: 0,
        minX: 0,
        minY: 0,
        x: 0,
        y: 0,
        rx: 0,
        ry: 0,
        width: 0,
        height: 0,
        init: function(w, h, dh, dv) {
            this.originalWidth = w;
            this.originalHeight = h;
            this.diviationHorizontal = dh;
            this.diviationVertical = dv;
        },
        down: function(x, y, w, h) {
            this._ratio(w, h);
            this.startX = (x - this.diviationHorizontal) / this.zoomRatio;
            this.startY = (y - this.diviationVertical) / this.zoomRatio;
            this.minX = this.startX;
            this.minY = this.startY;
            this.x = this.startX;
            this.y = this.startY;
            this.isMoving = true;
        },
        move: function(x, y, w, h) {
            if (this.isMoving) {
                this._ratio(w, h);
                this.currentX = (x - this.diviationHorizontal) / this.zoomRatio;
                this.currentY = (y - this.diviationVertical) / this.zoomRatio;
                this.minX = (this.startX < this.currentX) ? this.startX : this.currentX;
                this.minY = (this.startY < this.currentY) ? this.startY : this.currentY;
                this.width = Math.abs(this.currentX - this.startX);
                this.height = Math.abs(this.currentY - this.startY);
                this.rx = this.width / 2;
                this.ry = this.height / 2;
                this.x = (this.startX < this.currentX) ? this.startX + this.rx : this.currentX + this.rx;
                this.y = (this.startY < this.currentY) ? this.startY + this.ry : this.currentY + this.ry;
            }
        },
        up: function(x, y, w, h) {
            this.isMoving = false;
            this._ratio(w, h);
            this.currentX = (x - this.diviationHorizontal) / this.zoomRatio;
            this.currentY = (y - this.diviationVertical) / this.zoomRatio;
            this.minX = (this.startX < this.currentX) ? this.startX : this.currentX;
            this.minY = (this.startY < this.currentY) ? this.startY : this.currentY;
            this.width = Math.abs(this.currentX - this.startX);
            this.height = Math.abs(this.currentY - this.startY);
            this.rx = this.width / 2;
            this.ry = this.height / 2;
            this.x = (this.startX < this.currentX) ? this.startX + this.rx : this.currentX + this.rx;
            this.y = (this.startY < this.currentY) ? this.startY + this.ry : this.currentY + this.ry;
        },
        _ratio: function(w, h) {
            this.currentWidth = w;
            this.currentHeight = h;
            this.zoomRatio = this.currentWidth / this.originalWidth;
        }
    }
};
// 静的関数
UNIVERSALVIEWER.Common.ImageViewer.mode = {
    NONE: 0,
    MOVE: 1,
    ELLIPSE: 2,
    RECT: 3,
    ARROW: 4,
    LINE: 5,
    ANNOTATE: 6
};
UNIVERSALVIEWER.Common.ImageViewer.lineStyle = {
    SOLID: 0,
    DOTTED: 1
};
UNIVERSALVIEWER.Common.ImageViewer.strokeWidth = {
    NARROW: 3,
    NORMAL: 6,
    BOLD: 9
};
UNIVERSALVIEWER.Common.ImageViewer.figureType = {
    ELLIPSE: 1,
    RECT: 2,
    ARROW: 3,
    LINE: 4,
    ANNOTATE: 5
};
UNIVERSALVIEWER.Common.ImageViewer.editAction = {
    DRAW: 0,
    DELETE: 1,
    EDIT: 2,
    DRAW_ALL: 3,
    DELETE_ALL: 4
};
UNIVERSALVIEWER.Common.ImageViewer.setType = {
    INITIALIZE: 0, // 初期化
    HISTORY: 1, // Undo/Redoから
    TEMPORARY: 2, // テンポラリ描画用
    DRAW: 3, // 図形・注釈描画
    DRAW_ALL: 4 // 図形・一括描画用
};

UNIVERSALVIEWER.Common.ImageViewer.prototype = {

    /**
     * 画像ビューアの初期化
     *
     * @param viewerId
     * @param viewerName
     * @param prefixUrl
     * @param tileSource
     * @param overlayId
     * @param overlayClass
     * @param url
     * @param width
     * @param height
     * @param messages
     * @param annotations
     * @param annotateModalFunction
     */
    initialize: function(viewerId, viewerName, prefixUrl, tileSource, overlayId, overlayClass, url, width, height, messages, annotations, annotateModalFunction)
    {
        var self = this;
        var openseadragon = self.openseadragon;
        var image = self.image;
        var graphics = self.graphics;

        // 翻訳済みメッセージ
        self.message.close = messages.close;
        self.message.edit = messages.edit;
        self.message.delete = messages.delete;
        self.message.confirmDelete = messages.confirmDelete;

        // Set Openseadragon info
        openseadragon.viewerId = viewerId;
        openseadragon.viewerName = viewerName;
        openseadragon.prefixUrl = prefixUrl;
        openseadragon.tileSource = tileSource;
        openseadragon.overlayId = overlayId;
        openseadragon.overlayClass = overlayClass;

        // Set image info
        image.url = url;
        image.width = width;
        image.height = height;

        // Create Openseadragon object
        openseadragon.viewer = OpenSeadragon({
            id: openseadragon.viewerId,
            name: openseadragon.viewerName,
            prefixUrl: openseadragon.prefixUrl,
            tileSources: openseadragon.tileSource
        });

        // event handler when viewer open
        openseadragon.viewer.addHandler('open', function(event) {
            var div = document.createElement("div");
            div.className = openseadragon.overlayClass;
            div.setAttribute('id', openseadragon.overlayId);
            openseadragon.overlay = div;
            // Create overlay
            openseadragon.viewer.addOverlay({
                element: div,
                location: openseadragon.viewer.viewport.imageToViewportRectangle(new OpenSeadragon.Rect(0, 0, width, height)),
                onDraw: function(position, size, element) {
                    angular.element(element).css({
                        left: position.x,
                        top: position.y,
                        position: 'absolute',
                        display: (self.edit.isViewOverlay) ? 'block' : 'none',
                        width: size.x,
                        height: size.y
                    });
                    angular.element('#'+graphics.svg.id).css({
                        width: '100%',
                        height: '100%'
                    });
                    graphics.init(image.width, image.height, position.x, position.y);
                }
            });
        });
        // event handler when overlay added
        openseadragon.viewer.addHandler('add-overlay', function(event) {

            openseadragon.overlay.oncontextmenu = function(event) {
                event.preventDefaultAction = true;
                event.stopBubbling = true;
                event.stopHandlers = true;
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                // マウスイベントオブジェクトを作成
                var mousee = document.createEvent("MouseEvent");
                mousee.initMouseEvent("contextmenu",true,true,window,0,event.screenX,event.screenY,event.clientX,event.clientY,false,false,false,false,2,null);
                angular.element(".openseadragon-container canvas")[0].dispatchEvent(mousee);
            };


            // Create Snap.svg
            graphics.svg.paper = Snap(image.width, image.height).remove().svg(0, 0, image.width, image.height);
            graphics.svg.paper.attr({
                id: graphics.svg.id,
                style: 'visibility:visible;'
            });
            var element = event.element.appendChild(graphics.svg.paper.node);

            var ms = graphics.svg.paper.path("M0,0L8,5L0,10L4,5z").attr({stroke: self.edit.stroke, fill: self.edit.stroke});
            graphics.svg.marker = ms.marker(0,0,10,10,5,5);

            // 注釈の初期設定
            self._initAnnotations(annotations);
            self._setAnnotateEvent();
        });

        // event handler when overlay redraw(zoom etc.)
        openseadragon.viewer.addHandler('animation', function(event) {
            angular.element('#'+graphics.svg.id).find('.'+graphics.svg.figureClass).each(function() {
                self._transform(Snap(angular.element(this)[0]));
            });
        });

        openseadragon.viewer.addHandler('layer-level-changed', function(event) {

        });

        openseadragon.viewer.setMouseNavEnabled(true);

        // 注釈をポップアップする関数を指定
        self.edit.annotateModalFunction = annotateModalFunction;

    },

    /**
     * アノテーションの初期化
     *
     * @param annotations (UNIVERSALVIEWER.Class.AnnotationClass) OriginalPageClass.annotation
     * @private
     */
    _initAnnotations: function(annotations)
    {
        var self = this;

        var maxId = 0;
        angular.forEach(annotations, function(annotation, i) {

            var parentElement = angular.element('#' + self.graphics.svg.id);
            self.setAnnotation(
                UNIVERSALVIEWER.Common.ImageViewer.setType.INITIALIZE,
                parentElement,
                annotation.id,
                annotation.parentId,
                annotation.figureType,
                annotation.svgId,
                annotation.x,
                annotation.y,
                annotation.x2,
                annotation.y2,
                annotation.rx,
                annotation.ry,
                annotation.width,
                annotation.height,
                annotation.stroke,
                annotation.strokeWidth,
                'none',
                annotation.lineStyle,
                self.graphics.zoomRatio,
                annotation.annotationTextId,
                annotation.title,
                annotation.text,
                annotation.url
            );
            if (maxId < Number(annotation.id)) {
                maxId = Number(annotation.id);
            }

        });

        // 自動採番の開始番号を設定
        self.annotation.currentId = maxId + 1;

    },

    // ==============================================
    //  Undo/Redo 関連
    // ==============================================

    /**
     * 履歴を登録する
     *
     * @param action
     * @param src
     * @param dst
     */
    setHistory: function(action, src, dst)
    {
        var self = this;
        var history = self.edit.history;
        // 履歴を追加
        history.undoStack.push({
            action: action,
            src: src,
            dst: dst
        });

        // REDO 用 stack をクリアする
        history.redoStack = [];

    },

    /**
     * UNDOを実行する
     */
    undo: function()
    {
        var self = this;
        var history = self.edit.history;

        // 履歴が残っていた場合にUNDOを実行
        if (history.undoStack.length > 0) {

            // 処理情報を取得
            var h = history.undoStack.pop();
            switch(h.action) {

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DRAW:

                    // draw の undo なので、 remove を行う
                    self.removeAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY, h.src.svgId);
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DRAW_ALL:

                    // drawAll の undo なので、removeAll を行う
                    self.removeAnnotations(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY);
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DELETE:

                    // delete の undo なので、draw を行う
                    var svgElement = angular.element('#'+self.graphics.svg.id);
                    self.setAnnotation(
                        UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY,
                        svgElement,
                        h.src.id, h.src.parentId,
                        h.src.figureType, h.src.svgId, h.src.x, h.src.y, h.src.x2, h.src.y2, h.src.rx, h.src.ry, h.src.width, h.src.height,
                        h.src.stroke, h.src.strokeWidth, 'none', h.src.lineStyle, self.graphics.zoomRatio,
                        h.src.annotationTextId, h.src.title, h.src.text, h.src.url
                    );
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DELETE_ALL:

                    // removeAll の undo なので、drawAll を行う
                    self.drawAnnotations(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY, h.src);
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.EDIT:

                    // 注釈の場合のみ、編集がある dst になっているはずなので、src に戻す
                    self.editAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY, h.src.svgId, h.src.title, h.src.text, h.src.url);
                    break;

            }

            // undo から redo へ履歴を移動する
            history.redoStack.push(h);
        }
    },

    /**
     * REDOを実行する
     */
    redo: function()
    {
        var self = this;
        var history = self.edit.history;

        if (history.redoStack.length > 0) {

            var h = history.redoStack.pop();
            switch(h.action) {

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DRAW:

                    // draw の redo なので、 draw を行う
                    var svgElement = angular.element('#'+self.graphics.svg.id);
                    self.setAnnotation(
                        UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY,
                        svgElement,
                        h.src.id, h.src.parentId,
                        h.src.figureType, h.src.svgId, h.src.x, h.src.y, h.src.x2, h.src.y2, h.src.rx, h.src.ry, h.src.width, h.src.height,
                        h.src.stroke, h.src.strokeWidth, 'none', h.src.lineStyle, self.graphics.zoomRatio,
                        h.src.annotationTextId, h.src.title, h.src.text, h.src.url
                    );
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DRAW_ALL:

                    // drawAll の redo なので drawAll を行う
                    self.drawAnnotations(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY, h.src);
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DELETE:

                    // delete の redo なので delete を行う
                    self.removeAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY, h.src.svgId);
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.DELETE_ALL:

                    // deleteAll の redo なので deleteAll を行う
                    self.removeAnnotations(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY);
                    break;

                case UNIVERSALVIEWER.Common.ImageViewer.editAction.EDIT:

                    // 注釈の場合のみ、編集がある src になっているはずなので、dst に戻す
                    self.editAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.HISTORY, h.dst.svgId, h.dst.title, h.dst.text, h.dst.url);
                    break;
            }

            // redo から undo へ履歴を移動する
            history.undoStack.push(h);
        }
    },

    // ==============================================
    //  編集状態関連（ダーティーチェック）
    // ==============================================

    /**
     * ダーティーチェック
     */
    isDirty: function() {
        var self = this;
        return self.edit.isDirty;
    },

    /**
     * 編集した
     */
    setDirty: function() {
        var self = this;
        self.edit.isDirty = true;
    },

    /**
     * ダーティー状態クリア
     */
    clearDirty: function() {
        var self = this;
        self.edit.isDirty = false;
    },

    // ==============================================
    //  アノテーション描画関連
    // ==============================================

    /**
     * 注釈を描画する
     * データやイベントの設定も含めて行う
     *
     * @param setType 0:初期化, 1:Undo/Redoから, 2:テンポラリ描画用, 3:図形・注釈描画
     * @param parentElement (angular.element) SVGエレメント
     * @param id (int) Annotation.id
     * @param parentId (int) OriginalPage.id or BinderPage.id
     * @param figureType (int) 1:ELLIPSE, 2:RECT, 3:ARROW, 4:LINE, 5:ANNOTATION
     * @param svgId (int) svg figure tag id
     * @param x (float) svg figure attribute x
     * @param y (float) svg figure attribute y
     * @param x2 (float) svg figure attribute x2
     * @param y2 (float) svg figure attribute y2
     * @param rx (float) svg figure attribute rx
     * @param ry (float) svg figure attribute ry
     * @param width (float) svg figure attribute width
     * @param height (float) svg figure attribute height
     * @param stroke (string) svg figure attribute stroke. CSS color code
     * @param strokeWidth (int) svg figure attribute strokeWidth
     * @param fill (string) svg fiugre attribute fill 'none' only
     * @param lineStyle (int) svg figure line style. 0:SOLID, 1:DOTTED
     * @param zoomRatio (float) svg scale
     * @param annotationTextId (int) annotationTextId (DB only)
     * @param title (string)
     * @param text (string)
     * @param url (string)
     */
    setAnnotation: function(setType, parentElement, id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, fill, lineStyle, zoomRatio, annotationTextId, title, text, url)
    {
        var self = this;
        var graphics = self.graphics;
        var annotation = self.annotation;

        // ======================================
        // 1.データセット
        // ======================================

        // ダーティーフラグをセットする (初期化時を除いてセットする)
        if (setType != UNIVERSALVIEWER.Common.ImageViewer.setType.INITIALIZE) {
            self.setDirty();
        }

        // データをセット（SVGと1:1で同期されているはず） (一時描画時以外はセットする)
        if (setType != UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY) {

            annotation.append(
                id,
                parentId,
                figureType,
                svgId,
                x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, lineStyle,
                annotationTextId, title, text, url
            );
        }

        // ======================================
        // 2.図形を描画
        // ======================================
        var attr = {};
        var f;
        switch (figureType) {

            case UNIVERSALVIEWER.Common.ImageViewer.figureType.ELLIPSE:

                // 図形のパラメータを設定
                attr = {
                    id: svgId,
                    class: graphics.svg.figureClass,
                    stroke: stroke,
                    strokeWidth: strokeWidth,
                    fill: fill,
                    transform: 'scale(' + zoomRatio + ')'
                };
                // 破線設定
                if (lineStyle == UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED) {
                    attr["stroke-dasharray"] = strokeWidth + " " + strokeWidth;
                }
                // SVG要素の生成
                f = graphics.svg.paper.ellipse(x, y, rx, ry).attr(attr);
                // SVG要素をSVGへ追加する
                parentElement.append(f);
                // 大きさの調整
                self._transform(f);
                break;

            case UNIVERSALVIEWER.Common.ImageViewer.figureType.RECT:

                // 図形のパラメータを設定
                attr = {
                    id: svgId,
                    class: graphics.svg.figureClass,
                    stroke: stroke,
                    strokeWidth: strokeWidth,
                    fill: fill,
                    transform: 'scale(' + zoomRatio + ')'
                };
                // 破線設定
                if (lineStyle == UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED) {
                    attr["stroke-dasharray"] = strokeWidth + " " + strokeWidth;
                }
                // SVG要素の生成
                f = graphics.svg.paper.rect(x, y, width, height, rx, ry).attr(attr);
                // SVG要素をSVGへ追加する
                parentElement.append(f);
                // 大きさの調整
                self._transform(f);
                break;

            case UNIVERSALVIEWER.Common.ImageViewer.figureType.ARROW:

                // 図形のパラメータを設定
//                var marker = graphics.svg.marker;
                var ms = graphics.svg.paper.path("M0,0L8,5L0,10L4,5z").attr({stroke: stroke, fill: stroke});
                var marker = ms.marker(0,0,10,10,5,5);
                marker.attr({id: "m_"+svgId});
                attr = {
                    id: svgId,
                    class: graphics.svg.figureClass,
                    stroke: stroke,
                    strokeWidth: strokeWidth,
                    markerWidth: 1,
                    markerHeight: 1,
                    markerEnd: marker,
                    transform: 'scale(' + zoomRatio + ')'
                };
                // 破線設定
                if (lineStyle == UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED) {
                    attr["stroke-dasharray"] = strokeWidth + " " + strokeWidth;
                }
                // SVG要素の生成
                f = graphics.svg.paper.line(x, y, x2, y2).attr(attr);
                // SVG要素をSVGへ追加する
                parentElement.append(f);
                // 大きさの調整
                self._transform(f);
                break;

            case UNIVERSALVIEWER.Common.ImageViewer.figureType.LINE:

                // 図形のパラメータを設定
                attr = {
                    id: svgId,
                    class: graphics.svg.figureClass,
                    stroke: stroke,
                    strokeWidth: strokeWidth,
                    transform: 'scale(' + zoomRatio + ')'
                };
                // 破線設定
                if (lineStyle == UNIVERSALVIEWER.Common.ImageViewer.lineStyle.DOTTED) {
                    attr["stroke-dasharray"] = strokeWidth + " " + strokeWidth;
                }
                // SVG要素の生成
                f = graphics.svg.paper.line(x, y, x2, y2).attr(attr);
                // SVG要素をSVGへ追加する
                parentElement.append(f);
                // 大きさの調整
                self._transform(f);
                break;

            case UNIVERSALVIEWER.Common.ImageViewer.figureType.ANNOTATE:

                // SVG要素の生成
                var g = graphics.svg.paper.g(graphics.svg.paper.path("M3.161,63.357c0.471,0,0.968-0.115,1.479-0.342l14.346-6.376c1.234-0.549,2.887-1.684,3.843-2.64L62,14.829 c0.754-0.754,1.17-1.759,1.17-2.829S62.754,9.925,62,9.172l-7.172-7.173C54.074,1.246,53.07,0.831,52,0.831S49.926,1.246,49.172,2 L9,42.171c-0.968,0.967-2.09,2.651-2.612,3.917L0.912,59.389c-0.594,1.444-0.174,2.42,0.129,2.873 C1.507,62.958,2.28,63.357,3.161,63.357z M20,51.171C20,51.171,20,51.172,20,51.171L12.828,44L46,10.828L53.172,18L20,51.171z M52,4.828L59.172,12L56,15.172L48.828,8L52,4.828z M10.088,47.611c0.059-0.142,0.138-0.303,0.226-0.469l6.213,6.213L5.751,58.143 L10.088,47.611z"))
                    .attr({
                        transform: 'translate(' + x + ',' + (y - 64) + ')'
                    });
                f = graphics.svg.paper.g(g)
                    .attr({
                        id: svgId,
                        class: graphics.svg.figureClass,
                        transform: 'scale(' + zoomRatio + ')'
                    });
                // SVG要素をSVGへ追加する
                parentElement.append(f);
                // 大きさの調整
                self._transform(f);
                break;

            default:
                break;
        }

        // ======================================
        // 3.画像へのイベントの設定
        // ======================================

        // 注釈タブに新規要素を追加・注釈内容入力パネルを開く (描画時かつANNOTATEの場合)
        if (setType == UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW &&
            figureType == UNIVERSALVIEWER.Common.ImageViewer.figureType.ANNOTATE)
        {
            // 注釈内容入力モーダルを開く
            self.edit.annotateModalFunction(annotation.get(svgId));
        }

        // 削除用のクリックイベントを設定 (一時描画時を除いて設定する)
        if (setType != UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY) {

            if (figureType == UNIVERSALVIEWER.Common.ImageViewer.figureType.ANNOTATE) {

                // --------------------
                // ポップアップパネル生成
                // --------------------

                // popup panel を生成
                angular.element("#page-wrapper")
                    .append(
                        "<div id='modal"+svgId+"' class='panel-modal panel panel-default' style='display:none;position:absolute;' data-svg-id='"+svgId+"'>" +
                            "<div id='modalhead"+svgId+"' class='panel-heading'>" +
                            "</div>" +
                            "<div id='modalbody"+svgId+"' class='panel-body'>" +
                            "</div>" +
                            "<div class='panel-footer'>" +
                                "<button id='modalclose" + svgId +"' class='btn btn-default btn-sm'>"+self.message.close+"</button>" +
                                "<button id='modaledit" + svgId +"' class='btn btn-default btn-sm'>"+self.message.edit+"</button>" +
                                "<button id='modaldelete" + svgId +"' class='btn btn-default btn-sm'>"+self.message.delete+"</button>" +
                            "</div>" +
                        "</div>"
                    );
                // モーダルを閉じる（マウスがアウトした時）
                angular.element('#modal'+svgId).on('mouseleave', function(event) {
                    var evtSvgId = angular.element(event.currentTarget).data('svg-id');
                    // モーダルを綴じたときの処理
                    angular.element('#modal'+evtSvgId).css({'display':'none'});
                    setTimeout( function() {
                        angular.element(f.node).data("is-modal", false);
                    }, 300);
                });
                // モーダルを閉じるボタンイベント
                angular.element('#modalclose'+svgId).on('click', function(event) {
                    var evtSvgId = angular.element(event.currentTarget).parents('.panel-modal').data('svg-id');
                    // モーダルを綴じたときの処理
                    angular.element('#modal'+evtSvgId).css({'display':'none'});
                    setTimeout( function() {
                        angular.element(f.node).data("is-modal", false);
                    }, 300);
                });
                // コメント編集ボタンイベント
                angular.element('#modaledit'+svgId).on('click', function(event) {
                    var evtSvgId = angular.element(event.currentTarget).parents('.panel-modal').data('svg-id');
                    // モーダルを綴じたときの処理
                    angular.element('#modal'+evtSvgId).css({'display':'none'});
                    angular.element(f.node).data("is-modal", false);
                    // 注釈内容入力モーダルを開く
                    self.edit.annotateModalFunction(annotation.get(evtSvgId));
                });
                // コメント削除ボタンイベント
                angular.element('#modaldelete'+svgId).on('click', function(event) {
                    var evtSvgId = angular.element(event.currentTarget).parents('.panel-modal').data('svg-id');
                    self.removeAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, evtSvgId);
                });

                // --------------------
                // hover イベントを設定
                // --------------------

                // data-is-modal を false に設定
                angular.element(f.node).data("is-modal", false);
                angular.element(f.node).data("svg-id", svgId);
                angular.element(f.node).on('mouseenter', function(event) {


                    // モーダルが閉じていない時
                    var evtSvgId = angular.element(event.currentTarget).data('svg-id');
                    var isModal = angular.element(event.currentTarget).data("is-modal");
                    if (isModal == false) {
                        // モーダルを開いた状態のフラグを立てる
                        var evtAnnotation = annotation.get(evtSvgId);
                        angular.element("#modal"+evtSvgId).css({'display':'block','left':event.pageX-25,'top':event.pageY-25,'z-index':100}).data("is-modal", true);
                        angular.element("#modalhead"+evtSvgId).html(evtAnnotation.title);
                        var url = (evtAnnotation.url.trim())
                            ? '<br/><a href="'+encodeURI(evtAnnotation.url.trim())+'" target="_blank">'+UNIVERSALVIEWER.Common.Utils.escapeHtml(evtAnnotation.url.trim())+'</a>'
                            : '';
                        angular.element("#modalbody"+evtSvgId).html(evtAnnotation.text+url);
                    }
                });
            }
            else {
                UNIVERSALVIEWER.Common.Utils.onLoad(f.node, "mousedown", function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    console.log("delete modal");
                    if (confirm(self.message.confirmDelete)) {
                        self.removeAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, e.currentTarget.id);
                    }
                    return false;
                });
            }
        }

        // 履歴に登録する (描画時のみ、[初期化,Undo/Redo,一時描画時]は履歴に登録しない)
        if (setType == UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW) {
            self.setHistory(UNIVERSALVIEWER.Common.ImageViewer.editAction.DRAW, annotation.get(svgId), {});
        }

    },

    /**
     * 注釈を一括で描画する
     *
     * @param setType
     * @param annotations
     */
    drawAnnotations: function(setType, annotations) {
        var self = this;
        var selector = '#' + self.graphics.svg.id;
        var src = [];

        src = angular.copy(annotations);

        angular.forEach(src, function(annotation) {
            self.setAnnotation(
                UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW_ALL,
                angular.element(selector),
                annotation.id,
                annotation.parentId,
                annotation.figureType,
                annotation.svgId,
                annotation.x,
                annotation.y,
                annotation.x2,
                annotation.y2,
                annotation.rx,
                annotation.ry,
                annotation.width,
                annotation.height,
                annotation.stroke,
                annotation.strokeWidth,
                'none',
                annotation.lineStyle,
                self.graphics.zoomRatio,
                annotation.annotationTextId,
                annotation.title,
                annotation.text,
                annotation.url
            );
        });

        if (setType == UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW) {
            self.setHistory(UNIVERSALVIEWER.Common.ImageViewer.editAction.DRAW_ALL, src, {});
        }
    },

    /**
     * 注釈を削除する
     * 同時にデータ類も削除する
     *
     * @param setType
     * @param svgId
     */
    removeAnnotation: function(setType, svgId)
    {
        var self = this;
        var annotation = self.annotation;
        var annotations = annotation.annotations;

        // ダーティーフラグをセットする (初期化時を除いてセットする)
        if (setType != UNIVERSALVIEWER.Common.ImageViewer.setType.INITIALIZE) {
            self.setDirty();
        }

        // 1.現状を退避
        var src = annotation.get(svgId);

        // 2.データから
        annotation.remove(svgId);

        // 3.イベント削除
        // モーダルマウスアウトで閉じるイベントの削除
        angular.element('#modal'+svgId).off('mouseleave');
        // モーダルを閉じるボタンイベントの削除
        angular.element('#modalclose'+svgId).off('click');
        // コメント編集ボタンイベントの削除
        angular.element('#modaledit'+svgId).on('click');
        // コメント削除ボタンイベントの削除
        angular.element('#modaldelete'+svgId).on('click');
        // SVGのイベント削除
        angular.element('#'+svgId).off('mouseenter');

        // 4.付属要素(モーダル用パネル)の削除
        angular.element('#modal'+svgId).remove();

        // 5.SVGから削除
        angular.element("#"+svgId).remove();
        angular.element("#m_"+svgId).remove();

        // 5.履歴に登録する
        if (setType == UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW) {
            self.setHistory(UNIVERSALVIEWER.Common.ImageViewer.editAction.DELETE, src, {});
        }
    },

    /**
     * 注釈を一括で削除する
     * 同時にデータ類も削除する
     *
     * @param setType
     */
    removeAnnotations: function(setType)
    {
        var self = this;
        var annotations = self.annotation.annotations;
        var src = angular.copy(annotations);

        angular.forEach(src, function(annotation, i) {
            self.removeAnnotation(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW_ALL, annotation.svgId);
        });

        if (setType == UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW) {
            self.setHistory(UNIVERSALVIEWER.Common.ImageViewer.editAction.DELETE_ALL, src, {});
        }
    },

    /**
     * 注釈を編集する
     *
     * @param setType
     * @param svgId
     * @param title
     * @param text
     * @param url
     */
    editAnnotation: function(setType, svgId, title, text, url)
    {
        var self = this;
        var annotation = self.annotation;
        var src = {}, dst = {};

        // ダーティーフラグをセット
        self.setDirty();

        // 元のannotationをsrcにセット
        src = angular.copy(annotation.get(svgId));

        // 注釈を更新する
        annotation.edit(svgId, title, text, url);

        // 変更後のannotationをdstにセット
        dst = angular.copy(annotation.get(svgId));

        // 履歴に登録する
        if (setType == UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW) {
            self.setHistory(UNIVERSALVIEWER.Common.ImageViewer.editAction.EDIT, src, dst);
        }
    },

    /**
     * 楕円を描画する
     *
     * @param setType
     * @param parentElement
     * @param svgId
     * @param x
     * @param y
     * @param rx
     * @param ry
     * @param zoomRatio
     * @param stroke
     * @param strokeWidth
     * @param fill
     * @param lineStyle
     */
    ellipse: function(setType, parentElement, svgId, x, y, rx, ry, zoomRatio, stroke, strokeWidth, fill, lineStyle)
    {
        var self = this;
        var figureType = UNIVERSALVIEWER.Common.ImageViewer.figureType.ELLIPSE;
        var id = 0, parentId = 0, x2 = 0, y2 = 0, width = 0, height = 0, annotationTextId = 0, title = '', text = '', url = '';

        self.setAnnotation(setType, parentElement, id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, fill, lineStyle, zoomRatio, annotationTextId, title, text, url);
    },

    /**
     * 四角を描画する
     *
     * @param setType
     * @param parentElement
     * @param svgId
     * @param x
     * @param y
     * @param width
     * @param height
     * @param rx
     * @param ry
     * @param zoomRatio
     * @param stroke
     * @param strokeWidth
     * @param fill
     * @param lineStyle
     */
    rect: function(setType, parentElement, svgId, x, y, width, height, rx, ry, zoomRatio, stroke, strokeWidth, fill, lineStyle)
    {
        var self = this;
        var figureType = UNIVERSALVIEWER.Common.ImageViewer.figureType.RECT;
        var id = 0, parentId = 0, x2 = 0, y2 = 0, annotationTextId = 0, title = '', text = '', url = '';
        self.setAnnotation(setType, parentElement, id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, fill, lineStyle, zoomRatio, annotationTextId, title, text, url);
    },

    /**
     * 矢印を描画する
     *
     * @param setType
     * @param parentElement
     * @param svgId
     * @param x1
     * @param y1
     * @param x2
     * @param y2
     * @param zoomRatio
     * @param stroke
     * @param strokeWidth
     * @param lineStyle
     */
    arrow: function(setType, parentElement, svgId, x1, y1, x2, y2, zoomRatio, stroke, strokeWidth, lineStyle)
    {
        var self = this;
        var figureType = UNIVERSALVIEWER.Common.ImageViewer.figureType.ARROW;
        var id = 0, parentId = 0, rx = 0, ry = 0, width = 0, height = 0, annotationTextId = 0, title = '', text = '', url = '';
        self.setAnnotation(setType, parentElement, id, parentId, figureType, svgId, x1, y1, x2, y2, rx, ry, width, height, stroke, strokeWidth, '', lineStyle, zoomRatio, annotationTextId, title, text, url);
    },

    /**
     * 線を描画する
     *
     * @param setType
     * @param parentElement
     * @param svgId
     * @param x1
     * @param y1
     * @param x2
     * @param y2
     * @param zoomRatio
     * @param stroke
     * @param strokeWidth
     * @param lineStyle
     */
    line: function(setType, parentElement, svgId, x1, y1, x2, y2, zoomRatio, stroke, strokeWidth, lineStyle)
    {
        var self = this;
        var figureType = UNIVERSALVIEWER.Common.ImageViewer.figureType.LINE;
        var id = 0, parentId = 0, rx = 0, ry = 0, width = 0, height = 0, annotationTextId = 0, title = '', text = '', url = '';
        self.setAnnotation(setType, parentElement, id, parentId, figureType, svgId, x1, y1, x2, y2, rx, ry, width, height, stroke, strokeWidth, '', lineStyle, zoomRatio, annotationTextId, title, text, url);
    },

    /**
     * 注釈をいれる
     *
     * @param setType
     * @param parentElement
     * @param svgId
     * @param x
     * @param y
     * @param zoomRatio
     * @param stroke
     */
    annotate: function(setType, parentElement, svgId, x, y, zoomRatio, stroke)
    {
        var self = this;
        var figureType = UNIVERSALVIEWER.Common.ImageViewer.figureType.ANNOTATE;
        var lineStyle = UNIVERSALVIEWER.Common.ImageViewer.lineStyle.SOLID;
        var id = 0, parentId = 0, x2 = 0, y2 = 0, rx = 0, ry = 0, width = 0, height = 0, strokeWidth = 3, fill = '', annotationTextId = 0, title = '', text = '', url = '';
        self.setAnnotation(setType, parentElement, id, parentId, figureType, svgId, x, y, x2, y2, rx, ry, width, height, stroke, strokeWidth, fill, lineStyle, zoomRatio, annotationTextId, title, text, url);

    },

    // ==============================================
    //  マウスイベント関連
    // ==============================================

    /**
     * 注釈用オーバーレイに対するイベント処理を設定する
     *
     * 拡大・縮小
     * 図形・注釈を描画
     *
     * @private
     */
    _setAnnotateEvent: function()
    {
        var self = this;
        var openseadragon = self.openseadragon;
        var edit = self.edit;
        var graphics = self.graphics;

        var parentSelector = '#' + openseadragon.viewerId;
        var overlaySelector = '#' + openseadragon.overlayId;
        var selector = '#' + graphics.svg.id;
        var selectingSelector = '#' + edit.selectingId;

        openseadragon.viewer.addViewerInputHook({
            hooks: [
                {
                    tracker: 'viewer',
                    handler: 'pressHandler',
                    hookHandler: function(event)
                    {
                        // 移動・拡大縮小モードでは何もしない
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            angular.element(selectingSelector).remove();
                            graphics.down(
                                event.position.x,
                                event.position.y,
                                angular.element(overlaySelector).width(),
                                angular.element(overlaySelector).height()
                            );
                            event.preventDefaultAction = true;
                            event.stopBubbling = true;
                        }
                    }
                },
                {
                    tracker: 'viewer',
                    handler: 'moveHandler',
                    hookHandler: function(event)
                    {
                        // 編集モードの場合に処理をする
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            if (graphics.isMoving) {
                                angular.element(selectingSelector).remove();
                                graphics.move(
                                    event.position.x,
                                    event.position.y,
                                    angular.element(overlaySelector).width(),
                                    angular.element(overlaySelector).height()
                                );

                                var svgElement = angular.element(selector);

                                switch (edit.mode) {

                                    // 図形：丸（楕円）
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.ELLIPSE:

                                        if (graphics.width > 0 && graphics.height > 0) {
                                            self.ellipse(UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY, svgElement, edit.selectingId, graphics.x, graphics.y, graphics.rx, graphics.ry, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.fill, edit.lineStyle);
                                        }
                                        break;

                                    // 図形：四角
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.RECT:

                                        if (graphics.width > 0 && graphics.height > 0) {
                                            self.rect(UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY, svgElement, edit.selectingId, graphics.minX, graphics.minY, graphics.width, graphics.height, edit.rectR, edit.rectR, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.fill, edit.lineStyle);
                                        }
                                        break;

                                    // 図形：矢印
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.ARROW:

                                        if (graphics.width > 0 || graphics.height > 0) {
                                            self.arrow(UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY, svgElement, edit.selectingId, graphics.startX, graphics.startY, graphics.currentX, graphics.currentY, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.lineStyle);
                                        }
                                        break;

                                    // 図形：線
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.LINE:

                                        if (graphics.width > 0 || graphics.height > 0) {
                                            self.line(UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY, svgElement, edit.selectingId, graphics.startX, graphics.startY, graphics.currentX, graphics.currentY, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.lineStyle);
                                        }
                                        break;

                                    // 注釈
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.ANNOTATE:
                                        self.annotate(UNIVERSALVIEWER.Common.ImageViewer.setType.TEMPORARY, svgElement, edit.selectingId, graphics.currentX, graphics.currentY, graphics.zoomRatio, edit.stroke);
                                        break;

                                    default:
                                        break;
                                } // End of switch
                            } // End of annotator.flag
                            event.preventDefaultAction = true;
                            return true;
                        } // End of edit.mode != 1
                    }
                },

                /**
                 * Openseadragon viewer マウスボタンを離したとき
                 *
                 * 1.描画を確定する
                 * 2.HistoryManager に操作をスタックして「Undo」可能にする
                 */
                {
                    tracker: 'viewer',
                    handler: 'releaseHandler',
                    hookHandler: function(event)
                    {
                        // 移動・拡大縮小モードでは何もしない
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            if (graphics.isMoving) {
                                angular.element(selectingSelector).remove();
                                graphics.up(
                                    event.position.x,
                                    event.position.y,
                                    angular.element(overlaySelector).width(),
                                    angular.element(overlaySelector).height()
                                );

                                var id = self.annotation.currentId++;
                                var svgElement = angular.element(selector);

                                var result = false;
                                switch (edit.mode) {

                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.ELLIPSE:

                                        // 図形：丸
                                        if (graphics.width > 0 && graphics.height > 0) {
                                            self.ellipse(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, svgElement, id, graphics.x, graphics.y, graphics.rx, graphics.ry, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.fill, edit.lineStyle);
                                        }
                                        break;

                                    // 図形：四角
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.RECT:

                                        if (graphics.width > 0 && graphics.height > 0) {
                                            self.rect(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, svgElement, id, graphics.minX, graphics.minY, graphics.width, graphics.height, edit.rectR, edit.rectR, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.fill, edit.lineStyle);
                                        }
                                        break;

                                    // 図形：矢印
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.ARROW:

                                        if (graphics.width > 0 || graphics.height > 0) {
                                            self.arrow(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, svgElement, id, graphics.startX, graphics.startY, graphics.currentX, graphics.currentY, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.lineStyle);
                                        }
                                        break;

                                    // 図形：線
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.LINE:

                                        if (graphics.width > 0 || graphics.height > 0) {
                                            self.line(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, svgElement, id, graphics.startX, graphics.startY, graphics.currentX, graphics.currentY, graphics.zoomRatio, edit.stroke, edit.strokeWidth, edit.lineStyle);
                                        }
                                        break;

                                    // 注釈
                                    case UNIVERSALVIEWER.Common.ImageViewer.mode.ANNOTATE:

                                        self.annotate(UNIVERSALVIEWER.Common.ImageViewer.setType.DRAW, svgElement, id, graphics.currentX, graphics.currentY, graphics.zoomRatio, edit.stroke);
                                        break;

                                    default:
                                        result = true;
                                        break;
                                }
                            }
                            event.preventDefaultAction = true;
                            event.stopBubbling = true;
                            return result;
                        }
                    }
                },
                {
                    tracker: 'viewer',
                    handler: 'scrollHandler',
                    hookHandler: function(event)
                    {
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            // Disable mousewheel zoom on the viewer and let the original mousewheel events bubble
                            event.preventDefaultAction = true;
                            return true;
                        }
                    }
                },
                {
                    tracker: 'viewer',
                    handler: 'dragHandler',
                    hookHandler: function(event)
                    {
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            // Disable mousewheel zoom on the viewer and let the original mousewheel events bubble
                            event.preventDefaultAction = true;
                            return true;
                        }
                    }
                },
                {
                    tracker: 'viewer',
                    handler: 'dragEndHandler',
                    hookHandler: function(event)
                    {
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            // Disable mousewheel zoom on the viewer and let the original mousewheel events bubble
                            event.preventDefaultAction = true;
                            return true;
                        }
                    }
                },
                {
                    tracker: 'viewer',
                    handler: 'pinchHandler',
                    hookHandler: function(event)
                    {
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            // Disable mousewheel zoom on the viewer and let the original mousewheel events bubble
                            event.preventDefaultAction = true;
                            return true;
                        }
                    }
                },
                {
                    tracker: 'viewer',
                    handler: 'clickHandler',
                    hookHandler: function(event)
                    {
                        if (edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.NONE &&
                            edit.mode != UNIVERSALVIEWER.Common.ImageViewer.mode.MOVE &&
                            edit.isViewOverlay == true &&
                            edit.isViewModal == false)
                        {
                            // Disable mousewheel zoom on the viewer and let the original mousewheel events bubble
                            event.preventDefaultAction = true;
                            return true;
                        }
                    }
                }
            ]
        });
    },

    _transform: function(el) {
        var self = this;
        var openseadragon = self.openseadragon;
        var zoom = openseadragon.viewer.viewport.viewportToImageZoom(openseadragon.viewer.viewport.getZoom(true));
        el.transform('S'+zoom+','+zoom+',0,0');
    }
};
