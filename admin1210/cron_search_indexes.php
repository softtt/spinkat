<?php

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}
include(_PS_ADMIN_DIR_.'/../config/config.inc.php');

p('start');
if (isset($_GET['secure_key'])) {
    p('check key');
    $secureKey = md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME'));

    if (!empty($secureKey) && $secureKey === $_GET['secure_key']) {

        p('start update');

        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('TRUNCATE `ps_search_indexes`');

        $sql_models_index = "
            INSERT INTO `ps_search_indexes`
            (
            `id_product`,
            `id_product_attribute`,
            `text`)
            SELECT
                pa.id_product,
                pa.id_product_attribute,
                concat_ws(' ',
                    replace(pa.reference, '-', ' '),
                    replace(pa.title, '-', ' '),
                    pl.name,
                    m.name
                )
            FROM
                ps_product_attribute pa
            LEFT JOIN
                ps_product p ON p.id_product = pa.id_product
            LEFT JOIN
                ps_product_lang pl ON pl.id_product = pa.id_product
            LEFT JOIN
                ps_manufacturer m ON m.id_manufacturer = p.id_manufacturer
            WHERE
                pa.hide = 0
                AND p.visibility IN ('both', 'search');
        ";
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_models_index);


        $sql_series_products_index = "
            INSERT INTO `ps_search_indexes`
            (
            `id_product`,
            `id_product_attribute`,
            `text`)
            SELECT
                p.id_product,
                0,
                concat_ws(' ',
                    replace(p.reference, '-', ' '),
                    pl.name,
                    m.name
                )
            FROM
                ps_product p
            LEFT JOIN
                ps_product_lang pl ON pl.id_product = p.id_product
            LEFT JOIN
                ps_manufacturer m ON m.id_manufacturer = p.id_manufacturer
            WHERE
                p.visibility IN ('both', 'search');
        ";
        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_series_products_index);

        p('end update');
    }
}
