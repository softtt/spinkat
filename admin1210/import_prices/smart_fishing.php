<?php

require_once 'base.php';

$upload_file = 'prices/smart_fishing.xls';

if (move_uploaded_file($_FILES['price_list']['tmp_name'], $upload_file)) {
    $parser = new SmartFishingParser($upload_file, new SmartFishingReadFilter);

    try {
        $parser->parseFile();
    } catch (Exception $e) {
        echo $e->__toString() . '<br>';
    }
}

echo '</pre>';
