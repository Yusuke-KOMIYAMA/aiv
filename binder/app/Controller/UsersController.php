<?php

Class UsersController extends AppController {

	public $name = 'User';
	public $uses = array('User','Log');
	public $layout = "admin";

    public $helpers = array('Html', 'Form', 'Session');
    public $components = array('Session', // Sessionコンポーネント
						);

	public $binder;
	public $user = array();


//	public $autoLayout = true;
//	public $autoRender = true;

	public function beforeFilter() {
		parent :: beforeFilter();

		//言語切り替えテスト
		//Configure::write('Config.language', 'en');

		//config
        Configure::load("uv.php");
        Configure::load("log.php");

        CakeSession::start();

        // 認証済みかどうかチェックする
        if (!$this->Session->check('user')) {
            // ログイン画面へリダイレクト
            $this->response->header('Location', Configure::read("login.login_uri"));
        }

        $this->user = $this->Session->read("user");

		//システム管理者以外は通常メインへ
		if($this->user["User"]["authority"]  != 9){
			//$this->redirect(array('controller' => 'binder'));
		}
	}

	//ディレクトリ作成
	private function makeDir($path){
		$msg = "";

	    $msg .= $path .'<br />';

	    if (file_exists($path)) {
	        $msg .= 'exists<br />';
	    }
	    else {
	        $msg .= 'not exists<br />';
			if(mkdir($path, 0777)){
				$msg .= 'creat true<br />'; 
				if(chmod($path, 0777)){
					$msg .= 'chmod true<br />'; 
				}else{
					$msg .= 'chmod false<br />'; 
				}

			}else{
				$msg .= 'create false<br />'; 
			}
	    }

	    $msg .= '<br />';

		//echo $msg;

	}

	//ユーザーディレクトリ作成
	public function makeuserdir($user){
		//config
        Configure::load("uv.php");
		$configMedia = Configure::read("media");

//		echo "<pre>";
//		echo print_r($user);
//		echo "</pre>";

//		echo "<pre>";
//		echo print_r($configMedia);
//		echo "</pre>";

		//1.tmp_dir以下に$userIdディレクトリ
		$tmp_dir = $configMedia["tmp_dir"] . "/" . $user["User"]["id"] . "/";
		$this->makeDir($tmp_dir);

		//2.upload_dir以下に$userIdディレクトリ
		$upload_dir =  $configMedia["upload_dir"] . "/" . $user["User"]["id"] . "/";
		$this->makeDir($upload_dir);

		//3.user_dir以下に$userIdディレクトリ(UDIR)
		$UDIR = $configMedia["user_dir"] . "/" . $user["User"]["id"] ;
		$this->makeDir($UDIR);

		//4.UDIR以下にthumbディレクトリ(TDIR)
		$UDIRthumb = $UDIR. "/" . "thumb";
		$this->makeDir($UDIRthumb);

		//5.UDIR以下にdziディレクトリ
		$UDIRdzi = $UDIR. "/" . "dzi";
		$this->makeDir($UDIRdzi);

		//6.TDIR以下にthumb/smallディレクトリ
		$UDIRsmall = $UDIRthumb. "/" . "small";
		$this->makeDir($UDIRsmall);

		//7.TDIR以下にthumb/middleディレクトリ
		$UDIRmiddle = $UDIRthumb. "/" . "middle";
		$this->makeDir($UDIRmiddle);

		//8.TDIR以下にthumb/largeディレクトリ
		$UDIRlarge = $UDIRthumb. "/" . "large";
		$this->makeDir($UDIRlarge);
	}


	//ユーザーログイン画面
/*
	public function login(){
		$this->theme = "AdminTheme";

		//App::uses('Sanitize', 'Utility');
		$localLoginForm = array();
		$user = array();
		$message = "IDとパスワードを押して送信ボタンを押してください。";


		//◆localLogin認証
        if ($this->request->is('post')) {
			$localLoginForm = $this->request->data['localLoginForm'];

			//◆認証
			if($this->User->auth($localLoginForm)){
				//◆OK
				$user = $this->User->getByLoginid($localLoginForm);
				//ユーザー情報をセッションにセット
				$this->Session->write('user', $user);
				//メニューにリダイレクト
                return $this->redirect(array('action' => 'menu'));				
				//print_r($user);
				//$message = "ログインに成功しました。";

			}else{
				//◆NG エラーメッセージ
				$message = "ログインに失敗しました。";
			}

		}

		//画面表示
        $this->set('user', $this->user);
		$this->set("message",$message);

	}

*/
	//ユーザーログイン画面
	public function menu(){


		//print_r($this->user);

		//画面表示
        $this->set('user', $this->user);
	}

/*
	//ユーザーログアウト画面
	public function logout(){
		$this->Session->delete('user');

        return $this->redirect(array('action' => 'login'));
	}
*/
	//ユーザー一覧
	public function index(){

		$users = $this->User->find('all');

        $this->set('user', $this->user);
		$this->set("users",$users);

		//ログ出力
		$this->Log->write("0001",$this->user["User"]["id"],"user list");

	}

	//ユーザー登録
	public function add(){

        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {

				//ユーザーディレクトリ作成
				$user["User"] = $this->request->data;
				$user["User"]["id"] =  $this->User->getLastInsertID();
				$this->makeuserdir($user);

                $this->Session->setFlash(__('Your post has been saved.'));

				//ログ出力
				$this->Log->write("0001",$this->user["User"]["id"],"user add");

                return $this->redirect(array('action' => 'index'));
            }

            $this->Session->setFlash(__('Unable to add your post.'));
        }


        $this->set('user', $this->user);

	}

	//ユーザー参照
	public function view($id = null){

        if (!$id) {
            throw new NotFoundException(__('Invalid post'));
        }

		$message = "";

		//$User = $this->User->find('first',array('conditions'=>array('User.UserNo'=>$UserNo)));
		$user = $this->User->findById($id);
        if (!$user) {
            throw new NotFoundException(__('Invalid post'));
        }
//		print_r($user);

		//ユーザーディレクトリ作成
		$this->makeuserdir($user);

        $this->set('user', $this->user);
		$this->set("data",$user);

	}


	//ユーザー編集
	public function edit($id = null){

	    if (!$id) {
	        throw new NotFoundException(__('Invalid post'));
	    }

	    $user = $this->User->findById($id);
	    if (!$user) {
	        throw new NotFoundException(__('Invalid post'));
	    }


	    if ($this->request->is(array('post', 'put'))) {
	        $this->User->id = $id;
	        if ($this->User->save($this->request->data)) {
				//ユーザーディレクトリ作成
				$this->makeuserdir($user);

	            $this->Session->setFlash(__('Your post has been updated.'));

				//ログ出力
				$this->Log->write("0001",$this->user["User"]["id"],"user edit");

	            return $this->redirect(array('action' => 'index'));
	        }
	        $this->Session->setFlash(__('Unable to update your post.'));

	    }

	    if (!$this->request->data) {
	        $this->request->data = $user;
	    }

        $this->set('user', $this->user);
		//$this->set("data",$user);

	}
}
?>
