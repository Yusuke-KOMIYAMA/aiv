<?php
/**
 * VTag.php
 * タグのビューモデル
 * 検索のみで利用する
 */
class VTag extends AppModel {

    public $name = 'VTag';
    public $useTable = 'vTags';

    /**
     * ユーザーIDを指定してタグを検索する
     *
     * @param $userId
     * @return array|null
     */
    public function selectWithByUserId($userId)
    {
        $params = array(
            'fields' => array(
                'DISTINCT VTag.id AS id',
                'VTag.tag AS tag',
            ),
            'conditions' => array(
                'VTag.userId' => $userId,
            )
        );
$this->log($params, LOG_DEBUG);
        $results = $this->find('all', $params);
$this->log($results, LOG_DEBUG);
        return $results;
    }

    /**
     * オリジナルページタグサジェスト用検索を行う
     *
     * @param $userId
     * @return array|null
     */
    public function selectWithByUserIdForOriginalPage($userId)
    {
        $params = array(
            'fields' => array(
                'DISTINCT VTag.id AS id',
                'VTag.tag AS tag',
            ),
            'conditions' => array(
                'parentType' => 0,
                'VTag.userId' => $userId,
            )
        );
        return $this->find('all', $params);
    }

    /**
     * バインダーページタグサジェスト用検索を行う
     *
     * @param $userId
     * @return array|null
     */
    public function selectWithByUserIdForBinderPage($userId)
    {
        $params = array(
            'fields' => array(
                'DISTINCT VTag.id AS id',
                'VTag.tag AS tag',
            ),
            'conditions' => array(
                'parentType' => 2,
                'VTag.userId' => $userId,
            )
        );
        return $this->find('all', $params);
    }

    /**
     * REST 用フォーマットに変換する
     *
     * @param $results
     * @return array
     */
    public function restFormat($results)
    {
$this->log($results, LOG_DEBUG);
        $formatted = array(
            "Tag" => array()
        );
        // 重複した値を取り除く
        $uniques = Hash::Combine($results, "{n}.VTag.id", "{n}.VTag.tag");
        foreach($uniques as $key => $tag) {
            array_push(
                $formatted["Tag"],
                array(
                    "id" => $key,
                    "text" => $tag,
                )
            );
        }
$this->log($formatted, LOG_DEBUG);
        return $formatted;
    }

}
