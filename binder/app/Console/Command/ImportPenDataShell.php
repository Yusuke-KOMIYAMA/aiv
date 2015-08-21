<?php
App::uses('Shell', 'Console');
App::import('Vendor', 'Util', array('file' => 'Util.class.php'));

class ImportPenDataShell extends AppShell
{
    public $uses = array('User', 'ImportInformation', 'OriginalPageHeader', 'OriginalPage', 'Image', 'Tag', 'TagRelation');

    /**
     * バッチ開始前の処理
     */
    public function startup()
    {
        parent::startup();
        $this->cleanup();
    }

    /**
     * バッチのメイン処理
     */
    public function main()
    {
        // Configの取得
        Configure::load("uv.php");
        $conf = Configure::read('shell.pen');

        // インポート開始
        $this->out("バッチ処理を開始します");
        $this->degpenin($conf);
        $this->out("バッチ処理を終了します");
    }

    /**
     * 独自のデータを初期化する処理
     */
    public function cleanup()
    {
    }

    /**
     * ペンサーバーからデータをインポートする
     *
     * @param $conf
     */
    public function degpenin($conf)
    {
        try {
            // $peidir ペンサーバーの画像ディレクトリ
            $pendir = $conf['path'];

            // 5.0.0 path が ftp:// URL ラッパーをサポートします。
            $dirres = opendir($pendir);

            // ディレクトリ内のファイルを１件ずつ処理する
            while ($filename = readdir($dirres)) {

                // 文字列先頭が 'A' の場合
                if (substr($filename, 0, 1) === 'A') {

                    // 対象penIDのユーザをDBから取得
                    $uinfo = $this->degpeniduid($filename);
                    if ($uinfo) {

                        // ノート or ページ が保存されているディレクトリの表示
                        $notedir = $pendir . DS . $filename;
                        $ndirres = opendir($notedir);
                        while ($ndirfile = readdir($ndirres)) {
                            // ドットディレクトリ以外を処理
                            if ($ndirfile !== '.' && $ndirfile !== '..') {

                                // サブディレクトリの場合（ノート）
                                if (is_dir($notedir . DS . $ndirfile)) {
                                    // ページが保存されているディレクトリの表示
                                    $pagedir = $notedir . DS . $ndirfile;
                                    $pdirres = opendir($pagedir);
                                    while ($pfdir = readdir($pdirres)) {
                                        // ドットディレクトリ以外を処理
                                        if ($pfdir !== '.' && $pfdir !== '..') {

                                            // ディレクトリの場合
                                            if ( is_dir($pagedir.'/'.$pfdir )){

                                                // データ登録処理開始
                                                $dataflag = 0 ;
                                                $imgflag = 0 ;
                                                $txtConf = '' ;

                                                // ページディレクトリへ移動
                                                $pageeachdir = $pagedir . DS . $pfdir;
                                                $pedirres = opendir($pageeachdir);
                                                while ($pef = readdir($pedirres)) {
                                                    // ドットディレクトリ除外
                                                    if ($pef !== '.' && $pef !== '..') {
                                                        $exts = preg_split("/\./", $pef);
                                                        $n = count($exts) - 1;


                                                        // ファイル拡張子が 'txt' の場合、 $txtConf 変数に内容を配列で入れる(ok.txtは除く)
                                                        if ($exts[$n] === 'txt' && $pef !== 'ok.txt') {

                                                            $data = file_get_contents( $pageeachdir."/".$pef ) ;
                                                            $data = explode("\n", mb_convert_encoding($data, 'utf8', 'sjis'));
                                                            foreach ($data as $key => $ndatas) {
                                                                if (trim($ndatas) !== '' && trim($ndatas) !== '//') {
                                                                    $sdata = explode("\t", $ndatas);
                                                                    if (trim($sdata[1]) !== '') {
                                                                        $txtConf["$sdata[0]"] = trim($sdata[1]);
                                                                    }
                                                                    $dataflag = 1;
                                                                }
                                                            }
                                                        }
                                                        // ファイルがPNG画像の場合
                                                        if ($exts[$n] === 'png') {

                                                            $img = file_get_contents($pageeachdir . DS . $pef);
                                                            $fname = $pef;
                                                            $imgflag = 1;
                                                        }
                                                    }

                                                } // END OF while ($pef = readdir($pedirres))

                                                //データも画像も存在すれば、DBにデータを登録する。
                                                if ($dataflag == 1 && $imgflag == 1) {

                                                    //ページデータの登録
                                                    $this->degpenupdate($uinfo, $filename, $pfdir, $txtConf, $fname, $img);

                                                }

                                            }

                                        }

                                    } // END OF while ($pfdir = readdir($pdirres))

                                }

                            }

                        } // END OF while ($ndirfile = readdir($ndirres))

                        // 最終更新日をアップデート
                        $this->setLastImportDate($uinfo);
                    }

                }

            } // END OF while ($filename = readdir($dirres))
        }
        catch(Exception $e) {
            // TODO: エラーログ
            echo $e->getMessage();
        }
    }

    /**
     * ディレクトリ名からユーザー情報を取得する
     *
     * @param $penID
     */
    public function degpeniduid($penID)
    {
        return $this->User->find('first',
            array(
                'conditions' => array(
                    'User.penID' => $penID,
                )
            )
        );
    }

    /**
     * 画像の保存・データベースの取得
     *
     * @param $uinfo int usersから取得したユーザー情報
     * @param $pid int ペンID
     * @param $nid int ノート固有ID
     * @param $ninf string txt情報
     * @param $fname string 画像ファイル名
     * @param $pngdata array 画像バイナリ
     */
    public function degpenupdate($uinfo, $pid, $nid, $ninf, $fname, $pngdata)
    {
        $userId = $uinfo['User']['id'];
        $penNoteId = abs(trim($nid));

        try {

            // トランザクション開始
            $this->OriginalPage->begin();
            $this->Image->begin();

            // インポート情報を取得（最終更新日）
            $iinfo = $this->ImportInformation->getByUserId($userId);

            // 1.日付による処理設定（前回の情報なしまたは、更新日が最終インポート時刻よりも後の場合ににも処理する）
            if (empty($iinfo) || strtotime($iinfo['ImportInformation']['lastImportDate']) < strtotime($ninf['StrokeEndTime'])) {

                // userID, penNoteID, かつペンサーバーからのデータ(informationSources=1)があるか確認する
                $alnote = $this->OriginalPage->getPenData($userId, $penNoteId);

                // 画像処理（オリジナルコピー、サムネイル生成、DeepZoomImage生成）
                list($originalImage, $deepZoomImage) = $this->createImages($userId, $fname, $pngdata);

                // 画像情報の取得
                $originalImageInfo = pathinfo($originalImage);
                $fileSize = filesize($originalImage);
                $originalImageSize = getimagesize($originalImage);

                // 2.1.新規データの場合
                if (empty($alnote)) {

                    // DBへオリジナルページを保存
                    $this->_saveNewOriginalPage(
                        $userId,
                        $penNoteId,
                        $ninf,
                        $fname,
                        $originalImageInfo["basename"],
                        $deepZoomImage,
                        $fileSize,
                        $originalImageSize[0],
                        $originalImageSize[1]
                    );
                } // 2.2.データ更新の場合
                else {

                    $this->_updateOriginalPage(
                        $userId,
                        $penNoteId,
                        $ninf,
                        $alnote[0]['OriginalPage']['id'],
                        $alnote[0]['OriginalPage']['imageId'],
                        $alnote[0]['OriginalPage']['imageRotate'],
                        $originalImageInfo["basename"],
                        $deepZoomImage,
                        $fileSize,
                        $originalImageSize[0],
                        $originalImageSize[1]
                    );
                }
            }

            $this->Image->commit();
            $this->OriginalPage->commit();
        }
        catch (Exception $e) {
            echo $e->getMessage();
            $this->Image->rollback();
            $this->OriginalPage->rollback();
        }
    }

    /**
     * ペンサーバーからの新規オリジナルページを保存する
     *
     * @param $userId
     * @param $ninf
     */
    private function _saveNewOriginalPage($userId, $penNoteId, $ninf, $fileName, $localFileName, $deepZoomImage, $fileSize, $x, $y)
    {

        // 画像の保存（必須項目）
        $imageId = $this->Image->saveFromPenServer(
            $fileName,
            $localFileName,
            $deepZoomImage,
            $fileSize,
            $x,
            $y
        );

        $id = 0;
        $title = $this->checkOptionValue('Title', $ninf, 'No data');
        $text = $this->checkOptionValue('Note', $ninf);
        $creator = $this->checkOptionValue('Name_writer', $ninf);
        $confirmor = $this->checkOptionValue('Name_checker', $ninf);
        if ($this->checkDatetime('Y-m-d', $ninf['Date'])) {
            $creationDate = $this->checkOptionValue('Date', $ninf, date('Y-m-d'));
        }
        elseif ($this->checkDatetime('Y-m-d', $ninf['Date'])) {
            $creationDate = $this->checkOptionValue('Date', $ninf, date('Y-m-d'));
        }
        else {
            $creationDate = date('Y-m-d');
        }
        $imageRotate = 0;
        $pageNoTxt = $this->checkOptionValue('PageNo', $ninf, 1);
        $pageNo = is_numeric($pageNoTxt) ? abs($pageNoTxt) : 1;

        // データベースに挿入する
        $this->OriginalPage->saveFromPenServer($id, $userId, $title, $text, $creator, $confirmor, $creationDate, $imageId, $imageRotate, $pageNo, $penNoteId);
    }

    /**
     * ペンサーバーからのオリジナルページを更新する
     *
     * @param $userId
     * @param $penNoteId
     * @param $ninf
     * @param $originalPageId
     * @param $imageId
     * @param $imageRotate
     * @param $localFileName
     * @param $deepZoomImage
     * @param $fileSize
     * @param $x
     * @param $y
     */
    private function _updateOriginalPage($userId, $penNoteId, $ninf, $originalPageId, $imageId, $imageRotate, $localFileName, $deepZoomImage, $fileSize, $x, $y)
    {
        // 現在の画像の取得
        $params = array(
            'conditions' => array(
                'id' => $imageId,
            ),
        );
        $oldImg = $this->Image->find('first', $params);

        // 画像の更新
        $data = array(
            'Image' => array(
                'id' => $imageId,
                'localFileName' => $localFileName,
                'deepZoomImage' => $deepZoomImage,
                'fileSize' => $fileSize,
                'sizeX' => $x,
                'sizeY' => $y,
            ),
        );
        $this->{'Image'}->create(false);
        $this->{'Image'}->save($data);
        $imageId = $this->{'Image'}->getLastInsertID();

        // オリジナルページの更新
        $id = $originalPageId;
        $title = $this->checkOptionValue('Title', $ninf, 'No data');
        $text = $this->checkOptionValue('Note', $ninf);
        $creator = $this->checkOptionValue('Name_writer', $ninf);
        $confirmor = $this->checkOptionValue('Name_checker', $ninf);
        if ($this->checkDatetime('Y-m-d', $ninf['Date'])) {
            $creationDate = $this->checkOptionValue('Date', $ninf, date('Y-m-d'));
        }
        elseif ($this->checkDatetime('Y-m-d', $ninf['Date'])) {
            $creationDate = $this->checkOptionValue('Date', $ninf, date('Y-m-d'));
        }
        else {
            $creationDate = date('Y-m-d');
        }
        $pageNoTxt = $this->checkOptionValue('PageNo', $ninf, 1);
        $pageNo = is_numeric($pageNoTxt) ? abs($pageNoTxt) : 1;

        $this->OriginalPage->saveFromPenServer($id, $userId, $title, $text, $creator, $confirmor, $creationDate, $imageId, $imageRotate, $pageNo, $penNoteId);

        // 現在の画像の削除
        $user_dir = Configure::read("media.user_dir") . DS . $userId;
        $oldOriginalImg = $user_dir . DS . $oldImg['Image']['localFileName'];
        unlink($oldOriginalImg);

        $deepZoomImageRoot = $user_dir . DS . 'dzi';
        $oldDeepZoomImage = $deepZoomImageRoot . DS . $oldImg['Image']['deepZoomImage'];
        $reg="/(.*)(?:\.([^.]+$))/";
        preg_match($reg, $oldImg['Image']['deepZoomImage'], $oldDeepZoomImageInfo);
        $oldDeepZoomImageDir = $deepZoomImageRoot . DS . $oldDeepZoomImageInfo[1] . "_files";
        Util::rmdirAll($oldDeepZoomImageDir);
        unlink($oldDeepZoomImage);

        $thumbs = array('large', 'middle', 'small');
        foreach($thumbs as $thumb) {
            unlink($user_dir . DS . "thumb" . DS . $thumb . DS . $oldImg['Image']['localFileName']);
        }
    }

    /**
     * 与えられた配列の中から、必須データを取得（空データもしくはスペースのみもだめ）
     *
     * @param $key
     * @param $ary
     * @return string
     * @throws Exception
     */
    public function checkRequireValue($key, $ary)
    {
        if (empty($ary)) {
            throw new Exception("Data array is empty.");
        }
        if (!array_key_exists($key, $ary)) {
            throw new Exception($key." does not found in txt file.");
        }
        $value = trim($ary[$key]);
        if ($value == '') {
            throw new Exception("Space only data '".$key."' is found in txt file.");
        }
        return $value;
    }

    /**
     * 与えられた配列の中から、任意データを取得（なかったり、データが不正の場合はdefault値で返す）
     *
     * @param $key
     * @param $ary
     * @param string $default
     * @return string
     */
    public function checkOptionValue($key, $ary, $default = '')
    {
        if (empty($ary)) {
            return $default;
        }
        if (!array_key_exists($key, $ary)) {
            return $default;
        }
        return trim($ary[$key]);
    }

    /**
     * 日付チェック
     *
     * @param $format
     * @param $time
     * @return bool
     */
    public function checkDatetime($format, $time)
    {
        DateTime::createFromFormat($format, $time);
        $info = DateTime::getLastErrors();
        return !$info['errors'] && !$info['warnings'];
    }

    /**
     * @param $userId
     * @param $name
     * @param $img
     * @return array
     */
    public function createImages($userId, $name, $img)
    {
        $user_dir = Configure::read("media.user_dir") . DS . $userId;

        $original_file_info = pathinfo($name);

        // オリジナル画像の保存先指定
        $originalFilename = Util::createUniqueName(date('Ymd_'), "." . $original_file_info["extension"]);
        $originalFilepath = $user_dir . DS . $originalFilename;

        // オリジナルファイルの保存
        file_put_contents($originalFilepath, $img);

        // オリジナルファイルの情報取得
        $originalFileInfo = pathinfo($originalFilepath);
        $originalFileSize = getimagesize($originalFilepath);
        $width = $originalFileSize[0];
        $height = $originalFileSize[1];
        $deepZoomImage = $originalFileInfo['filename'] . '.dzi';

        // サムネイルの生成
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

            $thumbnail_file = $user_dir . DS . "thumb" . DS . $type . DS . $originalFilename;

            // 保存
            $canvas = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
            switch ($originalFileInfo["extension"]) {

                case "jpg":
                case "jpeg":

                    $image = imagecreatefromjpeg($originalFilepath);
                    break;

                case "png":

                    $image = imagecreatefrompng($originalFilepath);
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
            switch ($originalFileInfo["extension"]) {

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

        // DeepZoomImageの生成
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
        $deep->create(realpath($originalFilepath), $deepzoom_file);
        return array($originalFilepath, $deepZoomImage);
    }

    /**
     * 最終更新日をアップデートする
     *
     * @param $uinfo
     */
    public function setLastImportDate($uinfo)
    {
        if (!empty($uinfo)) {
            // 現在のデータがあるかチェックする
            $iinfo = $this->ImportInformation->find('first',
                array(
                    'conditions' => array(
                        'ImportInformation.userID' => $uinfo['User']['id'],
                    )
                )
            );
            // 最終更新日をアップデート
            $params = array();
            if (!empty($iinfo)) {
                $params['id'] = $iinfo["ImportInformation"]["id"];
            }
            else {
                $params['userId'] = $uinfo['User']['id'];
            }
            $params['lastImportDate']  = date('Y-m-d H:i:s');
            $this->ImportInformation->save($params);
        }
    }

}


