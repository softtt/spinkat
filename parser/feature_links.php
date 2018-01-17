<?php
    include('lib.php');

    $feature_links = $pdo->query('SELECT * FROM `feature_bind` WHERE `header` < 99 AND `header` != 2.29 AND true', PDO::FETCH_ASSOC);

    foreach ($feature_links as $feature_link) {

    	$attr_titles = $pdo->query('SELECT `header` FROM `feature_type` WHERE `id` = "'.$feature_link["header"].'" AND true', PDO::FETCH_ASSOC);

    	foreach ($attr_titles as $attr_title) {

		  	// getting id of feature value in new db
	    	$sql = 'SELECT id_feature_value FROM '._DB_PREFIX_.'feature_value_lang WHERE value = "'.$attr_title["header"].'"';
	        $new_feature_value_id = Db::getInstance()->getValue($sql);

		  	// getting id of featue in new db
	    	$sql = 'SELECT id_feature FROM '._DB_PREFIX_.'feature_value WHERE id_feature_value = "'.$new_feature_value_id.'"';
	        $new_feature_id = Db::getInstance()->getValue($sql);
    	}

	  	// getting id of product in new db
    	$sql = 'SELECT new_id FROM '._DB_PREFIX_.'old_id WHERE old_id = '.$feature_link["tovar_id"];
        $new_product_id = Db::getInstance()->getValue($sql);

	    $row = array('id_feature' => (int)$new_feature_id, 'id_product' => (int)$new_product_id, 'id_feature_value' => (int)$new_feature_value_id);
        
        // ckeking for entries
        $sql = 'SELECT COUNT(id_feature) FROM '._DB_PREFIX_.'feature_product WHERE id_feature = '.$new_feature_id.' AND id_product = '. $new_product_id;
        $is_isset = Db::getInstance()->getValue($sql);

        if ((bool)$is_isset == false){  
            Db::getInstance()->insert('feature_product', $row);
            SpecificPriceRule::applyAllRules(array((int)$new_product_id));
        }

    }

    $feature_links = $pdo->query('SELECT * FROM `feature_bind` WHERE `header` > 99 AND true', PDO::FETCH_ASSOC);

    foreach ($feature_links as $feature_link) {

        // getting id of feature value in new db
        $sql = 'SELECT id_feature_value FROM '._DB_PREFIX_.'feature_value_lang WHERE value = "'.$feature_link["header"].'"';
        $new_feature_value_id = Db::getInstance()->getValue($sql);
        

        $feature_names = $pdo->query('SELECT `header` FROM `feature_type` WHERE `id` ='.$feature_link['feature_id'].' AND true', PDO::FETCH_ASSOC);

        foreach ($feature_names as $feature_name) {
            // getting id of featue in new db
            $sql = 'SELECT id_feature FROM '._DB_PREFIX_.'feature_lang WHERE name = "'.$feature_name['header'].'"';
            $new_feature_id = Db::getInstance()->getValue($sql);
        }

        // getting id of product in new db
        $sql = 'SELECT new_id FROM '._DB_PREFIX_.'old_id WHERE old_id = '.$feature_link["tovar_id"];
        $new_product_id = Db::getInstance()->getValue($sql);

        $row = array('id_feature' => (int)$new_feature_id, 'id_product' => (int)$new_product_id, 'id_feature_value' => (int)$new_feature_value_id);
        
        // ckeking for entries
        $sql = 'SELECT COUNT(id_feature) FROM '._DB_PREFIX_.'feature_product WHERE id_feature = '.$new_feature_id.' AND id_product = '. $new_product_id;
        $is_isset = Db::getInstance()->getValue($sql);

        if ((bool)$is_isset == false){  
            Db::getInstance()->insert('feature_product', $row);
            SpecificPriceRule::applyAllRules(array((int)$new_product_id));
        }
    }

echo 'Экспорт Связи характеристик завершён!<br><a href="/parser">Назад</a>';

?>