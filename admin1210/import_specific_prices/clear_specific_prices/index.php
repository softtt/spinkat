<?php

// Prestashop system loading files
require_once __DIR__ . '/../../../config/config.inc.php';
require_once __DIR__ . '/../../../init.php';

$specificPrices = Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
    TRUNCATE TABLE `'._DB_PREFIX_.'specific_price`
');

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Очистка скидок</title>
</head>
<body>
    <p>Cкидки были удалены</p>
</body>
</html>
