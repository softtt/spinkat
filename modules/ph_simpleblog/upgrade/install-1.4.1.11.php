<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_11($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD `display_on_homepage` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 AFTER is_gallery');

    return true;
}
