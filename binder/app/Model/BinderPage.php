<?php
/**
 * BinderPage.php
 * バインダーページモデル
 */
class BinderPage extends AppModel {

    public $name = 'BinderPage';
    public $useTable = 'binderPages';

    public $recursive = -1;
    public $order = "BinderPage.created DESC";

    public $validate = array(
        'id' => array(
            'rule'    => 'numeric',
        ),
        'userId' => array(
            'rule' => 'numeric',
        ),
        'binderId' => array(
            'rule' => array('isUniqueWith', 'originalPageId'),
            'on' => 'create',
        ),
        'originalPageId' => array(
            'rule' => array('isUniqueWith', 'binderId'),
            'on' => 'create',
        ),
        'pageNo' => array(
            'rule' => 'numeric',
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
        'imageId' => array(
            'rule' => 'numeric',
        ),
        'imageRotate' => array(
            'rule'    => array('inList', array(0, 90, 180, 270)),
        ),
    );

    public $belongsTo = array(
        'Image' => array(
            'className' => 'Image',
            'foreignKey' => 'imageId',
        )
    );
    public $hasAndBelongsToMany = array(
        'Tag' =>
            array(
                'className'              => 'Tag',
                'joinTable'              => 'tagRelations',
                'foreignKey'             => 'parentId',
                'associationForeignKey'  => 'tagId',
                'unique'                 => false,
                'conditions'             => array('TagRelation.parentType = 2'),
                'fields'                 => '',
                'order'                  => '',
                'limit'                  => '',
                'offset'                 => '',
                'finderQuery'            => '',
                'deleteQuery'            => '',
                'insertQuery'            => '',
                'with'                   => 'TagRelation',
            )
    );
    public $hasMany = array(
        'VAnnotation' => array(
            'className'  => 'VAnnotation',
            'foreignKey' => 'binderPageId',
            'order'      => 'VAnnotation.svgId',
        )
    );

    /**
     * 複合ユニークキーチェックを行う
     * アソシエーション定義のための関数として利用する
     *
     * @param $data
     * @param $fields
     * @return bool
     */
    public function isUniqueWith($data, $fields)
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        $fields = array_merge($data, $fields);
        return $this->isUnique($fields, false);
    }

    /**
     * バインダーページを検索して、アソシエーション先を含めて結果を返す
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
                'Image',
                'Tag',
                'VAnnotation',
            );
        }
        $this->Behaviors->load('Containable');
        return $this->find('all', $params);
    }

    /**
     * IDを指定してバインダーページを検索するアソシエーション先を含めて結果を返す
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
                'BinderPage.id' => $id
            )
        );
        return $this->selectWith($params);
    }

    /**
     * UserIDを指定してバインダーページを検索するアソシエーション先を含めて結果を返す
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
                'BinderPage.userId' => $userId
            )
        );
        return $this->selectWith($params);
    }

    /**
     * IDを指定してバインダーページを検索するアソシエーション先を含めて結果を返す
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
                'BinderPage.id' => $id,
                'BinderPage.userId' => $userId,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * 詳細な条件を指定して、バインダーページ検索を行う
     *
     * @param $userId
     * @param $text
     * @param $isTitle
     * @param $isText
     * @param $isTag
     * @param $isAnnotation
     * @return array|null
     */
    public function selectWithConditions($userId, $text, $isTitle, $isText, $isTag, $isAnnotation)
    {
        $this->VBinderPageTag = Classregistry::init('VBinderPageTag');
        $ids = $this->VBinderPageTag->getIdsWithConditions($userId, $text, $isTitle, $isText, $isTag, $isAnnotation);
        if (!$ids) {
            return array();
        }
        $params = array(
            'conditions' => array(
                'BinderPage.id' => $ids,
            )
        );
        return $this->selectWith($params);
    }

    /**
     * タグを指定して、バインダーページ検索を行う
     *
     * @param $userId
     * @param $tag
     * @return array|null
     */
    public function selectWithTag($userId, $tag)
    {
        $this->VBinderPageTag = Classregistry::init('VBinderPageTag');
        $ids = $this->VBinderPageTag->getIdsWithTag($userId, $tag);
        if (!$ids) {
            return array();
        }
        $params = array(
            'conditions' => array(
                'BinderPage.id' => $ids,
            ),
        );
        return $this->selectWith($params);
    }

    /**
     * タイムライン用検索を行う
     *
     * @param $userId
     * @return array|bool|null
     */
    public function selectForTimeline($userId)
    {
        if (!$userId) {
            return false;
        }
        $params = array(
            'conditions' => array(
                'BinderPage.userId' => $userId
            ),
            'order' => array(
                'BinderPage.creationDate',
            ),
        );
        return $this->selectWith($params);

    }

    /**
     * バインダーページを1件保存する
     *
     * @param $binderPages
     * @param $userId
     * @return mixed
     * @throws BadRequestException
     * @throws CakeException
     */
    public function saveBinderPage($binderPages, $userId)
    {
        App::import('Model', 'Tag');
        App::import('Model', 'Annotation');

        $binderPage = null;
        if (count($binderPages) == 0) {
            throw new BadRequestException("[BinderPage] No data found in saveBinderPage");
        }
        $binderPage = $binderPages[0];
        if ($binderPage["BinderPage"]["binderId"] == 0) {
            throw new BadRequestException("[BinderPage] Set invalid parameter 'binderId'.");
        }

        // ID取得
        $binderPageId = $binderPage["BinderPage"]["id"];

        // ユーザーIDをログインユーザーに固定する
        if ($userId != 0) {
            $binderPage["BinderPage"]["userId"] = $userId;
        }

        // リクエストデータを保存
        if (!$this->save($binderPage["BinderPage"])) {
            throw new CakeException("[BinderPage] save failed.");
        }

        if (0 == $binderPageId) {
            $binderPageId = $this->getLastInsertID();
        }

        // タグの保存
        $Tag = new Tag;
        $Tag->saveTags($binderPage["Tags"], TagRelation::PARENT_TYPE_BINDER_PAGE, $binderPageId, $userId);

        // アノテーションの保存
        $Annotation = new Annotation;
        $Annotation->saveAnnotation($binderPage["Annotations"], $binderPageId);

        return $binderPageId;
    }

    /**
     * バインダーページを保存する
     *
     * @param $binderPages
     * @param int $binderId
     * @param int $userId
     * @return array
     * @throws CakeException
     */
    public function saveBinderPages($binderPages, $binderId, $userId)
    {
        App::import('Model','Tag');
        App::import('Model','Annotation');

        $binderPageIds = array();

        // データベースから対象のバインダーページを取得
        $params = array(
            'conditions' => array(
                'BinderPage.binderId' => $binderId
            )
        );
        $currentRecords = $this->find('all', $params);

        // リクエストデータ
        foreach ($binderPages as $page) {

            // IDリセット
            $this->create(false);
            $binderPageId = $page["BinderPage"]["id"];

            // ユーザーIDをログインユーザーに固定する
            if ($userId != 0) {
                $page["BinderPage"]["userId"] = $userId;
            }

            // バインダーIDを更新 (0の場合は未設定)
            if ($binderId != 0) {
                $page["BinderPage"]["binderId"] = $binderId;
            }

            // データベースのレコードとリクエストデータの一致があるかをチェックする
            foreach ($currentRecords as $key => $current) {
                // オリジナルページIDが一致した場合
                if ($current["BinderPage"]["originalPageId"] == $page["BinderPage"]["originalPageId"]) {
                    $page["BinderPage"]["id"] = $current["BinderPage"]["id"];
                    $binderPageId = $page["BinderPage"]["id"];
                    unset($currentRecords[$key]);
                    break;
                }
            }

            // リクエストデータを保存
            if (!$this->save($page["BinderPage"])) {
                throw new CakeException("[BinderPage] Save failed.");
            }
            if (0 == $binderPageId) {
                $binderPageId = $this->getLastInsertID();
            }

            // タグの保存
            $Tag = new Tag;
            $Tag->saveTags($page["Tags"], TagRelation::PARENT_TYPE_BINDER_PAGE, $binderPageId, $userId);

            // アノテーションの保存
            $Annotation = new Annotation;
            $Annotation->saveAnnotation($page["Annotations"], $binderPageId);

            array_push($binderPageIds, $binderPageId);
        }

        // 現在のデータのうち、リクエストデータと一致しないものがあった場合は削除する
        foreach ($currentRecords as $current) {
            $this->delete($current["BinderPage"]["id"]);
        }

        return $binderPageIds;
    }

    /**
     * バインダーページIDを指定してバインダーページを削除する
     *
     * @param $binderPageId
     * @param $userId
     * @return bool
     * @throws NotFoundException
     * @throws CakeException
     */
    public function deleteBinderPage($binderPageId, $userId)
    {
        App::import('Model','Tag');
        App::import('Model','Annotation');

        // バインダーページ検索
        $params = array(
            'conditions' => array(
                'BinderPage.id' => $binderPageId,
                'BinderPage.userId' => $userId
            )
        );
        $binderPages = $this->find('all', $params);

        // バインダーページがなかった場合は例外を投げる
        if (!$binderPages) {
            throw new NotFoundException("[BinderPage] Requested delete binderPage data does not found.");
        }

        foreach($binderPages as $binderPage) {

            // アノテーション削除
            $Annotation = new Annotation;
            $Annotation->deleteAnnotations($binderPage["BinderPage"]["id"]);

            // タグ削除
            $Tag = new Tag;
            $Tag->deleteTags(TagRelation::PARENT_TYPE_BINDER_PAGE, $binderPage["BinderPage"]["id"], $userId);

            // バインダーページ削除
            if (!$this->delete($binderPage["BinderPage"]["id"])) {
                throw new CakeException("[BinderPage] Delete failed.");
            }
        }

        return true;
    }

    /**
     * バインダーIDを指定してバインダーページを削除する
     *
     * @param $binderId
     * @param $userId
     * @throws CakeException
     */
    public function deleteBinderPages($binderId, $userId)
    {
        App::import('Model','Tag');
        App::import('Model','TagRelation');
        App::import('Model','Annotation');

        // バインダーページ検索
        $params = array(
            'conditions' => array(
                'BinderPage.binderId' => $binderId,
                'BinderPage.userId' => $userId
            )
        );
        $binderPages = $this->find('all', $params);

        // バインダーページがある場合のみ削除実行
        // 無い場合はそのまま処理を終了
        if ($binderPages) {

            foreach($binderPages as $binderPage) {

                // アノテーション削除
                $Annotation = new Annotation;
                $Annotation->deleteAnnotations($binderPage["BinderPage"]["id"]);
                // タグ削除
                $Tag = new Tag;
                $Tag->deleteTags(TagRelation::PARENT_TYPE_BINDER_PAGE, $binderPage["BinderPage"]["id"], $userId);

                // バインダーページ削除
                if (!$this->delete($binderPage["BinderPage"]["id"])) {
                    throw new CakeException("[BinderPage] Delete failed.");
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
$this->log($this->getDataSource()->getLog(),LOG_DEBUG);
        $formatted = array(
            "BinderPage" => array()
        );
        foreach ($results as $page) {
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
                $formatted["BinderPage"],
                array(
                    "id" => $page["BinderPage"]["id"],
                    "userId" => $page["BinderPage"]["userId"],
                    "binderId" => $page["BinderPage"]["binderId"],
                    "originalPageId" => $page["BinderPage"]["originalPageId"],
                    "pageNo" => $page["BinderPage"]["pageNo"],
                    "title" => $page["BinderPage"]["title"],
                    "text" => $page["BinderPage"]["text"],
                    "creator" => $page["BinderPage"]["creator"],
                    "confirmor" => $page["BinderPage"]["confirmor"],
                    "creationDate" => $page["BinderPage"]["creationDate"],
                    "imageId" => $page["BinderPage"]["imageId"],
                    "imageRotate" => $page["BinderPage"]["imageRotate"],
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
                    "Annotation" => $annotations
                )
            );
        }
        return $formatted;
    }

    /**
     * 検索結果を timelinejs 形式のjsonに整形する
     *
     * @param $results
     * @param $userName
     * @param $download_url
     * @return array
     */
    public function timelineFormat($results, $userName, $download_url)
    {
        $formatted = array(
            "date" => array(),
        );
        $flag = true;
        foreach ($results as $page) {

            // Headline 生成
            if ($flag) {
                $flag == false;
                $formatted["headline"] = "Your all binder pages.";
                $formatted["type"] = "default";
                $formatted["text"] = "This is all ".$userName."'s binder pages.";
                $formatted["asset"] = array(
                    "media" => $download_url . $page["Image"]["localFileName"],
                    "credit" => $page["BinderPage"]["title"],
                    "caption" => $page["BinderPage"]["text"],
                );
            }

            // 各ページ出力
            array_push(
                $formatted["date"],
                array(
                    "startDate" => date('Y,m,d', strtotime($page["BinderPage"]["creationDate"])),
                    "endDate" => date('Y,m,d', strtotime($page["BinderPage"]["creationDate"])),
                    "headline" => '<a class="detailButton" data-binder-page-id="' . $page["BinderPage"]["id"] . '">' . $page["BinderPage"]["title"] . '</a>',
                    "text" => $page["BinderPage"]["text"],
                    "asset" => array(
                        "media" => $download_url . $page["Image"]["localFileName"],
                        "credit" => $page["BinderPage"]["title"],
                        "caption" => $page["BinderPage"]["text"],
                    )
                )
            );
        }
        return $formatted;
    }

    /**
     * XML のレスポンスに対応した形式に検索結果を整形する
     *
     * @param $results
     * @param $original
     * @return array
     */
    public function xmlFormat($results, $original)
    {
//        $this->log($this->getDataSource()->getLog(),LOG_DEBUG);
        $formatted = array(
            "BinderPage" => array()
        );

        // TODO: x, y を出力する画像の縮小率で再計算する

        foreach ($results as $page) {
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
                $formatted["BinderPage"],
                array(
                    "pageNo" => $page["BinderPage"]["pageNo"],
                    "title" => $page["BinderPage"]["title"],
                    "text" => $page["BinderPage"]["text"],
                    "creator" => $page["BinderPage"]["creator"],
                    "confirmor" => $page["BinderPage"]["confirmor"],
                    "creationDate" => $page["BinderPage"]["creationDate"],
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
        return $formatted;
    }

}

