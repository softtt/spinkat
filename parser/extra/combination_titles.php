<?php
include(__DIR__.'/../lib.php');

$replace_text = [
    'Спиннинговое удилище',
    'Спиннинг',
    'Cпиннинг',
    'cпиннинг',
    'Кастинговое удилище',
    'Катушка',
    'катушка',
    '(cast)',
    "\r",
    "\n"
];

$all_series = Db::getInstance()->executeS("SELECT os.*, p.id_manufacturer FROM `ps_old_series` os LEFT JOIN ps_product p ON p.id_product = os.new_series_id WHERE p.id_manufacturer IN (51, 27, 18, 6)");

foreach ($all_series as $series) {

    p($series);

    $products = $pdo->query('SELECT t.*, m.header as manufacturer_title FROM `tovar` as t
                             LEFT JOIN manufacture m ON m.id = t.manufacture_id
                             WHERE t.`coll_id` = '. $series["old_series_id"] .' AND true', PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        // p($product);
        $combination_title = $product['header'];
        $combination_title = str_ireplace($product['manufacturer_title'], '', $combination_title);
        $combination_title = str_ireplace($series['title'], '', $combination_title);
        $combination_title = mb_ucfirst(trim(str_ireplace($replace_text, '', $combination_title), ' -"\'®™'), 'utf8');

        p($product['id'].' '.$combination_title);

        $new_model_id = Db::getInstance()->executeS("SELECT * FROM ps_old_product WHERE old_product_id = " . $product['id']);

        p($new_model_id[0]['new_product_id']);

        if ($new_model_id && $new_model_id[0]['new_product_id']) {
            Db::getInstance()->execute("UPDATE `ps_product_attribute` SET `title` = '".$combination_title."' WHERE `ps_product_attribute`.`id_product_attribute` = " . $new_model_id[0]['new_product_id']);
        }

    }
}
