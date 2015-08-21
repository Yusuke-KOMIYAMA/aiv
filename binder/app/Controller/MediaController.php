<?php
/**
 * MediaController.php
 * Date: 2015/02/23
 */
App::uses('AppController', 'Controller');
App::import('Vendor', 'Util', array('file' => 'Util.class.php'));

class MediaController extends AppController
{
    public $name = 'Upload';
    public $uses = array('Binder', 'BinderPage', 'OriginalPageHeader', 'OriginalPage', 'Image', 'TagRelation', 'Tag', 'Log');
    public $components = array('RequestHandler');

    public $layout = "";

    public $user;
    public $loginUser;

    /**
     * この関数はコントローラの各アクションの前に実行されます。
     *
     * 1.認証済みチェック
     */
    public function beforeFilter()
    {
        Configure::load("uv.php");
        Configure::load("log.php");

        // 認証済みかどうかチェックする
        if (!$this->Session->check('user')) {
            // ログイン画面へリダイレクト
            $this->response->header('Location', Configure::read("login.login_uri"));
        }

        // XML 対応
        if ($this->action === 'xml') {
            $this->response->header('Access-Control-Allow-Origin: *');
            $this->response->header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
            $this->response->header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        }

        $this->user = $this->Session->read("user");
        $this->loginUser = $this->Session->read('loginUser');

    }

    /**
     * ユーザー制限を設けてファイルをダウンロード
     */
    public function download()
    {
        $path = func_get_args();

        $this->autoLayout = false;//自動レイアウト
        $this->autoRender = false;//自動レンダリング

        $sub_dir = '';
        $deep_zoom_dir = '';
        $thumb_dir = '';

        try {

            // ------------------------------
            // リクエスト
            // ------------------------------

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報取得
            $userId = $this->user["User"]["id"];

            if (count($path) == 1) {
                $thumb_type = null;
                $file_name = $path[0];
            } elseif (count($path) == 2) {
                $thumb_type = $path[0];
                $file_name = $path[1];
            } elseif (count($path) == 3) {
                $thumb_type = null;
                $file_name = $path[2];
                $sub_dir = DS . $path[0] . DS . $path[1];
                $deep_zoom_dir = DS . 'dzi';
            } else {
                throw new Exception("No image found.");
            }

            if ($file_name == null) {
                throw new Exception("No image found.");
            }

            // ファイル名
            $file_info = pathinfo($file_name);

            $user_dir = Configure::read("media.user_dir") . DS . $userId;

            if ($file_info['extension'] === 'dzi') {
                $deep_zoom_dir = DS . 'dzi';
            }

            if (!is_null($thumb_type)) {
                $thumb_dir = DS . 'thumb' . DS . Configure::read("media.type." . $thumb_type);
            }

            $file_path = $user_dir . $thumb_dir . $deep_zoom_dir . $sub_dir . DS . $file_name;

            // response->file()でダウンロードもしくは表示するファイルをセット
            $this->response->file($file_path);

            // 単にダウンロードさせる場合はこれを使う
            //$this->response->download($file_name);

            // pdfをブラウザ上で開かせるような場合はこちら
            $this->response->body($file_name);

        }
        // 全てのエラー・例外時でダミー画像を返すようにする。
        catch (Exception $e) {
            $file_name = Configure::read("media.dummy");
            $this->response->file(IMAGES . $file_name);
            $this->response->body($file_name);
        }
    }

    /**
     * バインダーを印刷用PDFに変換してダウンロードする
     *
     * 注釈なし： /media/pdf/:id
     * 注釈あり： /media/pdf/annotation/:id
     */
    public function pdfs()
    {
        $path = func_get_args();

        // ------------------------------
        // 画面設定
        // ------------------------------

        $this->response->disableCache(); // キャッシュしない

        $this->autoLayout = false; // 自動レイアウトしない
        $this->autoRender = false; // 自動レンダリングしない

        try {

            // ------------------------------
            // リクエスト
            // ------------------------------

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");
            $userId = $this->user["User"]["id"];

            // 画像リクエスト取得
            if (count($path) == 1) {
                // 注釈なし
                $id = $path[0];
                $isAnnotation = false;
            } elseif (count($path) == 2 && $path[0] == 'annotation') {
                // 注釈あり /media/pdf/annotation/id
                $isAnnotation = true;
                $id = $path[1];
            } else {
                throw new BadRequestException("[MediaController] Image id not found in request data.");
            }

            // ------------------------------
            // データ取得
            // ------------------------------

            // 選択した画像を取得
            $results = $this->Binder->selectWithByIdAndUserId($id, $userId);

            // バインダーページがあること。
            if (!$results || count($results[0]["BinderPage"]) == 0) {
                throw new BadRequestException("[MediaController] Cannot find image in this binder.");
            }

            // ------------------------------
            // 情報を生成
            // ------------------------------

            // ディレクトリ情報生成
            $userDir = Configure::read("media.user_dir") . DS . $userId;
            $tmpDir = Configure::read("media.tmp_dir") . DS . $userId;
            $mediaDir = $tmpDir . DS . 'media';

            // ------------------------------
            // media ディレクトリ生成
            // ------------------------------

            // media ディレクトリがなかったら生成する
            if (!file_exists($mediaDir)) {
                mkdir($mediaDir);
                chmod($mediaDir, 0777);
            }

            // ------------------------------
            // アノテーション付画像を生成
            // ------------------------------

            $binderPages = $results[0]["BinderPage"];
            $pdfPages = array();
            $tmpImages = array();
            foreach ($binderPages as $binderPage) {

                if ($isAnnotation) {
                    // アノテーション情報の取得
                    $annotations = $binderPage["VAnnotation"];

                    // 画像にアノテーションを合成する
                    App::import('Vendor', 'AnnotationUtil', array('file' => 'AnnotationUtil.class.php'));

                    // media dir に アノテーション画像を生成
                    $Util = new AnnotationUtil($userDir . "/" . $binderPage["Image"]["localFileName"], $mediaDir, $tmpDir, '1.0', 'UTF-8');
                    $tmp = $Util->composite($annotations);
                    if (null == $tmp) {
                        throw new CakeException("[MediaController] Failed to create image.");
                    }
                    array_push($tmpImages, $tmp); // 一時生成ファイルなのであとで削除
                } else {
                    $tmp = $userDir . "/" . $binderPage["Image"]["localFileName"];
                }

                array_push($pdfPages, $tmp);
            }

            // ------------------------------
            // PDFを生成
            // ------------------------------

            // 出力ファイル（PDF）
            $dstName = $results[0]["Binder"]["title"] . ".pdf";
            $dst = $tmpDir . "/" . "binder_" . $results[0]["Binder"]["id"] . ".pdf";

            // PDFの元画像設定
            $image = "";
            foreach ($pdfPages as $pdfPage) {
                if ($image !== "") {
                    $image = $image . " ";
                }
                $image = $image . $pdfPage;
            }

            // PDF生成
            set_time_limit(600);
            $this->log("convert $image -compress jpeg -resize 1753x1240 -units PixelsPerInch -density 150x150 -gravity center -background white -extent 1753x1240 -page 1753x1240 $dst", LOG_DEBUG);
            exec("convert $image -compress jpeg -resize 1753x1240 -units PixelsPerInch -density 150x150 -gravity center -background white -extent 1753x1240 -page 1753x1240 $dst");

            // 一時画像を削除
            foreach ($tmpImages as $tmpImg) {
                unlink($tmpImg);
            }

            // ------------------------------
            // レスポンス
            // ------------------------------

            // Logging: Binder PDF downloaded.
            $this->Log->write("1021", $this->loginUser['User']['id'], "Binder PDF downloaded. LoginUser:".$this->loginUser['User']['id'].",User:".$this->user['User']['id'].",BinderID:".$id);

            // 生成した画像を返す
            $this->response->file($dst);
            $this->response->body($dstName);

        }
        catch (CakeException $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
        catch (Exception $e) {
            // エラー・例外時は何も返さない。
        }
    }

    /**
     * 画像を印刷用PDFに変換してダウンロードする
     *
     * 注釈なし： /media/pdf/:id
     * 注釈あり： /media/pdf/annotation/:id
     */
    public function pdf()
    {
        $path = func_get_args();

        $this->response->disableCache(); // キャッシュしない

        $this->autoLayout = false; // 自動レイアウトしない
        $this->autoRender = false; // 自動レンダリングしない

        try {

            // ------------------------------
            // リクエスト
            // ------------------------------

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");
            $userId = $this->user["User"]["id"];

            // 画像リクエスト取得
            if (count($path) == 1) {
                // 注釈なし
                $id = $path[0];
                $isAnnotation = false; // 注釈なし
                $isOriginal = false; // オリジナルページではない
            } elseif (count($path) == 2 && $path[0] == 'annotation') {
                // 注釈あり /media/pdf/annotation/filename
                $id = $path[1];
                $isAnnotation = true; // 注釈あり
                $isOriginal = false; // オリジナルページではない
            } elseif (count($path) == 2 && $path[0] == 'original') {
                $id = $path[1];
                $isAnnotation = false; // 注釈なし
                $isOriginal = true; // オリジナルページ
            } else {
                throw new BadRequestException("[MediaController] Image id not found in request data.");
            }

            // ------------------------------
            // データ取得
            // ------------------------------

            // 選択した画像を取得
            if ($isOriginal) {
                $results = $this->OriginalPage->selectWithByIdAndUserId($id, $userId);
            } else {
                $results = $this->BinderPage->selectWithByIdAndUserId($id, $userId);
            }


            // ------------------------------
            // 情報を生成
            // ------------------------------

            // ディレクトリ情報生成
            $userDir = Configure::read("media.user_dir") . DS . $userId;
            $tmpDir = Configure::read("media.tmp_dir") . DS . $userId;
            $mediaDir = $tmpDir . DS . 'media';

            // 元画像
            $srcName = $results[0]["Image"]["localFileName"];
            $src = $userDir . "/" . $srcName;
            $srcInfo = pathinfo($src);

            // 出力ファイル（PDF）
            $dstName = $srcInfo["filename"] . ".pdf";
            $dst = $tmpDir . "/" . $dstName;


            // ------------------------------
            // media ディレクトリ生成
            // ------------------------------

            // media ディレクトリがなかったら生成する
            if (!file_exists($mediaDir)) {
                mkdir($mediaDir);
                chmod($mediaDir, 0777);
            }

            // ------------------------------
            // アノテーション付画像を生成
            // ------------------------------

            if ($isAnnotation) {
                // アノテーション情報の取得
                $annotations = $results[0]["VAnnotation"];

                // 画像にアノテーションを合成する
                App::import('Vendor', 'AnnotationUtil', array('file' => 'AnnotationUtil.class.php'));
                $Util = new AnnotationUtil($src, $mediaDir, $tmpDir, '1.0', 'UTF-8');
                $tmp = $Util->composite($annotations);
                if (null == $tmp) {
                    throw new CakeException("[MediaController] Failed to create image.");
                }
            }

            // ------------------------------
            // PDFを生成
            // ------------------------------

            // PDFの元画像設定
            $image = ($isAnnotation) ? $tmp : $src;

            // PDF生成
            set_time_limit(600);
            $this->log("convert $image -compress jpeg -resize 1753x1240 -units PixelsPerInch -density 150x150 -gravity center -background white -extent 1753x1240 -page 1753x1240 $dst", LOG_DEBUG);
            exec("convert $image -compress jpeg -resize 1753x1240 -units PixelsPerInch -density 150x150 -gravity center -background white -extent 1753x1240 -page 1753x1240 $dst");

            // アノテーション付画像を生成した場合は一時ファイルを削除
            if ($isAnnotation && !empty($tmp)) {
                unlink($tmp);
            }

            // ------------------------------
            // レスポンス
            // ------------------------------

            if (!$isOriginal) {
                // Logging: BinderPage PDF downloaded.
                $this->Log->write("1022", $this->loginUser['User']['id'], "BinderPage PDF downloaded. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",BinderPageID:" . $id);
            }
            else {
                // Logging: OriginalPage PDF downloaded.
                $this->Log->write("1023", $this->loginUser['User']['id'], "OriginalPage PDF downloaded. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",OriginalPageID:" . $id);
            }

            // 生成した画像を返す
            $this->response->file($dst);
            $this->response->body($dstName);

        }
        catch (CakeException $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
        catch (Exception $e) {
            // エラー・例外時は何も返さない
        }
    }

    /**
     * バインダーページ画像をダウンロードする
     */
    public function output()
    {
        $path = func_get_args();

        $this->response->disableCache(); // キャッシュしない

        $this->autoLayout = false; // 自動レイアウトしない
        $this->autoRender = false; // 自動レンダリングしない

        try {

            // ------------------------------
            // リクエスト
            // ------------------------------

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");
            $userId = $this->user["User"]["id"];

            // 画像リクエスト取得
            if (count($path) == 1) {
                // オリジナル画像
                $id = $path[0];
                $isAnnotation = false;
                $isOriginal = false;
            } elseif (count($path) == 2 && $path[0] === 'annotation') {
                $id = $path[1];
                $isAnnotation = true;
                $isOriginal = false;
            } elseif (count($path) == 2 && $path[0] === 'original') {
                $id = $path[1];
                $isAnnotation = false;
                $isOriginal = true;
            } else {
                throw new BadRequestException("[MediaController] Image id not found in request data.");
            }

            // ------------------------------
            // データ取得
            // ------------------------------

            // 選択した画像を取得
            if ($isOriginal) {
                $results = $this->OriginalPage->selectWithByIdAndUserId($id, $userId);
            } else {
                $results = $this->BinderPage->selectWithByIdAndUserId($id, $userId);
            }

            // ------------------------------
            // 情報を生成
            // ------------------------------

            // ディレクトリ情報
            $userDir = Configure::read("media.user_dir") . DS . $userId;
            $tmpDir = Configure::read("media.tmp_dir") . DS . $userId;
            $mediaDir = $tmpDir . DS . 'media';

            // 元画像
            $srcName = $results[0]["Image"]["localFileName"];
            $src = $userDir . "/" . $srcName;

            // ------------------------------
            // media ディレクトリ生成
            // ------------------------------

            // media ディレクトリがなかったら生成する
            if (!file_exists($mediaDir)) {
                mkdir($mediaDir);
                chmod($mediaDir, 0777);
            }

            // ------------------------------
            // アノテーション付画像を生成
            // ------------------------------

            if ($isAnnotation) {
                // アノテーション情報の取得
                $annotations = $results[0]["VAnnotation"];

                // 画像にアノテーションを合成する
                App::import('Vendor', 'AnnotationUtil', array('file' => 'AnnotationUtil.class.php'));
                $Util = new AnnotationUtil($src, $mediaDir, $tmpDir, '1.0', 'UTF-8');
                $out = $Util->composite($annotations);
                if (null == $out) {
                    throw new CakeException("[MediaController] Failed to create image.");
                }
            }

            // ------------------------------
            // レスポンス
            // ------------------------------

            // 出力画像（アノテーションあり・なし）
            $dst = ($isAnnotation) ? $out : $src;

            if (!$isOriginal) {
                // Logging: BinderPage Image downloaded.
                $this->Log->write("1024", $this->loginUser['User']['id'], "BinderPage Image downloaded. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",BinderPageID:" . $id);
            }
            else {
                // Logging: OriginalPage Image downloaded.
                $this->Log->write("1025", $this->loginUser['User']['id'], "BinderPage Image downloaded. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",OriginalPageID:" . $id);
            }

            // オリジナル画像を返す
            $this->response->file($dst);
            $this->response->download($srcName);

        }
        catch (CakeException $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
        catch (Exception $e) {
        }
    }

    /**
     * XMLを出力する
     */
    public function data()
    {
        $path = func_get_args();

        $this->response->disableCache();

        $this->autoLayout = false;//自動レイアウト
        $this->autoRender = false;//自動レンダリング

        try {

            // ------------------------------
            // リクエスト
            // ------------------------------

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");
            $userId = $this->user["User"]["id"];

            // 画像リクエスト取得
            $model = $path[0];
            $id = $path[1];

            // Binder, BinderPage のみ受け付ける
            if (!in_array($model, array('binder', 'binderPage')) || !$id) {
                throw new BadRequestException("[MediaController] Request url is invalid.");
            }

            // ------------------------------
            // データ取得
            // ------------------------------

            // 選択した画像を取得
            switch (ucfirst($model)) {

                case 'Binder':
                    $results = $this->Binder->selectWithByIdAndUserId($id, $userId);
                    if (!$results) {
                        throw new NotFoundException("[MediaController] Request data does not found.");
                    }
                    $original = array();
                    foreach ($results[0]["BinderPage"] as $binderPage) {
                        $original[$binderPage["id"]] = $this->OriginalPage->selectWithByIdAndUserId($binderPage["originalPageId"], $userId);
                    }
                    break;

                case 'BinderPage':
                    $results = $this->BinderPage->selectWithByIdAndUserId($id, $userId);
                    if (!$results) {
                        throw new NotFoundException("[MediaController] Request data does not found.");
                    }
                    $original = $this->OriginalPage->selectWithByIdAndUserId($results[0]["BinderPage"]["originalPageId"], $userId);
                    break;

                default:
                    break;
            }

            // ------------------------------
            // 情報を生成
            // ------------------------------

            // ディレクトリ情報
            $userDir = Configure::read("media.user_dir") . DS . $userId;
            $tmpDir = Configure::read("media.tmp_dir") . DS . $userId;
            $mediaDir = $tmpDir . DS . 'media';

            $xmlDir = $mediaDir . DS . 'xml';
            $imgDir = $xmlDir . DS . 'img';

            $xmlFilename = ucfirst($model) . ".xml";
            $zipFilename = "download.zip";

            $zipFilepath = $mediaDir . DS . $zipFilename;

            // ------------------------------
            // media ディレクトリ生成
            // ------------------------------

            // media ディレクトリがなかったら生成する
            if (!file_exists($mediaDir)) {
                mkdir($mediaDir);
                chmod($mediaDir, 0777);
            }

            // ------------------------------
            // ZIP用ディレクトリの生成
            // ------------------------------

            // ディレクトリがあったら、中身を空にする
            if (file_exists($xmlDir)) {
                Util::rmdirAll($xmlDir);
            }
            // ZIPファイルがあったら、削除する
            if (file_exists($zipFilepath)) {
                unlink($zipFilepath);
            }
            // 生成・モードの変更
            mkdir($xmlDir, 0777);
            chmod($xmlDir, 0777);
            mkdir($imgDir, 0777);
            chmod($imgDir, 0777);

            // ------------------------------
            // XMLに付随する画像生成
            // ------------------------------

            switch (ucfirst($model)) {

                case 'Binder':

                    if (!is_null($results[0]["Image"]["localFileName"])) {
                        $coverName = $results[0]["Image"]["localFileName"];
                        $cover = $userDir . "/" . $coverName;
                        copy($cover, $imgDir . DS . "/cover_" . $coverName);
                        // 生成された画像情報にXMLデータを書き換え
                        $results[0]["Image"]["localFileName"] = "cover_" . $coverName;
                    }

                    foreach ($results[0]["BinderPage"] as $binderPage) {
                        // 画像情報の取得
                        $srcName = $binderPage["Image"]["localFileName"];
                        $src = $userDir . "/" . $srcName;

                        // アノテーション情報の取得
                        $annotations = $binderPage["VAnnotation"];

                        // 画像にアノテーションを合成する
                        App::import('Vendor', 'AnnotationUtil', array('file' => 'AnnotationUtil.class.php'));
                        $Util = new AnnotationUtil($src, $imgDir, $tmpDir, '1.0', 'UTF-8');
                        $out = $Util->composite($annotations);
                        if (null == $out) {
                            throw new CakeException("[MediaController] Failed to create image.");
                        }

                        // 生成された画像情報にXMLデータを書き換え
                        $infoOut = pathinfo($out);
                        $binderPage["Image"]["localFileName"] = $infoOut['basename'];
                    }
                    break;

                case 'BinderPage':

                    // 画像情報の取得
                    $srcName = $results[0]["Image"]["localFileName"];
                    $src = $userDir . "/" . $srcName;

                    // アノテーション情報の取得
                    $annotations = $results[0]["VAnnotation"];

                    // 画像にアノテーションを合成する
                    App::import('Vendor', 'AnnotationUtil', array('file' => 'AnnotationUtil.class.php'));
                    $Util = new AnnotationUtil($src, $imgDir, $tmpDir, '1.0', 'UTF-8');
                    $out = $Util->composite($annotations);
                    if (null == $out) {
                        throw new CakeException("[MediaController] Failed to create image.");
                    }

                    // 生成された画像情報にXMLデータを書き換え
                    $infoOut = pathinfo($out);
                    $results[0]["Image"]["localFileName"] = $infoOut['basename'];

                    break;

                default:
                    throw new BadRequestException("[MediaController] Image id not found in request data.");
                    break;
            }

            // ------------------------------
            // XMLを生成
            // ------------------------------

            $formatted = $this->{ucfirst($model)}->xmlFormat($results, $original);
            $xmlObject = Xml::fromArray($formatted, array('format' => 'tags'));
            $xmlObject->asXML($xmlDir . DS . $xmlFilename);

            // ------------------------------
            // 圧縮
            // ------------------------------

            // アーカイブオブジェクト生成
            $zip = new ZipArchive();

            set_time_limit(600);
            $archive = $zip->open($zipFilepath, ZipArchive::CREATE);

            $baselen = mb_strlen($xmlDir);

            // Iterator を使って処理をまとめる
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $xmlDir,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
                ),
                RecursiveIteratorIterator::SELF_FIRST
            );
            // アーカイブ生成
            foreach ($iterator as $pathname => $info) {
                $localpath = mb_substr($pathname, $baselen);
                if ($info->isFile()) {
                    $zip->addFile($pathname, $localpath);
                } else {
                    $archive = $zip->addEmptyDir($localpath);
                }
            }
            // 閉じる
            $zip->close();

            // アーカイブ後に一時ディレクトリを削除
            if (file_exists($xmlDir)) {
                Util::rmdirAll($xmlDir);
            }

            // ------------------------------
            // レスポンス
            // ------------------------------

            // Logging
            switch (ucfirst($model)) {
                case 'Binder':
                    // Logging: Binder XML downloaded.
                    $this->Log->write("1026", $this->loginUser['User']['id'], "Binder XML downloaded. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",BinderID:" . $id);
                    break;
                case 'BinderPage':
                    // Logging: BinderPage XML downloaded.
                    $this->Log->write("1027", $this->loginUser['User']['id'], "BinderPage Image downloaded. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",BinderPageID:" . $id);
                    break;
                default:
                    break;
            }

            // レスポンス処理実行
            $this->response->statusCode(200);
            $this->response->file($zipFilepath);
            $this->response->download($zipFilename);

        }
        catch (CakeException $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
        catch (Exception $e) {
            // エラー・例外は何も返さない
        }
    }

    /**
     * ユーザー制限ありのファイルアップロード処理
     *
     * 1.オリジナルファイル: {user_dir}/{file_name.ext}
     * 2.サムネイルファイル: {user_dir}/thumb/{size}/{file_name.ext}
     * 3.Deepzoom Image: {user_dir}/dzi/{file_name}.dzi
     */
    public function upload()
    {
        $this->autoLayout = false;//自動レイアウト
        $this->autoRender = false;//自動レンダリング
        $userId = $this->user["User"]["id"];

        try {

            $tmp_dir = Configure::read("media.tmp_dir") . DS . $userId;
            $upload_dir = Configure::read("media.upload_dir") . DS . $userId;
            $user_dir = Configure::read("media.user_dir") . DS . $userId;

            $config = new \Flow\Config();
            $config->setTempDir($tmp_dir);
            $request = new \Flow\Request();

            if (\Flow\Basic::save($upload_dir . DS . $request->getIdentifier(), $config, $request)) {

                // \Flow\Basic にて保存したファイル名
                $uploaded_file = $upload_dir . DS . $request->getIdentifier();
                $uploaded_file_info = pathinfo($this->request->data("flowFilename"));
                $originalFileName = $this->request->data("flowFilename");

                try {

                    // トランザクション開始
                    $this->{"OriginalPageHeader"}->begin();
                    $this->{"OriginalPage"}->begin();
                    $this->{"Image"}->begin();
                    $this->{"Tag"}->begin();
                    $this->{"TagRelation"}->begin();

                    // PDF処理（画像抽出＋画像処理）
                    if ($uploaded_file_info["extension"] === 'pdf') {

                        // PDFから画像を抽出
                        $gs = Configure::read('media.ghostscript');
                        $deviceType = Configure::read('media.pdftoimagetype');
                        $deviceExt = ($deviceType === 'jpeg') ? 'jpg' : $deviceType;
                        $dpi = Configure::read('media.dpi');
                        $quality = Configure::read('media.quality');

                        // 一時ディレクトリ・ファイル名の生成
                        // $output_prefix = Util::createUniqueName(date('Ymd_'), "_" . $uploaded_file_info["filename"]);
                        $output_prefix = Util::createUniqueName(date('Ymd_'), "." . $uploaded_file_info["extension"]);
                        $output_file_name = $output_prefix . "_%04d" . "." . $deviceExt;
                        $output_dir = $tmp_dir . DS . $output_prefix;
                        $output_file = $output_dir . DS . $output_file_name;

                        // 一時ディレクトリを生成
                        mkdir($output_dir);

                        // ディレクトリ内にPDFから画像を抽出していく
                        set_time_limit(600);
                        exec($gs . " -q -dSAFER -dBATCH -dNOPAUSE -dGraphicsAlphaBits=4 -dTextAlphaBits=4 -sDEVICE=" . $deviceType . " -dJPEGQ=" . $quality . " -r" . $dpi . " -sOutputFile=" . $output_file . " " . $uploaded_file, $arr);

                        // 画像一覧の取得 (Use SPL PHP > 5.3.0)
                        $iterator = new RecursiveDirectoryIterator($output_dir, FilesystemIterator::SKIP_DOTS);
                        $iterator = new RecursiveIteratorIterator($iterator);
                        $image_list = array();
                        $num = 0;
                        foreach ($iterator as $fileinfo) {
                            if ($fileinfo->isFile()) {
                                $image_list[] = array(
                                    'filename' => $fileinfo->getFilename(),
                                    'filesize' => $fileinfo->getSize(),
                                );
                                $num++;
                            }
                        }

                        // 名前で昇順並び換え
                        uasort($image_list, function ($str1, $str2) {
                            return strnatcasecmp($str1["filename"], $str2["filename"]);
                        });

                        // 共通ヘッダーの登録
                        $data = array(
                            'OriginalPageHeader' => array(
                                'title' => $this->request->data('title'),
                            ),
                        );
                        $this->{'OriginalPageHeader'}->save($data);
                        $originalPageHeaderId = $this->{'OriginalPageHeader'}->getLastInsertID();

                        // 画像一つ一つに対して通常のアップロードと同等の処理を行う
                        $penNoteId = 0;
                        $pageNo = 1;
                        foreach ($image_list as $image_file) {
                            $title = $this->request->data('title') . "(" . $pageNo . ")";
                            $text = $this->request->data('text');
                            $creator = $this->request->data('creator');
                            $confirmor = $this->request->data('confirmor');
                            $creationDate = $this->request->data('creationDate');
                            $this->_uploadImage(
                                $userId,
                                $output_dir . DS . $image_file['filename'],
                                $user_dir . DS . $image_file['filename'],
                                $pageNo,
                                $originalFileName,
                                $image_file['filesize'],
                                $originalPageHeaderId,
                                $title,
                                $text,
                                $creator,
                                $confirmor,
                                $creationDate,
                                $penNoteId,
                                explode(',', $this->request->data('tag'))
                            );
                            $pageNo++;
                        }
                        rmdir($output_dir);
                    } // 画像処理
                    else {
                        // オリジナルファイル名の取得とローカルファイル名の生成
                        $local_file = $user_dir . DS . Util::createUniqueName(date('Ymd_'), "." . $uploaded_file_info["extension"]);

                        // 画像一つ一つに対して通常のアップロードと同等の処理を行う
                        $originalPageHeaderId = 0;
                        $penNoteId = 0;
                        $pageNo = 1;
                        $title = $this->request->data('title');
                        $text = $this->request->data('text');
                        $creator = $this->request->data('creator');
                        $confirmor = $this->request->data('confirmor');
                        $creationDate = $this->request->data('creationDate');
                        $fileSize = $this->request->data('flowTotalSize');
                        $this->_uploadImage(
                            $userId,
                            $uploaded_file,
                            $local_file,
                            $pageNo,
                            $originalFileName,
                            $fileSize,
                            $originalPageHeaderId,
                            $title,
                            $text,
                            $creator,
                            $confirmor,
                            $creationDate,
                            $penNoteId,
                            explode(',', $this->request->data('tag'))
                        );
                    }

                    // コミット
                    $this->{"TagRelation"}->commit();
                    $this->{"Tag"}->commit();
                    $this->{"Image"}->commit();
                    $this->{"OriginalPage"}->commit();
                    $this->{"OriginalPageHeader"}->commit();

                } catch (Exception $e) {
                    $this->{"TagRelation"}->rollback();
                    $this->{"Tag"}->rollback();
                    $this->{"Image"}->rollback();
                    $this->{"OriginalPage"}->rollback();
                    $this->{"OriginalPageHeader"}->rollback();
                    throw $e;
                }

            } else {

                // This is not a final chunk or request is invalid, continue to upload.

            }

        }
        catch (Exception $e) {
            $filename = ($this->request->data("flowFilename"))?$this->request->data("flowFilename"):"NO File";
            $userName = ($this->user)?$this->user['User']['userName']:'NO User';
            // Image upload failed.
            $this->Log->write("1028", 0, "[UPLOAD ERROR] Image upload failed. User:".$userName.",Filename:".$filename);
            $this->response->statusCode(500);
        }
    }


    /**
     * @param $userId
     * @param $src_file
     * @param $dst_file
     * @param $pageNo
     * @param $originalFileName
     * @param $fileSize
     * @param $originalPageHeaderId
     * @param $title
     * @param $text
     * @param $creator
     * @param $confirmor
     * @param $creationDate
     * @param $penNoteId
     * @param $tags
     * @throws Exception Database, File
     */
    private function _uploadImage($userId, $src_file, $dst_file, $pageNo, $originalFileName, $fileSize, $originalPageHeaderId, $title, $text, $creator, $confirmor, $creationDate, $penNoteId, $tags)
    {
        $user_dir = Configure::read("media.user_dir") . DS . $userId;

        // ファイルの保存
        if (!rename($src_file, $dst_file)) {
            throw new Exception("500");
        }

        // ファイル情報の取得
        $dst_file_info = pathinfo($dst_file);
        list($width, $height, $type, $attr) = getimagesize($dst_file);
        $deepZoomImage = $dst_file_info['filename'] . '.dzi';

        // 3.サムネイルの生成
        $thumbnailType = array('small', 'middle', 'large');
        foreach ($thumbnailType as $type) {

            // サイズの取得
            $thumbnailMaxWidth = Configure::read("media.thumb." . $type . ".width");
            $thumbnailMaxHeight = Configure::read("media.thumb." . $type . ".height");

            // 幅の縮小比率・高さの縮小比率の大きい方を1として合わせる。
            $widthRatio = $thumbnailMaxWidth / $width;
            $heightRatio = $thumbnailMaxHeight / $height;

            // 幅の縮小率の方が高い場合
            if ($widthRatio > $heightRatio) {
                $thumbnailWidth = $width * $heightRatio;
                $thumbnailHeight = $height * $heightRatio;
            } // 高さの縮小率が高い場合
            else {
                $thumbnailWidth = $width * $widthRatio;
                $thumbnailHeight = $height * $widthRatio;
            }

            $thumbnail_file = $user_dir . DS . "thumb" . DS . $type . DS . $dst_file_info["basename"];

            // 保存
            $canvas = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
            switch ($dst_file_info["extension"]) {

                case "jpg":
                case "jpeg":

                    $image = imagecreatefromjpeg($dst_file);
                    break;

                case "png":

                    $image = imagecreatefrompng($dst_file);
                    //ブレンドモードを無効にする
                    imagealphablending($canvas, false);
                    //完全なアルファチャネル情報を保存するフラグをonにする
                    imagesavealpha($canvas, true);
                    break;
            }
            imagecopyresampled(
                $canvas,
                $image,
                0,
                0,
                0,
                0,
                $thumbnailWidth,
                $thumbnailHeight,
                $width,
                $height
            );
            switch ($dst_file_info["extension"]) {

                case "jpg":
                case "jpeg":

                    imagejpeg(
                        $canvas,
                        $thumbnail_file,
                        100
                    );
                    break;

                case "png":

                    $res = imagepng(
                        $canvas,
                        $thumbnail_file
                    );
                    break;

            }
            imagedestroy($canvas);
        }

        // 4.DeepZoomImageの生成
        $deepzoom_file = $user_dir . DS . 'dzi' . DS . $deepZoomImage;
        $deep = new \Deepzoom\ImageCreator(
            new \Deepzoom\StreamWrapper\File(),
            new \Deepzoom\Descriptor(new \Deepzoom\StreamWrapper\File()),
            new \Deepzoom\ImageAdapter\GdThumb()
        );

        //
        // Vendor/nfabre/deepzoom/src/Deepzoom/ImageAdapter/GdThumb.php にて、
        // 自分自身のディレクトリをテンポラリディレクトリに利用しているので、
        // ./ を /tmp/ に書き換える!!
        //
        $deep->create(realpath($dst_file), $deepzoom_file);

        // 5.データベース「OriginalPage」に insert record
        try {
            // 画像の保存（必須項目）
            $data = array(
                'Image' => array(
                    'fileName' => $originalFileName,
                    'localFileName' => $dst_file_info["basename"],
                    'deepZoomImage' => $deepZoomImage,
                    'fileSize' => $fileSize,
                    'sizeX' => $width,
                    'sizeY' => $height,
                ),
            );

            $this->{'Image'}->create(false);
            $this->{'Image'}->save($data);
            $imageId = $this->{'Image'}->getLastInsertID();

            // オリジナルページ保存（必須項目）
            $data = array(
                'OriginalPage' => array(
                    'userId' => $userId,
                    'originalPageHeaderId' => $originalPageHeaderId,
                    'title' => $title,
                    'text' => $text,
                    'creator' => $creator,
                    'confirmor' => $confirmor,
                    'creationDate' => $creationDate,
                    'orgTitle' => $title,
                    'orgText' => $text,
                    'orgCreator' => $creator,
                    'orgConfirmor' => $confirmor,
                    'orgCreationDate' => $creationDate,
                    'imageId' => $imageId,
                    'imageRotate' => 0,
                    'informationSources' => OriginalPage::INFORMATION_SOURCES_UPLOAD,
                    'penNoteId' => $penNoteId,
                    'pageNo' => $pageNo,
                ),
            );

            $this->{'OriginalPage'}->create(false);
            $this->{'OriginalPage'}->save($data);
            $originalPageId = $this->{'OriginalPage'}->getLastInsertID();

            // タグの保存（オプション項目）
            if ($tags) {
                foreach ($tags as $tag) {
                    if ($tag === '') {
                        continue;
                    }
                    $params = array(
                        'conditions' => array('tag' => $tag),
                    );
                    if ($result = $this->Tag->find('first', $params)) {
                        $tagId = $result['Tag']['id'];
                    } else {
                        $data = array(
                            'Tag' => array(
                                'tag' => $tag
                            )
                        );
                        $this->{'Tag'}->create(false);
                        $this->{'Tag'}->save($data);
                        $tagId = $this->{'Tag'}->getLastInsertID();
                    }
                    $data = array(
                        'TagRelation' => array(
                            'userId' => $userId,
                            'parentType' => TagRelation::PARENT_TYPE_ORIGINAL_PAGE,
                            'parentId' => $originalPageId,
                            'tagId' => $tagId,
                        ),
                    );
                    $this->{'TagRelation'}->create(false);
                    $this->{'TagRelation'}->save($data);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 検索結果をXML形式に変換
     *
     * @param $results
     */
    function _set_xml($results)
    {
        $this->set(array(
            'results' => $results,
            '_serialize' => array('results')
        ));
    }

}


