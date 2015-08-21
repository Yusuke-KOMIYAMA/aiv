<?php
/**
 * AnnotationTextModel.php
 *
 * アノテーションテキスト(コメント)モデル
 * 検索は VAnnotation を使う
 */
class AnnotationText extends AppModel {

    public $name = 'AnnotationText';
    public $useTable = 'annotationTexts';

    public $recursive = -1;

    /**
     * アノテーションテキストを保存する
     *
     * @param $annotationTexts
     * @param $annotationId
     * @return array
     * @throws BadRequestException
     * @throws CakeException
     */
    public function saveAnnotationText($annotationTexts, $annotationId)
    {
        $annotationTextIds = array();

        // データベースから対象のアノテーションを取得
        $params = array(
            'conditions' => array(
                'AnnotationText.annotationId' => $annotationId
            )
        );
        $currentRecords = $this->find('all', $params);

        // リクエストデータ（ひとつだけのはずですが…）
        foreach ($annotationTexts as $annotationText) {

            // IDリセット
            $this->create(false);

            // バインダーページIDを更新 (0の場合は未設定)
            if ($annotationId == 0) {
                throw new BadRequestException("[AnnotationText] Set zero to Request parameter 'annotationId'.");
            }
            $annotationText["AnnotationText"]["annotationId"] = $annotationId;

            // データベースのレコードとリクエストデータの一致があるかをチェックする
            foreach ($currentRecords as $key => $current) {
                // オリジナルページIDが一致した場合
                if ($current["AnnotationText"]["id"] == $annotationText["AnnotationText"]["id"]) {
                    $annotationText["AnnotationText"]["id"] = $current["AnnotationText"]["id"];
                    unset($currentRecords[$key]);
                    break;
                }
            }
            $annotationTextId = 0;
            if ($annotationText["AnnotationText"]["id"] != 0) {
                $annotationTextId = $annotationText["AnnotationText"]["id"];
            }
            // リクエストデータを保存
            if (!$this->save($annotationText)) {
                throw new CakeException("[AnnotationText] Save failed.");
            }
            if ($annotationTextId == 0) {
                $annotationTextId = $this->getLastInsertID();
            }
            array_push($annotationTextIds, $annotationTextId);
        }

        // 現在のデータのうち、リクエストデータと一致しないものがあった場合は削除する
        foreach ($currentRecords as $current) {
            $this->delete($current["AnnotationText"]["id"]);
        }

        return $annotationTextIds;
    }

    /**
     * アノテーションテキストを削除する
     *
     * @param $annotationId
     * @throws CakeException
     */
    public function deleteAnnotationTexts($annotationId)
    {
        // データベースから対象のアノテーションを取得
        $params = array(
            'conditions' => array(
                'AnnotationText.annotationId' => $annotationId
            )
        );
        $annotationTexts = $this->find('all', $params);

        foreach ($annotationTexts as $annotationText) {

            // アノテーションテキストを削除する
            if (!$this->delete($annotationText["AnnotationText"]["id"])) {
                throw new CakeException("[AnnotationText] Delete failed.");
            }
        }
    }

    /**
     * アノテーションIDを指定してアノテーションテキストを削除する
     *
     * @param $annotationId
     * @throws CakeException
     */
    public function deleteByAnnotationId($annotationId) {

        if (0 < $annotationId) {

            $params = array(
                'AnnotationText.annotationId' => $annotationId,
            );
            if (!$this->deleteAll($params)) {
                throw new CakeException("[AnnotationText] Delete failed.");
            }
        }
    }
}
