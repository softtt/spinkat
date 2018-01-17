<?php

if (!defined('_PS_VERSION_'))
    exit;

class BlockPaymentLogoOverride extends BlockPaymentLogo
{
    public function install()
    {
        Configuration::updateValue('PS_PAYMENT_LOGO_CMS_ID', 0);
        return (parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('leftColumn') &&
            $this->registerHook('displayTopFooter1') &&
            $this->registerHook('displayTopFooter2') &&
            $this->registerHook('displayTopFooter3'));
    }

    public function hookDisplayTopFooter1($params)
    {
        return $this->hookFooter($params);
    }

    public function hookDisplayTopFooter2($params)
    {
        return $this->hookFooter($params);
    }

    public function hookDisplayTopFooter3($params)
    {
        return $this->hookFooter($params);
    }
}
