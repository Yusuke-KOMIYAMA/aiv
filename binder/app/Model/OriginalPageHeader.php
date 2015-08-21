<?php
/**
 * OriginalPageHeader.php
 *
 * �I���W�i���y�[�W�w�b�_�[���f��
 */

App::uses('SoftDeleteBehavior', 'Model/Behavior');

class OriginalPageHeader extends AppModel
{
    public $name = 'OriginalPageHeaders';
    public $useTable = 'originalPageHeaders';
    public $actsAs = array('SoftDelete');

    public $hasMany = array(
        'OriginalPage' => array(
            'className' => 'OriginalPage',
            'foreignKey' => 'originalPageHeaderId'
        )
    );

    public $recursive = -1;
}


