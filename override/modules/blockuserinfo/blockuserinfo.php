<?php

if (!defined('_PS_VERSION_'))
    exit;

class BlockUserInfoOverride extends BlockUserInfo
{
    public function install()
    {
        return (Module::install()
            && $this->registerHook('displayTopMenu')
            && $this->registerHook('displayHeader'));
    }

    public function hookDisplayTopMenu($params)
    {
        return $this->hookDisplayTop($params);
    }

    public function hookMobileNav($params)
    {
        return $this->hookDisplayTop($params);
    }
}
