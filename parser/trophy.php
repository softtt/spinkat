<?php
    
    include('lib.php');

    $image_dir = _SIMPLEBLOG_GALLERY_DIR_;

    $trophy_s = $pdo->query('SELECT * FROM `fotobank` WHERE `parent` = 0 AND true', PDO::FETCH_ASSOC);

    foreach ($trophy_s as $trophy) {

        $new_trophy = new SimpleBlogPost();

        $new_trophy->id_simpleblog_post_type = 2;
        $new_trophy->id_simpleblog_category = 5;
        $new_trophy->active = 1;
        $new_trophy->access = 'a:3:{i:1;b:1;i:2;b:1;i:3;b:1;}';
        $new_trophy->id_product = $trophy['tovar_id'];
        $new_trophy->date_add = date('Y-m-d G:i:s');

        foreach (Language::getLanguages(true) as $lang){
            $new_trophy->title[$lang['id_lang']] = $trophy['header'];
            $new_trophy->link_rewrite[$lang['id_lang']] = str2url($trophy['header'], $trophy['id']);  
            $new_trophy->short_content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($trophy['descript']))));
            $new_trophy->content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($trophy['descript']))));
        }

        $trophy_pics = $pdo->query('SELECT * FROM `fotobank` LEFT JOIN `db_pics` ON (`fotobank`.`pic_id` = `db_pics`.`id`) WHERE `fotobank`.`parent` = '.$trophy["id"].' AND true', PDO::FETCH_ASSOC);

        foreach ($trophy_pics as $picture) {

            if ($picture['fmain'] == 1) {
                $new_trophy->cover = $picture['type'];

                $new_trophy->add();

                // images
                $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_trophy->id.'.'.$new_trophy->cover;
                $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_trophy->id.'-thumb.'.$new_trophy->cover;
                $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_trophy->id.'-wide.'.$new_trophy->cover;       

                if (isset($new_trophy->cover)) {
                    $fileTmpLoc = 'img/'.$picture['id'].'.'.$new_trophy->cover;
                    
                    try {
                        $orig = PhpThumbFactory::create($fileTmpLoc);
                        $thumb = PhpThumbFactory::create($fileTmpLoc);
                        $thumbWide = PhpThumbFactory::create($fileTmpLoc);
                    }

                    catch (Exception $e) {
                        echo $e;
                    }

                    if ($thumbMethod == '1') {
                        $thumb->adaptiveResize($thumbX,$thumbY);
                        $thumbWide->adaptiveResize($thumb_wide_X,$thumb_wide_Y);
                    }
                    elseif ($thumbMethod == '2') {
                        $thumb->cropFromCenter($thumbX,$thumbY);
                        $thumbWide->cropFromCenter($thumb_wide_X,$thumb_wide_Y);
                    }

                    $orig->save($origPath);
                    $thumb->save($pathAndName);
                    $thumbWide->save($pathAndNameWide);
                }

            }
                
            if (isset($new_trophy->id)) {

                // insert data in simpleblog_post_shop table
                Db::getInstance()->insert('simpleblog_post_shop', array(
                    'id_simpleblog_post' => $new_trophy->id,
                    'id_shop'      => 1,
                ));

                $galleries = $pdo->query('SELECT `pic_id`, `header` FROM `fotobank` WHERE `parent` = '.$trophy["id"].' AND true', PDO::FETCH_ASSOC);

                foreach ($galleries as $gallery) {

                    $new_image = new SimpleBlogPostImage();
                    $new_image->id_simpleblog_post = $new_trophy->id;
                    $new_image->position = SimpleBlogPostImage::getNewLastPosition($new_trophy->id);
                    $new_image->title = $gallery['header'];
                    $new_image->add();
                    $new_image->image = $new_image->id . '-' . $new_image->id_simpleblog_post;
                    $new_image->update();

                    $fileTmpLoc = 'img/'.$gallery['pic_id'].'.'.$new_trophy->cover;

                    $destFiles = array(
                        'original'  => $image_dir . $new_image->id . '-' . $new_image->id_simpleblog_post . '.jpg',
                        'thumbnail' => $image_dir . $new_image->id . '-' . $new_image->id_simpleblog_post . '-thumb.jpg',
                        'square'    => $image_dir . $new_image->id . '-' . $new_image->id_simpleblog_post . '-square.jpg',
                        'wide'      => $image_dir . $new_image->id . '-' . $new_image->id_simpleblog_post . '-wide.jpg',
                    );

                    try
                    {
                        $orig = PhpThumbFactory::create($fileTmpLoc);
                        $thumb = PhpThumbFactory::create($fileTmpLoc);
                        $square = PhpThumbFactory::create($fileTmpLoc);
                        $wide = PhpThumbFactory::create($fileTmpLoc);
                    }
                    catch (Exception $e)
                    {
                        echo $e;
                    }

                    if ($thumbMethod == '1')
                    {
                        $thumb->adaptiveResize($thumbX,$thumbY);
                        $square->adaptiveResize(800,800);
                        $wide->adaptiveResize($thumb_wide_X,$thumb_wide_Y);
                    }
                    elseif ($thumbMethod == '2')
                    {
                        $thumb->cropFromCenter($thumbX,$thumbY);
                        $square->cropFromCenter(800,800);
                        $wide->cropFromCenter($thumb_wide_X,$thumb_wide_Y);
                    }

                    $orig->save($destFiles['original']);
                    $thumb->save($destFiles['thumbnail']);
                    $square->save($destFiles['square']);
                    $wide->save($destFiles['wide']);
                }
            }   

        }
    }
    
    echo "Экспорт Трофеев завершён!<br><a href='/parser'>Назад</a>";
?>