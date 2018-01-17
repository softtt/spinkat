<?php
/**
 * @author Victor Scherba <dev@smart-raccoon.com>
 * @link   http://smart-raccoon.com
 */

if (!defined('_PS_VERSION_'))
    exit;

class Callback extends Module
{
    public function __construct()
    {
        $this->name = 'callback';
        $this->tab = 'others';
        $this->need_instance = 1;

        $this->version = '1.1.0.1';
        $this->author = 'Smart Raccoon';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Callback me module');
        $this->description = $this->l('Show callback me button, modal form and send request ot manager email.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }


    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('displayBackOfficeHeader') ||
            !$this->registerHook('displayNav')
        ) {
            return false;
        }

        if (file_exists(_PS_MODULE_DIR_.'callback/sql/install.php'))
            include_once (_PS_MODULE_DIR_.'callback/sql/install.php');

        /**

        Tabs

        **/

        // Tabs
        $parent_tab = new Tab();

        $parent_tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent_tab->name[$lang['id_lang']] = $this->l('Callback');

        $parent_tab->class_name = 'AdminCallback';
        $parent_tab->id_parent = 0;
        $parent_tab->module = $this->name;
        $parent_tab->add();

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;

        $tab = Tab::getInstanceFromClassName('AdminCallback');
        $tab->delete();

        return true;
    }

    public function hookHeader($params)
    {
        $this->context->controller->addJqueryPlugin(array('fancybox', 'inputmask.min'));
        $this->context->controller->addJS(array(($this->_path).'js/callback.js', _PS_JS_DIR_.'validate.js'));
        $this->context->controller->addCSS(($this->_path).'css/callback.css');
    }

    public function hookDisplayNav($params)
    {
        $this->context->smarty->assign(array(
            'callback_controller_url' => $this->context->link->getModuleLink('callback'),
            'email' => Configuration::get('BLOCKCONTACT_EMAIL'),
        ));
        return $this->display(__FILE__, 'nav.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (method_exists($this->context->controller, 'addCSS'))
            $this->context->controller->addCSS(($this->_path).'css/callback-admin.css', 'all');
    }
}
