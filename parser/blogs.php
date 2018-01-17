<?php
    include('lib.php');

    $blogs = $pdo->query('SELECT * FROM `posts` WHERE true', PDO::FETCH_ASSOC);

    foreach ($blogs as $blog) {

        $new_blog = new SimpleBlogPost();
        $new_blog->cover = 'jpg';
        $new_blog->allow_comments = 1;
        $new_blog->id_simpleblog_post_type = 1;
        $new_blog->id_simpleblog_category = 4;
        $new_blog->active = 1;
        $new_blog->access = $access;
        $new_blog->date_add = date('Y-m-d G:i:s', $blog['post_date']);

        $tags = $pdo->query('SELECT `tags`.`title` FROM `tags` LEFT JOIN `posts2tags` ON `tags`.`id`=`posts2tags`.`tag_id` WHERE `posts2tags`.`post_id` = '.$blog["id"].'  AND true', PDO::FETCH_ASSOC);

        $new_tags = array();

        foreach ($tags as $tag) {
    		$new_tags[] = $tag['title'];
        }

        $new_tags = implode(', ', $new_tags);

        $experts = $pdo->query('SELECT `header` FROM `experts` WHERE `id` = '. $blog['expert_id'] .' AND true', PDO::FETCH_ASSOC);

        foreach ($experts as $expert) {
            $sql = 'SELECT id_simpleblog_author FROM '._DB_PREFIX_.'simpleblog_author_lang WHERE name = "'. $expert['header'].'"';
            $author_id = Db::getInstance()->getValue($sql);
        }

        $new_blog->id_simpleblog_author = $author_id;

        foreach (Language::getLanguages(true) as $lang){
            $new_blog->title[$lang['id_lang']] = $blog['title'];
            $new_blog->link_rewrite[$lang['id_lang']] = str2url($blog['title'], $blog['id']);  
            $new_blog->short_content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($blog['abstract']))));
            $new_blog->content[$lang['id_lang']] = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($blog['text']))));
        }

        $new_blog->add();

        // add comments
        // $comments = $pdo->query('SELECT * FROM `post_reviews` WHERE `post_id` = 11 AND true', PDO::FETCH_ASSOC);
        $comments = $pdo->query('SELECT * FROM `post_reviews` WHERE `post_id` = '.$blog["id"].' AND true', PDO::FETCH_ASSOC);

        foreach ($comments as $comment) {
            // p($comment);

            $new_comment = new SimpleBlogComment();

            $new_comment->id_simpleblog_post = $new_blog->id;
            $new_comment->id_parent = 0;
            $new_comment->id_customer = 0;
            // $new_comment->id_guest = ; //?????
            $new_comment->name = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($comment['name']))));
            // $new_comment->email = ; //absent in old db
            $new_comment->comment = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($comment['text']))));
            $new_comment->active = $comment['is_published'];
            // $new_comment->ip = ; //localhost???
            $new_comment->date_add = $comment['pdate'];
            // $new_comment->date_upd = ;

            $new_comment->add();

        }

        // add tags 
        foreach (Language::getLanguages(true) as $lang){
       		SimpleBlogTag::addTags($lang['id_lang'], $new_blog->id, $new_tags, $separator = ',');
        }

        // insert data in simpleblog_post_shop table
        Db::getInstance()->insert('simpleblog_post_shop', array(
            'id_simpleblog_post' => $new_blog->id,
            'id_shop'      => 1,
        ));

        // covers
        $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_blog->id.'.jpg';
        $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_blog->id.'-thumb.jpg';
        $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$new_blog->id.'-wide.jpg';

        $fileTmpLoc = _PS_ROOT_DIR_.'/blog_pics/'.$blog['id'].'.jpg';
        
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
    
    echo "Экспорт статей Блога завершён!<br><a href='/parser'>Назад</a>";

?>