<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class BlockcontactOverride extends Blockcontact
{
    public function install()
    {
        return Module::install()
            && Configuration::updateValue('BLOCKCONTACT_TELNUMBER', '')
            && Configuration::updateValue('BLOCKCONTACT_EMAIL', '')
            && $this->registerHook('displayNav')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayHeader');
    }

    public function hookDisplayRightColumn($params)
    {
        global $smarty;
        $tpl = 'blockcontact';
        if (isset($params['blockcontact_tpl']) && $params['blockcontact_tpl'])
            $tpl = $params['blockcontact_tpl'];
        if (!$this->isCached($tpl.'.tpl', $this->getCacheId()))
            $smarty->assign(array(
                'telnumber' => explode(',', Configuration::get('BLOCKCONTACT_TELNUMBER')),
                'email' => Configuration::get('BLOCKCONTACT_EMAIL')
            ));
        return $this->display(__FILE__, $tpl.'.tpl', $this->getCacheId());
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'description' => $this->l('This block displays in the header your phone number (‘Call us now’), and a link to the ‘Contact us’ page.').'<br/><br/>'.
                        $this->l('To edit the email addresses for the ‘Contact us’ page: you should go to the ‘Contacts’ page under the ‘Customer’ menu.').'<br/>'.
                        $this->l('To edit the contact details in the footer: you should go to the ‘Contact Information Block’ module.'),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Telephone number'),
                        'name' => 'blockcontact_telnumber',
                        'desc' => $this->l('If you need to show few numbers, enter them separated with coma'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'name' => 'blockcontact_email',
                        'desc' => $this->l('Enter here your customer service contact details.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function hookDisplayFooter($params)
    {
        $params['blockcontact_tpl'] = 'footer';
        return $this->hookDisplayRightColumn($params);
    }
}
