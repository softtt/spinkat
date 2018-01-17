<?php
    include('lib.php');
        
    $pdo = new PDO(
        'mysql:host=localhost;dbname=presta',
        'presta',
        'presta',
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );

    $news = $pdo->query('SELECT * FROM `news` WHERE true', PDO::FETCH_ASSOC);

    foreach ($news as $news_post) {

        $new_news_post = new SimpleBlogPost();

        // define extension of curren image
        $images_type = $pdo->query('SELECT `type` FROM `db_pics` WHERE `id`='.$news_post["bank"].' AND true', PDO::FETCH_ASSOC);

        foreach ($images_type as $image_type) {
            $new_news_post->cover = $image_type['type'];
        }

        $new_news_post->id_simpleblog_post_type = 1;
        $new_news_post->id_simpleblog_category = 2;
        $new_news_post->active = 1;
        $new_news_post->access = $access;

        $new_news_post->date_add = $news_post['pdate'];

        foreach (Language::getLanguages(true) as $lang){
            $new_news_post->title[$lang['id_lang']] = $news_post['header'];
            $new_news_post->link_rewrite[$lang['id_lang']] = 'read' . $news_post['id']; 
            $new_news_post->short_content[$lang['id_lang']] = substr(trim(stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode(strip_tags($news_post['text'])))))), 0, 96).'...';
            $new_news_post->content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($news_post['text']))));

        }

        $new_news_post->add();


        // insert data in simpleblog_post_shop table
        Db::getInstance()->insert('simpleblog_post_shop', array(
            'id_simpleblog_post' => $new_news_post->id,
            'id_shop'      => 1,
        ));


        // images
        $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_news_post->id.'.'.$new_news_post->cover;
        $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_news_post->id.'-thumb.'.$new_news_post->cover;
        $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_news_post->id.'-wide.'.$new_news_post->cover;

        if (isset($new_news_post->cover)) {
            $fileTmpLoc = 'img/'.$news_post['bank'].'.'.$new_news_post->cover;
            
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

    echo "Экспорт Новостей завершён!<br><a href='/parser'>Назад</a>";

?>