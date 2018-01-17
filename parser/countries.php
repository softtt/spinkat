<?php
    include('lib.php');

    $manufacturers = $pdo->query('SELECT `manufacture`.`header` AS manufacturer, `countrys`.`header` AS country FROM `manufacture`, `countrys` WHERE `manufacture`.`flags` = `countrys`.`id` AND true', PDO::FETCH_ASSOC);
    foreach ($manufacturers as $manufacturer) {

    	switch ($manufacturer['country']) {
		case 'США':
		    $id_country = 21;
		    break;
		case 'Швеция':
		    $id_country = 18;
		    break;
		case 'Япония':
		    $id_country = 11;
		    break;
		case 'Китай':
		    $id_country = 203;
		    break;
		case 'Корея':
		    $id_country = 28;
		    break;
		case 'Тайвань':
		    $id_country = 245;
		    break;
		case 'Украина':
		    $id_country = 216;
		    break;
		case 'Великобритания':
		    $id_country = 17;
		    break;
		case 'Финляндия':
		    $id_country = 7;
		    break;
		case 'Россия':
		    $id_country = 177;
		    break;		 
		case 'Новая Зеландия':
		    $id_country = 27;
		    break;			       		    
		}

    	$id_manufacturer = Db::getInstance()->executeS('SELECT id_manufacturer FROM ps_manufacturer WHERE name = "' . $manufacturer["manufacturer"].'"');


		$new_adress = new Address();

		$new_adress->id_country = $id_country;
		$new_adress->id_manufacturer = $id_manufacturer[0]['id_manufacturer'];
		$new_adress->alias = 'manufacturer';

    	$new_adress->save();
		
    }

echo 'Экспорт Стран производителей завершён!<br><a href="/parser">Назад</a>';