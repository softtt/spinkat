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
    "\r",
    "\n"
];


$all_series = Db::getInstance()->executeS('SELECT os.*, p.id_manufacturer FROM `ps_old_series` os LEFT JOIN ps_product p ON p.id_product = os.new_series_id WHERE p.id_manufacturer IN (51, 27, 18, 6)');

foreach ($all_series as $series) {

    p($series);

    $series_product = new Product($series['new_series_id']);

    $series_product->deleteProductAttributes();

    $products = $pdo->query('SELECT t.*, m.header as manufacturer_title FROM `tovar` as t
                             LEFT JOIN manufacture m ON m.id = t.manufacture_id
                             WHERE t.`coll_id` = '. $series["old_series_id"] .' AND true', PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        // p($product);
        $combination_title = $product['header'];
        $combination_title = str_ireplace($product['manufacturer_title'], '', $combination_title);
        $combination_title = str_ireplace($series['title'], '', $combination_title);
        $combination_title = mb_ucfirst(trim(str_ireplace($replace_text, '', $combination_title), ' -"\'®™'), 'utf8');

        if (!$combination_title) {
            $combination_title = $product['header'];
        }

        p($combination_title);

        $id_product_attribute = $series_product->addCombinationEntity(
            0, # $wholesale_price,
            $product['price'], # $price,
            0, # $weight,
            0, # $unit_impact,
            0, # $ecotax,
            0, # $quantity DEPRECATED,
            [], # $id_images,
            $product['articul'], # $reference,
            0, # $id_supplier,
            null, # $ean13,
            0, # $default,
            null, # $location = null,
            null, # $upc = null,
            1, # $minimal_quantity = 1,
            [1], # array $id_shop_list = array(),
            null, # $available_date = null,
            $combination_title # $title = ''
        );
        StockAvailable::setProductDependsOnStock((int)$series_product->id, $series_product->depends_on_stock, null, (int)$id_product_attribute);
        StockAvailable::setProductOutOfStock((int)$series_product->id, $series_product->out_of_stock, null, (int)$id_product_attribute);

        $sql = 'SELECT b.tovar_id, t2.header as attribute_name, t.header as attribute_value
                FROM `feature_bind` b
                LEFT JOIN feature_type t on t.id = b.header
                LEFT JOIN feature_type t2 on t2.id = b.feature_id
                WHERE `tovar_id` = '.$product['id'].'
                AND b.`header` < 99

                UNION SELECT b.tovar_id, t.header as attribute_name, b.header as attribute_value
                FROM `feature_bind` b
                LEFT JOIN feature_type as t ON t.id = b.feature_id
                WHERE `tovar_id` = '.$product['id'].'
                AND b.`header` >= 99';

        $combination_attributes = $pdo->query($sql, PDO::FETCH_ASSOC);

        $combination_attributes_array = [];

        foreach($combination_attributes as $attribute) {
            $new_attribute = Db::getInstance()->executeS('SELECT id_attribute FROM ps_attribute_lang WHERE name = "'.$attribute['attribute_value'] . '"');
            if (isset($new_attribute[0]) && isset($new_attribute[0]['id_attribute'])) {
                $combination_attributes_array[] = $new_attribute[0]['id_attribute'];
            }
        }
p($combination_attributes_array);
        $combination = new Combination((int)$id_product_attribute);
        $combination->setAttributes($combination_attributes_array);

        Db::getInstance()->execute('DELETE FROM ps_old_product WHERE old_product_id = ' . $product['id']);
        Db::getInstance()->insert('old_product', array(
            'old_product_id' => $product['id'],
            'new_product_id' => $id_product_attribute,
        ));

        $combination->short_description = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['descript']))));

        $body = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['body']))));
        $combination->long_description = $body;

        $combination->save();
    }

    $series_product->checkDefaultAttributes();
}

echo 'Экспорт Серий завершён!<br><a href="/parser">Назад</a>';

?>
