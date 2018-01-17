<?php
    include('lib.php');

    $links = $pdo->query('SELECT * FROM `feature_bind` WHERE `header` < 99 AND true', PDO::FETCH_ASSOC);
    // $links = $pdo->query('SELECT * FROM `feature_bind` WHERE `header` < 99 AND `tovar_id` = '.$id_value.' AND true', PDO::FETCH_ASSOC);

    foreach ($links as $link) {

    	$attr_titles = $pdo->query('SELECT `header` FROM `feature_type` WHERE `id` = "'.$link["header"].'" AND true', PDO::FETCH_ASSOC);

    	foreach ($attr_titles as $attr_title) {

		  	// getting id of attribute in new db
	    	$sql = 'SELECT id_attribute FROM '._DB_PREFIX_.'attribute_lang WHERE name = "'.$attr_title["header"].'"';
	        $new_attr_id = Db::getInstance()->getValue($sql);

		  	// getting id of attribute parent in new db
	    	$sql = 'SELECT id_attribute_group FROM '._DB_PREFIX_.'attribute WHERE id_attribute = "'.$new_attr_id.'"';
	        $new_attr_group_id = Db::getInstance()->getValue($sql);


    	}
	
	  	// getting id of product in new db
    	$sql = 'SELECT new_id FROM '._DB_PREFIX_.'old_id WHERE old_id = '.$link["tovar_id"];
        $new_product_id = Db::getInstance()->getValue($sql);


		if (isset($new_product_id) && $new_product_id!==false) {
			Db::getInstance()->insert('product_attribute', array(
			    'id_product'      => $new_product_id,
			    'wholesale_price' => 0.000000,
			    'price' =>  0.000000,
			    'ecotax' =>  0.000000,
			    'quantity' => 0,
			    'weight' =>  0.000000,
			    'unit_price_impact' =>  0.000000,
			    'minimal_quantity' => 1,
			    'default_on' =>0,
			));

			// getting id_product_attribute in new db
	    	$sql = 'SELECT MAX(	id_product_attribute), 	id_product_attribute FROM '._DB_PREFIX_.'product_attribute WHERE id_product = '.$new_product_id;
	        $new_id_product_attribute = Db::getInstance()->getValue($sql);


			Db::getInstance()->insert('product_attribute_shop', array(
			    'id_product'      => $new_product_id,
			    'id_product_attribute' => $new_id_product_attribute,
			    'wholesale_price' => 0.000000,
			    'price' =>  0.000000,
			    'ecotax' =>  0.000000,
			    'weight' =>  0.000000,
			    'unit_price_impact' =>  0.000000,
			    'default_on' =>0,
			    'minimal_quantity' => 1,
			    'id_shop' => 1,
			));


			Db::getInstance()->insert('product_attribute_combination', array(
			    'id_attribute'      => $new_attr_id,
			    'id_product_attribute' => $new_id_product_attribute,
			));

		}
	}

    $links = $pdo->query('SELECT `feature_bind`.`header` AS value, `feature_bind`.`tovar_id`, `feature_bind`.`feature_id`, `feature_type`.`header`, `tovar`.`header` AS tovar_header FROM `feature_bind`, `feature_type`, `tovar` WHERE `feature_bind`.`feature_id` = `feature_type`.`id` AND `feature_bind`.`tovar_id` = `tovar`.`id` AND `feature_bind`.`header` > 99 AND true', PDO::FETCH_ASSOC);

    foreach ($links as $link) {

	  	// getting id of attribute in new db
    	$sql = 'SELECT id_attribute FROM '._DB_PREFIX_.'attribute_lang WHERE name = \''.$link["value"].'\'';
        $new_attr_id = Db::getInstance()->getValue($sql);


	  	// getting id of attribute parent in new db
    	$sql = 'SELECT id_attribute_group FROM '._DB_PREFIX_.'attribute WHERE id_attribute = "'.$new_attr_id.'"';
        $new_attr_group_id = Db::getInstance()->getValue($sql);

	  	// getting id of product in new db
    	$sql = 'SELECT new_id FROM '._DB_PREFIX_.'old_id WHERE old_id = '.$link["tovar_id"];
        $new_product_id = Db::getInstance()->getValue($sql);
        
        Db::getInstance()->insert('product_attribute', array(
		    'id_product'      => $new_product_id,
		    'wholesale_price' => 0.000000,
		    'price' =>  0.000000,
		    'ecotax' =>  0.000000,
		    'quantity' => 0,
		    'weight' =>  0.000000,
		    'unit_price_impact' =>  0.000000,
		    'minimal_quantity' => 1,
		    'default_on' =>0,
		));

		// getting id_product_attribute in new db
    	$sql = 'SELECT MAX(	id_product_attribute), 	id_product_attribute FROM '._DB_PREFIX_.'product_attribute WHERE id_product = '.$new_product_id;
        $new_id_product_attribute = Db::getInstance()->getValue($sql);

		Db::getInstance()->insert('product_attribute_shop', array(
		    'id_product'      => $new_product_id,
		    'id_product_attribute' => $new_id_product_attribute,
		    'wholesale_price' => 0.000000,
		    'price' =>  0.000000,
		    'ecotax' =>  0.000000,
		    'weight' =>  0.000000,
		    'unit_price_impact' =>  0.000000,
		    'default_on' =>0,
		    'minimal_quantity' => 1,
		    'id_shop' => 1,
		));


		Db::getInstance()->insert('product_attribute_combination', array(
		    'id_attribute'      => $new_attr_id,
		    'id_product_attribute' => $new_id_product_attribute,
		));

    }
	echo 'Экспорт Связей завершён!<br><a href="/parser">Назад</a>';

  ?> 