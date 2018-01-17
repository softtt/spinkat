<?php

require_once _PS_MODULE_DIR_.'users_reviews/models/ShopReview.php';

class AdminUsersReviewsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'shop_review';
        $this->className = 'ShopReview';
        $this->lang = false;
        $this->list_no_link = true;

        $this->list_simple_header = false;
        $this->show_toolbar = false;

        $this->bootstrap = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        // Single record form fields setting
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Review')
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name:'),
                    'name' => 'customer_name',
                    'required' => true,
                    'col' => 4,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Email'),
                    'name' => 'email',
                    'col' => 4,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Review text'),
                    'name' => 'text',
                    'rows' => 8,
                    'col' => 4,
                    'required' => true,
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Grade'),
                    'name' => 'grade',
                    'required' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'grade_1',
                            'value' => 1,
                            'label' => $this->l('1')
                        ),
                        array(
                            'id' => 'grade_2',
                            'value' => 2,
                            'label' => $this->l('2')
                        ),
                        array(
                            'id' => 'grade_3',
                            'value' => 3,
                            'label' => $this->l('3')
                        ),
                        array(
                            'id' => 'grade_4',
                            'value' => 4,
                            'label' => $this->l('4')
                        ),
                        array(
                            'id' => 'grade_5',
                            'value' => 5,
                            'label' => $this->l('5')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Displayed:'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'class' => 'btn btn-default',
                'stay' => true,
            )
        );

        // Reviews list columns settings
        $this->fields_list = array(
            'id_shop_review' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 30
            ),
            'customer_name' => array(
                'title' => $this->l('Customer'),
                'width' => 'auto'
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'width' => 'auto'
            ),
            'text' => array(
                'title' => $this->l('Text'),
                'width' => 'auto',
                'search' => false,
            ),
            'grade' => array(
                'title' => $this->l('Rating'),
                'type' => 'text',
                'suffix' => '/5',
            ),
            'active' => array(
                'title' => $this->l('Show review'),
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
