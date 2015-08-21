<?php
/**
 * Tag.php
 *
 * タグモデル
 *
 * 検索は、主に VTag から行う
 */

class Tag extends AppModel
{
    public $name = 'Tag';
    public $useTable = 'tags';
    public $recursive = -1;
    public $hasAndBelongsToMany = array(
        'OriginalPage' => array(
            'className'              => 'OriginalPage',
            'joinTable'              => 'TagRelations',
            'foreignKey'             => 'tagId',
            'associationForeignKey'  => 'parentId',
            'unique'                 => false,
            'conditions'             => array('TagRelation.parentId' => 0),
            'fields'                 => '',
            'order'                  => '',
            'limit'                  => '',
            'offset'                 => '',
            'finderQuery'            => '',
            'deleteQuery'            => '',
            'insertQuery'            => '',
            'with'                   => 'TagRelation'
        ),
        'Binder' => array(
            'className'              => 'Binder',
            'joinTable'              => 'TagRelations',
            'foreignKey'             => 'tagId',
            'associationForeignKey'  => 'parentId',
            'unique'                 => false,
            'conditions'             => array('TagRelation.parentId' => 1),
            'fields'                 => '',
            'order'                  => '',
            'limit'                  => '',
            'offset'                 => '',
            'finderQuery'            => '',
            'deleteQuery'            => '',
            'insertQuery'            => '',
            'with'                   => 'TagRelation'
        ),
        'BinderPage' => array(
            'className'              => 'BinderPage',
            'joinTable'              => 'TagRelations',
            'foreignKey'             => 'tagId',
            'associationForeignKey'  => 'parentId',
            'unique'                 => false,
            'conditions'             => array('TagRelation.parentId' => 2),
            'fields'                 => '',
            'order'                  => '',
            'limit'                  => '',
            'offset'                 => '',
            'finderQuery'            => '',
            'deleteQuery'            => '',
            'insertQuery'            => '',
            'with'                   => 'TagRelation'
        ),
    );

    /**
     * Tagを検索して、アソシエーション先を含めて結果を返す
     * 関連を含めるために、 contain パラメータを設定する
     *
     * @param null $params
     * @return array|null
     */
    public function selectWith($params = null)
    {
        if (!$params || !is_array($params)) {
            $params = array();
        }
        $params['contain'] = array(
            'TagRelation',
        );

        $this->Behaviors->load('Containable');

        return $this->find('all', $params);
    }

    /**
     * IDを指定してバインダーを検索するアソシエーション先を含めて結果を返す
     * 関連を含めるために、 contain パラメータを設定する
     *
     * @param $id
     * @return array|bool|null
     */
    public function selectWithById($id)
    {
        if (!$id) {
            return false;
        }
        $params = array(
            'conditions' => array(
                'Tag.id' => $id
            )
        );
        return $this->selectWith($params);
    }

    /**
     * UserIDを指定してバインダーを検索するアソシエーション先を含めて結果を返す
     * 関連を含めるために、 contain パラメータを設定する
     *
     * @param $userId
     * @return array|bool|null
     */
    public function selectWithByUserId($userId)
    {
        if (!$userId) {
            return false;
        }
        $params = array(
            "joins" => array(
                array(
                    'table' => 'tagRelations',
                    'alias' => 'TagRelation',
                    'type' => 'inner',
                    'conditions' => array(
                        'TagRelation.tagId = Tag.id',
                    )
                ),
            ),
            'conditions' => array(
                'TagRelation.userId' => $userId
            )
        );
        return $this->selectWith($params);
    }

    /**
     * IDを指定してバインダーを検索するアソシエーション先を含めて結果を返す
     * 関連を含めるために、 contain パラメータを設定する
     *
     * @param $id
     * @param $userId
     * @return array|bool|null
     */
    public function selectWithByIdAndUserId($id, $userId)
    {
        if (!$id) {
            return false;
        }
        if (!$userId) {
            return false;
        }
        $params = array(
            "joins" => array(
                array(
                    'table' => 'tagRelations',
                    'alias' => 'TagRelation',
                    'type' => 'inner',
                    'conditions' => array(
                        'TagRelation.tagId = Tag.id',
                    )
                ),
            ),
            'conditions' => array(
                'Tag.id' => $id,
                'TagRelation.userId' => $userId,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * タグを保存する
     *
     * @param $tags
     * @param $parentType
     * @param $parentId
     * @param $userId
     * @return bool
     * @throws BadRequestException
     * @throws CakeException
     */
    public function saveTags($tags, $parentType, $parentId, $userId)
    {
        // タグの保存時は、必ず親タイプ、親ID、ユーザーIDを指定すること
        if(is_null($parentType) || !is_numeric($parentType)) {
            $this->log("TagRelation delete false. parentType is not numeric.", LOG_DEBUG);
            throw new BadRequestException("[Tag] Set invalid params 'parentType'.");
        }
        if(is_null($parentId) || !is_numeric($parentId)) {
            $this->log("TagRelation delete false. parentId is not numeric.", LOG_DEBUG);
            throw new BadRequestException("[Tag] Set invalid params 'parentId'.");
        }
        if(is_null($userId) || !is_numeric($userId)) {
            $this->log("TagRelation delete false. userId is not numeric.", LOG_DEBUG);
            throw new BadRequestException("[Tag] Set invalid params 'userId'.");
        }

        App::import('Model','TagRelation');

        // 同一関連の削除
        $TagRelation = new TagRelation;
        $params = array(
            'TagRelation.userId' => $userId,
            'TagRelation.parentType' => $parentType,
            'TagRelation.parentId' => $parentId,
        );
        if (!$TagRelation->deleteAll($params, false)) {
            $this->log("TagRelation delete false.", LOG_DEBUG);
            throw new CakeException("[saveTags] TagRelation save error");
        }

        foreach ($tags as $tag) {

            // IDリセット
            $this->create(false);

            // タグの検索
            $params = array(
                'conditions' => array('tag' => $tag["Tag"]["text"]),
            );
            // タグがあったらIDの取得
            if($result = $this->find('first', $params)) {
                $tagId = $result['Tag']['id'];
            }
            // なかったらタグを保存して、新しく割り振られたIDを取得する
            else {
                $data = array(
                    'Tag' => array(
                        'tag' => $tag["Tag"]["text"]
                    )
                );
                $this->save($data);
                $tagId = $this->getLastInsertID();
            }

            // 関連の保存
            $TagRelation->create(false);
            $data = array(
                'TagRelation' => array(
                    'userId' => $userId,
                    'parentType' => $parentType,
                    'parentId' => $parentId,
                    'tagId' => $tagId,
                ),
            );

            $TagRelation->save($data);
        }

        return true;
    }

    /**
     * タグを削除する
     *
     * @param $parentType
     * @param $parentId
     * @param $userId
     * @return bool
     * @throws CakeException
     */
    public function deleteTags($parentType, $parentId, $userId)
    {
        App::import('Model','TagRelation');

        // タグリレーションを取得
        $TagRelation = new TagRelation;
        $params = array(
            'conditions' => array(
                'TagRelation.userId' => $userId,
                'TagRelation.parentType' => $parentType,
                'TagRelation.parentId' => $parentId,
            )
        );
        $tagRelations = $TagRelation->find('all', $params);

        // タグがあった場合のみ削除する
        if ($tagRelations) {

            foreach($tagRelations as $tagRelation) {

                // タグリレーションを削除
                if (!$TagRelation->delete($tagRelation["TagRelation"]["id"])) {
                    throw new CakeException("[Tag] Delete 'TagRelation' failed.");
                }

                // tagId にて TagRelation を検索
                $conditions = array(
                    'TagRelation.tagId' => $tagRelation["TagRelation"]["tagId"],
                );
                if (!$TagRelation->hasAny($conditions)) {
                    // リレーションがなかった場合はタグを削除する
                    if (!$this->delete($tagRelation["TagRelation"]["tagId"])) {
                        throw new CakeException("[Tag] Delete failed.");
                    }
                }

            }

        }
    }

    /**
     * RESTful API のレスポンスに対応した形式に検索結果を整形する
     *
     * @param $results
     * @return array
     */
    public function restFormat($results)
    {
        $formatted = array(
            "Tag" => array()
        );
        // 重複した値を取り除く
        $uniques = Hash::Combine($results, "{n}.Tag.id", "{n}.Tag.tag");
        foreach($uniques as $key => $tag) {
            array_push(
                $formatted["Tag"],
                array(
                    "id" => $key,
                    "text" => $tag,
                )
            );
        }
        return $formatted;
    }
}
