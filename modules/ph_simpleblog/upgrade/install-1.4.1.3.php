<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_3($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author_lang` DROP `firstname`, DROP `lastname`');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author_lang` ADD name VARCHAR(255) NOT NULL id_lang');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author_lang` CHANGE `link_rewrite` `link_rewrite` VARCHAR(255)');

    return true;
}
