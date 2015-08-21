<?php
/**
 * Class User
 */
App::uses('AuthComponent', 'Controller/Component');
class User extends AppModel {

    public $name = 'User';

    public $validate = array(
        'userName' => array(
            'rule' => 'notEmpty'
        ),
        'ssoLoginID' => array(
            'rule' => 'notEmpty'
        ),
        'localLoginID' => array(
            'alphaNumeric' => array(
                'rule'     => 'alphaNumeric',
                'required' => true,
                'message'  => 'Letters and numbers only'
            ),
            'between' => array(
                'rule'    => array('between', 5, 15),
                'message' => 'Between 5 to 15 characters'
            )
        ),
        'localLoginPassword' => array(
            'rule'    => array('minLength', '8'),
            'message' => 'Minimum 8 characters long'
        )
    );

    public $validete = array(
        'authority' => array(
            'rule' => array(
                'chkAllowLocalLogin',
                0    ),
            'message' => '管理者は独自認証を制限できません。',
        )
    );

    public function chkAllowLocalLogin($data,$allowLocalLogin){
//echo "$data=".$data;
//die();
        if($data == 9 && $allowLocalLogin != 1){
            return false;
        }
        return true;
    }

    public function beforeSave($options = array()) {
        //if (isset($this->data[$this->alias]['localLoginPassword']) && isset($this->data[$this->alias]['localLoginPassword_old'])) {
        //	if($this->data[$this->alias]['localLoginPassword'] != $this->data[$this->alias]['localLoginPassword_old']){
        //    	// パスワードハッシュ化
        //    	$this->data[$this->alias]['localLoginPassword'] = AuthComponent::password($this->data[$this->alias]['localLoginPassword']);
        //	}
        //}
    }

    /**
     * 認証
     *
     * @param $data
     * @return bool
     */
    public function auth($data){

        //if (isset($data['password'])) {
        //    // パスワードハッシュ化
        //    $data['password'] = AuthComponent::password($data['password']);
        //}

        $n = $this->find('count',
            array('conditions'=>
                array(
                    'User.localLoginId'=>$data['loginID'],
                    'User.localLoginPassword'=>$data['password'],
                    'User.allowLocalLogin'=>1,
                )
            )
        );

        return $n > 0 ? true : false;
    }

    /**
     * ユーザー情報取得(ログイン情報から)
     *
     * @param $data
     * @return array|null
     */
    public function getByLoginid($data){
        $User = $this->find('first',
            array('conditions'=>
                array(
                    'User.localLoginId'=>$data['loginID'],
                    'User.localLoginPassword'=>$data['password'],
                    'User.allowLocalLogin'=>1,
                )
            )
        );

        return $User;
    }

    /**
     * シングルサインオンのユーザーIDから本アプリケーションのユーザー情報を取得する
     *
     * @param $ssoUserId
     * @return array|null
     */
    public function getBySSOUserId($ssoUserId)
    {
        return $this->find('first',
            array(
                'conditions' => array(
                    'User.ssoLoginID' => $ssoUserId,
                )
            )
        );
    }

    /**
     * ユーザーIDで検索する
     *
     * @param $userId
     * @return array|null
     */
    public function getByUserId($userId)
    {
        return $this->find('first',
            array(
                'conditions' => array(
                    'User.id' => $userId,
                )
            )
        );
    }
}


