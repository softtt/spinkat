<?php

if (!defined('_PS_VERSION_'))
    exit;

require_once _PS_MODULE_DIR_.'blockcmsinfo/classes/InfoBlock.php';

class BlockcmsinfoOverride extends Blockcmsinfo
{
    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayAfterColumnsBeforeFooter')) {
            return false;
        }
    }

    public function hookDisplayAfterColumnsBeforeFooter($params)
    {
        return $this->hookHome($params);
    }
}
