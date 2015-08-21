<?php
/**
 * OriginalPageHeader.php
 *
 */

class ImportInformation extends AppModel
{
    public $name = 'ImportInformation';
    public $useTable = 'importInformations';

    public $recursive = -1;

    /**
     * ユーザーIDをキーにインポート情報を取得する
     *
     * @param $userId
     * @return array|null
     */
    public function getByUserId($userId)
    {
        return $this->find('first',
            array(
                'conditions' => array(
                    'ImportInformation.userID' => $userId,
                )
            )
        );
    }

}
