<?php

Class BackupsController extends AppController {

	public $name = 'Backup';
	//public $uses = null;
	public $layout = "admin";

    public $helpers = array('Html', 'Form', 'Session');
    public $components = array('Session');

//	public $autoLayout = true;
//	public $autoRender = true;

	public function beforeFilter() {
		parent :: beforeFilter();

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

	//DBバックアップ
	public function index(){
		$msg = "";
		$bkFullPath = "";

		$db = $this->Backup->getDbc();
		$dbHost = $db->config['host'];
		$dbUser = $db->config['login'];
		$dbPass = $db->config['password'];
		$dbName = $db->config['database'];

//print_r($this->request->data);
	    if ($this->request->is('post') && isset($this->request->data['Post']['doBackup'])) {
			//バックアップ実行
			$dbbackup = Configure::read("dbbackup");

			$mysqldumpPath = $dbbackup["mysqldumpPath"];
			$filePath =  $dbbackup["outPath"];
			$fileName = date('ymd').'_'.date('His').'.sql';

			$bkFullPath = $filePath.$fileName;

			$command = $mysqldumpPath ."mysqldump ".$dbName." --host=".$dbHost." --user=".$dbUser." --password=".$dbPass." > ".$bkFullPath. ' 2>&1' ;
	//		echo $command . "<br />";
			exec( $command , $out , $res) ;
			if( $res ){ 
				$msg = 'backup failure';

			}else {
				$msg = 'backup success';
			}

		}

        $this->set('user', $this->user);
		$this->set("dbName",$dbName);
		$this->set("bkFullPath",$bkFullPath);
		$this->set("msg",$msg);
	}

}
?>
