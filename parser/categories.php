<?php
    include('lib.php');

    $categories = $pdo->query('SELECT * FROM `catalog` WHERE `parent` = 0 AND true ORDER BY `pos`', PDO::FETCH_ASSOC);

    foreach ($categories as $category) {

        $new_category = new Category();

        $new_category->active = $category['visible'];
        $new_category->position = $category['pos'];
        $new_category->id_parent = 2;
        $new_category->level_depth = 2;
        $new_category->date_add = date('Y-m-d G:i:s');
        $new_category->groupBox = Array("0" => 1, "1" => 2, "2" => 3);


        foreach (Language::getLanguages(true) as $lang){
            $new_category->name[$lang['id_lang']] = $category['header']; 
            $new_category->link_rewrite[$lang['id_lang']] = str2url($category['header'], $category['id']); 
            $new_category->description[$lang['id_lang']] = $category['description'];
            $new_category->meta_title[$lang['id_lang']] = $category['ht_head'];
            $new_category->meta_description [$lang['id_lang']]= $category['ht_desc'];
        }


        // covers

        $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$category["pic"].' AND true', PDO::FETCH_ASSOC);

        foreach ($images_type as $image_type) {
            $fileTmpLoc = 'img/'.$category['pic'].'.'.$image_type['type'];
        }

        $new_category->add();

        $origPath = _PS_CAT_IMG_DIR_ . $new_category->id.'.jpg';

        $generate_hight_dpi_images = (bool)Configuration::get('PS_HIGHT_DPI');

        if(isset($fileTmpLoc) && isset($category['pic'])) {
            
            try {
                $orig = PhpThumbFactory::create($fileTmpLoc);
            } catch (Exception $e) {
                echo $e;
            }

            $orig->save($origPath);
        }


        if(_PS_CAT_IMG_DIR_.$new_category->id.'.jpg') {
           
            $images_types = ImageType::getImagesTypes('categories');
           
            foreach ($images_types as $k => $image_type) {
                ImageManager::resize(
                    _PS_CAT_IMG_DIR_.$new_category->id.'.jpg',
                    _PS_CAT_IMG_DIR_.$new_category->id.'-'.stripslashes($image_type['name']).'.jpg',
                    (int)$image_type['width'], (int)$image_type['height']
                );

                if ($generate_hight_dpi_images) {
                    ImageManager::resize(
                        _PS_CAT_IMG_DIR_.$new_category->id.'.jpg',
                        _PS_CAT_IMG_DIR_.$new_category->id.'-'.stripslashes($image_type['name']).'2x.jpg',
                        (int)$image_type['width']*2, (int)$image_type['height']*2
                    );
                }
            }
        }
    }

    echo "Экспорт Категорий завершён!<br><a href='/parser'>Назад</a>";

?>