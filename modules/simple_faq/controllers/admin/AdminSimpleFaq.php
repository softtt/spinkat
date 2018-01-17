<?php

require_once _PS_MODULE_DIR_.'simple_faq/models/Question.php';

class AdminSimpleFaqController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'question';
        $this->className = 'Question';
        $this->lang = false;
        $this->list_no_link = false;

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
                'title' => $this->l('Question')
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name:'),
                    'name' => 'customer_name',
                    'required' => true,
                    'col' => 5,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Email'),
                    'name' => 'email',
                    'col' => 5,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Question'),
                    'name' => 'question',
                    'col' => 5,
                    'required' => true,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Answer'),
                    'name' => 'answer',
                    'rows' => 8,
                    'col' => 5,
                    'required' => false,
                    'autoload_rte' => true,
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

        // Questions list columns settings
        $this->fields_list = array(
            'id_question' => array(
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
            'question' => array(
                'type' => 'textarea',
                'title' => $this->l('Question'),
                'width' => 'auto',
                'search' => false,
            ),
            'answer' => array(
                'type' => 'textarea',
                'title' => $this->l('Answer'),
                'width' => 'auto',
                'search' => false,
                'maxlength' => 100,
                'callback' => 'getDescriptionClean'
            ),
            'active' => array(
                'title' => $this->l('Show question'),
                'width' => 25,
                'active' => 'status',
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true
            ),
        );

        parent::__construct();
    }

    public static function getDescriptionClean($description)
    {
        return strip_tags(stripslashes($description));
    }
}
