<?php

require_once 'base.php';

$upload_file = 'prices/black_hole.xls';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new BlackHoleParser($upload_file, new BlackHoleReadFilter);

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';
