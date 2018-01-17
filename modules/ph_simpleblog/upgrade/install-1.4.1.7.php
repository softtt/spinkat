<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_7($object)
{
    Configuration::updateValue('PH_BLOG_DISPLAY_CATEGORY_IMAGE', '0');
    Configuration::updateValue('PH_BLOG_DISPLAY_LIKES', '0');
    Configuration::updateValue('PH_BLOG_DISPLAY_VIEWS', '0');
    Configuration::updateValue('PH_BLOG_DISPLAY_FEATURED', '0');
    Configuration::updateValue('PH_BLOG_FB_INIT', '0');
    Configuration::updateValue('PH_BLOG_DISPLAY_SHARER', '0');

    return true;
}
