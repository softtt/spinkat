<?php
include('lib.php');


$products = Db::getInstance()->executeS('SELECT * FROM ps_old_product WHERE new_product_id = 4200');


foreach ($products as $product) {
	$fileTmpLoc = null;

    $images = $pdo->query('SELECT `pic_id` FROM `tovar_images` WHERE `tovar_id`='.$product["old_product_id"].' AND true', PDO::FETCH_ASSOC);

    $series = Db::getInstance()->executeS('SELECT * FROM ps_product_attribute WHERE id_product_attribute = ' . $product["new_product_id"]);

    $combination_images = [];

    foreach ($images as $image) {

        $types = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$image["pic_id"].' AND true', PDO::FETCH_ASSOC);

        foreach ($types as $type) {

           $fileTmpLoc = 'img/'.$image["pic_id"].'.'.$type['type'];

            if (file_exists($fileTmpLoc) == false) {
                $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtoupper($type['type']);
                if (file_exists($fileTmpLoc) == false) {
                    $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtolower($type['type']);
                }
            }

            $new_image = new Image();
            $imagesTypes = ImageType::getImagesTypes('products');


            $new_image->id_product = $series[0]['id_product'];
            $new_image->cover = 0;
            $new_image->add();
            $new_path = $new_image->getPathForCreation();
             
            ImageManager::resize($fileTmpLoc, $new_path.'.jpg');

            foreach ($imagesTypes as $k => $image_type) {
                if (!ImageManager::resize($fileTmpLoc, $new_path.'-'.stripslashes($image_type['name']).'.'.$new_image->image_format, $image_type['width'], $image_type['height'], $new_image->image_format)) {
                    echo 'FOTO FAIL!';
                }
            }  

            $combination_images[] = $new_image->id;  
        }

    }

    if ($combination_images) {
	    $combination = new Combination((int)$product["new_product_id"]);
	    $combination->setImages($combination_images);
    }
}