<?php
/**
 * オリジナルページビューモデル（タグを使った検索用）
 * 検索のみで利用する
 */

App::uses('SoftDeleteBehavior', 'Model/Behavior');

class VOriginalPageTag extends AppModel {

    public $name = 'VOriginalPageTag';
    public $useTable = 'vOriginalPagesTags';

    public $order = "VOriginalPageTag.created DESC";

    /**
     * 条件に該当するオリジナルページIDを取得
     *
     * @param $userId
     * @param $text
     * @param $isTitle
     * @param $isText
     * @param $isTag
     * @return array|null
     */
    public function getIdsWithConditions($userId, $text, $isTitle, $isText, $isTag)
    {
        // 検索文字列設定（AND検索）
        $exploded = explode(' ', $text);
        if (!$exploded || !is_array($exploded))
        {
            throw new CakeException('[OriginalPageTag::getIdsWithConditions] Text is not valid value.');
        }
        elseif (count($exploded) == 1) {
            $textCond = $exploded[0];
        }
        else {
            $textCond = array();
            foreach($exploded as $cond) {
                array_push($textCond, $cond);
            }
        }

        // 検索対象設定
        $params = array(
            'fields' => array('DISTINCT VOriginalPageTag.id AS id'),
            'conditions' => array(
                'VOriginalPageTag.userId' => $userId,
            ),
            'group' => array(),
        );
        $subConditions = array();
        if ($isTitle) {
            array_push($subConditions, $this->_createCondition('title', $textCond));
        }
        if ($isText) {
            array_push($subConditions, $this->_createCondition('text', $textCond));
        }
        if ($isTag) {
            array_push($subConditions, $this->_createCondition('tag', $textCond));
        }

        // 検索条件生成
        if (count($subConditions) == 0) {
            throw new CakeException('[VOriginalPageTag::getIdsWithConditions] I guess field is not selected.');
        }
        elseif (count($subConditions) == 1) {
            foreach ($subConditions as $condition)
                $keys = array_keys($condition);
                $params['conditions'][$keys[0]] = $condition[$keys[0]];
        }
        else {
            $params['conditions']['AND'] = array();
            $params['conditions']['AND']['OR'] = array();
            foreach($subConditions as $condition) {
                array_push($params['conditions']['AND']['OR'], $condition);
            }
        }

        // 検索実行
        $results = $this->find('all', $params);
        if (!$results) {
            return array();
        }
        return Hash::extract($results, '{n}.VOriginalPageTag.id');
    }

    /**
     * タグを条件に、オリジナルページを検索する
     *
     * @param $userId
     * @param $tag
     * @return array|null
     */
    public function getIdsWithTag($userId, $tag)
    {
        // 検索条件設定
        $params = array(
            'fields' => array('DISTINCT VOriginalPageTag.id AS id'),
            'conditions' => array(
                'VOriginalPageTag.userId' => $userId,
                'VOriginalPageTag.tag' => $tag,
            ),
        );
        // 検索実行
        $results = $this->find('all', $params);
        if (!$results) {
            return array();
        }
        return Hash::extract($results, '{n}.VOriginalPageTag.id');
    }

    /**
     * テキストを指定されたフィールドをキーにした条件に変換する
     *
     * @param $fieldName
     * @param $conditions
     * @return array|null
     */
    function _createCondition($fieldName, $conditions)
    {
        if (!$conditions) {
            return null;
        }
        elseif (!is_array($conditions)) {
            return array("VOriginalPageTag.{$fieldName} LIKE" => '%' . addcslashes($conditions, '\_%') . '%');
        }
        else {
            $createdConditions = array('AND' => array());
            foreach($conditions as $condition) {
                // TODO: sql インジェクション対策ができているか確認
                array_push($createdConditions['AND'], array("VOriginalPageTag.{$fieldName} LIKE" => '%' . addcslashes($condition, '\_%') . '%'));
            }
            return $createdConditions;
        }
    }

}


