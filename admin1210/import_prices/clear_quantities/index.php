<?php

// Prestashop system loading files
require_once __DIR__ . '/../../../config/config.inc.php';
require_once __DIR__ . '/../../../init.php';

$manufacturers = Manufacturer::getManufacturers();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Очистка остатков товаров</title>
</head>
<body>
    <h1>Очистка остатков товаров</h1>
    <table>
        <?php foreach ($manufacturers as $manufacturer) { ?>
            <tr>
                <td><?= $manufacturer['name'] ?></td>
                <td><a href="clear.php?id=<?= $manufacturer['id_manufacturer'] ?>">Очистить</a></td>
            </tr>

        <?php } ?>
    </table>
</body>
</html>
