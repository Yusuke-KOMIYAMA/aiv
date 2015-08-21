<?php
/**
 * LoginController.php
 * Date: 2015/02/03
 */
App::uses('AppController', 'Controller');

use InoOicClient\Flow\Basic;
use InoOicClient\Http\ClientFactory;
use InoOicClient\Oic\UserInfo;
use InoOicClient\Client\ClientInfo;
use InoOicClient\Oic\Authorization;
use InoOicClient\Oic\Authorization\State\Manager;
use InoOicClient\Http;
use InoOicClient\Oic\Token;
use InoOicClient\Oic\Exception\ErrorResponseException;
use InoOicClient\Oic\Authorization\Exception\StateException;
use Zend\Http\Client;

class LoginController extends AppController
{
    public $name = 'Login';

    public $components = array(
        'Session',
    );
    public $uses = array(
        'User',
        'Log',
    );

    public $layout = "";
    public $autoLayout = false; //自動レイアウト
    public $autoRender = false; //自動レンダリング

    /**
     * アクション前処理
     */
    public function beforeFilter()
    {
        parent::beforeFilter();

        // セッションの開始
        CakeSession::start();

        // 設定ファイルの読込
        Configure::load("binder.php");
        Configure::load("uv.php");
        Configure::load("log.php");
    }

    /**
     * ログイントップ
     *
     * シングルサインオンを利用する設定の場合は直接 SSOサーバー へリダイレクト
     */
    public function index()
    {
        try {
            // ログインしていた場合はログイン後の画面へ移動
            $allowSSOLogin = Configure::read('allowSSOLogin');
            $allowLocalLogin = Configure::read('allowLocalLogin');
            $uri = '';
            $message = "";

            // ログインチェック
            if ($this->Session->check('user')) {
                // 既にログインしていた場合はログイン後ホームへリダイレクト
                $this->response->header('Location', Configure::read("login.loggedin_uri"));
            }

            $this->autoRender = true; //自動レンダリング

            if ($this->request->is('post')) {

                $localLoginForm = $this->request->data['localLoginForm'];

                //◆認証
                if($this->User->auth($localLoginForm)){
                    //◆OK
                    $user = $this->User->getByLoginid($localLoginForm);
                    if ($allowLocalLogin || $user['User']['authority'] == 9) {
                        $user['User']['displayName'] = $user['User']['userName'];
                        //ユーザー情報をセッションにセット
                        $this->Session->write('loginUser', $user);
                        $this->Session->write('user', $user);
                        $this->Session->write('loginType', 'LOCAL');
                        $this->log($user, LOG_DEBUG);

                        // Logging: Localログイン成功
                        $this->Log->write("1011", $user['User']['id'], "User[".$user['User']['userName']."] logged in.");

                        if ($user["User"]['authority'] == 9) {
                            //ログイン後 home にリダイレクト
                            return $this->redirect(array('controller' => 'users', 'action' => 'menu'));
                        }
                        else {
                            //ログイン後 home にリダイレクト
                            $this->response->header('Location', Configure::read("login.loggedin_uri"));
                        }
                    }
                    else {

                        // Logging: Localログイン失敗
                        $this->Log->write("1012", $user['User']['id'], "User[".$user['User']['userName']."] login failed.");

                        //◆NG エラーメッセージID
                        $message = "0001";
                    }

                }else{

                    //◆NG エラーメッセージID
                    $message = "0001";
                }

            }

            // シングルサインオンを利用する
            if ($allowSSOLogin) {

                // 1.Client info オブジェクトを生成
                $clientOptions = array(
                    'client_id' => Configure::read("login.client_id"),
                    'redirect_uri' => Configure::read("login.redirect_uri"),

                    'authorization_endpoint' => Configure::read("login.authorization_endpoint"),
                    'token_endpoint' => Configure::read("login.token_endpoint"),
                    'user_info_endpoint' => Configure::read("login.userinfo_endpoint"),

                    'authentication_info' => array(
                        'method' => 'client_secret_post',
                        'params' => array(
                            'client_secret' => Configure::read("login.client_secret")
                        )
                    )
                );
                $clientInfo = new ClientInfo();
                $clientInfo->fromArray($clientOptions);

                // 2.Authorization Request URI の準備
                $stateManager = new Manager();

                $dispatcher = new Authorization\Dispatcher();
                $dispatcher->setStateManager($stateManager);

                $request = new Authorization\Request($clientInfo, 'code', 'openid profile email');
                $uri = $dispatcher->createAuthorizationRequestUri($request);

            }

            // SSO用のログイン表示設定
            $this->set('allowSSOLogin', $allowSSOLogin);
            $this->set('allowLocalLogin', $allowLocalLogin);

            // SSOサーバーへのURL設定
            $this->set('uri', $uri);

            //画面表示
            $this->set("message", $message);

            // 直接リダイレクトする場合
            //  $this->response->header('Location', $uri);

        }
        catch(BadRequestException $e) {
        }
        catch(UnauthorizedException $e) {
        }
        catch(ForbiddenException $e) {
        }
        catch(NotFoundException $e) {
        }
        catch(CakeException $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
        catch(Exception $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }

    }

    /**
     * SSOサーバーからの戻りアクション
     */
    public function signup()
    {
        try {

            // 1.2.Client info オブジェクトを生成
            $clientOptions = array(
                'client_id' => Configure::read("login.client_id"),
                'redirect_uri' => Configure::read("login.redirect_uri"),
                'authorization_endpoint' => Configure::read("login.authorization_endpoint"),
                'token_endpoint' => Configure::read("login.token_endpoint"),
                'user_info_endpoint' => Configure::read("login.userinfo_endpoint"),
                'authentication_info' => array(
                    'method' => 'client_secret_post',
                    'params' => array(
                        'client_secret' => Configure::read("login.client_secret")
                    )
                )
            );
            $clientInfo = new ClientInfo();
            $clientInfo->fromArray($clientOptions);

            // 3.認証コードを取得
            $stateManager = new Manager();

            $dispatcher = new Authorization\Dispatcher();
            $dispatcher->setStateManager($stateManager);

            $response = $dispatcher->getAuthorizationResponse();

            $tokenRequest = new Token\Request();
            $tokenRequest->fromArray(
                array(
                    'client_info' => $clientInfo,
                    'code' => $response->getCode(),
                    'grant_type' => 'authorization_code'
                )
            );

            $httpClientFactory = new ClientFactory();
            $httpClient = $httpClientFactory->createHttpClient();
            $tokenDispatcher = new Token\Dispatcher($httpClient);

            // Tokenデータ取得
            $tokenResponse = $tokenDispatcher->sendTokenRequest($tokenRequest);
            $token_endpoint_result = \Zend\Json\Json::encode($tokenResponse, \Zend\Json\Json::TYPE_ARRAY);

            $id_token = $tokenResponse->getIdToken();
            $access_token = $tokenResponse->getAccessToken();

            list($header, $payload, $token_signature) = explode('.', $id_token);
            // BASE64デコードする
            $header_cont = base64_decode(str_pad(strtr($header, '-_', '+/'), strlen( $header ) % 4, '=', STR_PAD_RIGHT));
            $payload_cont = base64_decode(str_pad(strtr($payload, '-_', '+/'), strlen( $payload ) % 4, '=', STR_PAD_RIGHT));
            $header_json = json_decode( $header_cont );
            $payload_json = json_decode( $payload_cont );

            $userInfoRequest = new UserInfo\Request();
            $userInfoRequest->setAccessToken($tokenResponse->getAccessToken());
            $userInfoRequest->setClientInfo($clientInfo);

            $userInfoDispatcher = new UserInfo\Dispatcher($httpClient);

            $userInfoResponse = $userInfoDispatcher->sendUserInfoRequest($userInfoRequest);

            $userinfo_endpoint_result = \Zend\Json\Json::encode($userInfoResponse->getClaims(), \Zend\Json\Json::TYPE_ARRAY);

            // メインページへの情報引継ぎ
            $this->Session->write('sso.id_token_encode', $id_token);
            $this->Session->write('sso.access_token', $access_token);
            $this->Session->write('sso.id_token', $payload_json);
            $this->Session->write('sso.userinfo', $userinfo_endpoint_result);

            // sub, name, family_name, given_name, updated_at が userinfo として取得できる
            $userInfo = \Zend\Json\Json::decode($userinfo_endpoint_result);
            $ssoUserId = $userInfo->name;
            $ssoUserName = $userInfo->name;

            // ============================================================================
            // UniversalViewer 側のログイン処理
            // ============================================================================

            // ユーザー情報を取得
            $user = $this->User->getBySSOUserId($ssoUserId);

            // ユーザー情報がない場合
            if (is_null($user) || count($user) === 0) {
                // Logging: SSO Login failed.
                $this->Log->write("1014", 0, "[SSO Login] Failed.");
                $this->redirect(array('controller'=>'login', 'action'=>'nouser'));
            }

            // セッションにユーザー情報を保持
            $user['User']['displayName'] = (is_null($ssoUserName)||$ssoUserName==='')?$user['User']['userName']:$ssoUserName;
            $this->Session->write('loginUser', $user);
            $this->Session->write('user', $user);
            $this->Session->write('loginType', 'SSO');

            // Logging: SSO Login succeed.
            $this->Log->write("1013", $user['User']['id'], "[SSO Login] User[".$user['User']['userName']."] logged in.");

            // ログイン後のページへリダイレクト
            $this->response->header('Location', Configure::read("login.loggedin_uri"));

        }
        catch (ErrorResponseException $e) {
        }
        catch (StateException $e) {
        }
        catch(BadRequestException $e) {
        }
        catch(UnauthorizedException $e) {
        }
        catch(ForbiddenException $e) {
        }
        catch(NotFoundException $e) {
        }
        catch(CakeException $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
        catch(Exception $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }

        $this->autoRender = true;
    }

    /**
     * ローカルユーザーが見つからなかった時のアクション
     */
    public function nouser()
    {
        $this->autoRender = true; //自動レンダリング
    }

    /**
     * ローカル側ログアウト
     */
    public function logout()
    {
        if ($this->Session->check('loginType')) {
            $this->set('loginType', $this->Session->read("loginType"));
        }
        else {
            $this->set('loginType', '');
        }

        if ($this->Session->read('user')) {
            $usr = $this->Session->read('user');
            $userId = $usr['User']['id'];

            // Logging: User logout.
            $this->Log->write("1015", $userId, "[Logout] User logout succeed.");

            $this->Session->delete('user');
            $this->Session->delete('loginUser');
            $this->Session->delete('loginType');
        }

        $this->autoRender = true; //自動レンダリング
    }

    /**
     * 監督者のみ、ユーザーを切り替え可能
     */
    public function change()
    {
        try {

            // ユーザー認証されていない場合は 401 を返す
            if (!$this->Session->check('user')) {
                throw new UnauthorizedException("[Api controller] Authorization failed.");
            }

            // ユーザー情報の取得
            $loginUser = $this->Session->read('loginUser');
            $user = $this->Session->read('user');

            // 監督者でない場合はリクエストエラー
            if ($loginUser['User']['authority'] != 1) {
                throw new BadRequestException("[Api controller] This user can not change the operation user.");
            }

            // 変更するユーザーの情報を取得
            if (!$this->request->is('post')) {
                throw new BadRequestException("[Api controller] Cannot find request data.");
            }

            $userId = $this->request->data['userId'];
            if (empty($userId) || !is_numeric($userId)) {
                throw new BadRequestException("[Api controller] Cannot find userId at request data.");
            }

            // 変更するユーザーを取得する
            $changedUser = $this->User->getByUserId($userId);
            if (!$changedUser) {
                throw new NotFoundException("[Api controller] Cannot find user data.");
            }

            // Logging: Change user succeed.
            $this->Log->write("1016", $loginUser['User']['id'], "[CHANGE USER] User changed to ".$user['User']['userName']."(UserID:".$user['User']['id'].").");

            // ユーザーを変更する
            if ($loginUser['User']['id'] == $userId) {
                $this->Session->write('user', $loginUser);
            }
            else {
                $changedUser['User']['displayName'] = $changedUser['User']['userName'];
                $this->Session->write('user', $changedUser);
            }

            // ホームへ戻る
            $this->response->header('Location', Configure::read("login.loggedin_uri"));

        }
        catch(UnauthorizedException $e) {
            // ログイン画面へリダイレクト
            $this->response->header('Location', Configure::read("login.login_uri"));
        }
        catch(BadRequestException $e) {
        }
        catch(Exception $e) {
            // Logging: 500 error happened.
            $this->Log->write("0011", 0, "[500 Error] Server execute error happened.");
        }
    }
}


