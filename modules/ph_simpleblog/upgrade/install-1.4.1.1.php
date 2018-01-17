<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_1($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author` ADD active tinyint(1) UNSIGNED NOT NULL DEFAULT 0 AFTER cover');

    return true;
}
