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

class Ext_sales_page extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'ext_sales_page';
        $this->tab = 'others';
        $this->version = '1.1.0';
        $this->author = 'Smart Raccoon';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('Extended sales page');
        $this->description = $this->l('Extended sales page with flag for product to display on sales page.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (file_exists($this->getLocalPath().'sql/install.php'))
            include_once ($this->getLocalPath().'sql/install.php');

        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}
