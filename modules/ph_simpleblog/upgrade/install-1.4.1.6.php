<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_6($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD `display_author` tinyint(1) UNSIGNED NOT NULL DEFAULT 3 AFTER active');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD `display_tags` tinyint(1) UNSIGNED NOT NULL DEFAULT 3 AFTER display_author');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD `allow_comments` tinyint(1) UNSIGNED NOT NULL DEFAULT 3 AFTER display_tags');
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD `is_gallery` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 AFTER allow_comments');

    return true;
}
