<?php

  include('lib.php');

  $manufacturers = $pdo->query('SELECT * FROM `manufacture` WHERE true', PDO::FETCH_ASSOC);

  foreach ($manufacturers as $manufacturer) {

  	$new_manufacturer = new ManufacturerCore();

    $new_manufacturer->name = $manufacturer['header'];
    $new_manufacturer->date_add = date('Y-m-d G:i:s');
    $new_manufacturer->date_upd = date('Y-m-d G:i:s');
	  $new_manufacturer->active = $manufacturer['visible'];	
    $new_manufacturer->link_rewrite = str2url($manufacturer['header'], $manufacturer['id']);  

    foreach (Language::getLanguages(true) as $lang){

      $new_manufacturer->short_description[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode(strip_tags($manufacturer['mini'])))));
      $new_manufacturer->description[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($manufacturer['body']))));
      $new_manufacturer->meta_title = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode(strip_tags($manufacturer['ht_head'])))));
      $new_manufacturer->meta_description = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode(strip_tags($manufacturer['ht_desc'])))));
    }

    $new_manufacturer->add();

    // // insert data in manufacturer_shop table
    Db::getInstance()->insert('manufacturer_shop', array(
        'id_manufacturer' => $new_manufacturer->id,
        'id_shop'      => 1,
    ));

    //images

    $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$manufacturer["brunch"].' AND true', PDO::FETCH_ASSOC);

    foreach ($images_type as $image_type) {
        $fileTmpLoc = 'img/'.$manufacturer['brunch'].'.'.$image_type['type'];
    }


    $origPath = _PS_MANU_IMG_DIR_ . $new_manufacturer->id.'.jpg';

    $generate_hight_dpi_images = (bool)Configuration::get('PS_HIGHT_DPI');

    if(isset($fileTmpLoc) && isset($manufacturer['brunch'])) {
        
      try {
        $orig = PhpThumbFactory::create($fileTmpLoc);
      } catch (Exception $e) {
        echo $e;
      }

      $orig->save($origPath);
    }

    if(_PS_MANU_IMG_DIR_.$new_manufacturer->id.'.jpg') {
       
      $images_types = ImageType::getImagesTypes('manufacturers');
     
      foreach ($images_types as $k => $image_type) {
        ImageManager::resize(
          _PS_MANU_IMG_DIR_.$new_manufacturer->id.'.jpg',
          _PS_MANU_IMG_DIR_.$new_manufacturer->id.'-'.stripslashes($image_type['name']).'.jpg',
          (int)$image_type['width'], (int)$image_type['height']
        );

        if ($generate_hight_dpi_images) {
          ImageManager::resize(
            _PS_MANU_IMG_DIR_.$new_manufacturer->id.'.jpg',
            _PS_MANU_IMG_DIR_.$new_manufacturer->id.'-'.stripslashes($image_type['name']).'2x.jpg',
            (int)$image_type['width']*2, (int)$image_type['height']*2
          );
        }
      }
    }

  }

  echo "Экспорт Производителей завершён!<br><a href='http://spinkat.local/parser/'>Назад</a>";
?>