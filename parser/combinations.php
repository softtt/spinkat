<?php
    include('lib.php');

    Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'old_product` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `new_product_id` int(10) NOT NULL,
            `old_product_id` int(10) NOT NULL,
                PRIMARY KEY(`id`)
        ) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8');

    $replace_text = [
        'Спиннинговое удилище',
        'Спиннинг',
        'Cпиннинг',
        'cпиннинг',
        'Кастинговое удилище',
        'Катушка',
        'катушка',
        "\r",
        "\n"
    ];


    $all_series = Db::getInstance()->executeS('SELECT * FROM ps_old_series');


    foreach ($all_series as $series) {

        p($series);

        // $manufacturer_title = $series['manufacturer'];
        // $sql = 'SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE name = '. $pdo->quote($manufacturer_title);
        // $manufacturer_id = Db::getInstance()->getValue($sql);


        // $products = $pdo->query('SELECT * FROM `tovar` WHERE `coll_id` = '. $series["id"] .' AND true', PDO::FETCH_ASSOC);

        // $sql = 'SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = '. $pdo->quote($series['category']);
        // $category_id = Db::getInstance()->getValue($sql);
        
        // foreach ($products as $product) {

        //     $images = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$product["picture"].' AND true', PDO::FETCH_ASSOC);

        //     foreach ($images as $image) {

        //         $fileTmpLoc = 'img/'.$product["picture"].'.'.$image['type'];

        //         if (file_exists($fileTmpLoc) == false) {
        //             $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtoupper($image['type']);
        //             if (file_exists($fileTmpLoc) == false) {
        //                 $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtolower($image['type']);
        //             }
        //         }
        //     }
        // }

        // End of getting category_id

        // $new_product = new Product();

        // $new_product->id_shop_default = 1;
        // $new_product->id_manufacturer = $manufacturer_id;
        // $new_product->id_category_default = $category_id;
        // $new_product->id_tax_rules_group = 0 ; //means none
        // $new_product->minimal_quantity = 1; // as default
        // $new_product->redirect_type = '404';
        // $new_product->indexed = 1; //search index
        // $new_product->is_series = 1;


        // $body = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['body']))));

        // if ($pos = strpos($body, '<script')) {
        //     $body = substr($body, 0, $pos);
        // }

        // foreach (Language::getLanguages(true) as $lang){
        //     $new_product->name[$lang['id_lang']] = $pdo->prepare($series['header'])->queryString;
        //     $new_product->link_rewrite[$lang['id_lang']] = Tools::link_rewrite($series['header']);
        //     // $new_product->description[$lang['id_lang']] = $body;
        // }

        // $new_product->add();

        // // insert associations with categories in ps_category_product table
        // Db::getInstance()->insert('category_product', array(
        //     'id_product'  => $new_product->id,
        //     'id_category' => $category_id,
        // ));

        // Db::getInstance()->insert('old_series', array(
        //     'old_series_id' => $series['id'],
        //     'new_series_id' => $new_product->id,
        //     'title' => $series['header']
        // ));


        // $sql = 'SELECT MAX(id_image), id_image FROM '._DB_PREFIX_.'image';
        // $last_img_id = Db::getInstance()->getValue($sql);

        // $new_image = new Image();
        // $imagesTypes = ImageType::getImagesTypes('products');

        // $new_image->id_product = $new_product->id;
        // $new_image->cover = 1;
        // $new_image->add();
        // $new_path = $new_image->getPathForCreation();
       
        // ImageManager::resize($fileTmpLoc, $new_path.'.jpg');

        // foreach ($imagesTypes as $k => $image_type) {
        //     if (!ImageManager::resize($fileTmpLoc, $new_path.'-'.stripslashes($image_type['name']).'.'.$new_image->image_format, $image_type['width'], $image_type['height'], $new_image->image_format)) {
                    
        //             echo "COVER FAIL";
   
        //     }
        // }


        // images 


        $series_product = new Product($series['new_series_id']);
        // d($series_product);

        $products = $pdo->query('SELECT t.*, m.header as manufacturer_title FROM `tovar` as t
                                 LEFT JOIN manufacture m ON m.id = t.manufacture_id
                                 WHERE t.`coll_id` = '. $series["old_series_id"] .' AND true', PDO::FETCH_ASSOC);
        
        foreach ($products as $product) {


            $combination_title = $product['header'];
            $combination_title = str_ireplace($product['manufacturer_title'], '', $combination_title);
            $combination_title = str_ireplace($series['title'], '', $combination_title);
            $combination_title = mb_ucfirst(trim(str_ireplace($replace_text, '', $combination_title), ' -"\'®™'), 'utf8');

            if (!$combination_title) {
                $combination_title = $product['header'];
            }

            p($combination_title);


            $id_product_attribute = $series_product->addCombinationEntity(
                0, # $wholesale_price,
                $product['price'], # $price,
                0, # $weight,
                0, # $unit_impact,
                0, # $ecotax,
                0, # $quantity DEPRECATED,
                [], # $id_images,
                $product['articul'], # $reference,
                0, # $id_supplier,
                null, # $ean13,
                0, # $default,
                null, # $location = null,
                null, # $upc = null,
                1, # $minimal_quantity = 1,
                [1], # array $id_shop_list = array(),
                null, # $available_date = null,
                $combination_title # $title = ''
            );
            StockAvailable::setProductDependsOnStock((int)$series_product->id, $series_product->depends_on_stock, null, (int)$id_product_attribute);
            StockAvailable::setProductOutOfStock((int)$series_product->id, $series_product->out_of_stock, null, (int)$id_product_attribute);


            $sql = 'SELECT b.tovar_id, t2.header as attribute_name, t.header as attribute_value
                    FROM `feature_bind` b
                    LEFT JOIN feature_type t on t.id = b.header
                    LEFT JOIN feature_type t2 on t2.id = b.feature_id
                    WHERE `tovar_id` = '.$product['id'].'
                    AND b.`header` < 99

                    UNION SELECT b.tovar_id, t.header as attribute_name, b.header as attribute_value
                    FROM `feature_bind` b
                    LEFT JOIN feature_type as t ON t.id = b.feature_id
                    WHERE `tovar_id` = '.$product['id'].'
                    AND b.`header` >= 99';

            $combination_attributes = $pdo->query($sql, PDO::FETCH_ASSOC);

            $combination_attributes_array = [];

            foreach($combination_attributes as $attribute) {
                $new_attribute = Db::getInstance()->executeS('SELECT id_attribute FROM ps_attribute_lang WHERE name = "'.$attribute['attribute_value'] . '"');
                $combination_attributes_array[] = $new_attribute[0]['id_attribute'];
            }


            $combination = new Combination((int)$id_product_attribute);
            $combination->setAttributes($combination_attributes_array);


            // // images could be deleted before

            // $id_images = Tools::getValue('id_image_attr');
            // if (!empty($id_images)) {
            //     $combination->setImages($id_images);
            // }

            // $series_product->checkDefaultAttributes();
            // Product::updateDefaultAttribute((int)$series_product->id);

            Db::getInstance()->insert('old_product', array(
                'old_product_id' => $product['id'],
                'new_product_id' => $id_product_attribute,
            ));


            // $combination_title = str_ireplace($spinning_rod, "", $product['header']);
            // $combination_title = str_ireplace($spinning, "", $combination_title);
            // $combination_title = str_ireplace($spinning2, "", $combination_title);
            // $combination_title = str_ireplace($casting_rod, "", $combination_title);
            // $combination_title = str_ireplace($coil, "", $combination_title);
            // $combination_title = str_ireplace($coil2, "", $combination_title);
            // $combination_title = str_ireplace($series['header'], "", $combination_title);
            // $combination_title = str_ireplace($manufacturer_title, "", $combination_title);
            // $combination_title = str_ireplace(array("\r","\n"), "", $combination_title);
            // $combination_title = rtrim($combination_title, ' -"\'®™');




            // $images = $pdo->query('SELECT `pic_id` FROM `tovar_images` WHERE `tovar_id`='.$product["id"].' AND true', PDO::FETCH_ASSOC);

            // foreach ($images as $image) {

            //     $types = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$image["pic_id"].' AND true', PDO::FETCH_ASSOC);

            //     foreach ($types as $type) {
        
            //        $fileTmpLoc = 'img/'.$image["pic_id"].'.'.$type['type'];

            //         if (file_exists($fileTmpLoc) == false) {
            //             $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtoupper($type['type']);
            //             if (file_exists($fileTmpLoc) == false) {
            //                 $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtolower($type['type']);
            //             }
            //         }

            //         $sql = 'SELECT MAX(id_image), id_image FROM '._DB_PREFIX_.'image';
            //         $last_img_id = Db::getInstance()->getValue($sql);

            //         $new_image = new Image();
            //         $imagesTypes = ImageType::getImagesTypes('products');


            //         $new_image->id_product = $new_product->id;
            //         $new_image->cover = 0;
            //         $new_image->add();
            //         $new_path = $new_image->getPathForCreation();
                     
            //         ImageManager::resize($fileTmpLoc, $new_path.'.jpg');

            //         foreach ($imagesTypes as $k => $image_type) {
            //             if (!ImageManager::resize($fileTmpLoc, $new_path.'-'.stripslashes($image_type['name']).'.'.$new_image->image_format, $image_type['width'], $image_type['height'], $new_image->image_format)) {
                            
            //                 echo 'FOTO FAIL!';

            //             }
            //         }    
            //     }
            // }

            // $links = $pdo->query('SELECT * FROM `feature_bind` WHERE `header` < 99 AND `header` != 2.29 AND `tovar_id` = '.$product['id'].' AND true', PDO::FETCH_ASSOC);

            // foreach ($links as $link) {

            //     $attr_titles = $pdo->query('SELECT `header` FROM `feature_type` WHERE `id` = "'.$link["header"].'" AND true', PDO::FETCH_ASSOC);

            //     foreach ($attr_titles as $attr_title) {

            //         // getting id of attribute in new db
            //         $sql = 'SELECT id_attribute FROM '._DB_PREFIX_.'attribute_lang WHERE name = "'.$attr_title["header"].'"';
            //         $new_attr_id = Db::getInstance()->getValue($sql);

            //         // getting id of attribute parent in new db
            //         $sql = 'SELECT id_attribute_group FROM '._DB_PREFIX_.'attribute WHERE id_attribute = "'.$new_attr_id.'"';
            //         $new_attr_group_id = Db::getInstance()->getValue($sql);
            //     }

                // if (isset($new_product->id) && $new_product->id !== false) {
                //     Db::getInstance()->insert('product_attribute', array(
                //         'id_product'      => $new_product->id,
                //         'wholesale_price' => 0.000000,
                //         'reference' => $product['articul'],
                //         'price' =>  $product['price'],
                //         'ecotax' =>  0.000000,
                //         'quantity' => 0,
                //         'weight' =>  0.000000,
                //         'unit_price_impact' =>  0.000000,
                //         'minimal_quantity' => 1,
                //         'default_on' =>0,
                //         'title' => $combination_title,
                //     ));

                //     // getting id_product_attribute in new db
                //     $sql = 'SELECT MAX( id_product_attribute),  id_product_attribute FROM '._DB_PREFIX_.'product_attribute WHERE id_product = '.$new_product->id;
                //     $new_id_product_attribute = Db::getInstance()->getValue($sql);


                //     Db::getInstance()->insert('product_attribute_shop', array(
                //         'id_product'      => $new_product->id,
                //         'id_product_attribute' => $new_id_product_attribute,
                //         'wholesale_price' => 0.000000,
                //         'price' =>  $product['price'],
                //         'ecotax' =>  0.000000,
                //         'weight' =>  0.000000,
                //         'unit_price_impact' =>  0.000000,
                //         'default_on' =>0,
                //         'minimal_quantity' => 1,
                //         'id_shop' => 1,
                //     ));


                //     Db::getInstance()->insert('product_attribute_combination', array(
                //         'id_attribute'      => $new_attr_id,
                //         'id_product_attribute' => $new_id_product_attribute,
                //     ));

                // }
            // }

            // $new_product->addAttribute($product['price'], 0, 0, 0, $id_images=null, $product['articul'], null,
            //                      0, $location = null, $upc = null, $minimal_quantity = 1, $id_shop_list = array('0' => 1), $available_date = null, $combination_title);
    
        }

        $series_product->checkDefaultAttributes();
    }

echo 'Экспорт Серий завершён!<br><a href="/parser">Назад</a>';
    
?>