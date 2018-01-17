<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_12($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category_lang` ADD `homepage_title` varchar(128) NOT NULL');

    return true;
}
