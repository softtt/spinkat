<?php

require_once 'base.php';

$upload_file = 'prices/spinkat.xls';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new SpinkatParser($upload_file, new SpinkatReadFilter);

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';
