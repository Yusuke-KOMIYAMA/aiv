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
		//$pMsgID ���b�Z�[�WID config log�ɐݒ�
		//$pUserId ���샆�[�U�[��ID
		//$pMsg �ǋL���������b�Z�[�W

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

		//�ǉ����b�Z�[�W
		if($pMsg != ""){
			$message .= " : ". $pMsg;
		}

		//�ڑ���IP
		$RemoteAddress = $_SERVER["REMOTE_ADDR"];

		// �o�^������e��ݒ�
		$data = array('Log' => array(
			'category' => $category, 
			'level' => $level, 
			'messageID' => $pMsgID, 
			'message' => $message, 
			'remoteAddress' => $RemoteAddress, 
			'userId' => $pUserId));
		 
		// �o�^���鍀�ځi�t�B�[���h�w��j
		$fields = array(
			'category', 
			'level', 
			'messageID', 
			'message', 
			'remoteAddress', 
			'userId', 
		);
		 
		// �o�^
//        $this-create();
        if ($this->save($data, false, $fields)) {
            return true;
        }

		return false;

	}


}


