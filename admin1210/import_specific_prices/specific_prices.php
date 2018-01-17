<?php
require_once 'base.php';

$upload_file = 'specific_prices/specific_prices.xlsx';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new SpecificPriceParser($upload_file, new SpecificPriceReadFilter());

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';