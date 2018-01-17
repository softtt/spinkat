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

require(dirname(__FILE__).'/additionalheaderlink.class.php');

class Header_additional_links extends Module
{
    protected $config_form = false;

    /*
     * Pattern for matching config values
     */
    protected $pattern = '/^([A-Z_]*)[0-9]+/';

    public function __construct()
    {
        $this->name = 'header_additional_links';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Smart Raccoon';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Header additional links');
        $this->description = $this->l('Header additional links for Spinkat');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('HEADER_ADDITIONAL_LINKS', '');
        Configuration::updateValue('DEFAULT_TOP_LINKS_OPTION_TITLE', '');

        return parent::install() &&
            $this->installDb() &&
            $this->registerHook('header') &&
            $this->registerHook('displayTop') &&
            $this->registerHook('displayTopMenu') &&
            $this->registerHook('backOfficeHeader');
    }

    public function installDb()
    {
        return (Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'additionalheaderlinks` (
            `id_additionalheaderlinks` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `new_window` TINYINT( 1 ) NOT NULL,
            INDEX (`id_shop`)
        ) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;') &&
            Db::getInstance()->execute('
             CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'additionalheaderlinks_lang` (
            `id_additionalheaderlinks` INT(11) UNSIGNED NOT NULL,
            `id_lang` INT(11) UNSIGNED NOT NULL,
            `id_shop` INT(11) UNSIGNED NOT NULL,
            `label` VARCHAR( 128 ) NOT NULL ,
            `link` VARCHAR( 128 ) NOT NULL ,
            INDEX ( `id_additionalheaderlinks` , `id_lang`, `id_shop`)
        ) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;'));
    }

    public function uninstall()
    {
        Configuration::deleteByName('HEADER_ADDITIONAL_LINKS');
        Configuration::deleteByName('DEFAULT_TOP_LINKS_OPTION_TITLE');
        if (!$this->uninstallDB()) {
            return false;
        }

        return parent::uninstall();
    }

    protected function uninstallDb()
    {
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'additionalheaderlinks`');
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'additionalheaderlinks_lang`');
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $message = '';

        if (Tools::isSubmit('submitBlocktopmenu')) {
            $errors_update_shops = array();
            $items = Tools::getValue('items');
            $shops = Shop::getContextListShopID();

            foreach ($shops as $shop_id) {
                $shop_group_id = Shop::getGroupFromShop($shop_id);
                $updated = true;


                if (count($shops) == 1) {
                    if (is_array($items) && count($items)) {
                        $updated = Configuration::updateValue('HEADER_ADDITIONAL_LINKS', (string)implode(',', $items), false, (int)$shop_group_id, (int)$shop_id);
                    } else {
                        $updated = Configuration::updateValue('HEADER_ADDITIONAL_LINKS', '', false, (int)$shop_group_id, (int)$shop_id);
                    }
                }

                if (!$updated) {
                    $shop = new Shop($shop_id);
                    $errors_update_shops[] =  $shop->name;
                }
            }

            if (Tools::isSubmit('DEFAULT_TOP_LINKS_OPTION_TITLE')) {
                Configuration::updateValue('DEFAULT_TOP_LINKS_OPTION_TITLE', Tools::getValue('DEFAULT_TOP_LINKS_OPTION_TITLE', ''));
            }

            if (!count($errors_update_shops)) {
                $message .= $this->displayConfirmation($this->l('The settings have been updated.'));
            } else {
                $message .= $this->displayError(sprintf($this->l('Unable to update settings for the following shop(s): %s'), implode(', ', $errors_update_shops)));
            }
        } else {
            if (Tools::isSubmit('submitBlocktopmenuLinks')) {
                $errors_add_link = array();

                $languages = $this->context->controller->getLanguages();
                $default_language = (int)Configuration::get('PS_LANG_DEFAULT');

                foreach ($languages as $key => $val) {
                    $links_label[$val['id_lang']] = Tools::getValue('link_'.(int)$val['id_lang']);
                    $labels[$val['id_lang']] = Tools::getValue('label_'.(int)$val['id_lang']);
                }

                $count_links_label = count($links_label);
                $count_label = count($labels);

                if ($count_links_label || $count_label) {
                    if (!$count_links_label) {
                        $message .= $this->displayError($this->l('Please complete the "Link" field.'));
                    } elseif (!$count_label) {
                        $message .= $this->displayError($this->l('Please add a label.'));
                    } elseif (!isset($labels[$default_language])) {
                        $message .= $this->displayError($this->l('Please add a label for your default language.'));
                    } else {
                        $shops = Shop::getContextListShopID();
                        foreach ($shops as $shop_id) {
                            $added = AdditionalHeaderLinks::add($links_label, $labels,  Tools::getValue('new_window', 0), (int)$shop_id);

                            if (!$added) {
                                $shop = new Shop($shop_id);
                                $errors_add_link[] =  $shop->name;
                            }
                        }

                        if (!count($errors_add_link)) {
                            $message .= $this->displayConfirmation($this->l('The link has been added.'));
                        } else {
                            $message .= $this->displayError(sprintf($this->l('Unable to add link for the following shop(s): %s'), implode(', ', $errors_add_link)));
                        }
                    }
                }
            } elseif (Tools::isSubmit('deleteadditionalheaderlinks')) {
                $errors_delete_link = array();
                $id_additionalheaderlinks = Tools::getValue('id_additionalheaderlinks', 0);
                $shops = Shop::getContextListShopID();

                foreach ($shops as $shop_id) {
                    $deleted = AdditionalHeaderLinks::remove($id_additionalheaderlinks, (int)$shop_id);
                    Configuration::updateValue('HEADER_ADDITIONAL_LINKS', str_replace(array('LNK'.$id_additionalheaderlinks.',', 'LNK'.$id_additionalheaderlinks), '', Configuration::get('HEADER_ADDITIONAL_LINKS')));

                    if (!$deleted) {
                        $shop = new Shop($shop_id);
                        $errors_delete_link[] =  $shop->name;
                    }
                }

                if (!count($errors_delete_link)) {
                    $message .= $this->displayConfirmation($this->l('The link has been removed.'));
                } else {
                    $message .= $this->displayError(sprintf($this->l('Unable to remove link for the following shop(s): %s'), implode(', ', $errors_delete_link)));
                }
            } elseif (Tools::isSubmit('updateadditionalheaderlinks')) {
                $id_additionalheaderlinks = (int)Tools::getValue('id_additionalheaderlinks', 0);
                $id_shop = (int)Shop::getContextShopID();

                if (Tools::isSubmit('updatelink')) {
                    $link = array();
                    $label = array();
                    $new_window = (int)Tools::getValue('new_window', 0);

                    foreach (Language::getLanguages(false) as $lang) {
                        $link[$lang['id_lang']] = Tools::getValue('link_'.(int)$lang['id_lang']);
                        $label[$lang['id_lang']] = Tools::getValue('label_'.(int)$lang['id_lang']);
                    }

                    AdditionalHeaderLinks::update($link, $label, $new_window, (int)$id_shop, (int)$id_additionalheaderlinks, (int)$id_additionalheaderlinks);
                    $message .= $this->displayConfirmation($this->l('The link has been edited.'));
                }
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $output.$message.$this->renderForm().$this->renderAddForm().$this->renderList();
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
        $helper->submit_action = 'submitHeader_additional_linksModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'choices' => $this->renderChoicesSelect(),
            'selected_links' => $this->makeMenuOption(),
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
                    'title' => $this->l('Menu Top Link'),
                    'icon' => 'icon-link'
                ),
                'input' => array(
                    array(
                        'type' => 'link_choice',
                        'label' => '',
                        'name' => 'link',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Default list option'),
                        'name' => 'DEFAULT_TOP_LINKS_OPTION_TITLE',
                    ),
                ),
                'submit' => array(
                    'name' => 'submitBlocktopmenu',
                    'title' => $this->l('Save')
                )
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'HEADER_ADDITIONAL_LINKS' => Configuration::get('HEADER_ADDITIONAL_LINKS', true),
            'DEFAULT_TOP_LINKS_OPTION_TITLE' => Configuration::get('DEFAULT_TOP_LINKS_OPTION_TITLE'),
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

    public function hookDisplayTop()
    {
        $this->page_name = Dispatcher::getInstance()->getController();

        $links = $this->makeMenu();

        $this->smarty->assign('links', $links);
        $this->smarty->assign('this_path', $this->_path);
        $this->smarty->assign('default', Configuration::get('DEFAULT_TOP_LINKS_OPTION_TITLE', ''));

        return $this->display(__FILE__, 'additional_top_menu.tpl');
    }

    public function hookMobileNav()
    {
        $this->page_name = Dispatcher::getInstance()->getController();

        $links = $this->makeMenu();

        $this->smarty->assign('links', $links);
        $this->smarty->assign('this_path', $this->_path);
        $this->smarty->assign('default', Configuration::get('DEFAULT_TOP_LINKS_OPTION_TITLE', ''));

        return $this->display(__FILE__, 'additional_top_menu-mobile-nav.tpl');
    }

    public function hookDisplayTopMenu()
    {
        return 'hookDisplayTopMenu';
    }

    public function renderChoicesSelect()
    {
        $spacer = str_repeat('&nbsp;', $this->spacer_size);
        $items = $this->getMenuItems();

        $html = '<select multiple="multiple" id="availableItems" style="width: 300px; height: 160px;">';

        // TODO - copy links

        $html .= '<optgroup label="'.$this->l('Menu Top Links').'">';
        $links = AdditionalHeaderLinks::gets($this->context->language->id, null, (int)Shop::getContextShopID());
        foreach ($links as $link) {
            if ($link['label'] == '') {
                $default_language = Configuration::get('PS_LANG_DEFAULT');
                $link = AdditionalHeaderLinks::get($link['id_additionalheaderlinks'], $default_language, (int)Shop::getContextShopID());
                if (!in_array('LNK'.(int)$link[0]['id_additionalheaderlinks'], $items)) {
                    $html .= '<option value="LNK'.(int)$link[0]['id_additionalheaderlinks'].'">'.$spacer.Tools::safeOutput($link[0]['label']).'</option>';
                }
            } elseif (!in_array('LNK'.(int)$link['id_additionalheaderlinks'], $items)) {
                $html .= '<option value="LNK'.(int)$link['id_additionalheaderlinks'].'">'.$spacer.Tools::safeOutput($link['label']).'</option>';
            }
        }
        $html .= '</optgroup>';
        $html .= '</select>';
        return $html;
    }

    protected function getMenuItems()
    {
        $items = Tools::getValue('items');
        if (is_array($items) && count($items)) {
            return $items;
        } else {
            $shops = Shop::getContextListShopID();
            $conf = null;

            if (count($shops) > 1) {
                foreach ($shops as $key => $shop_id) {
                    $shop_group_id = Shop::getGroupFromShop($shop_id);
                    $conf .= (string)($key > 1 ? ',' : '').Configuration::get('HEADER_ADDITIONAL_LINKS', null, $shop_group_id, $shop_id);
                }
            } else {
                $shop_id = (int)$shops[0];
                $shop_group_id = Shop::getGroupFromShop($shop_id);
                $conf = Configuration::get('HEADER_ADDITIONAL_LINKS', null, $shop_group_id, $shop_id);
            }

            if (strlen($conf)) {
                return explode(',', $conf);
            } else {
                return array();
            }
        }
    }

    protected function makeMenuOption()
    {
        $id_shop = (int)Shop::getContextShopID();

        $menu_item = $this->getMenuItems();
        $id_lang = (int)$this->context->language->id;

        $html = '<select multiple="multiple" name="items[]" id="items" style="width: 300px; height: 160px;">';
        foreach ($menu_item as $item) {
            if (!$item) {
                continue;
            }
            preg_match($this->pattern, $item, $values);
            $id = (int)substr($item, strlen($values[1]), strlen($item));

            switch (substr($item, 0, strlen($values[1]))) {
                case 'LNK':
                    $link = AdditionalHeaderLinks::get((int)$id, (int)$id_lang, (int)$id_shop);
                    if (count($link)) {
                        if (!isset($link[0]['label']) || ($link[0]['label'] == '')) {
                            $default_language = Configuration::get('PS_LANG_DEFAULT');
                            $link = AdditionalHeaderLinks::get($link[0]['id_additionalheaderlinks'], (int)$default_language, (int)Shop::getContextShopID());
                        }
                        $html .= '<option selected="selected" value="LNK'.(int)$link[0]['id_additionalheaderlinks'].'">'.Tools::safeOutput($link[0]['label']).'</option>';
                    }
                    break;
            }
        }

        return $html.'</select>';
    }

    public function renderAddForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('updateadditionalheaderlinks') && !Tools::getValue('updateadditionalheaderlinks')) ?
                        $this->l('Update link') : $this->l('Add a new link'),
                    'icon' => 'icon-link'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Label'),
                        'name' => 'label',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Link'),
                        'name' => 'link',
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'name' => 'submitBlocktopmenuLinks',
                    'title' => $this->l('Add')
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->fields_value = $this->getAddLinkFieldsValues();

        if (Tools::getIsset('updateadditionalheaderlinks') && !Tools::getValue('updateadditionalheaderlinks')) {
            $fields_form['form']['submit'] = array(
                'name' => 'updateadditionalheaderlinks',
                'title' => $this->l('Update')
            );
        }

        if (Tools::isSubmit('updateadditionalheaderlinks')) {
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'updatelink');
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_additionalheaderlinks');
            $helper->fields_value['updatelink'] = '';
        }

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $this->context->controller->getLanguages();
        $helper->default_form_language = (int)$this->context->language->id;

        return $helper->generateForm(array($fields_form));
    }

    public function getAddLinkFieldsValues()
    {
        $links_label_edit = '';
        $labels_edit = '';
        $new_window_edit = '';

        if (Tools::isSubmit('updateadditionalheaderlinks')) {
            $link = AdditionalHeaderLinks::getLinkLang(Tools::getValue('id_additionalheaderlinks'), (int)Shop::getContextShopID());

            foreach ($link['link'] as $key => $label) {
                $link['link'][$key] = Tools::htmlentitiesDecodeUTF8($label);
            }

            $links_label_edit = $link['link'];
            $labels_edit = $link['label'];
            $new_window_edit = $link['new_window'];
        }

        $fields_values = array(
            'new_window' => Tools::getValue('new_window', $new_window_edit),
            'id_additionalheaderlinks' => Tools::getValue('id_additionalheaderlinks'),
        );

        if (Tools::getValue('submitAddmodule')) {
            foreach (Language::getLanguages(false) as $lang) {
                $fields_values['label'][$lang['id_lang']] = '';
                $fields_values['link'][$lang['id_lang']] = '';
            }
        } else {
            foreach (Language::getLanguages(false) as $lang) {
                $fields_values['label'][$lang['id_lang']] = Tools::getValue('label_'.(int)$lang['id_lang'], isset($labels_edit[$lang['id_lang']]) ?
                    $labels_edit[$lang['id_lang']] : '');
                $fields_values['link'][$lang['id_lang']] = Tools::getValue('link_'.(int)$lang['id_lang'], isset($links_label_edit[$lang['id_lang']]) ?
                    $links_label_edit[$lang['id_lang']] : '');
            }
        }

        return $fields_values;
    }

    public function renderList()
    {
        $shops = Shop::getContextListShopID();
        $links = array();

        foreach ($shops as $shop_id) {
            $links = array_merge($links, AdditionalHeaderLinks::gets((int)$this->context->language->id, null, (int)$shop_id));
        }

        $fields_list = array(
            'id_additionalheaderlinks' => array(
                'title' => $this->l('Link ID'),
                'type' => 'text',
            ),
            'label' => array(
                'title' => $this->l('Label'),
                'type' => 'text',
            ),
            'link' => array(
                'title' => $this->l('Link'),
                'type' => 'link',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_additionalheaderlinks';
        $helper->table = 'additionalheaderlinks';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Link list');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($links, $fields_list);
    }

    protected function makeMenu()
    {
        $menu_items = $this->getMenuItems();
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)Shop::getContextShopID();

        $links = array();

        foreach ($menu_items as $item) {
            if (!$item) {
                continue;
            }

            preg_match($this->pattern, $item, $value);
            $id = (int)substr($item, strlen($value[1]), strlen($item));

            switch (substr($item, 0, strlen($value[1]))) {
                case 'LNK':
                    $link = AdditionalHeaderLinks::get((int)$id, (int)$id_lang, (int)$id_shop);
                    if (count($link)) {
                        if (!isset($link[0]['label']) || ($link[0]['label'] == '')) {
                            $default_language = Configuration::get('PS_LANG_DEFAULT');
                            $link = AdditionalHeaderLinks::get($link[0]['id_additionalheaderlinks'], $default_language, (int)Shop::getContextShopID());
                        }
                    }
                    $links[] = $link[0];
                    break;
            }
        }

        return $links;
    }
}
