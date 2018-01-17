<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_1_9($object)
{
    $sections = array(
        array(
            'name' => 'Обзоры',
            'is_gallery' => 0,
            'link_rewrite' => 'surveys',
            'meta_description' => 'Наша компания регулярно проводит обзоры новинок удилищ, спиннингов и катушек поступающих к нам в продажу, а так же продукцию известных и производителей рыболовной оснастки.',
            'meta_title' => 'Обзоры рыболовных снастей, спинингов, катушек, обзоры спиннинговых удилищ',
            'meta_keywords' => 'Обзоры',
            'display_author' => 1,
            'display_tags' => 2,
            'allow_comments' => 2,
            'is_gallery' => 0,
        ),
        array(
            'name' => 'Новости',
            'is_gallery' => 0,
            'link_rewrite' => 'news',
            'meta_description' => 'Новости',
            'meta_title' => 'Новости',
            'meta_keywords' => 'Новости',
            'display_author' => 2,
            'display_tags' => 2,
            'allow_comments' => 2,
            'is_gallery' => 0,
        ),
        array(
            'name' => 'Статьи',
            'is_gallery' => 0,
            'link_rewrite' => 'articles',
            'meta_description' => 'Статьи',
            'meta_title' => 'Статьи',
            'meta_keywords' => 'Статьи',
            'display_author' => 2,
            'display_tags' => 2,
            'allow_comments' => 2,
            'is_gallery' => 0,
        ),
        array(
            'name' => 'Блог',
            'is_gallery' => 0,
            'link_rewrite' => 'blog',
            'meta_description' => 'Блог',
            'meta_title' => 'Блог',
            'meta_keywords' => 'Блог',
            'display_author' => 1,
            'display_tags' => 1,
            'allow_comments' => 1,
            'is_gallery' => 0,
        ),
        array(
            'name' => 'Наши трофеи',
            'is_gallery' => 1,
            'link_rewrite' => 'booty',
            'meta_description' => 'Наши трофеи',
            'meta_title' => 'Наши трофеи',
            'meta_keywords' => 'Наши трофеи',
            'display_author' => 2,
            'display_tags' => 2,
            'allow_comments' => 1,
            'is_gallery' => 1,
        ),
    );

    // Default categories
    foreach($sections as $section)
    {
        $simple_blog_category = new SimpleBlogCategory();

        foreach($section as $key => $value)
        {
            if ($key == 'name')
            {
                foreach (Language::getLanguages(true) as $lang)
                    $simple_blog_category->name[$lang['id_lang']] = $value;
            }
            elseif ($key == 'link_rewrite')
            {
                foreach (Language::getLanguages(true) as $lang)
                    $simple_blog_category->link_rewrite[$lang['id_lang']] = $value;
            }
            else
            {
                $simple_blog_category->{$key} = $value;
            }

        }
        $simple_blog_category->add();
        $simple_blog_category->associateTo(Shop::getCompleteListOfShopsID());
    }

    return true;
}
