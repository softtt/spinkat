<?php

class BlocktopmenuOverride extends Blocktopmenu
{
    public function install($delete_params = true)
    {
        if (!parent::install($delete_params) ||
            !$this->registerHook('displayTopPagesMenu')) {
            return false;
        }
    }


    public function hookdisplayTopPagesMenu($param)
    {
        return $this->hookDisplayTop($param);
    }

    public function hookMobileNav($param)
    {
        return $this->hookDisplayTop($param);
    }
}
