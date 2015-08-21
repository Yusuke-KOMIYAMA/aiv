<?php
/**
 * Util.class.php
 * Date: 2015/03/03
 */
class Util {

    public static function createUniqueName($prefix = '', $suffix = '')
    {
        // こんなのとかもある
        // return date("Ymd").md5(uniqid(microtime(),1)).getmypid();
        return $prefix . uniqid() . $suffix;
    }

    /**
     * 内包するファイル・ディレクトリを全て削除する
     *
     * @param $dir
     */
    public static function rmdirAll($dir)
    {
        if ($handle = opendir($dir)) {

            while (false !== ($item = readdir($handle))) {
                // ".", ".." でない場合に削除処理
                if ($item != "." && $item != "..") {
                    // ディレクトリの場合は再起呼び出し
                    if (is_dir("$dir/$item")) {
                        Util::rmdirAll("$dir/$item");
                    }
                    // ファイルの場合は削除
                    else {
                        unlink("$dir/$item");
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
    }
}
