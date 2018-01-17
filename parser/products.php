<?php

include('lib.php');

Db::getInstance()->Execute('
                             CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'old_id` (

                                 `id` int(10) NOT NULL AUTO_INCREMENT,

                                 `new_id` int(10) NOT NULL,
                                 
                                 `old_id` int(10) NOT NULL,

                                 PRIMARY KEY(`id`)

                             ) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8');


$imported_products = Db::getInstance()->executeS('SELECT * FROM ps_old_product');


$imported_ids = '';

foreach ($imported_products as $imported_product) {

    $imported_ids .= $imported_product['old_product_id'].', ';

}

$imported_ids = substr($imported_ids, 0, -2);

$products = $pdo->query('SELECT * FROM `tovar` WHERE `id` NOT IN ('.$imported_ids.') AND true', PDO::FETCH_ASSOC);

foreach ($products as $product) {

    //Getting manufacture_id
    $manufacturers = $pdo->query('SELECT `header` FROM `manufacture` WHERE `id` = '. $product['manufacture_id'] .' AND true', PDO::FETCH_ASSOC);
    foreach ($manufacturers as $manufacturer) {
        $sql = 'SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE name = "'. mysql_escape_string($manufacturer['header']).'"';
        $manufacture_id = Db::getInstance()->getValue($sql);
    }
    // End of getting manufacture_id

    //Getting category_id
    $categories = $pdo->query('SELECT `header` FROM `catalog` WHERE `id` = '. $product['catalog_id'] .' AND true', PDO::FETCH_ASSOC);
    foreach ($categories as $category) {
        $sql = 'SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = "'. mysql_escape_string($category['header']).'"';
        $category_id = Db::getInstance()->getValue($sql);
    }
    // End of getting category_id

    //Getting status of product: new or 

    $new_product = new ProductCore();
    $new_product->id_shop_default = 1;
    $new_product->id_manufacturer = $manufacture_id;
    // $new_product->id_supplier = ;
    $new_product->reference = $product['articul'];
    // $new_product->supplier_reference = ;
    // $new_product->location = ;
    // $new_product->width = ;
    // $new_product->height = ;
    // $new_product->depth = ;
    // $new_product->weight = ;
    // $new_product->quantity_discount = ;
    // $new_product->ean13 = ;
    // $new_product->upc = ;
    // $new_product->cache_is_pack = ;
    // $new_product->cache_has_attachments = ;
    // $new_product->is_virtual = ;
    $new_product->id_category_default = $category_id;
    $new_product->id_tax_rules_group = 0 ; //means none
    // $new_product->on_sale = 0; //like in old site
    // $new_product->online_only = ;
    // $new_product->ecotax = ;
    $new_product->minimal_quantity = 1; // as default
    $new_product->price = $product['price'];
    // $new_product->wholesale_price = ; //price_opt = NULL in old site
    // $new_product->unity = ;
    // $new_product->unit_price_ratio = ;
    // $new_product->additional_shipping_cost = ;
    // $new_product->customizable = ;
    // $new_product->text_fields = ;
    // $new_product->uploadable_files = ; !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! They are exist in `tovar_files`
    // $new_product->active = ;
    $new_product->redirect_type = '404';
    // $new_product->id_product_redirected = ;
    // $new_product->available_for_order = 1; //as default
    // $new_product->available_date = ; 
    // $new_product->condition = 'new'; //!!!!!!!!!!!!
    // $new_product->show_price = 1;  //as default
    $new_product->indexed = 1; //search index
    // $new_product->visibility = 'both';  //as default
    // $new_product->cache_default_attribute = ; 
    // $new_product->advanced_stock_management = ; 
    // $new_product->date_add = ; 
    // $new_product->date_upd = ; 
    // $new_product->pack_stock_type = 3; //as default  !!!!!!!!!!!! Show to Victor line 238 in Product.php 


    $body = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['body']))));

    if ($pos = strpos($body, '<script')) {
        $body = substr($body, 0, $pos);
    }

    $keywords = substr($product['ht_words'], 0, 178);

    foreach (Language::getLanguages(true) as $lang){
        $new_product->name[$lang['id_lang']] = mysql_escape_string($product['header']);
        $new_product->link_rewrite[$lang['id_lang']] = str2url($product['header'], $product['id']);
        $new_product->description[$lang['id_lang']] = $body;
        $new_product->description_short[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['descript']))));
        $new_product->meta_keywords[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($keywords))));
        $new_product->meta_description[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['ht_desc']))));
        $new_product->meta_title[$lang['id_lang']] =stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($product['ht_head']))));
    }

    $new_product->add();

    Db::getInstance()->Execute('INSERT INTO `ps_old_id` (`id`, `new_id`, `old_id`) VALUES (NULL, '.$new_product->id.', '.$product['id'].')');

    // insert associations with categories in ps_category_product table
    Db::getInstance()->insert('category_product', array(
        'id_product'  => $new_product->id,
        'id_category' => $category_id,
    ));


    //images

    // cover

    $images = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$product["picture"].' AND true', PDO::FETCH_ASSOC);

    foreach ($images as $image) {

        $fileTmpLoc = 'img/'.$product["picture"].'.'.$image['type'];

        if (file_exists($fileTmpLoc) == false) {
            $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtoupper($image['type']);
            if (file_exists($fileTmpLoc) == false) {
                $fileTmpLoc = 'img/'.$product["picture"].'.'.mb_strtolower($image['type']);
            }
        }
        
        $sql = 'SELECT MAX(id_image), id_image FROM '._DB_PREFIX_.'image';
        $last_img_id = Db::getInstance()->getValue($sql);

        $new_image = new Image();
        $imagesTypes = ImageType::getImagesTypes('products');


        $new_image->id_product = $new_product->id;
        $new_image->cover = 1;
        $new_image->add();
        $new_path = $new_image->getPathForCreation();
       
        ImageManager::resize($fileTmpLoc, $new_path.'.jpg');

        foreach ($imagesTypes as $k => $image_type) {
            if (!ImageManager::resize($fileTmpLoc, $new_path.'-'.stripslashes($image_type['name']).'.'.$new_image->image_format, $image_type['width'], $image_type['height'], $new_image->image_format)) {
                    
                    echo " | " .$product["id"]." |-- " .$product["header"]. " img_id: ".$product["picture"].".".$type['type']."- COVER FAIL <br>";
            
                    p('cover: '.$fileTmpLoc);
                    var_dump(file_exists($fileTmpLoc));
                    echo "<br>";

            }
        }

    }

    // other images

    $images = $pdo->query('SELECT `pic_id` FROM `tovar_images` WHERE `tovar_id`='.$product["id"].' AND true', PDO::FETCH_ASSOC);

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

            $sql = 'SELECT MAX(id_image), id_image FROM '._DB_PREFIX_.'image';
            $last_img_id = Db::getInstance()->getValue($sql);

            $new_image = new Image();
            $imagesTypes = ImageType::getImagesTypes('products');


            $new_image->id_product = $new_product->id;
            $new_image->cover = 0;
            $new_image->add();
            $new_path = $new_image->getPathForCreation();
             
            ImageManager::resize($fileTmpLoc, $new_path.'.jpg');

            foreach ($imagesTypes as $k => $image_type) {
                if (!ImageManager::resize($fileTmpLoc, $new_path.'-'.stripslashes($image_type['name']).'.'.$new_image->image_format, $image_type['width'], $image_type['height'], $new_image->image_format)) {
                    
                    echo " | " .$product["id"]." |-- " .$product["header"]. " img_id: ".$image["pic_id"].".".$type['type']."- FAIL <br>";
                    
                    p('image: '.$fileTmpLoc);
                    var_dump(file_exists($fileTmpLoc));
                    echo "<br>";

                }
            }
        }

    }
}
echo 'Экспорт Товаров завершён!<br><a href="/parser">Назад</a>';