<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_10($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post_image` ADD `title` varchar(255) NOT NULL');

    return true;
}
