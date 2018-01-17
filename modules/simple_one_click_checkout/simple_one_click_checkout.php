<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Simple_one_click_checkout extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'simple_one_click_checkout';
        $this->tab = 'checkout';
        $this->version = '1.1.0';
        $this->author = 'Smart Raccoon';
        $this->need_instance = 0;
        $this->controllers = array('oneclickorder');

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Simple one-click checkout');
        $this->description = $this->l('Simple one-click checkout');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (file_exists($this->getLocalPath().'sql/install.php'))
            include_once ($this->getLocalPath().'sql/install.php');

        // Tab
        $parent_tab = new Tab();

        $parent_tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent_tab->name[$lang['id_lang']] = $this->l('One click orders');

        $parent_tab->class_name = 'AdminSimpleOneClickOrders';
        $parent_tab->id_parent = Tab::getIdFromClassName('AdminOrders');
        $parent_tab->module = $this->name;
        $parent_tab->add();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayProductButtons') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        $tab = Tab::getInstanceFromClassName('AdminSimpleOneClickOrders');
        $tab->delete();

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    // public function getContent()
    // {
    //     /**
    //      * If values have been submitted in the form, process.
    //      */
    //     if (((bool)Tools::isSubmit('submitSimple_one_click_checkoutModule')) == true) {
    //         $this->postProcess();
    //     }

    //     $this->context->smarty->assign('module_dir', $this->_path);

    //     return $output.$this->renderForm();
    // }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    // protected function renderForm()
    // {
    //     $helper = new HelperForm();

    //     $helper->show_toolbar = false;
    //     $helper->table = $this->table;
    //     $helper->module = $this;
    //     $helper->default_form_language = $this->context->language->id;
    //     $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

    //     $helper->identifier = $this->identifier;
    //     $helper->submit_action = 'submitSimple_one_click_checkoutModule';
    //     $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
    //         .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
    //     $helper->token = Tools::getAdminTokenLite('AdminModules');

    //     $helper->tpl_vars = array(
    //         'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
    //         'languages' => $this->context->controller->getLanguages(),
    //         'id_language' => $this->context->language->id,
    //     );

    //     return $helper->generateForm(array($this->getConfigForm()));
    // }

    /**
     * Create the structure of your form.
     */
    // protected function getConfigForm()
    // {
    //     return array(
    //         'form' => array(
    //             'legend' => array(
    //             'title' => $this->l('Settings'),
    //             'icon' => 'icon-cogs',
    //             ),
    //             'input' => array(),
    //             'submit' => array(
    //                 'title' => $this->l('Save'),
    //             ),
    //         ),
    //     );
    // }

    /**
     * Set values for the inputs.
     */
    // protected function getConfigFormValues()
    // {
    //     return array();
    // }

    /**
     * Save form data.
     */
    // protected function postProcess()
    // {
    //     $form_values = $this->getConfigFormValues();

    //     foreach (array_keys($form_values) as $key) {
    //         Configuration::updateValue($key, Tools::getValue($key));
    //     }
    // }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayProductButtons()
    {
        if (Configuration::get('PS_CATALOG_MODE'))
            return;

        return $this->display(__FILE__, 'simple_one_click_checkout.tpl');
    }
}
