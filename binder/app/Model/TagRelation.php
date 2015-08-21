<?php
/**
 * TagRelation.php
 *
 * �^�O�����[�V�������f��
 * ���ڂ̌����́A��� VTag ���s���B�ԐړI�� Containable �r�w�C�r�A�ɂČ������ʂƂ��ė��p�����
 */
class TagRelation extends AppModel
{
    const PARENT_TYPE_ORIGINAL_PAGE = 0;
    const PARENT_TYPE_BINDER = 1;
    const PARENT_TYPE_BINDER_PAGE = 2;

    public $name = 'TagRelation';
    public $useTable = 'tagRelations';
    public $belongsTo = array(
        'OriginalPage' => array(
            'className' => 'OriginalPage',
            'foreignKey' => 'parentId',
            'conditions' => array('TagRelation.parentType' => 0),
            'fields' => '',
            'order' => '',
        ),
        'Binder' => array(
            'className' => 'Binder',
            'foreignKey' => 'parentId',
            'conditions' => array('TagRelation.parentType' => 1),
            'fields' => '',
            'order' => '',
        ),
        'BinderPage' => array(
            'className' => 'BinderPage',
            'foreignKey' => 'parentId',
            'conditions' => array('TagRelation.parentType' => 2),
            'fields' => '',
            'order' => '',
        ),
        'Tag' => array(
            'className' => 'Tag',
            'foreignKey' => 'tagId',
            'conditions' => '',
            'fields' => '',
            'order' => '',
        ),
    );

    public $recursive = -1;
}
