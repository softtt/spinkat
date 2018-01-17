<?php
    include('lib.php');
   
    $parents = $pdo->query('SELECT `id`, `header` FROM `catalog` WHERE `parent` = 0 AND true ORDER BY `parent`', PDO::FETCH_ASSOC);

    foreach ($parents as $parent) {

        $subcategories = $pdo->query('SELECT * FROM `catalog` WHERE `parent` = '.(int)$parent["id"].' AND true ORDER BY `pos`', PDO::FETCH_ASSOC);


        foreach ($subcategories as $subcategory) {

            $sql = 'SELECT `id_category` FROM '._DB_PREFIX_.'category_lang WHERE name = "'. $parent['header'].'"';
            $id_category = Db::getInstance()->getValue($sql);

            $new_subcategory = new Category();

            $new_subcategory->active = $subcategory['visible'];
            $new_subcategory->position = $subcategory['pos'];
            $new_subcategory->id_parent = $id_category;
            $new_subcategory->level_depth = 3;
            $new_subcategory->date_add = date('Y-m-d G:i:s');
            $new_subcategory->groupBox = Array("0" => 1, "1" => 2, "2" => 3);


            foreach (Language::getLanguages(true) as $lang){
                $new_subcategory->name[$lang['id_lang']] = $subcategory['header']; 
                $new_subcategory->link_rewrite[$lang['id_lang']] = str2url($subcategory['header'], $subcategory['id']); 
                $new_subcategory->description[$lang['id_lang']] = $subcategory['description'];
                $new_subcategory->meta_title[$lang['id_lang']] = $subcategory['ht_head'];
                $new_subcategory->meta_description [$lang['id_lang']]= $subcategory['ht_desc'];
            }

            // covers
            $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$subcategory["pic"].' AND true', PDO::FETCH_ASSOC);

            foreach ($images_type as $image_type) {
                $fileTmpLoc = 'img/'.$subcategory['pic'].'.'.$image_type['type'];
            }

            $new_subcategory->add();

            $origPath = _PS_CAT_IMG_DIR_ . $new_subcategory->id.'.jpg';

            $generate_hight_dpi_images = (bool)Configuration::get('PS_HIGHT_DPI');

            if(isset($fileTmpLoc) && isset($subcategory['pic'])) {
                try {
                    $orig = PhpThumbFactory::create($fileTmpLoc);
                } catch (Exception $e) {
                    echo $e;
                }

                $orig->save($origPath);
            }


            if(_PS_CAT_IMG_DIR_.$new_subcategory->id.'.jpg') {
                $images_types = ImageType::getImagesTypes('categories');
                foreach ($images_types as $k => $image_type) {
                    ImageManager::resize(
                        _PS_CAT_IMG_DIR_.$new_subcategory->id.'.jpg',
                        _PS_CAT_IMG_DIR_.$new_subcategory->id.'-'.stripslashes($image_type['name']).'.jpg',
                        (int)$image_type['width'], (int)$image_type['height']
                    );

                    if ($generate_hight_dpi_images) {
                        ImageManager::resize(
                            _PS_CAT_IMG_DIR_.$new_subcategory->id.'.jpg',
                            _PS_CAT_IMG_DIR_.$new_subcategory->id.'-'.stripslashes($image_type['name']).'2x.jpg',
                            (int)$image_type['width']*2, (int)$image_type['height']*2
                        );
                    }
                }
            }
        }
    }

    echo "Экспорт Податегорий завершён!<br><a href='/parser'>Назад</a>";

?>