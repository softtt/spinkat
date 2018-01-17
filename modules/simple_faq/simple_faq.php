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

class Simple_faq extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'simple_faq';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Smart Raccoon';
        $this->need_instance = 0;
        $this->is_configurable = false;
        $this->controllers = array('list');

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Simple FAQ');
        $this->description = $this->l('Frequently asked questions');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (file_exists(_PS_MODULE_DIR_.'simple_faq/sql/install.php'))
            include_once (_PS_MODULE_DIR_.'simple_faq/sql/install.php');

        // Tab
        $parent_tab = new Tab();

        $parent_tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent_tab->name[$lang['id_lang']] = $this->l('FAQ');

        $parent_tab->class_name = 'AdminSimpleFaq';
        $parent_tab->id_parent = 0;
        $parent_tab->module = $this->name;
        $parent_tab->add();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        $tab = Tab::getInstanceFromClassName('AdminSimpleFaq');
        $tab->delete();

        return parent::uninstall();
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/simple_faq-admin.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
