<?php
    include('lib.php');

    Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `ps_old_series` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `old_series_id` int(11) NOT NULL,
            `new_series_id` int(11) NOT NULL,
            `title` varchar(250) NOT NULL,
            PRIMARY KEY(`id`)
        ) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8');

    $all_series = $pdo->query('SELECT c.id, c.header, c.man_id, m.header as manufacturer, cat.header as category, c.visible
                               FROM `coll` c
                               LEFT JOIN tovar t ON t.coll_id = c.id
                               LEFT JOIN manufacture m ON m.id = c.man_id
                               LEFT JOIN catalog cat ON cat.id = t.catalog_id
                               GROUP BY (c.id)
                               HAVING count(t.id) > 0', PDO::FETCH_ASSOC);

    foreach ($all_series as $series) {

        $manufacturer_title = $series['manufacturer'];


        $sql = 'SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE name = '. $pdo->quote($manufacturer_title);
        $manufacturer_id = Db::getInstance()->getValue($sql);


        $products = $pdo->query('SELECT * FROM `tovar` WHERE `coll_id` = '. $series["id"] .' AND true LIMIT 1', PDO::FETCH_ASSOC);

        $sql = 'SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = '. $pdo->quote($series['category']);
        $category_id = Db::getInstance()->getValue($sql);

        $new_product = new Product();

        $new_product->id_shop_default = 1;
        $new_product->id_manufacturer = $manufacturer_id;
        $new_product->id_category_default = $category_id;
        $new_product->id_tax_rules_group = 0 ; //means none
        $new_product->minimal_quantity = 1; // as default
        $new_product->redirect_type = '404';
        $new_product->indexed = 1; //search index
        $new_product->is_series = 1;
        $new_product->active = $series['visible'];


        foreach (Language::getLanguages(true) as $lang){
            $new_product->name[$lang['id_lang']] = $pdo->prepare($series['header'])->queryString;
            $new_product->link_rewrite[$lang['id_lang']] = Tools::link_rewrite($series['header']);
        }

        $new_product->add();

        // insert associations with categories in ps_category_product table
        Db::getInstance()->insert('category_product', array(
            'id_product'  => $new_product->id,
            'id_category' => $category_id,
        ));

        p($series['id'] . ' ' . $new_product->id . ' ' . Db::getInstance()->escape($series['header']));

        Db::getInstance()->insert('old_series', array(
            'old_series_id' => $series['id'],
            'new_series_id' => $new_product->id,
            'title' => Db::getInstance()->escape($series['header']),
        ));
    }

echo 'Экспорт Серий завершён!<br><a href="/parser">Назад</a>';

