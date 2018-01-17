<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_4($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author_lang` DROP COLUMN bio');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author_lang` ADD description TEXT NOT NULL name');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author_lang` ADD description_short TEXT NOT NULL name');

    return true;
}
