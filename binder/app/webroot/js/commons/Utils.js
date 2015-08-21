/**
 * Created on 2015/01/06.
 */
'use strict';

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};
UNIVERSALVIEWER.Common = UNIVERSALVIEWER.Common || {};

UNIVERSALVIEWER.Common.Utils = {

    /**
     * クラス生成の為の継承関係生成関数
     *
     * @param ctor 子クラス
     * @param superCtor 親クラス
     */
    inherits: function(ctor, superCtor) {
        ctor.super_ = superCtor;
        ctor.prototype = Object.create(superCtor.prototype, {
            constructor: {
                value: ctor,
                enumerable: false,
                writable: true,
                configurable: true
            }
        });
    },

    /**
     * Dateオブジェクトを日付をハイフン区切りフォーマットに変換する
     *
     * @param date Dateオブジェクト
     * @returns {string} yyyy-mm-dd 形式の日付文字列
     */
    originalDateFormat: function(date) {
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var date = date.getDate();
        return year + '-' + ("00" + month).slice(-2) + '-' + ("00" + date).slice(-2);
    },

    /**
     * Dateオブジェクトを日付をカンマ区切りフォーマットに変換する
     *
     * @param date Dateオブジェクト
     * @returns {string} yyyy-mm-dd 形式の日付文字列
     */
    timelineDateFormat: function(date) {
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var date = date.getDate();
        return year + ',' + ("00" + month).slice(-2) + ',' + ("00" + date).slice(-2);
    },

    /**
     * Window のロード前では、 onLoad に、 ロード後ではそのタイミングで jquery.on() を設定する
     *
     * @param selector
     * @param eventName
     * @param func
     */
    onLoad: function(selector, eventName, func) {

        var fn = function() {
            angular.element(selector).on(eventName, func)
        };
        if (document.readyState === 'complete') {
            fn();
        }
        else {
            angular.element(window).load(fn);
        }
    },

    /**
     * Window のロード前では、 onLoad に、 ロード後ではそのタイミングで jquery.on() を設定する
     *
     * @param selector
     * @param child
     * @param eventName
     * @param func
     */
    onLoadWithChild: function(selector, child, eventName, func) {

        var fn = function() {
            angular.element(selector).on(eventName, child, func)
        };
        if (document.readyState === 'complete') {
            fn();
        }
        else {
            angular.element(window).load(fn);
        }
    },

    /**
     * 配列の要素数チェック
     *
     * @param obj
     * @returns {number}
     */
    count_array: function(obj) {

        //配列かどうかの判定
        if(!(obj instanceof Array)){
            return -1;
        }

        //要素数カウント
        var cnt = 0;
        for(var i = 0; i < obj.length; i++) {
            //undefinedじゃなかったらカウント
            if(typeof obj[i] !== "undefined") {
                cnt++;
            }
        }
        return cnt;
    },

    escapeHtml: (function (String) {
        var escapeMap = {
            '&': '&amp;',
            "'": '&#x27;',
            '`': '&#x60;',
            '"': '&quot;',
            '<': '&lt;',
            '>': '&gt;'
        };
        var escapeReg = '[';
        var reg;
        for (var p in escapeMap) {
            if (escapeMap.hasOwnProperty(p)) {
                escapeReg += p;
            }
        }
        escapeReg += ']';
        reg = new RegExp(escapeReg, 'g');
        return function escapeHtml (str) {
            str = (str === null || str === undefined) ? '' : '' + str;
            return str.replace(reg, function (match) {
                return escapeMap[match];
            });
        };
    }(String))
};
