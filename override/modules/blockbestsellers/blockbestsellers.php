<?php

if (!defined('_PS_VERSION_'))
    exit;

class BlockBestSellersOverride extends BlockBestSellers
{
    public function hookDisplayHome($params)
    {
        BlockBestSellers::$cache_best_sellers = $this->getBestSellers($params);

        return parent::hookDisplayHome($params);
    }
}
