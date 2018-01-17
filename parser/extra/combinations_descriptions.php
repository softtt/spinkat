<?php

include(__DIR__.'/../lib.php');

/*
Execute query in database to add fields for short and long combination descriptions.
ALTER TABLE `ps_product_attribute` ADD `short_description` TEXT NOT NULL , ADD `long_description` TEXT NOT NULL ;
 */
exit;
$products = Db::getInstance()->executeS('SELECT * FROM ps_old_product');

foreach ($products as $product) {
    p($product);

    $sql = 'SELECT id, body, descript FROM tovar WHERE id = ' . $product['old_product_id'] . ' AND true';

    $info = $pdo->prepare($sql);
    $info->execute();
    $row = $info->fetch();

    p('Atribute exists '. (int)Combination::existsInDatabase($product['new_product_id'], 'product_attribute'));

    if (Combination::existsInDatabase($product['new_product_id'], 'product_attribute')) {
        $combination = new Combination($product['new_product_id']);

        $body = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($row['body']))));

        if ($pos = strpos($body, '<script')) {
            $body = substr($body, 0, $pos);
        }

        $combination->short_description = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($row['descript']))));
        $combination->long_description = $body;

        $combination->save();
        unset($combination);
    }

}
