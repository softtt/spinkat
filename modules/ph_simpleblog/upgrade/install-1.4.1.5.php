<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_5($object)
{
    Configuration::updateGlobalValue('PH_BLOG_AUTHORS_SLUG', 'authors');
    Configuration::updateGlobalValue('PH_BLOG_AUTHORS_MAIN_TITLE', '');
    Configuration::updateGlobalValue('PH_BLOG_AUTHORS_MAIN_META_DESCRIPTION', '');

    return true;
}
