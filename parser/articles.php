<?php
    include('lib.php');

    $articles = $pdo->query('SELECT * FROM `articles` WHERE true', PDO::FETCH_ASSOC);

    foreach ($articles as $article) {

        $new_article = new SimpleBlogPost();

        $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$article["brunch"].' AND true', PDO::FETCH_ASSOC);

        foreach ($images_type as $image_type) {
            $new_article->cover = $image_type['type'];
        }

        $new_article->id_simpleblog_post_type = 1;
        $new_article->id_simpleblog_category = 3;
        $new_article->active = $article['visible'];
        $new_article->access = $access;
        $new_article->id_product = $article['tid'];
        $new_article->date_add = $article['pdate'];

        foreach (Language::getLanguages(true) as $lang){
            $new_article->title[$lang['id_lang']] = $article['header'];
            $new_article->link_rewrite[$lang['id_lang']] = str2url($article['header'], $article['id']);  
            $new_article->short_content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($article['daily_block']))));
            $new_article->content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($article['text']))));
        }

        $done = $new_article->add();

        // insert data in simpleblog_post_shop table
        Db::getInstance()->insert('simpleblog_post_shop', array(
            'id_simpleblog_post' => $new_article->id,
            'id_shop'      => 1,
        ));

        // covers
        $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_article->id.'.'.$new_article->cover;
        $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_article->id.'-thumb.'.$new_article->cover;
        $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_article->id.'-wide.'.$new_article->cover;

        if (isset($new_article->cover)) {
            $fileTmpLoc = 'img/'.$article['brunch'].'.'.$new_article->cover;
            
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

    echo "Экспорт Статей завершён!<br><a href='/parser'>Назад</a>";

?>