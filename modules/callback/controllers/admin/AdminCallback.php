<?php

require_once _PS_MODULE_DIR_.'callback/models/CallbackOrder.php';

class AdminCallbackController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'callback_order';
        $this->className = 'CallbackOrder';
        $this->lang = false;
        $this->list_no_link = true;

        $this->bootstrap = true;
        $this->addRowAction('edit', 'delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->fields_list = array(
            'id_callback_order' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 30
            ),
            'client' => array(
                'title' => $this->l('Client'),
                'width' => 'auto'
            ),
            'phone' => array(
                'title' => $this->l('Phone'),
                'width' => 'auto'
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'width' => 'auto'
            ),
            'message' => array(
                'title' => $this->l('Message'),
                'width' => 'auto',
                'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Order active'),
                'width' => 25,
                'active' => 'status',
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true
            ),
        );

        parent::__construct();
    }
}
