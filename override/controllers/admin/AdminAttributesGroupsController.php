<?php


class AdminAttributesGroupsController extends AdminAttributesGroupsControllerCore
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'attribute_group';
        $this->list_id = 'attribute_group';
        $this->identifier = 'id_attribute_group';
        $this->className = 'AttributeGroup';
        $this->lang = true;
        $this->_defaultOrderBy = 'position';

        $this->fields_list = array(
            'id_attribute_group' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'b!name',
                'align' => 'left'
            ),
            'count_values' => array(
                'title' => $this->l('Values count'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => false,
                'search' => false
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'hide' => array(
                'title' => $this->l('Скрывать атрибут'),
                'active' => 'hide',
                'align' => 'text-center',
                'filter_key' => 'a!hide',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false
            )
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );
        $this->fieldImageSettings = array('name' => 'texture', 'dir' => 'co');

        AdminControllerCore::__construct();
    }

    public function postProcess()
    {
        if(key_exists('hideattribute_group',$_GET)){
            $this->processHide();
        }
        parent::postProcess();
    }

    public function processHide()
    {
        $id_attribute_group = Tools::getValue('id_attribute_group');
        $group = new AttributeGroup($id_attribute_group);
        if($group->hide)
            $group->hide = 0;
        else
            $group->hide = 1;
        $group->save();
    }
}
