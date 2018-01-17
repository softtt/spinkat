<?php

// Prestashop system loading files
require_once __DIR__ . '/../../../config/config.inc.php';
require_once __DIR__ . '/../../../init.php';

echo '<pre>';

if ($_GET['id']) {
    $manufacturer_id = $_GET['id'];

    $manufacturer = new Manufacturer($manufacturer_id);

    if ($manufacturer) {
        $products = $manufacturer->getProductsLite(1);

        if ($products && count($products)) {
            foreach ($products as $product) {
                $id_product = $product['id_product'];

                $p = new Product($id_product);
                $combinations = $p->getWsCombinations();

                if ($combinations && count($combinations)) {
                    foreach ($combinations as $combination) {
                        StockAvailable::setQuantity($id_product, $combination['id'], $amount);
                        Hook::exec('actionProductUpdate', array('id_product' => (int)$id_product, 'product' => new Product($id_product)));
                    }
                }
            }
        }
    }


    echo 'Очистка остатков завершена';
}

echo '</pre>';
