<?php
/**
 * オリジナルページモデル
 *
 * SoftDeleteを利用しています。
 * originalPages.deleted, originalPaged.deleted_date は SoftDelete が利用する
 * 物理的に削除したい場合は、$this->OriginalPage->Behavior->detach('SoftDelete');などで一時的に無効化する。
 *
 * @see https://github.com/CakeDC/utils
 */

App::uses('SoftDeleteBehavior', 'Model/Behavior');

class OriginalPage extends AppModel {

    const INFORMATION_SOURCES_PEN_SERVER = 1;
    const INFORMATION_SOURCES_UPLOAD = 2;
    const INFORMATION_SOURCES_PACKAGE_UPLOAD = 3;

    /**
     * MAIN: originalPages
     *      imageId FOREIGN KEY: images.id
     */
    public $name = 'OriginalPage';
    public $useTable = 'originalPages';

    public $actsAs = array('SoftDelete');

    public $order = "OriginalPage.created DESC";
    public $recursive = -1;

    public $validate = array(
        'id' => array(
            'rule'    => 'numeric',
        ),
        'userId' => array(
            'rule' => 'numeric',
        ),
        'originalPageHeaderId' => array(
            'rule' => 'numeric'
        ),
        'title' => array(
            'rule' => 'notEmpty',
        ),
        'text' => array(
            'rule' => array('maxLength', 255),
        ),
        'creationDate' => array(
            'rule' => array('date', 'ymd'),
        ),
        'orgTitle' => array(
            'rule' => 'notEmpty',
        ),
        'orgText' => array(
            'rule' => array('maxLength', 255),
        ),
        'orgCreationDate' => array(
            'rule' => array('date', 'ymd'),
        ),
        'imageId' => array(
            'rule' => 'numeric',
        ),
        'imageRotate' => array(
            'rule'    => array('inList', array(0, 90, 180, 270)),
        ),
        'informationSources' => array(
            'rule' => 'numeric',
        ),
        'pageNo' => array(
            'rule' => 'numeric',
        ),
        'penNoteId' => array(
            'rule' => 'numeric',
        ),
    );

    public $belongsTo = array(
        'Image' => array(
            'className'  => 'Image',
            'foreignKey' => 'imageId'
        ),
        'OriginalPageHeader' => array(
            'className'  => 'OriginalPageHeader',
            'foreignKey' => 'originalPageHeaderId'
        ),
    );
    public $hasAndBelongsToMany = array(
        'Tag' =>
            array(
                'className'              => 'Tag',
                'joinTable'              => 'tagRelations',
                'foreignKey'             => 'parentId',
                'associationForeignKey'  => 'tagId',
                'unique'                 => false,
                'conditions'             => array('TagRelation.parentType = 0'),
                'fields'                 => '',
                'order'                  => '',
                'limit'                  => '',
                'offset'                 => '',
                'finderQuery'            => '',
                'deleteQuery'            => '',
                'insertQuery'            => '',
                'with'                   => 'TagRelation'
            )
    );

    /**
     * オリジナルページを検索して、アソシエーション先を含めて結果を返す
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
        if (!in_array('contain', $params)) {
            $params['contain'] = array(
                'OriginalPageHeader',
                'Image',
                'Tag',
            );
        }
        $this->Behaviors->load('Containable');
        return $this->find('all', $params);
    }

    /**
     * IDを指定してオリジナルページを検索するアソシエーション先を含めて結果を返す
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
                'OriginalPage.id' => $id
            )
        );
        return $this->selectWith($params);
    }

    /**
     * UserIDを指定してオリジナルページを検索するアソシエーション先を含めて結果を返す
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
            'conditions' => array(
                'OriginalPage.userId' => $userId
            )
        );
        return $this->selectWith($params);
    }

    /**
     * IDを指定してオリジナルページを検索するアソシエーション先を含めて結果を返す
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
            'conditions' => array(
                'OriginalPage.id' => $id,
                'OriginalPage.userId' => $userId,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * 詳細な条件を指定して、オリジナルページ検索を行う
     *
     * @param $userId
     * @param $text
     * @param $isTitle
     * @param $isText
     * @param $isTag
     * @return array|null
     */
    public function selectWithConditions($userId, $text, $isTitle, $isText, $isTag)
    {
        $this->VOriginalPageTag = Classregistry::init('VOriginalPageTag');
        $ids = $this->VOriginalPageTag->getIdsWithConditions($userId, $text, $isTitle, $isText, $isTag);
        if (!$ids) {
            return array();
        }
        $params = array(
            'conditions' => array(
                'OriginalPage.id' => $ids,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * タグを指定して、オリジナルページ検索を行う
     *
     * @param $userId
     * @param $tag
     * @return array|null
     */
    public function selectWithTag($userId, $tag)
    {
        $this->VOriginalPageTag = Classregistry::init('VOriginalPageTag');
        $ids = $this->VOriginalPageTag->getIdsWithTag($userId, $tag);
        if (!$ids) {
            return array();
        }
        $params = array(
            'conditions' => array(
                'OriginalPage.id' => $ids,
            ),
        );
        return $this->selectWith($params);
    }

    /**
     * userID, penNoteID, かつペンサーバーからのデータ(informationSources=1)があるか確認する
     *
     * @param $userId
     * @param $penNoteId
     * @return array|null
     */
    public function getPenData($userId, $penNoteId) {
        $params = array(
            'conditions' => array(
                'OriginalPage.userId' => $userId,
                'OriginalPage.penNoteID' =>  $penNoteId,
                'informationSources' => 1,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * オリジナルページを保存する
     *
     * @param $originalPages
     * @param $userId
     * @return array
     * @throws NotFoundException
     * @throws CakeException
     */
    public function updateOriginalPages($originalPages, $userId)
    {
        App::import('Model','Tag');
        App::import('Model','TagRelation');

        $originalPageIds = array();

        // リクエストデータをループ
        foreach ($originalPages as $record) {

            // ユーザーIDをログインユーザーに固定
            $record["OriginalPage"]["userId"] = $userId;

            // Imageの更新はないので処理をしない

            // 対象レコードがリクエストユーザーが利用できるものかをチェックする
            if (!$this->findByIdAndUserId($record["OriginalPage"]["id"], $userId)) {
                // 対象がないので、エラー
                throw new NotFoundException("[OriginalPage] Requested save data does not found.");
            }
            $originalPageId = $record["OriginalPage"]["id"];

            // オリジナルページ更新
            if (!$this->save($record["OriginalPage"])) {
                // 500: 更新できなかった
                throw new CakeException("[OriginalPage] Save failed.");
            }
            array_push($originalPageIds, $originalPageId);

            // タグ保存
            $Tag = new Tag;
            $Tag->saveTags($record["OriginalPage"]["Tags"], TagRelation::PARENT_TYPE_ORIGINAL_PAGE, $originalPageId, $userId);

        }

        return $originalPageIds;
    }

    /**
     * ペンサーバーからの情報でオリジナルページを保存する
     *
     * @param $userId
     * @param $title
     * @param $text
     * @param $creator
     * @param $confirmor
     * @param $creationDate
     * @param $imageId
     * @param $imageRotate
     * @param $pageNo
     * @param $penNoteId
     * @return mixed
     * @throws Exception
     */
    public function saveFromPenServer($id, $userId, $title, $text, $creator, $confirmor, $creationDate, $imageId, $imageRotate, $pageNo, $penNoteId)
    {
        $params = array(
            'OriginalPage' => array(
                'userId' => $userId,
                'originalPageHeaderId' => 0,
                'title' => $title,
                'creationDate' => $creationDate,
                'creator' => $creator,
                'text' => $text,
                'confirmor' => $confirmor,
                'orgTitle' => $title,
                'orgCreationDate' => $creationDate,
                'orgCreator' => $creator,
                'orgText' => $text,
                'orgConfirmor' => $confirmor,
                'imageId' => $imageId,
                'imageRotate' => $imageRotate,
                'informationSources' => 1,
                'pageNo' => $pageNo,
                'penNoteId' => $penNoteId,
            ),
        );
        if ($id != 0) {
            $params['OriginalPage']['id'] = $id;
        }

        $this->save($params);
        return $this->getLastInsertID();
    }

    /**
     * オリジナルページを削除する（SoftDelete）
     *
     * @param $originalPageId
     * @param $userId
     * @throws NotFoundException
     * @throws CakeException
     */
    public function deleteOriginalPage($originalPageId, $userId)
    {
        // オリジナルページを検索
        $originalPages = $this->findByIdAndUserId($originalPageId, $userId);

        if (!$originalPages) {
            throw new NotFoundException("[OriginalPage] Requested delete data does not found.");
        }

        // オリジナルページを削除する
        if (!$this->delete($originalPageId)) {
            throw new CakeException("[OriginalPage] Delete failed.");
        }
    }

    /**
     * @param $results
     * @return array
     */
    public function restFormat($results)
    {
$this->log($this->getDataSource()->getLog(),LOG_DEBUG);
        $formatted = array(
            "OriginalPage" => array()
        );

        foreach($results as $page) {
            $tags = array();
            foreach($page["Tag"] as $tag) {
                array_push(
                    $tags,
                    array(
                        "text" => $tag["tag"]
                    )
                );
            }
            array_push($formatted["OriginalPage"],
                array(
                    "id" => $page["OriginalPage"]["id"],
                    "userId" => $page["OriginalPage"]["userId"],
                    "originalPageHeaderId" => $page["OriginalPage"]["originalPageHeaderId"],
                    "title" => $page["OriginalPage"]["title"],
                    "text" => $page["OriginalPage"]["text"],
                    "creator" => $page["OriginalPage"]["creator"],
                    "confirmor" => $page["OriginalPage"]["confirmor"],
                    "creationDate" => $page["OriginalPage"]["creationDate"],
                    "orgTitle" => $page["OriginalPage"]["orgTitle"],
                    "orgText" => $page["OriginalPage"]["orgText"],
                    "orgCreator" => $page["OriginalPage"]["orgCreator"],
                    "orgConfirmor" => $page["OriginalPage"]["orgConfirmor"],
                    "orgCreationDate" => $page["OriginalPage"]["orgCreationDate"],
                    "imageId" => $page["OriginalPage"]["imageId"],
                    "imageRotate" => $page["OriginalPage"]["imageRotate"],
                    "informationSources" => $page["OriginalPage"]["informationSources"],
                    "pageNo" => $page["OriginalPage"]["pageNo"],
                    "penNoteId" => $page["OriginalPage"]["penNoteId"],
                    "Image" => array(
                        "id" => $page["Image"]["id"],
                        "fileName" => $page["Image"]["fileName"],
                        "localFileName" => $page["Image"]["localFileName"],
                        "deepZoomImage" => $page["Image"]["deepZoomImage"],
                        "fileSize" => $page["Image"]["fileSize"],
                        "sizeX" => $page["Image"]["sizeX"],
                        "sizeY" => $page["Image"]["sizeY"],
                    ),
                    "Tag" => $tags
                )
            );
        }
        return $formatted;
    }

}
