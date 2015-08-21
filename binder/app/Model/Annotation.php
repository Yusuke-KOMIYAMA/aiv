<?php
/**
 * AnnotationModel.php
 * アノテーションモデル
 * 基本的に、検索はVAnnotationに任せて、保存と削除を行う
 */
class Annotation extends AppModel {

    public $name = 'Annotation';
    public $useTable = 'annotations';
    public $hasMany = array(
        'AnnotationText' => array(
            'className' => 'AnnotationText',
            'foreignKey' => 'annotationId',
        )
    );
    public $recursive = -1;

    /**
     * 保存
     *
     * @param $annotations
     * @param $binderPageId
     * @return array
     * @throws CakeException
     */
    public function saveAnnotation($annotations, $binderPageId)
    {
        App::import('Model','AnnotationText');

        $annotationIds = array();

        // データベースから対象のアノテーションを取得
        $params = array(
            'conditions' => array(
                'Annotation.binderPageId' => $binderPageId
            )
        );
        $currentRecords = $this->find('all', $params);

        // リクエストデータ
        foreach ($annotations as $annotation) {

            // IDリセット
            $this->create(false);

            // バインダーページIDを更新 (0の場合は未設定)
            if ($binderPageId == 0) {
                throw new CakeException("[Annotation] Set invalid param found 'binderPageId'.");
            }
            $annotation["Annotation"]["binderPageId"] = $binderPageId;

            // データベースのレコードとリクエストデータの一致があるかをチェックする
            foreach ($currentRecords as $key => $current) {
                // オリジナルページIDが一致した場合
                if ($current["Annotation"]["id"] == $annotation["Annotation"]["id"]) {
                    $annotation["Annotation"]["id"] = $current["Annotation"]["id"];
                    unset($currentRecords[$key]);
                    break;
                }
            }

            $annotationId = 0;
            if ($annotation["Annotation"]["id"] != 0) {
                $annotationId = $annotation["Annotation"]["id"];
            }
            // リクエストデータを保存
            if (!$this->save($annotation)) {
                throw new CakeException("[Annotation] save failed.");
            }

            if ($annotationId == 0) {
                $annotationId = $this->getLastInsertID();
            }

            // コメントの場合は AnnotationTextも保存
            if ($annotation["Annotation"]["figureType"] == 5) {
                $AnnotationText = new AnnotationText;
                $AnnotationText->saveAnnotationText($annotation["Annotation"]["AnnotationTexts"], $annotationId);
            }

            array_push($annotationIds, $annotationId);
        }

        // 現在のデータのうち、リクエストデータと一致しないものがあった場合は削除する
        foreach ($currentRecords as $current) {
            if ($current["Annotation"]["figureType"] == 5) {
                $AnnotationText = new AnnotationText;
                $AnnotationText->deleteByAnnotationId($current["Annotation"]["id"]);
            }
            $this->delete($current["Annotation"]["id"]);
        }

        return $annotationIds;
    }

    /**
     * アノテーションIDを指定してアノテーションを削除する
     *
     * @param $annotationId
     * @return bool
     * @throws NotFoundException
     * @throws CakeException
     */
    public function deleteAnnotation($annotationId)
    {
        App::import('Model','AnnotationText');

        // アノテーション検索
        $params = array(
            'conditions' => array(
                'Annotation.id' => $annotationId
            )
        );
        $annotations = $this->find('all', $params);

        // 指定されたデータがなかった場合は例外を投げる
        if (!$annotations) {
            throw new NotFoundException("[Annotation] Requested delete annotation data does not found.");
        }

        foreach($annotations as $annotation) {

            // アノテーションテキスト削除
            if ($annotation["Annotation"]["figureType"] == 5) {
                $AnnotationText = new AnnotationText;
                $AnnotationText->deleteAnnotationTexts($annotation["Annotation"]["id"]);
            }

            // アノテーション削除
            if (!$this->delete($annotation["Annotation"]["id"])) {
                throw new CakeException("[Annotation] Delete failed.");
            }
        }

    }

    /**
     * バインダーページIDを指定してアノテーションを削除する
     *
     * @param $binderPageId
     * @throws CakeException
     */
    public function deleteAnnotations($binderPageId)
    {
        App::import('Model','AnnotationText');

        // アノテーション検索
        $params = array(
            'conditions' => array(
                'Annotation.binderPageId' => $binderPageId
            )
        );
        $annotations = $this->find('all', $params);

        // データがある場合のみ削除を実行
        // データがない場合は処理を正常終了
        if ($annotations) {

            foreach($annotations as $annotation) {

                // アノテーションテキスト削除
                if ($annotation["Annotation"]["figureType"] == 5) {
                    $AnnotationText = new AnnotationText;
                    $AnnotationText->deleteAnnotationTexts($annotation["Annotation"]["id"]);
                }

                // アノテーション削除
                if (!$this->delete($annotation["Annotation"]["id"])) {
                    throw new CakeException("[Annotation] Delete failed.");
                }
            }

        }
    }
}
