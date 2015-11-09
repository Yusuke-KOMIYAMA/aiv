/**
 * Created on 2015/02/20.
 */
'use strict';

var rooturl = "http://" + location.host + "/binder/";
var loginurl = "http://" + location.host + "/binder/login/";
var logouturl = "http://" + location.host + "/binder/login/logout/";
var imagedir = "http://" + location.host + "/binder/media/download/";

var UNIVERSALVIEWER = UNIVERSALVIEWER || {};

UNIVERSALVIEWER.Config = {
    rootUrl: rooturl,
    loginUrl: loginurl,
    logoutUrl: logouturl,
    imageDir: imagedir,
    largeThumb: 'large/',
    middleThumb: 'middle/',
    smallThumb: 'small/',
    opensadragonPrefixUrl: 'app/webroot/lib/openseadragon/images/'
};
