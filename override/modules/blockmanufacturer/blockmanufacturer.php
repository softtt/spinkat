<?php

if (!defined('_PS_VERSION_'))
    exit;

class BlockManufacturerOverride extends BlockManufacturer
{
    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayAfterColumnsBeforeFooter') ||
            !$this->registerHook('displayLeftColumnTab') ||
            !$this->registerHook('displayLeftColumnTabContent')) {
            return false;
        }
    }

    public function hookDisplayAfterColumnsBeforeFooter($params)
    {
        if (!$this->isCached('blockmanufacturer-home-before-footer.tpl', $this->getCacheId()))
        {
            $manufacturers = Manufacturer::getManufacturers();
            foreach ($manufacturers as $key => &$manufacturer)
            {
                $manufacturer['image'] = $this->context->language->iso_code.'-default';
                if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                    $manufacturer['image'] = $manufacturer['id_manufacturer'];
                else
                    unset($manufacturers[$key]);
            }

            $this->smarty->assign(array(
                'manufacturers' => $manufacturers,
                'text_list' => Configuration::get('MANUFACTURER_DISPLAY_TEXT'),
                'text_list_nb' => Configuration::get('MANUFACTURER_DISPLAY_TEXT_NB'),
                'form_list' => Configuration::get('MANUFACTURER_DISPLAY_FORM'),
                'display_link_manufacturer' => Configuration::get('PS_DISPLAY_SUPPLIERS'),
            ));
        }
        return $this->display(__FILE__, 'blockmanufacturer-home-before-footer.tpl', $this->getCacheId());
    }

    public function hookDisplayLeftColumnTab($params) {
        return $this->display(__FILE__, 'left-column-tab.tpl');
    }

    public function hookDisplayLeftColumnTabContent($params) {
        $current_manufacturer_id = $this->context->controller->php_self == 'manufacturer' ? $this->context->controller->getManufacturer()->id : 0;
        $this->smarty->assign(compact('current_manufacturer_id'));

        return $this->hookLeftColumn($params);
    }

    public function hookMobileNav($params) {
        if (!$this->isCached('blockmanufacturer-mobile-nav.tpl', $this->getCacheId()))
        {
            $manufacturers = Manufacturer::getManufacturers(true);
            foreach ($manufacturers as &$manufacturer)
            {
                $manufacturer['image'] = $this->context->language->iso_code.'-default';
                if (file_exists(_PS_MANU_IMG_DIR_.$manufacturer['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                    $manufacturer['image'] = $manufacturer['id_manufacturer'];
            }

            $this->smarty->assign(array(
                'manufacturers' => $manufacturers,
                'text_list' => Configuration::get('MANUFACTURER_DISPLAY_TEXT'),
                'text_list_nb' => Configuration::get('MANUFACTURER_DISPLAY_TEXT_NB'),
                'display_link_manufacturer' => Configuration::get('PS_DISPLAY_SUPPLIERS'),
            ));
        }
        return $this->display(__FILE__, 'blockmanufacturer-mobile-nav.tpl', $this->getCacheId());
    }
}
