<?php

require_once 'base.php';

$upload_file = 'prices/akvatoria.xls';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new AkvatoriaParser($upload_file, new AkvatoriaReadFilter);

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';
