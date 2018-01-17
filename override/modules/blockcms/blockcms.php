<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class BlockCmsOverride extends BlockCms
{
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
