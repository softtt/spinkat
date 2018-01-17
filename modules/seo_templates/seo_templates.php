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

class Seo_templates extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'seo_templates';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Victor Scherba';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('SEO templates');
        $this->description = $this->l('Default SEO templates');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SEO_TEMPLATE_BLOG_AUTHORS_LIST_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_BLOG_AUTHORS_LIST_DESCRIPTION', '');
       
        Configuration::updateValue('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_DESCRIPTION', '');
       
        Configuration::updateValue('SEO_TEMPLATE_BLOG_CATEGORY_LIST_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_BLOG_CATEGORY_LIST_DESCRIPTION', '');
       
        Configuration::updateValue('SEO_TEMPLATE_BLOG_ARTICLE_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_BLOG_ARTICLE_DESCRIPTION', '');
       
        Configuration::updateValue('SEO_TEMPLATE_CATALOG_CATEGORY_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_CATALOG_CATEGORY_DESCRIPTION', '');
       
        Configuration::updateValue('SEO_TEMPLATE_CATALOG_SERIES_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_CATALOG_SERIES_DESCRIPTION', '');
       
        Configuration::updateValue('SEO_TEMPLATE_CATALOG_PRODUCT_TITLE', '');
        Configuration::updateValue('SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION', '');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_AUTHORS_LIST_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_AUTHORS_LIST_DESCRIPTION');
       
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_DESCRIPTION');
       
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_CATEGORY_LIST_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_CATEGORY_LIST_DESCRIPTION');
       
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_ARTICLE_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_BLOG_ARTICLE_DESCRIPTION');
       
        Configuration::deleteByName('SEO_TEMPLATE_CATALOG_CATEGORY_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_CATALOG_CATEGORY_DESCRIPTION');
       
        Configuration::deleteByName('SEO_TEMPLATE_CATALOG_SERIES_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_CATALOG_SERIES_DESCRIPTION');
       
        Configuration::deleteByName('SEO_TEMPLATE_CATALOG_PRODUCT_TITLE');
        Configuration::deleteByName('SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION');

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
        if (((bool)Tools::isSubmit('submitSeo_templatesModule')) == true) {
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
        $helper->submit_action = 'submitSeo_templatesModule';
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
                'title' => 'SEO шаблоны',
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_AUTHORS_LIST_TITLE',
                        'label' => 'Список экспертов SEO title',
                        // 'desc' => 'Список экспертов SEO title',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_AUTHORS_LIST_DESCRIPTION',
                        'label' => 'Список экспетов SEO description',
                        // 'desc' => 'Список экспетов SEO description',
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_TITLE',
                        'label' => 'Страница эксперта SEO title',
                        'desc' => 'Переменные: %AUTHOR_NAME%',
                        'hint' => '%AUTHOR_NAME% - Имя автора статьи',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_DESCRIPTION',
                        'label' => 'Страница эксперта SEO description',
                        'desc' => 'Переменные: %AUTHOR_NAME%',
                        'hint' => '%AUTHOR_NAME% - Имя автора статьи',
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_CATEGORY_LIST_TITLE',
                        'label' => 'Раздел блогов SEO title',
                        'desc' => 'Переменные: %BLOG_CATEGORY%',
                        'hint' => '%BLOG_CATEGORY% - Название раздела с записями (Статьи, обзоры и т.д.)',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_CATEGORY_LIST_DESCRIPTION',
                        'label' => 'Раздел блогов SEO title',
                        'desc' => 'Переменные: %BLOG_CATEGORY%',
                        'hint' => '%BLOG_CATEGORY% - Название раздела с записями (Статьи, обзоры и т.д.)',
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_ARTICLE_TITLE',
                        'label' => 'Запись в блогах SEO title',
                        'desc' => 'Переменные: %ARTICLE_TITLE%, %BLOG_CATEGORY%',
                        'hint' => '
                            %ARTICLE_TITLE% - Название статьи<br> 
                            %BLOG_CATEGORY% - Название раздела с записями (Статьи, обзоры и т.д.)
                        ',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_BLOG_ARTICLE_DESCRIPTION',
                        'label' => 'Запись в блогах SEO description',
                        'desc' => 'Переменные: %ARTICLE_TITLE%, %BLOG_CATEGORY%',
                        'hint' => '
                            %ARTICLE_TITLE% - Название статьи<br> 
                            %BLOG_CATEGORY% - Название раздела с записями (Статьи, обзоры и т.д.)
                        ',
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_CATALOG_CATEGORY_TITLE',
                        'label' => 'Категория в каталоге SEO title',
                        'desc' => 'Переменные: %CATEGORY_TITLE%',
                        'hint' => '%CATEGORY_TITLE% - Название категории в каталоге',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_CATALOG_CATEGORY_DESCRIPTION',
                        'label' => 'Категория в каталоге SEO description',
                        'desc' => 'Переменные: %CATEGORY_TITLE%',
                        'hint' => '%CATEGORY_TITLE% - Название категории в каталоге',
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_CATALOG_SERIES_TITLE',
                        'label' => 'Серия товаров SEO title',
                        'desc' => 'Переменные: %SERIES_TITLE%, %CATEGORY_TITLE%, %MANUFACTURER_TITLE%',
                        'hint' => '%SERIES_TITLE% - Название серии товаров<br>%CATEGORY_TITLE% - Название категории в каталоге<br>, %MANUFACTURER_TITLE% - Название производителя',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_CATALOG_SERIES_DESCRIPTION',
                        'label' => 'Серия товаров SEO description',
                        'desc' => 'Переменные: %SERIES_TITLE%, %CATEGORY_TITLE%, %MANUFACTURER_TITLE%',
                        'hint' => '%SERIES_TITLE% - Название серии товаров<br>%CATEGORY_TITLE% - Название категории в каталоге<br>, %MANUFACTURER_TITLE% - Название производителя',
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_CATALOG_PRODUCT_TITLE',
                        'label' => 'Карточка товара SEO title',
                        'desc' => 'Переменные: %PRODUCT_TITLE%, %CATEGORY_TITLE%, %MANUFACTURER_TITLE%',
                        'hint' => '%PRODUCT_TITLE% - Название товара<br>%CATEGORY_TITLE% - Название категории в каталоге<br>, %MANUFACTURER_TITLE% - Название производителя',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION',
                        'label' => 'Карточка товара SEO description',
                        'desc' => 'Переменные: %PRODUCT_TITLE%, %CATEGORY_TITLE%, %MANUFACTURER_TITLE%',
                        'hint' => '%PRODUCT_TITLE% - Название товара<br>%CATEGORY_TITLE% - Название категории в каталоге<br>, %MANUFACTURER_TITLE% - Название производителя',
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
            'SEO_TEMPLATE_BLOG_AUTHORS_LIST_TITLE' => Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_LIST_TITLE', null),
            'SEO_TEMPLATE_BLOG_AUTHORS_LIST_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_LIST_DESCRIPTION', null),

            'SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_TITLE' => Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_TITLE', null),
            'SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_DESCRIPTION', null),

            'SEO_TEMPLATE_BLOG_CATEGORY_LIST_TITLE' => Configuration::get('SEO_TEMPLATE_BLOG_CATEGORY_LIST_TITLE', null),
            'SEO_TEMPLATE_BLOG_CATEGORY_LIST_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_BLOG_CATEGORY_LIST_DESCRIPTION', null),

            'SEO_TEMPLATE_BLOG_ARTICLE_TITLE' => Configuration::get('SEO_TEMPLATE_BLOG_ARTICLE_TITLE', null),
            'SEO_TEMPLATE_BLOG_ARTICLE_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_BLOG_ARTICLE_DESCRIPTION', null),

            'SEO_TEMPLATE_CATALOG_CATEGORY_TITLE' => Configuration::get('SEO_TEMPLATE_CATALOG_CATEGORY_TITLE', null),
            'SEO_TEMPLATE_CATALOG_CATEGORY_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_CATALOG_CATEGORY_DESCRIPTION', null),

            'SEO_TEMPLATE_CATALOG_SERIES_TITLE' => Configuration::get('SEO_TEMPLATE_CATALOG_SERIES_TITLE', null),
            'SEO_TEMPLATE_CATALOG_SERIES_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_CATALOG_SERIES_DESCRIPTION', null),

            'SEO_TEMPLATE_CATALOG_PRODUCT_TITLE' => Configuration::get('SEO_TEMPLATE_CATALOG_PRODUCT_TITLE', null),
            'SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION' => Configuration::get('SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION', null),
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
        // if (Tools::getValue('module_name') == $this->name) {
        //     $this->context->controller->addJS($this->_path.'views/js/back.js');
        //     $this->context->controller->addCSS($this->_path.'views/css/back.css');
        // }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // $this->context->controller->addJS($this->_path.'/views/js/front.js');
        // $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
