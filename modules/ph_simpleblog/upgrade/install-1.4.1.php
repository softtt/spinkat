<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_author` DROP `firstname`, DROP `lastname`, DROP `bio`');

    $sql = array();

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_author_lang` (
        `id_simpleblog_author` int(10) UNSIGNED NOT NULL,
        `id_lang` int(10) UNSIGNED NOT NULL,
        `firstname` VARCHAR(128) NOT NULL,
        `lastname` VARCHAR(128) NOT NULL,
        `bio` TEXT NOT NULL,
        `link_rewrite` varchar(128) NOT NULL,
        `meta_title` varchar(128) NOT NULL,
        `meta_keywords` varchar(255) NOT NULL,
        `meta_description` varchar(255) NOT NULL,
        PRIMARY KEY (`id_simpleblog_author`,`id_lang`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

    /**

    DB

    **/
    foreach ($sql as $s) {
        if (!Db::getInstance()->Execute($s)) {
            return false;
        }
    }
    return true;
}
