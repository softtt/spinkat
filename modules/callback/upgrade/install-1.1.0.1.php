<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_1_0_1($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'callback_order` CHANGE name client VARCHAR(255) NOT NULL');

    return true;
}
