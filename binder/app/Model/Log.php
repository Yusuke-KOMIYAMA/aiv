<?php
/**
 * Class Log
 */
class Log extends AppModel {

    public $name = 'Log';

    public $validate = array(
        'category' => array(
            'rule' => 'notEmpty'
        ),
        'level' => array(
            'alphaNumeric' => array(
                'rule'     => 'alphaNumeric',
                'required' => true,
                'message'  => 'Letters and numbers only'
            )
        ),
        'messageID' => array(
            'rule' => 'notEmpty'
        )

    );

    public function write($pMsgID,$pUserId = 0,$pMsg = '')
    {
		//$pMsgID メッセージID config logに設定
		//$pUserId 操作ユーザーのID
		//$pMsg 追記したいメッセージ

		if($pMsgID == ""){
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

		//接続元IP
		$RemoteAddress = $_SERVER["REMOTE_ADDR"];

		// 登録する内容を設定
		$data = array('Log' => array(
			'category' => $category, 
			'level' => $level, 
			'messageID' => $pMsgID, 
			'message' => $message, 
			'remoteAddress' => $RemoteAddress, 
			'userId' => $pUserId));
		 
		// 登録する項目（フィールド指定）
		$fields = array(
			'category', 
			'level', 
			'messageID', 
			'message', 
			'remoteAddress', 
			'userId', 
		);
		 
		// 登録
//        $this-create();
        if ($this->save($data, false, $fields)) {
            return true;
        }

		return false;

	}


}


