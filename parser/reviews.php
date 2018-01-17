<?php
    include('lib.php');

    $image_dir = _SIMPLEBLOG_GALLERY_DIR_;

    $reviews = $pdo->query('SELECT * FROM `survey` WHERE true', PDO::FETCH_ASSOC);

    foreach ($reviews as $review) {

        $experts = $pdo->query('SELECT `header` FROM `experts` WHERE `id` = '. $review['exp_id'] .' AND true', PDO::FETCH_ASSOC);

        foreach ($experts as $expert) {
            $sql = 'SELECT id_simpleblog_author FROM '._DB_PREFIX_.'simpleblog_author_lang WHERE name = "'. $expert['header'].'"';
            $author_id = Db::getInstance()->getValue($sql);
        }


        $new_review = new SimpleBlogPost();

        $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$review["brunch"].' AND true', PDO::FETCH_ASSOC);

        foreach ($images_type as $image_type) {
            $new_review->cover = $image_type['type'];
        }

        $new_review->id_simpleblog_post_type = 1;
        $new_review->id_simpleblog_category = 1;
        $new_review->active = $review['visible'];
        $new_review->access = 'a:3:{i:1;b:1;i:2;b:1;i:3;b:1;}';
        $new_review->author = $author_id;


        $new_review->id_product = $review['tid'];
        $new_review->date_add = $review['pdate'];

        foreach (Language::getLanguages(true) as $lang){
            $new_review->title[$lang['id_lang']] = $review['header'];
            $new_review->link_rewrite[$lang['id_lang']] = str2url($review['header'], $review['id']);  
            $new_review->short_content[$lang['id_lang']] = substr(trim(stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode(strip_tags($review['text'])))))), 0, 96).'...';
            $new_review->content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($review['text']))));
        }

        $done = $new_review->add();

        // insert data in simpleblog_post_shop table
        Db::getInstance()->insert('simpleblog_post_shop', array(
            'id_simpleblog_post' => $new_review->id,
            'id_shop'      => 1,
        ));

        // covers
        $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_review->id.'.'.$new_review->cover;
        $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_review->id.'-thumb.'.$new_review->cover;
        $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_review->id.'-wide.'.$new_review->cover;

        if (isset($new_review->cover)) {
            $fileTmpLoc = 'img/'.$review['brunch'].'.'.$new_review->cover;
            
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

    echo "Экспорт Обзоров завершён!<br><a href='/parser'>Назад</a>";
?>