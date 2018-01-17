<?php
    include('lib.php');

    $all_series = Db::getInstance()->executeS('SELECT * FROM ps_old_series');

    foreach ($all_series as $series) {
    	p($series);

		$fileTmpLoc = null;

	    $products = $pdo->query('SELECT * FROM `tovar` WHERE `coll_id` = '. $series["old_series_id"] .' AND true', PDO::FETCH_ASSOC);

	    foreach ($products as $product) {

	        $images = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$product["picture"].' AND true', PDO::FETCH_ASSOC);

	        foreach ($images as $image) {

	            $fileTmpLoc = 'img/'.$product["picture"].'.'.$image['type'];

	            if (file_exists($fileTmpLoc) == false) {
	                $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtoupper($image['type']);
	                if (file_exists($fileTmpLoc) == false) {
	                    $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtolower($image['type']);
	                }
	            }

	        }
	    }

        p($fileTmpLoc);

        if ($fileTmpLoc) {
	        $new_image = new Image();
	        $imagesTypes = ImageType::getImagesTypes('products');

	        $new_image->id_product = $series['new_series_id'];
	        $new_image->cover = 1;
	        $new_image->add();
	        $new_path = $new_image->getPathForCreation();
	       
	        ImageManager::resize($fileTmpLoc, $new_path.'.jpg');

	        foreach ($imagesTypes as $k => $image_type) {
	            if (!ImageManager::resize($fileTmpLoc, $new_path.'-'.stripslashes($image_type['name']).'.'.$new_image->image_format, $image_type['width'], $image_type['height'], $new_image->image_format)) {
	                echo "COVER FAIL";
	            }
	        }        
        }
    }

