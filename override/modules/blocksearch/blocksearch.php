<?php

if (!defined('_PS_VERSION_'))
    exit;

class BlockSearchOverride extends BlockSearch
{
    public function install()
    {
        if (!Module::install() ||
            !$this->registerHook('top') ||
            !$this->registerHook('header') ||
            !$this->registerHook('displayMobileTopSiteMap') ||
            !$this->registerHook('displaySearch') ||
            !$this->registerHook('displayFooter') ||
            !$this->registerHook('displayTopFooter1') ||
            !$this->registerHook('displayTopFooter2') ||
            !$this->registerHook('displayTopFooter3'))
            return false;
        return true;
    }

    public function hookDisplayFooter($params)
    {
        return $this->hookRightColumn($params);
    }

    public function hookDisplayTopFooter1($params)
    {
        return $this->hookDisplayFooter($params);
    }

    public function hookDisplayTopFooter2($params)
    {
        return $this->hookDisplayFooter($params);
    }

    public function hookDisplayTopFooter3($params)
    {
        return $this->hookDisplayFooter($params);
    }

    public function hookMobileNav($params)
    {
        return $this->hookDisplayNav($params);
    }
}
