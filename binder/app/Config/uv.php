<?php
$hostname = $_SERVER['SERVER_NAME'];
$port = $_SERVER['SERVER_PORT'];
$config["server"]["integration_server_url"] = "http://{$hostname}:{$port}/binder/login";
//$config["server"]["language"] = "ja-JP";
$config["server"]["language"] = "en-US";

/**
 * Single sign on (SSO) 設定
 */
$config["login"]["authorization_endpoint"] = "http://XXXXX.co.jp:18080/openam/oauth2/authorize";
$config["login"]["redirect_uri"] = "http://{$hostname}:{$port}/binder/login/signup/";
$config["login"]["loggedin_uri"] = "http://{$hostname}:{$port}/binder/binder/";
$config["login"]["token_endpoint"] = "http://XXXXX.co.jp:18080/openam/oauth2/access_token";
$config["login"]["userinfo_endpoint"] = "http://XXXXX.co.jp:18080/openam/oauth2/userinfo";
$config["login"]["issue"] = "http://XXXXX.co.jp:18080/openam";
$config["login"]["client_id"] = "YYYYYYYY";
$config["login"]["client_secret"] = "ZZZZZZZZ";
$config["login"]["logout_endpoint"] = "http://XXXXX.co.jp:18080/XYZ/logout";

/**
 * ログイン設定
 */
$config["login"]["login_uri"] = "http://{$hostname}:{$port}/binder/login/";
$config["login"]["logout_uri"] = "http://{$hostname}:{$port}/binder/login/logout/";

/**
 * 1.OS temporary upload dir ... /tmp/XXXXX
 * 2.APP temporary upload dir ... /usr/local/files/tmp/{$user_id}
 * 3.APP operation files dir (It's temporary too) ... /usr/local/files/uploaded/{$user_id}
 * 4.APP permanent dir ... /usr/local/files/user/{$user_id}
 */
$config["media"]["download_url"] = "http://{$hostname}:{$port}/binder/media/download/";
$config["media"]["tmp_dir"]    = "/usr/local/webapp/binder/files/tmp";
$config["media"]["upload_dir"] = "/usr/local/webapp/binder/files/uploaded";
$config["media"]["user_dir"]   = "/usr/local/webapp/binder/files/user";
$config["media"]["type"]["small"] = "small";
$config["media"]["type"]["middle"] = "middle";
$config["media"]["type"]["large"] = "large";
$config["media"]["dummy"] = "dummy.png";

$config["media"]["thumb"]["small"]["width"] = 200;
$config["media"]["thumb"]["small"]["height"] = 200;
$config["media"]["thumb"]["middle"]["width"] = 400;
$config["media"]["thumb"]["middle"]["height"] = 400;
$config["media"]["thumb"]["large"]["width"] = 600;
$config["media"]["thumb"]["large"]["height"] = 600;

/**
 * Setting for PDF to Image converter
 */
$config["media"]["ghostscript"] = "/usr/bin/gs";
$config["media"]["pdftoimagetype"] = 'jpeg';
$config["media"]["dpi"] = 150;
$config["media"]["quality"] = 100;


/**
 * データベースバックアップ
 */
$config["dbbackup"]["mysqldumpPath"] = "/usr/bin/mysqldump";
$config["dbbackup"]["outPath"] = "/usr/local/webapp/binder/mysql/backup/";


/**
 * シェル：ペンサーバーインポート処理
 */
$config["shell"]["pen"]["path"] = "/usr/local/webapp/binder/import/";
