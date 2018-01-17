<?php

require_once 'base.php';

$upload_file = 'prices/raymarine.xls';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new RaymarineParser($upload_file, new RaymarineReadFilter);

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';
