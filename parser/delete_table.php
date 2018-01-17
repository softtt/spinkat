<?php

    include('lib.php');

    Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'old_id`');

    echo 'Таблица соотношений id удалена!<br><a href="/parser">Назад</a>';

?>