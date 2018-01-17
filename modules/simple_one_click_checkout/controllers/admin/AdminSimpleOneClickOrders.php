<?php

class AdminSimpleOneClickOrdersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'one_click_order';
        $this->className = 'OneClickOrder';
        $this->lang = false;
        $this->list_no_link = false;

        $this->list_simple_header = false;
        $this->show_toolbar = false;

        $this->bootstrap = true;
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        // Questions list columns settings
        $this->fields_list = array(
            'id_one_click_order' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 30
            ),
            'client' => array(
                'title' => $this->l('Client name'),
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
                'type' => 'textarea',
                'title' => $this->l('Message'),
                'width' => 'auto',
                'search' => false,
            ),
            'id_product' => array(
                'type' => 'text',
                'title' => $this->l('Ordered product'),
                'callback' => 'getOrderProductName',
            ),
            'proceed' => array(
                'title' => $this->l('Proceed'),
                'width' => 25,
                'active' => 'proceed',
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true
            ),
        );

        parent::__construct();

        require_once $this->module->getLocalPath().'models/OneClickOrder.php';
    }

    public function postProcess()
    {
        if (Tools::getIsset('proceed'.$this->table) && Tools::getIsset('id_one_click_order')) {
            $id_one_click_order = Tools::getValue('id_one_click_order', null);

            if ($id_one_click_order) {
                $one_click_order = new OneClickOrder($id_one_click_order);
                $one_click_order->proceed = $one_click_order->proceed ? 0 : 1;
                $one_click_order->save();

                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        }

        parent::postProcess();
    }

    public function getOrderProductName($order, $row)
    {
        return Product::getProductName($row['id_product'], $row['id_product_attribute']);
    }
}
