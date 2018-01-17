<?php

require_once 'base.php';

$upload_file = 'prices/mida.xls';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new MidaParser($upload_file, new MidaReadFilter);

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';
