<?php

Class LogsController extends AppController {

	public $name = 'Log';
	//public $uses = null;
	public $layout = "admin";

    public $helpers = array('Html', 'Form', 'Session', 'Csv');
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

	//ログ書き込み
	//★model移動
/*
	public function write($pMsgID,$pUserId = 0,$pMsg = ''){
		//$pMsgID メッセージID config logに設定
		//$pUserId 操作ユーザーのID
		//$pMsg 追記したいめっせーじ

		if($pMsgID = ""){
			return false;
		}

		$confLog = Configure::read("log");

		if(isset($confLog[$pMsgID])){
			$category = $confLog[$pMsgID]["category"];
			$level = $confLog[$pMsgID]["level"];
			$message = $confLog[$pMsgID]["message"];
		}else{
			$category = "-";
			$level = 0;
			$message = "-";
		}

		//追加メッセージ
		if($pMsg != ""){
			$message .= " : ". $pMsg;
		}

		// 登録する内容を設定
		$data = array('Log' => array(
			'category' => $category, 
			'level' => $level, 
			'messageID' => $pMsgID, 
			'message' => $message, 
			'userId' => $pUserId));
		 
		// 登録する項目（フィールド指定）
		$fields = array(
			'category', 
			'level', 
			'messageID', 
			'message', 
			'userId', 
		);
		 
		// 登録
        $this->Log->create();
        if ($this->Log->save($data, false, $fields)) {
            return true;
        }

		return false;

	}
*/


	//ログ一覧
	public function index(){


		$logs = array();
		$params = array();

    	if ($this->request->is('post') && isset($this->request->data['logDateFrom'])) {

			$logDateFrom = $this->request->data['logDateFrom'];
			$logDateTo = $this->request->data['logDateTo'];
			$category = $this->request->data['category'];
			$userId = $this->request->data['userId'];

			//ダウンロード用にsession保持
			$this->Session->write('LogForm.logDateFrom', $logDateFrom);
			$this->Session->write('LogForm.logDateTo', $logDateTo);
			$this->Session->write('LogForm.category', $category);
			$this->Session->write('LogForm.userId', $userId);


			$params = $this->setPamars($logDateFrom,$logDateTo,$category,$userId);

			$logs = $this->Log->find('all',$params);
		}

        $this->set('user', $this->user);
		$this->set("logs",$logs);

		//ログ出力
		$this->Log->write("0001",$this->user["User"]["id"],"log list");
	}

	//CSV出力
	public function download(){

        $this->layout = false;
        $filename = 'uvlog_' . date('YmdHis');
 
        // 表の一行目を作成
        $th = array('logid', 'date', 'category', 'level', 'messageid', 'message', 'userid');

        // 表の内容を取得
		$logDateFrom = $this->Session->read('LogForm.logDateFrom');
		$logDateTo = $this->Session->read('LogForm.logDateTo');
		$category = $this->Session->read('LogForm.category');
		$userId = $this->Session->read('LogForm.userId');

		$params = $this->setPamars($logDateFrom,$logDateTo,$category,$userId);

		$td = $this->Log->find('all',$params);

        $this -> set(compact('filename', 'th', 'td'));

		//ログ出力
		$this->Log->write("0001",$this->user["User"]["id"],"log download");
	}

	private function setPamars($logDateFrom,$logDateTo,$category,$userId){

	    $params = array(
			'conditions' => array(
				'logDate >=' => $logDateFrom['year'] . "-" . $logDateFrom['month'] . "-" . $logDateFrom['day']  . " 00:00:00" ,
				'logDate <=' => $logDateTo['year'] . "-" . $logDateTo['month'] . "-" . $logDateTo['day'] . " 23:59:59" ,
			),
	        'limit' => 1000,
	        'order' => array('logDate DESC')
	        );


		//種別指定あり
		if($category != ""){
			$params['conditions']['category'] = $category;
		}

		//ユーザーID指定あり
		if($userId != ""){
			$params['conditions']['userId'] = $userId;
		}

		return $params;

	}

}
?>
