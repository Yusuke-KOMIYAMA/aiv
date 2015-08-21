<?php
/**
 * Binder.php
 *
 * バインダーモデル
 */
class Binder extends AppModel {

    public $name = 'Binder';
    public $useTable = 'binders';

    public $recursive = -1;
    public $order = "Binder.created DESC";

    public $validate = array(
        'id' => array(
            'rule' => 'numeric',
        ),
        'userId' => array(
            'rule' => 'numeric',
        ),
        'title' => array(
            'rule' => 'notEmpty',
        ),
        'text' => array(
            'rule' => array('maxLength', 255),
        ),
        'category' => array(
            'rule' => 'numeric',
        ),
        'coverId' => array(
            'rule' => 'numeric'
        ),
    );

    public $hasMany = array(
        'BinderPage' => array(
            'className'  => 'BinderPage',
            'foreignKey' => 'binderId',
        ),
    );
    public $belongsTo = array(
        'Image' => array(
            'className'  => 'Image',
            'foreignKey' => 'coverId',
        ),
    );
    public $hasAndBelongsToMany = array(
        'Tag' => array(
            'className'              => 'Tag',
            'joinTable'              => 'tagRelations',
            'foreignKey'             => 'parentId',
            'associationForeignKey'  => 'tagId',
            'unique'                 => false,
            'conditions'             => array('TagRelation.parentType = 1'),
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
     * バインダーを検索して、アソシエーション先を含めて結果を返す
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
                'BinderPage' => array(
                    'Image',
                    'Tag',
                    'VAnnotation',
                ),
                'Image',
                'Tag',
            );
        }
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
                'Binder.id' => $id
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
            'conditions' => array(
                'Binder.userId' => $userId
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
            'conditions' => array(
                'Binder.id' => $id,
                'Binder.userId' => $userId,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * バインダーを保存する
     *
     * @param $binders
     * @param $userId
     * @return array
     * @throws CakeException
     */
    public function saveBinders($binders, $userId)
    {
        App::import('Model','BinderPage');
        App::import('Model','TagRelation');
        App::import('Model','Tag');

        $binderIds = array();

        // リクエストデータをループ
        foreach ($binders as $record) {

            $isUpdate = true;

            // ユーザーIDをログインユーザーに固定
            $record["Binder"]["userId"] = $userId;

            // 対象レコードがリクエストユーザーが利用できるものかをチェックする
            if (!$this->findByIdAndUserId($record["Binder"]["id"], $userId)) {
                // 対象がないので、新規作成
                $reocrd["Binder"]["id"] = 0;
                $isUpdate = false;
            }
            $binderId = $record["Binder"]["id"];

            // バインダー作成・更新
            if (!$this->save($record["Binder"])) {
                // 500: 更新できなかった
                throw new CakeException("[Binder] Save failed.");
            }
            if ($isUpdate == false) {
                $binderId = $this->getLastInsertID();
            }
            array_push($binderIds, $binderId);

            // バインダーページ保存
            $BinderPage = new BinderPage;
            $BinderPage->saveBinderPages($record["Binder"]["BinderPages"], $binderId, $userId);

            // タグ保存
            $Tag = new Tag;
            $Tag->saveTags($record["Binder"]["Tags"], TagRelation::PARENT_TYPE_BINDER, $binderId, $userId);

        }

        return $binderIds;
    }

    /**
     * バインダーを削除する
     *
     * @param $binderId
     * @param $userId
     * @throws NotFoundException
     * @throws CakeException
     */
    public function deleteBinder($binderId, $userId)
    {
        App::import('Model','BinderPage');
        App::import('Model','TagRelation');
        App::import('Model','Tag');

        // バインダー検索
        $binder = $this->findByIdAndUserId($binderId, $userId);
        if (!$binder) {
            throw new NotFoundException("[Binder] Requested delete binder data does not found.");
        }

        // タグ削除
        $Tag = new Tag;
        $Tag->deleteTags(TagRelation::PARENT_TYPE_BINDER, $binderId, $userId);

        // バインダーページ削除
        $BinderPage = new BinderPage;
        $BinderPage->deleteBinderPages($binderId, $userId);

        // バインダー削除
        if (!$this->delete($binderId)) {
            $this->log("Binder:delete.error", LOG_DEBUG);
            throw new CakeException("[Binder] Delete failed.");
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
//$this->log($this->getDataSource()->getLog(false, false), LOG_DEBUG);
        $formatted = array(
            "Binder" => array()
        );
        foreach ($results as $binder) {
            // BinderPage処理
            $binderPage = array();
            foreach ($binder["BinderPage"] as $page) {
                $annotations = array();
                foreach ($page["VAnnotation"] as $record) {
                    $annotation = array(
                        "id" => $record["id"],
                        "binderPageId" => $record["binderPageId"],
                        "figureType" => $record["figureType"],
                        "svgId" => $record["svgId"],
                        "x" => $record["x"],
                        "y" => $record["y"],
                        "x2" => $record["x2"],
                        "y2" => $record["y2"],
                        "rx" => $record["rx"],
                        "ry" => $record["ry"],
                        "width" => $record["width"],
                        "height" => $record["height"],
                        "stroke" => $record["stroke"],
                        "strokeWidth" => $record["strokeWidth"],
                        "lineStyle" => $record["lineStyle"],
                    );
                    if ($record["figureType"] == 5) {
                        $annotation["AnnotationText"] = array(
                            "id" => $record["annotationTextId"],
                            "annotationId" => $record["id"],
                            "title" => $record["title"],
                            "text" => $record["text"],
                            "url" => $record["url"],
                        );
                    }
                    array_push($annotations, $annotation);
                }
                $tags = array();
                foreach($page["Tag"] as $tag) {
                    array_push(
                        $tags,
                        array(
                            "text" => $tag["tag"]
                        )
                    );
                }
                array_push(
                    $binderPage,
                    array(
                        "id" => $page["id"],
                        "userId" => $page["userId"],
                        "binderId" => $page["binderId"],
                        "originalPageId" => $page["originalPageId"],
                        "pageNo" => $page["pageNo"],
                        "title" => $page["title"],
                        "text" => $page["text"],
                        "creator" => $page["creator"],
                        "confirmor" => $page["confirmor"],
                        "creationDate" => $page["creationDate"],
                        "imageId" => $page["imageId"],
                        "imageRotate" => $page["imageRotate"],
                        "Image" => array(
                            "id" => $page["Image"]["id"],
                            "fileName" => $page["Image"]["fileName"],
                            "localFileName" => $page["Image"]["localFileName"],
                            "deepZoomImage" => $page["Image"]["deepZoomImage"],
                            "fileSize" => $page["Image"]["fileSize"],
                            "sizeX" => $page["Image"]["sizeX"],
                            "sizeY" => $page["Image"]["sizeY"],
                        ),
                        "Tag" => $tags,
                        "Annotation" => $annotations,
                    )
                );
            }
            $tags = array();
            foreach($binder["Tag"] as $tag) {
                array_push(
                    $tags,
                    array(
                        "text" => $tag["tag"]
                    )
                );
            }

            array_push(
                $formatted["Binder"],
                array(
                    "id" => $binder["Binder"]["id"],
                    "userId" => $binder["Binder"]["userId"],
                    "title" => $binder["Binder"]["title"],
                    "text" => $binder["Binder"]["text"],
                    "category" => $binder["Binder"]["category"],
                    "coverId" => $binder["Binder"]["coverId"],
                    "BinderPage" => $binderPage,
                    "Tag" => $tags,
                    "Cover" => array(
                        "id" => is_null($binder["Image"]["id"])?'':$binder["Image"]["id"],
                        "fileName" => is_null($binder["Image"]["fileName"])?'':$binder["Image"]["fileName"],
                        "localFileName" => is_null($binder["Image"]["localFileName"])?'':$binder["Image"]["localFileName"],
                        "deepZoomImage" => is_null($binder["Image"]["deepZoomImage"])?'':$binder["Image"]["deepZoomImage"],
                        "fileSize" => is_null($binder["Image"]["fileSize"])?'':$binder["Image"]["fileSize"],
                        "sizeX" => is_null($binder["Image"]["sizeX"])?'':$binder["Image"]["sizeX"],
                        "sizeY" => is_null($binder["Image"]["sizeY"])?'':$binder["Image"]["sizeY"],
                    ),
                )
            );
        }
        return $formatted;
    }

    /**
     * XML
     *
     * @param $results
     * @param $originals
     * @return array
     */
    public function xmlFormat($results, $originals)
    {
//$this->log($this->getDataSource()->getLog(false, false), LOG_DEBUG);
        $formatted = array(
            "Binder" => array()
        );
        foreach ($results as $binder) {
            // BinderPage処理
            $binderPage = array();
            foreach ($binder["BinderPage"] as $page) {
                $original = $originals[$page["id"]];
                $annotations = array();
                foreach ($page["VAnnotation"] as $record) {
                    if ($record["figureType"] == 5) {
                        $annotation = array(
                            'x' => $record["x"],
                            "y" => $record["y"],
                            "title" => $record["title"],
                            "text" => $record["text"],
                            "url" => $record["url"],
                        );
                        array_push($annotations, $annotation);
                    }
                }
                $tags = array();
                foreach($page["Tag"] as $tag) {
                    array_push($tags, $tag["tag"]);
                }
                array_push(
                    $binderPage,
                    array(
                        "pageNo" => $page["pageNo"],
                        "title" => $page["title"],
                        "text" => $page["text"],
                        "creator" => $page["creator"],
                        "confirmor" => $page["confirmor"],
                        "creationDate" => $page["creationDate"],
                        "originalPage.title" => $original[0]["OriginalPage"]["title"],
                        "originalPage.text" => $original[0]["OriginalPage"]["text"],
                        "originalPage.creator" => $original[0]["OriginalPage"]["creator"],
                        "originalPage.confirmor" => $original[0]["OriginalPage"]["confirmor"],
                        "originalPage.creationDate" => $original[0]["OriginalPage"]["creationDate"],
                        "originalPage.orgTitle" => $original[0]["OriginalPage"]["orgTitle"],
                        "originalPage.orgText" => $original[0]["OriginalPage"]["orgText"],
                        "originalPage.orgCreator" => $original[0]["OriginalPage"]["orgCreator"],
                        "originalPage.orgConfirmor" => $original[0]["OriginalPage"]["orgConfirmor"],
                        "originalPage.orgCreationDate" => $original[0]["OriginalPage"]["orgCreationDate"],
                        "image" => "img/".$page["Image"]["localFileName"],
                        "Tag" => $tags,
                        "Annotation" => $annotations
                    )
                );
            }
            $tags = array();
            foreach($binder["Tag"] as $tag) {
                array_push(
                    $tags,
                    array(
                        "text" => $tag["tag"]
                    )
                );
            }

            array_push(
                $formatted["Binder"],
                array(
                    "title" => $binder["Binder"]["title"],
                    "text" => $binder["Binder"]["text"],
                    "cover" => empty($binder["Image"]["localFileName"])?'':"img/".$binder["Image"]["localFileName"],
                    "BinderPage" => $binderPage,
                    "Tag" => $tags,
                )
            );
        }
        return $formatted;
    }

}

