<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_8($object)
{
    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'simpleblog_post_type` WHERE slug IN ("video", "url")');

    return true;
}
