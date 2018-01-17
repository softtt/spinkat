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

class Brands_on_category_page extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'brands_on_category_page';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Smart Raccoon';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Brands on Category Page');
        $this->description = $this->l('Brands on Category Page');

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

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBrandsOnCategoryPage') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        return parent::uninstall();
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
        $this->context->controller->addJS($this->_path.'/views/js/brands_on_category_page.js');
        $this->context->controller->addCSS($this->_path.'/views/css/brands_on_category_page.css');
    }

    public function hookDisplayBrandsOnCategoryPage($params)
    {
        $id_category = ($params['category_id'] ? $params['category_id'] : false);
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $manufacturers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT m.id_manufacturer, m.name, COUNT(p.is_series)
            FROM `'._DB_PREFIX_.'manufacturer` m
            '.Shop::addSqlAssociation('manufacturer', 'm').'
            LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_manufacturer = m.id_manufacturer AND p.is_series = 1)'.
            ($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = p.`id_product`)
                LEFT JOIN '._DB_PREFIX_.'category c ON (c.id_category = cp.id_category)' : '').'
            WHERE m.`active` = 1'.
            ($id_category ? ' AND (cp.`id_category` = '.(int)$id_category.' OR c.`id_parent` = '.(int)$id_category.')' : '').'
            GROUP BY m.`id_manufacturer`
            HAVING COUNT(p.is_series) > 0
            ORDER BY m.`name` ASC'
        );

        foreach ($manufacturers as $key => &$manufacturer) {
            if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $manufacturer['image'] = $manufacturer['id_manufacturer'];
        }

        $rewrite_settings = (int)Configuration::get('PS_REWRITING_SETTINGS');
        foreach ($manufacturers as &$manufacturer) {
            $sql = 'SELECT p.id_product, pl.name AS series_name,
                           m.id_manufacturer, m.name AS manufacturer_name
                    FROM `'._DB_PREFIX_.'product` p
                    '.Shop::addSqlAssociation('product', 'p').'
                    LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
                    LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)'.
                    ($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = p.`id_product`)
                        LEFT JOIN '._DB_PREFIX_.'category c ON (c.id_category = cp.id_category)' : '').'
                    WHERE product_shop.`active` = 1 AND p.is_series = 1'.
                        ' AND m.id_manufacturer = '.$manufacturer['id_manufacturer'].
                        ($id_category ? ' AND (cp.`id_category` = '.(int)$id_category.' OR c.`id_parent` = '.(int)$id_category.')' : '').'
                    ORDER BY pl.`name` ASC';

            $manufacturer['series'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $manufacturer['link_rewrite'] = ($rewrite_settings ? Tools::link_rewrite($manufacturer['name']) : 0);

            foreach ($manufacturer['series'] as &$product) {
                $product['link'] = $this->context->link->getSeriesLink($product['id_product']);
            }
        }

        $this->smarty->assign(array(
            'manufacturers' => $manufacturers,
        ));

        return $this->display(__FILE__, 'brands_series.tpl');
    }
}
