<?php
/**
 * API controller
 *
 * ■ Request
 * GET: /api/:model.ext 検索
 *     action: index()
 * GET: /api/:model/:id.ext 検索
 *     action: get()
 * POST: /api/:model.ext 保存（条件付）
 *     action: save()
 * POST: /api/:model/:id.ext 保存
 *     action: save()
 * PUT: /api/:model.ext 検索（条件付）
 *     action: select()
 * PUT: /api/:model/:id.ext 検索（条件付）
 *     action: select()
 * DELETE: /api/:model/:id.ext 削除（対象ID）
 *     action: delete()
 *
 * ■ Result (Status code)
 * 200: OK (検索成功・更新された)
 * 201: Created (生成された)
 * 204: No content (削除された)
 * 400: フォーマット不正・データ不正
 * 401: 認証NG
 * 403: 権限がない
 * 404: URL不正・リソースがなかった
 * 405: 許可されていないメソッドを利用した
 * 406: Acceptヘッダー不正
 * 409: コンフリクトした
 * 415: サポートされないメディアタイプ（Content-type）
 * 500: リソースの検索・生成・更新・削除に失敗
 *
 * ■ Response (JSON ... XMLも可能か)
 */

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class ApiController extends AppController
{
    public $components = array('RequestHandler');

    public $uses = array(
        'Binder',
        'BinderPage',
        'OriginalPage',
        'Image',
        'Annotation',
        'AnnotationText',
        'Tag',
        'TagRelation',
        'VTag',
        'User',
        'Log',
    );

    public $user;
    public $loginUser;

    /**
     * アクション前処理
     */
    public function beforeFilter()
    {
        if ($this->request->is('options')) {
            $this->_set_json('OK');
        }

        // REST 対応
        $this->response->header('Access-Control-Allow-Origin: *');
        $this->response->header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        $this->response->header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

        // セッション開始
        CakeSession::start();

        // 設定ファイル読込
        Configure::load("uv.php");
        Configure::load("log.php");

        if ($this->Session->check('user')) {
            $this->user = $this->Session->read("user");
            $this->loginUser = $this->Session->read("loginUser");
        }

    }

    /**
     * 全件取得
     *
     * @access GET /api/:model.json
     * @param modelName Inflector::pluralize(ucfirst($this->params["model"]))
     */
    public function index()
    {
        try {
            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");

            if ($this->request->is('options') == false) {

                // モデル名を先頭大文字かつ複数形にする
                $modelName = Inflector::pluralize(ucfirst($this->params["model"]));

                // ユーザー情報取得
                $userId = $this->user["User"]["id"];

                switch($modelName) {

                    case "Binders":

                        // バインダー検索
                        $this->_set_json(
                            $this->Binder->restFormat(
                                $this->Binder->selectWithByUserId($userId)
                            )
                        );
                        break;

                    case "BinderPages":

                        // バインダーページ検索
                        $this->_set_json(
                            $this->BinderPage->restFormat(
                                $this->BinderPage->selectWithByUserId($userId)
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "OriginalPages":

                        // オリジナルページ検索
                        $this->_set_json(
                            $this->OriginalPage->restFormat(
                                $this->OriginalPage->selectWithByUserId($userId)
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "Tags":

                        // タグ検索
                        $this->_set_json(
                            $this->VTag->restFormat(
                                $this->VTag->selectWithByUserId($userId)
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "Users":

                        // ログインユーザーが「監督者」の場合のみ許可
                        $loginUser = $this->Session->read("loginUser");

                        if ($loginUser['User']['authority'] != 1) {
                            throw new ForbiddenException("[Api controller] Forbidden.");
                        }

                        // 全件検索を行う
                        $this->_set_json(
                            $this->User->find('all',
                                array(
                                    'conditions' => array(
                                        'NOT' => array(
                                            'User.authority' => 9,
                                        ),
                                    )
                                )
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "Images":
                    case "Logs":
                    case "TagRelations":

                        // APIからの検索を禁止しているレコード
                        // 403: 権限がない
                        throw new ForbiddenException("[Api controller] Forbidden.");
                        break;

                    default:

                        // 全件検索
                        $this->_set_json(
                            $this->{ucfirst($this->params["model"])}->find('all')
                        );
                        $this->response->statusCode(200);
                        break;
                }
            }
        }
        catch(BadRequestException $e) {
            $this->response->statusCode(400);
            $this->_set_json(array());
        }
        catch(UnauthorizedException $e) {
            $this->response->statusCode(401);
            $this->_set_json(array());
        }
        catch(ForbiddenException $e) {
            $this->response->statusCode(403);
            $this->_set_json(array());
        }
        catch(NotFoundException $e) {
            $this->response->statusCode(404);
            $this->_set_json(array());
        }
        catch(CakeException $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }
        catch(Exception $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }
    }

    /**
     * データを1件取得
     *
     * @access GET /api/:model/:id.json
     * @param modelName Inflector::pluralize(ucfirst($this->params["model"]))
     * @param id $this->params["id"]
     */
    public function get()
    {
        try {
            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");

            if ($this->request->is('options') == false) {

                // モデル名を先頭大文字かつ複数形にする
                $modelName = Inflector::pluralize(ucfirst($this->params["model"]));

                // ユーザー情報取得
                $userId = $this->user["User"]["id"];

                // ID取得
                $id = $this->params["id"];

                switch ($modelName) {

                    case "OriginalPages":

                        // オリジナルページ検索
                        $this->_set_json(
                            $this->OriginalPage->restFormat(
                                $this->OriginalPage->selectWithByIdAndUserId($id, $userId)
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "Binders":

                        // バインダー検索
                        $this->_set_json(
                            $this->Binder->restFormat(
                                $this->Binder->selectWithByIdAndUserId($id, $userId)
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "BinderPages":

                        // バインダーページ検索
                        $this->_set_json(
                            $this->BinderPage->restFormat(
                                $this->BinderPage->selectWithByIdAndUserId($id, $userId)
                            )
                        );
                        $this->response->statusCode(200);
                        break;

                    case "Images":
                    case "Logs":
                    case "Users":
                    case "Tags":
                    case "TagRelations":

                        // Apiからの検索禁止モデル
                        // 403: 権限がない
                        throw new ForbiddenException("[Api controller] Forbidden.");
                        break;

                    default:

                        // 全件検索
                        $this->_set_json(
                            $this->{ucfirst($this->params["model"])}->findById($id)
                        );
                        $this->response->statusCode(200);
                        break;

                }
            }
        }
        catch(BadRequestException $e) {
            $this->response->statusCode(400);
            $this->_set_json(array());
        }
        catch(UnauthorizedException $e) {
            $this->response->statusCode(401);
            $this->_set_json(array());
        }
        catch(ForbiddenException $e) {
            $this->response->statusCode(403);
            $this->_set_json(array());
        }
        catch(NotFoundException $e) {
            $this->response->statusCode(404);
            $this->_set_json(array());
        }
        catch(CakeException $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }
        catch(Exception $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }

    }

    /**
     * 詳細検索
     *
     * 1.オリジナルページ
     * 2.バインダーページ
     * 3.タグ
     *
     * @access PUT /api/:model(/:id).json
     * @param modelName Inflector::pluralize(ucfirst($this->params["model"]))
     * @param :id $this->params["id"] (option)
     */
    public function select()
    {
        try {
            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");
            if ($this->request->is('options') == false) {

                // モデル名を先頭大文字かつ複数形にする
                $modelName = Inflector::pluralize(ucfirst($this->params["model"]));

                // ユーザーID取得
                $userId = $this->user["User"]["id"];
                switch ($modelName) {

                    case "OriginalPages":

                        // オリジナルページ「削除したものを含めたID検索」
                        if (isset($this->params["id"])) {

                            // IDを取得
                            $id = $this->params["id"];
                            $this->OriginalPage->Behaviors->detach('SoftDelete');
                            $this->_set_json(
                                $this->OriginalPage->restFormat(
                                    $this->OriginalPage->selectWithByIdAndUserId($id, $userId)
                                )
                            );

                        }
                        // オリジナルページ「詳細条件」検索
                        else {
                            if ($this->request->data('text')) {
                                $this->_set_json(
                                    $this->OriginalPage->restFormat(
                                        $this->OriginalPage->selectWithConditions(
                                            $userId,
                                            $this->request->data('text'),
                                            $this->request->data('isTitle'),
                                            $this->request->data('isText'),
                                            $this->request->data('isTag')
                                        )
                                    )
                                );
                            }
                            // オリジナルページ「タグ」検索
                            elseif ($this->request->data('tag')) {
                                $this->_set_json(
                                    $this->OriginalPage->restFormat(
                                        $this->OriginalPage->selectWithTag(
                                            $userId,
                                            $this->request->data('tag')
                                        )
                                    )
                                );
                            }
                        }
                        break;

                    case "BinderPages":

                        // TimelineJS 用検索
                        // ログインユーザー全バインダーページを取得する
                        // creationDate 昇順で並び換え
                        if ($this->request->data('timeline')) {
                            $this->_set_json(
                                $this->BinderPage->restFormat(
                                    $this->BinderPage->selectForTimeline($userId)
                                )
                            );
                        }
                        // バインダーページ「詳細条件」検索
                        elseif ($this->request->data('text')) {
                            $this->_set_json(
                                $this->BinderPage->restFormat(
                                    $this->BinderPage->selectWithConditions(
                                        $userId,
                                        $this->request->data('text'),
                                        $this->request->data('isTitle'),
                                        $this->request->data('isText'),
                                        $this->request->data('isTag'),
                                        $this->request->data('isAnnotation')
                                    )
                                )
                            );
                        }
                        // タグ検索
                        elseif ($this->request->data('tag')) {
                            $this->_set_json(
                                $this->BinderPage->restFormat(
                                    $this->BinderPage->selectWithTag(
                                        $userId,
                                        $this->request->data('tag')
                                    )
                                )
                            );
                        }
                        break;

                    case "Tags":

                        // オリジナルページタグサジェスト用
                        if ($this->request->data('model') && $this->request->data('model') == "originalPage") {
                            $this->_set_json(
                                $this->VTag->restFormat(
                                    $this->VTag->selectWithByUserIdForOriginalPage($userId)
                                )
                            );
                        }
                        // バインダーページタグサジェスト用
                        else if ($this->request->data('model') && $this->request->data('model') == "binderPage") {
                            $this->_set_json(
                                $this->VTag->restFormat(
                                    $this->VTag->selectWithByUserIdForBinderPage($userId)
                                )
                            );
                        }
                        // モデル対象なし
                        else {
                            $this->_set_json(
                                $this->VTag->restFormat(
                                    $this->VTag->selectWithByUserId($userId)
                                )
                            );
                        }
                        break;

                    default:

                        // 403: 権限がない
                        throw new ForbiddenException("[Api controller] Forbidden.");
                        break;

                }
            }
        }
        catch(BadRequestException $e) {
            $this->response->statusCode(400);
            $this->_set_json(array());
        }
        catch(UnauthorizedException $e) {
            $this->response->statusCode(401);
            $this->_set_json(array());
        }
        catch(ForbiddenException $e) {
            $this->response->statusCode(403);
            $this->_set_json(array());
        }
        catch(NotFoundException $e) {
            $this->response->statusCode(404);
            $this->_set_json(array());
        }
        catch(CakeException $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }
        catch(Exception $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }

    }

    /**
     * データの保存（新規生成・更新）
     *
     * @access POST /api/:model(/:id).json
     * @param modelName Inflector::pluralize(ucfirst($this->params["model"]))
     * @param :id $this->params["id"] (option)
     */
    public function save()
    {
        try {
            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");

            if ($this->request->is('options') == false) {

                // モデル名を先頭大文字かつ複数形にする
                $modelName = Inflector::pluralize(ucfirst($this->params["model"]));

                // ユーザーID取得
                $userId = $this->user["User"]["id"];

                switch($modelName) {

                    case "OriginalPages":

                        // リクエストデータに複数形のモデル名でデータが入っている場合は保存を試みる
                        if ($this->request->data($modelName)) {

                            try {

                                // トランザクション開始
                                $this->OriginalPage->begin();
                                $this->Tag->begin();
                                $this->TagRelation->begin();

                                // 更新を行う。新規保存は 500エラーを返す
                                $resultIds = $this->OriginalPage->updateOriginalPages($this->request->data($modelName), $userId);

                                // 保存したデータを返す
                                $params = array(
                                    'conditions' => array(
                                        'OriginalPage.id' => $resultIds
                                    )
                                );
                                $this->_set_json(
                                    $this->OriginalPage->restFormat(
                                        $this->OriginalPage->selectWith($params)
                                    )
                                );

                                // コミット
                                $this->TagRelation->commit();
                                $this->Tag->commit();
                                $this->OriginalPage->commit();

                                // 保存の成功
                                $this->response->statusCode(200);

                                // Logging: OriginalPage saved.
                                $this->Log->write("1033", $this->loginUser['User']['id'], "OriginalPage saved. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",OriginalPageID:" . implode(',', $resultIds));
                            }
                            catch(Exception $e) {
                                // ロールバック
                                $this->TagRelation->rollback();
                                $this->Tag->rollback();
                                $this->OriginalPage->rollback();
                                throw $e;
                            }
                        }
                        else {
                            // リクエストデータ不正
                            throw new BadRequestException("[Api controller] Bad request.");
                        }
                        break;

                    case "Binders":

                        // リクエストデータに複数形のモデル名でデータが入っている場合は保存を試みる
                        if ($this->request->data($modelName)) {

                            try {

                                // トランザクション開始
                                $this->Binder->begin();
                                $this->BinderPage->begin();
                                $this->Tag->begin();
                                $this->TagRelation->begin();

                                // 保存する
                                $resultIds = $this->Binder->saveBinders($this->request->data($modelName), $userId);

                                // 保存したデータを返す
                                $params = array(
                                    'conditions' => array(
                                        'Binder.id' => $resultIds
                                    )
                                );
                                $this->_set_json(
                                    $this->Binder->restFormat(
                                        $this->Binder->selectWith($params)
                                    )
                                );

                                // コミット
                                $this->TagRelation->commit();
                                $this->Tag->commit();
                                $this->BinderPage->commit();
                                $this->Binder->commit();

                                // 保存の成功
                                $this->response->statusCode(200);

                                // Logging: Binder saved.
                                $this->Log->write("1031", $this->loginUser['User']['id'], "Binder saved. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",BinderID:" . implode(',', $resultIds));
                            }
                            catch(Exception $e) {
                                // ロールバック
                                $this->TagRelation->rollback();
                                $this->Tag->rollback();
                                $this->BinderPage->rollback();
                                $this->Binder->rollback();
                                throw $e;
                            }

                        }
                        else {
                            // リクエストデータ不正
                            throw new BadRequestException("[Api controller] Bad request.");
                        }
                        break;

                    case "BinderPages":

                        // バインダーページを単体で保存する
                        // バインダーページをバインダー単位でまとめて保存する場合は、バインダーを保存してください

                        // リクエストデータに複数形のモデル名でデータが入っている場合は保存を試みる
                        if ($this->request->data($modelName)) {

                            try {
                                // トランザクション開始
                                $this->BinderPage->begin();
                                $this->Tag->begin();
                                $this->TagRelation->begin();
                                $this->Annotation->begin();
                                $this->AnnotationText->begin();

                                // バインダーページ保存
                                $resultId = $this->BinderPage->saveBinderPage($this->request->data($modelName), $userId);

                                // 保存したバインダーページ一覧を返す
                                $params = array(
                                    'conditions' => array(
                                        'BinderPage.id' => $resultId
                                    )
                                );
                                $this->_set_json(
                                    $this->BinderPage->restFormat(
                                        $this->BinderPage->selectWith($params)
                                    )
                                );

                                // コミット
                                $this->AnnotationText->commit();
                                $this->Annotation->commit();
                                $this->TagRelation->commit();
                                $this->Tag->commit();
                                $this->BinderPage->commit();

                                // 保存の成功
                                $this->response->statusCode(200);

                                // Logging: BinderPage saved.
                                $this->Log->write("1032", $this->loginUser['User']['id'], "BinderPage saved. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",BinderPageID:" . $resultId);

                            }
                            catch(Exception $e) {
                                // ロールバック
                                $this->AnnotationText->rollback();
                                $this->Annotation->rollback();
                                $this->TagRelation->rollback();
                                $this->Tag->rollback();
                                $this->BinderPage->rollback();
                                throw $e;
                            }
                        }
                        else {
                            // リクエストデータ不正
                            throw new BadRequestException("[Api controller] Bad request.");
                        }
                        break;

                    default:
                        // 403: 権限がない
                        throw new ForbiddenException("[Api controller] Forbidden.");
                        break;
                }
            }
        }
        catch(BadRequestException $e) {
            $this->response->statusCode(400);
            $this->_set_json(array());
        }
        catch(UnauthorizedException $e) {
            $this->response->statusCode(401);
            $this->_set_json(array());
        }
        catch(ForbiddenException $e) {
            $this->response->statusCode(403);
            $this->_set_json(array());
        }
        catch(NotFoundException $e) {
            $this->response->statusCode(404);
            $this->_set_json(array());
        }
        catch(CakeException $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }
        catch(Exception $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }

    }

    /**
     * 削除
     *
     * @access DELETE /api/:model(/:id).json
     * @param modelName Inflector::pluralize(ucfirst($this->params["model"]))
     * @param id $this->params["id"] (option)
     */
    public function delete()
    {
        try {

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $this->user = $this->Session->read("user");

            if ($this->request->is('options') == false) {

                // モデル名を先頭大文字かつ複数形にする
                $modelName = Inflector::pluralize(ucfirst($this->params["model"]));

                // ユーザーID取得
                $userId = $this->user["User"]["id"];

                switch ($modelName) {

                    case "OriginalPages":

                        // ID指定削除
                        if (!isset($this->params["id"])) {
                            // 404 対象のメッソッドなし
                            throw new NotFoundException("This method does not found.");
                        }

                        // IDを取得
                        $id = $this->params["id"];

                        try {
                            // トランザクション開始
                            $this->OriginalPage->begin();
                            $this->Tag->begin();
                            $this->TagRelation->begin();

                            // 削除
                            $this->OriginalPage->deleteOriginalPage($id, $userId);

                            // 成功
                            $this->response->statusCode(200);
                            $this->_set_json(array());

                            // コミット
                            $this->TagRelation->commit();
                            $this->Tag->commit();
                            $this->OriginalPage->commit();

                            // Logging: OriginalPage saved.
                            $this->Log->write("1036", $this->loginUser['User']['id'], "OriginalPage deleted. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",ID:" . $id);

                        }
                        catch (Exception $e) {
                            // ロールバック
                            $this->TagRelation->rollback();
                            $this->Tag->rollback();
                            $this->OriginalPage->rollback();
                            throw $e;
                        }
                        break;

                    case "Binders":

                        // ID指定なし
                        if (!isset($this->params["id"])) {
                            // 404 対象のメッソッドなし
                            throw new NotFoundException("This method does not found.");
                        }

                        // IDを取得
                        $id = $this->params["id"];

                        try {
                            // トランザクション開始
                            $this->Binder->begin();
                            $this->BinderPage->begin();
                            $this->Tag->begin();
                            $this->TagRelation->begin();

                            // Binder 削除
                            $this->Binder->deleteBinder($id, $userId);

                            // 成功
                            $this->response->statusCode(200);
                            $this->_set_json(array());

                            // コミット
                            $this->TagRelation->commit();
                            $this->Tag->commit();
                            $this->BinderPage->commit();
                            $this->Binder->commit();

                            // Logging: Binder deleted.
                            $this->Log->write("1034", $this->loginUser['User']['id'], "Binder deleted. LoginUser:" . $this->loginUser['User']['id'] . ",User:" . $this->user['User']['id'] . ",ID:" . $id);
                        }
                        catch(Exception $e) {
                            // ロールバック
                            $this->TagRelation->rollback();
                            $this->Tag->rollback();
                            $this->BinderPage->rollback();
                            $this->Binder->rollback();
                            throw $e;
                        }
                        break;

                    default:
                        break;
                }
            }
        }
        catch(BadRequestException $e) {
            $this->response->statusCode(400);
            $this->_set_json(array());
        }
        catch(UnauthorizedException $e) {
            $this->response->statusCode(401);
            $this->_set_json(array());
        }
        catch(ForbiddenException $e) {
            $this->response->statusCode(403);
            $this->_set_json(array());
        }
        catch(NotFoundException $e) {
            $this->response->statusCode(404);
            $this->_set_json(array());
        }
        catch(CakeException $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }
        catch(Exception $e) {
            $this->response->statusCode(500);
            $this->_set_json(array());
        }

    }

    /**
     * 検索結果をJSON形式に変換
     *
     * @param $results
     */
    function _set_json($results)
    {
        $this->set(array(
            'results' => $results,
            '_serialize' => array('results')
        ));
    }

}

