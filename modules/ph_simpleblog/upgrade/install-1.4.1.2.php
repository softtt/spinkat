<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_2($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author` DROP COLUMN `email`');

    return true;
}
