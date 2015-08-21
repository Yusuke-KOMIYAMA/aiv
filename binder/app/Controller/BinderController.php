<?php
/**
 * ユニバーサルビューアーアプリ
 */
class BinderController extends AppController {

    public $name = 'Binder';

    public $uses = null;
    public $components = array('Session');

    public $layout = ""; // レイアウトファイル
    public $autoLayout;  // 自動レイアウト
    public $autoRender;  // 自動レンダリング

    public $user;
    public $loginUser;

    /**
     * この関数はコントローラの各アクションの前に実行されます。
     *
     * 1.認証済みチェック
     * 2.ユーザー種別が管理者の場合は /users/menu へリダイレクト
     */
    public function beforeFilter() {

        Configure::load("uv.php");

        CakeSession::start();

        // 認証済みかどうかチェックする
        if (!$this->Session->check('user')) {
            // ログイン画面へリダイレクト
            $this->response->header('Location', Configure::read("login.login_uri"));
        }

        $this->user = $this->Session->read("user");
        $this->loginUser = $this->Session->read("loginUser");

        // バインダーにアクセス可能か？（
        if ($this->user['User']['authority'] == 9) {
            $this->redirect(array('controller' => 'users', 'action' => 'menu'));
        }

    }

    /**
     * ユニバーサルビューアーメインアプリ表示する
     */
    public function index()
    {
        $path = func_get_args();

        $this->autoLayout = true;//自動レイアウト
        $this->autoRender = true;//自動レンダリング

        $this->set('user', $this->user);
        $this->set('loginUser', $this->loginUser);
    }

    /**
     * ユニバーサルビューアー画像アップロードアプリを表示する
     */
    public function upload()
    {
        $path = func_get_args();

        $this->autoLayout = true;//自動レイアウト
        $this->autoRender = true;//自動レンダリング

        $this->set('user', $this->user);
        $this->set('loginUser', $this->loginUser);
    }

}
