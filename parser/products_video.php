<?php
    include('lib.php');

    $videos = $pdo->query('SELECT `id`, `video` FROM `tovar` WHERE `video` != "" AND true', PDO::FETCH_ASSOC);

    foreach ($videos as $video) {
	        // getting product's id
	        $sql = 'SELECT new_id FROM '._DB_PREFIX_.'old_id WHERE old_id = '.$video['id'];
	        $product_id = Db::getInstance()->getValue($sql);

            if (!$product_id){
                $sql = 'SELECT new_product_id FROM '._DB_PREFIX_.'old_product WHERE old_product_id = '.$video['id'];
                $combination_id = Db::getInstance()->getValue($sql);

                if (ObjectModelCore::existsInDatabase($combination_id, 'product_attribute')){
                    $combination = new Combination((int)$combination_id);
                    $combination->attribute_video = $video['video'];

                    $combination->save();
                }
                    
            } else {

                if (ObjectModelCore::existsInDatabase($product_id, 'product')){
                    $product = new Product($product_id);
                    $product->video = $video['video'];

                    $product->save();
                }
	   }
    }

    echo "Прикрепление видео к товарам завершёно!<br><a href='/parser'>Назад</a>";

?>    