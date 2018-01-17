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

require_once _PS_MODULE_DIR_ . 'users_reviews/models/ShopReview.php';

class Users_reviews extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'users_reviews';
        $this->tab = 'others';
        $this->version = '1.2.1';
        $this->author = 'Smart Raccoon';
        $this->need_instance = 1;
        $this->controllers = array('shopreviews');

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Users reviews');
        $this->description = $this->l('Add users reviews page for shop');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('backOfficeHeader') ||
            !$this->registerHook('displayHome')
        ) {
            return false;
        }

        if (file_exists(_PS_MODULE_DIR_.'users_reviews/sql/install.php'))
            include_once (_PS_MODULE_DIR_.'users_reviews/sql/install.php');

        // Settings
        Configuration::updateValue('USERS_REVIEWS_TITLE', 'Reviews');
        Configuration::updateValue('USERS_REVIEWS_SLUG', 'users_reviews');
        Configuration::updateValue('USERS_REVIEWS_META_DESCRIPTION', '');
        Configuration::updateValue('USERS_REVIEWS_META_KEYWORDS', '');

        /**

        Tabs

        **/

        // Tabs
        $parent_tab = new Tab();

        $parent_tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent_tab->name[$lang['id_lang']] = $this->l('Users reviews');

        $parent_tab->class_name = 'AdminUsersReviews';
        $parent_tab->id_parent = 0;
        $parent_tab->module = $this->name;
        $parent_tab->add();
    }

    public function uninstall()
    {
        Configuration::deleteByName('USERS_REVIEWS_TITLE');
        Configuration::deleteByName('USERS_REVIEWS_SLUG');
        Configuration::deleteByName('USERS_REVIEWS_META_DESCRIPTION');
        Configuration::deleteByName('USERS_REVIEWS_META_KEYWORDS');

        $tab = Tab::getInstanceFromClassName('AdminUsersReviews');
        $tab->delete();

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitUsers_reviewsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUsers_reviewsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'USERS_REVIEWS_TITLE',
                        'label' => $this->l('Section title'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'USERS_REVIEWS_SLUG',
                        'label' => $this->l('Link rewrite'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'USERS_REVIEWS_META_DESCRIPTION',
                        'label' => $this->l('Meta description'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'USERS_REVIEWS_META_KEYWORDS',
                        'label' => $this->l('Meta keywords'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'USERS_REVIEWS_TITLE' => Configuration::get('USERS_REVIEWS_TITLE', $this->l('Reviews')),
            'USERS_REVIEWS_SLUG' => Configuration::get('USERS_REVIEWS_SLUG', 'users_reviews'),
            'USERS_REVIEWS_META_DESCRIPTION' => Configuration::get('USERS_REVIEWS_META_DESCRIPTION', ''),
            'USERS_REVIEWS_META_KEYWORDS' => Configuration::get('USERS_REVIEWS_META_KEYWORDS', ''),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/users_reviews-admin.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->page_name = Dispatcher::getInstance()->getController();

        if ($this->page_name == 'shopreviews') {
            $this->context->controller->addJS($this->_path.'/views/js/jquery.rating.pack.js');
            $this->context->controller->addJS($this->_path.'/views/js/users_reviews.js');
            $this->context->controller->addCSS($this->_path.'/views/css/users_reviews.css', 'all');
        } elseif ($this->page_name == 'index') {
            $this->context->controller->addCSS($this->_path.'/views/css/users_reviews_home.css', 'all');
        }
    }

    public function hookDisplayHome()
    {
        $this->context->smarty->assign(array(
            'reviews' => ShopReview::getReviews(2, true)
        ));

        return $this->display(__FILE__, 'home.tpl');
    }
}
