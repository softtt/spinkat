<?php
    include('lib.php');
    
    $sql = 'SELECT id_category, link_rewrite FROM '._DB_PREFIX_.'category_lang WHERE id_category != 1 AND id_category != 2 AND id_category != 58';
    $links = Db::getInstance()->ExecuteS($sql);

    foreach ($links as $link) {

        $new_link = substr($link['link_rewrite'], 0, -3);

        $sql2 = 'UPDATE '._DB_PREFIX_.'category_lang SET link_rewrite = "'.$new_link.'" WHERE id_category = '.$link['id_category'];
        if (!Db::getInstance()->execute($sql2)) {
            die('error!');
        }
    }
    echo "Циркумцизия завершена!<br><a href='/parser'>Назад</a>";
?>