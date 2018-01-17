<?php
    include('lib.php');

    $experts = $pdo->query('SELECT `id`, `header`, `body`, `brunch`, `mini` FROM `experts` WHERE true', PDO::FETCH_ASSOC);

    foreach ($experts as $expert) {

        $new_expert = new SimpleBlogAuthor();

        $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$expert["brunch"].' AND true', PDO::FETCH_ASSOC);

        foreach ($images_type as $image_type) {
            $new_expert->cover = $image_type['type'];
        }

        $new_expert->active = 1;

        foreach (Language::getLanguages(true) as $lang){
            $new_expert->name[$lang['id_lang']] = $expert['header'];
            $new_expert->description[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($expert['body']))));
            $new_expert->description_short[$lang['id_lang']] = $expert['mini'];
            $new_expert->link_rewrite[$lang['id_lang']] = str2url($expert['header'], $expert['id']); 
        }

        $new_expert->meta_title = '';
        $new_expert->meta_description = '';
        $new_expert->meta_keywords = '';
        $done = $new_expert->add();

        // covers

        $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$new_expert->id.'.'.$new_expert->cover;
        $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$new_expert->id.'-thumb.'.$new_expert->cover;
        $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$new_expert->id.'-wide.'.$new_expert->cover;

        if (isset($new_expert->cover)) {
            $fileTmpLoc = 'img/'.$expert['brunch'].'.'.$new_expert->cover;
            
            try {
                $orig = PhpThumbFactory::create($fileTmpLoc);
                $thumb = PhpThumbFactory::create($fileTmpLoc);
                $thumbWide = PhpThumbFactory::create($fileTmpLoc);
            } catch (Exception $e) {
                echo $e;
            }

            if ($thumbMethod == '1') {
                $thumb->adaptiveResize($thumbX,$thumbY);
                $thumbWide->adaptiveResize($thumb_wide_X,$thumb_wide_Y);
            } elseif ($thumbMethod == '2') {
                $thumb->cropFromCenter($thumbX,$thumbY);
                $thumbWide->cropFromCenter($thumb_wide_X,$thumb_wide_Y);
            }

            $orig->save($origPath);
            $thumb->save($pathAndName);
            $thumbWide->save($pathAndNameWide);
        }
        
    }

    echo "Экспорт Экспертов завершён!<br><a href='/parser'>Назад</a>";

?>