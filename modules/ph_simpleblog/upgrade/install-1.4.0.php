<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_0($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author` ADD cover TEXT AFTER bio');

    return true;
}
