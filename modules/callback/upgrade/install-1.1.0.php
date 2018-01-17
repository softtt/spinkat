<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_1_0($object)
{
    $sql = array();

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'callback_order` (
                `id_callback_order` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `phone` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `message` VARCHAR(255) NOT NULL,
                `date` datetime NOT NULL,
                `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_callback_order`)
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
