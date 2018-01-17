<?php

if (file_exists(_PS_ROOT_DIR_.'/config/settings.inc.php')) {
    include_once(_PS_ROOT_DIR_.'/config/settings.inc.php');
}

/**
 * Class DbCore
 */
abstract class Db extends DbCore
{
    public static function getSearchInstance()
    {
        if (DB::checkConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_) == 0) {
            $db_class = Db::getClass();

            return new $db_class(_DB_SEARCH_SERVER_, _DB_SEARCH_USER_, _DB_SEARCH_PASSWD_, _DB_SEARCH_NAME_, true);
        }

        return Db::getInstance();
    }
}
