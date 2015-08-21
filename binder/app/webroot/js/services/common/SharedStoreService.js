/**
 * SharedStoreService.js
 *
 * 各画面で持ち回しのデータを保持するオブジェクト
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Service = UNIVERSALVIEWER.Service || {};
UNIVERSALVIEWER.Service.Common = UNIVERSALVIEWER.Service.Common || {};

UNIVERSALVIEWER.Service.Common.SharedStoreServiceClass = function()
{
    // ページタイトル（translateキー）
    this._title = 'home.title';
    // クラス名（#page-wrapper と同じ要素に付与するクラス）
    this._className = 'home';

    // バインダー自動生成時のデフォルトタイトル
    this._defaultBinderTitle = '';
    this._defaultBinderText = '';

    // 未保存のままページ遷移するときの確認メッセージ
    this._confirmMovePageMessage = '';
    // バインダーが未保存の場合
    this._notSaveBinderMessage = '';
    // オリジナルページ削除メッセージ
    this._deleteOriginalPageMessage = '';

    // 画像ビューア用メッセージ
    this._imageViewerMessage = {
        'close':'',
        'edit':'',
        'delete':'',
        'confirmDelete':''
    };

    /**
     * バインダー新規作成・バインダー編集で選択されているバインダー
     * 子要素としての「バインダーページ」もこのオブジェクトを参照すること
     */
    this._binder = null;

    /**
     * オリジナルページ検索関連（バインダー新規生成・バインダーページ選択）
     *
     * 1.オリジナルページリスト
     * 2.オリジナルページ検索条件
     * 3.操作対象バインダー
     */
    // オリジナルページリスト
    this._originalPages = [];
    this._originalPage = null;

    /**
     * バインダー関連
     */
    // バインダー一覧
    this._binders = [];

    /**
     * バインダーページ関連：検索結果用
     */
    // バインダーページ一覧
    this._binderPages = [];
    this._binderPage = null;

    this._timelines = [];
    this._timelineStartSlide = 0;

    this._returnView = null;

    this._allTags = [];

};
UNIVERSALVIEWER.Service.Common.SharedStoreServiceClass.prototype = {

    // ####################################################
    // 初期化関連
    // ####################################################

    setReturnView: function(view)
    {
        this.returnView = view;
    },

    // ####################################################
    // getter/setter
    // ####################################################

    get title() { return this._title; },
    set title(title) { this._title = title; },
    get className() { return this._className; },
    set className(className) { this._className = className; },

    get defaultBinderTitle() { return this._defaultBinderTitle; },
    set defaultBinderTitle(defaultBinderTitle) { this._defaultBinderTitle = defaultBinderTitle; },
    get defaultBinderText() { return this._defaultBinderText; },
    set defaultBinderText(defaultBinderText) { this._defaultBinderText = defaultBinderText; },

    get confirmMovePageMessage() { return this._confirmMovePageMessage; },
    set confirmMovePageMessage(confirmMovePageMessage) { this._confirmMovePageMessage = confirmMovePageMessage; },

    get notSaveBinderMessage() { return this._notSaveBinderMessage; },
    set notSaveBinderMessage(notSaveBinderMessage) { this._notSaveBinderMessage = notSaveBinderMessage; },
    get deleteOriginalPageMessage() { return this._deleteOriginalPageMessage; },
    set deleteOriginalPageMessage(deleteOriginalPageMessage) { this._deleteOriginalPageMessage = deleteOriginalPageMessage; },

get imageViewerMessage() { return this._imageViewerMessage; },
    set imageViewerMessage(imageViewerMessage) { this._imageViewerMessage = imageViewerMessage; },

    get originalPages() { return this._originalPages; },
    set originalPages(originalPages) { this._originalPages = originalPages; },
    get originalPage() { return this._originalPage; },
    set originalPage(originalPage) { this._originalPage = originalPage; },
    get binder() { return this._binder; },
    set binder(binder) { this._binder = binder; },

    get binders() { return this._binders; },
    set binders(binders) { this._binders = binders; },

    get binderPages() { return this._binderPages; },
    set binderPages(binderPages) { this._binderPages = binderPages; },
    get binderPage() { return this._binderPage; },
    set binderPage(binderPage) { this._binderPage = binderPage; },

    get timelines() { return this._timelines; },
    set timelines(timelines) { this._timelines = timelines; },
    get timelineStartSlide() { return this._timelineStartSlide; },
    set timelineStartSlide(timelineStartSlide) { this._timelineStartSlide = timelineStartSlide; },

    get returnView() { return this._returnView; },
    set returnView(returnView) { this._returnView = returnView; },

    get allTags() { return this._allTags; },
    set allTags(allTags) { this._allTags = allTags; }

};

