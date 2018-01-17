<?php

class Meta extends MetaCore
{
    /* Add Series to $exclude_pages */
    public static function getPages($exclude_filled = false, $add_page = false)
    {
        $selected_pages = array();
        if (!$files = Tools::scandir(_PS_CORE_DIR_ . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR, 'php', '', true)) {
            die(Tools::displayError('Cannot scan "root" directory'));
        }

        if (!$override_files = Tools::scandir(_PS_CORE_DIR_ . DIRECTORY_SEPARATOR . 'override' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR, 'php', '', true)) {
            die(Tools::displayError('Cannot scan "override" directory'));
        }

        $files = array_values(array_unique(array_merge($files, $override_files)));

        // Exclude pages forbidden
        $exlude_pages = array(
            'category', 'changecurrency', 'cms', 'footer', 'header',
            'pagination', 'product', 'product-sort', 'statistics', 'series'
        );

        foreach ($files as $file) {
            if ($file != 'index.php' && !in_array(strtolower(str_replace('Controller.php', '', $file)), $exlude_pages)) {
                $class_name = str_replace('.php', '', $file);
                $reflection = class_exists($class_name) ? new ReflectionClass(str_replace('.php', '', $file)) : false;
                $properties = $reflection ? $reflection->getDefaultProperties() : array();
                if (isset($properties['php_self'])) {
                    $selected_pages[$properties['php_self']] = $properties['php_self'];
                } elseif (preg_match('/^[a-z0-9_.-]*\.php$/i', $file)) {
                    $selected_pages[strtolower(str_replace('Controller.php', '', $file))] = strtolower(str_replace('Controller.php', '', $file));
                } elseif (preg_match('/^([a-z0-9_.-]*\/)?[a-z0-9_.-]*\.php$/i', $file)) {
                    $selected_pages[strtolower(sprintf(Tools::displayError('%2$s (in %1$s)'), dirname($file), str_replace('Controller.php', '', basename($file))))] = strtolower(str_replace('Controller.php', '', basename($file)));
                }
            }
        }

        // Add modules controllers to list (this function is cool !)
        foreach (glob(_PS_MODULE_DIR_ . '*/controllers/front/*.php') as $file) {
            $filename = Tools::strtolower(basename($file, '.php'));
            if ($filename == 'index') {
                continue;
            }

            $module = Tools::strtolower(basename(dirname(dirname(dirname($file)))));
            $selected_pages[$module . ' - ' . $filename] = 'module-' . $module . '-' . $filename;
        }

        // Exclude page already filled
        if ($exclude_filled) {
            $metas = Meta::getMetas();
            foreach ($metas as $meta) {
                if (in_array($meta['page'], $selected_pages)) {
                    unset($selected_pages[array_search($meta['page'], $selected_pages)]);
                }
            }
        }
        // Add selected page
        if ($add_page) {
            $name = $add_page;
            if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9]+)$#i', $add_page, $m)) {
                $add_page = $m[1] . ' - ' . $m[2];
            }
            $selected_pages[$add_page] = $name;
            asort($selected_pages);
        }
        return $selected_pages;
    }

    public static function getMetaTags($id_lang, $page_name, $title = '')
    {
        global $maintenance;

        if (!(isset($maintenance) && (!in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')))))) {
            switch ($page_name) {
                case 'series':
                    if ($id_series = Tools::getValue('id_series')) {
                        return Meta::getSeriesMetas($id_series, $id_lang);
                    }
                    break;
                case 'tags':
                    if ($id_tag = Tools::getValue('id_tag')) {
                        return Meta::getTagMetas($id_tag, $id_lang);
                    }
                    break;

                case 'module-ph_simpleblog-authorslist':
                    return Meta::getBlogAuthorsMetas();
                case 'module-ph_simpleblog-authorsingle':
                    return Meta::getBlogAuthorPageMetas($id_lang);
                case 'module-ph_simpleblog-category':
                    return Meta::getBlogCategoryMetas($id_lang);
                case 'module-ph_simpleblog-single':
                    return Meta::getBlogArticleMetas($id_lang);
            }

        }

        return parent::getMetaTags($id_lang, $page_name, $title);
    }

    private static function getBlogAuthorsMetas()
    {
        $row = [
            'meta_title' => Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_LIST_TITLE'),
            'meta_description' => Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_LIST_DESCRIPTION'),
        ];

        return Meta::completeMetaTags($row, $row['meta_title']);
    }

    private static function getBlogAuthorPageMetas($id_lang)
    {
        $row = [
            'meta_title' => '',
            'meta_description' => '',
        ];

        if ($simpleblog_author_rewrite = Tools::getValue('rewrite', 0)) {
            $simpleBlogAuthor = SimpleBlogAuthor::getByRewrite($simpleblog_author_rewrite, $id_lang);

            $title_template = Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_TITLE');
            $description_template = Configuration::get('SEO_TEMPLATE_BLOG_AUTHORS_SINGLE_DESCRIPTION');

            $row['meta_title'] = $simpleBlogAuthor->meta_title;
            $row['meta_description'] = $simpleBlogAuthor->meta_description;

            if (empty($simpleBlogAuthor->meta_title) && $title_template) {
                $row['meta_title'] = str_replace('%AUTHOR_NAME%', $simpleBlogAuthor->name, $title_template);

                $page = Tools::getValue('p', 0);

                if ($page > 1) {
                    $row['meta_title'] .= ' (' . $page . ')';
                }
            }

            if (empty($simpleBlogAuthor->meta_description) && $description_template) {
                $row['meta_description'] = str_replace('%AUTHOR_NAME%', $simpleBlogAuthor->name, $description_template);
            }
        }

        return Meta::completeMetaTags($row, $row['meta_title']);
    }

    private static function getBlogCategoryMetas($id_lang)
    {
        $row = [
            'meta_title' => '',
            'meta_description' => '',
        ];

        $sb_category = Tools::getValue('sb_category');
        $category_link = Tools::getValue('category_link', null);

        if ($category_link)
            $sb_category = $category_link;

        $simple_blog_category = SimpleBlogCategory::getByRewrite($sb_category, $id_lang);

        $title_template = Configuration::get('SEO_TEMPLATE_BLOG_CATEGORY_LIST_TITLE');
        $description_template = Configuration::get('SEO_TEMPLATE_BLOG_CATEGORY_LIST_DESCRIPTION');

        $row['meta_title'] = $simple_blog_category->meta_title;
        $row['meta_description'] = $simple_blog_category->meta_description;

        if (empty($simple_blog_category->meta_title) && $title_template) {
            $row['meta_title'] = str_replace('%BLOG_CATEGORY%', $simple_blog_category->name, $title_template);

            $page = Tools::getValue('p', 0);

            if ($page > 1) {
                $row['meta_title'] .= ' (страница - ' . $page . ')';
            }
        }

        if (empty($simple_blog_category->meta_description) && $description_template) {
            $row['meta_description'] = str_replace('%BLOG_CATEGORY%', $simple_blog_category->name, $description_template);
        }

        return Meta::completeMetaTags($row, $row['meta_title']);
    }

    private static function getTagMetas($id_tag, $id_lang)
    {
        $tag = new Tag($id_tag);

        $row = [
            'meta_title' => $tag->meta_title .' - '.Configuration::get('PS_SHOP_NAME'),
            'meta_description' => $tag->meta_description,
        ];

        // $title_template = Configuration::get('SEO_TEMPLATE_TAG_TITLE');
        // $description_template = Configuration::get('SEO_TEMPLATE_TAG_DESCRIPTION');

        // if (empty($tag->meta_title) && $title_template) {
        //     $row['meta_title'] = str_replace(['%ARTICLE_TITLE%', '%BLOG_CATEGORY%'], [$tag->title, $tag->name], $title_template);
        // }

        // if (empty($tag->meta_description) && $description_template) {
        //     $row['meta_description'] = str_replace(['%ARTICLE_TITLE%', '%BLOG_CATEGORY%'],[$tag->title, $tag->name], $description_template);
        // }

        $result = Meta::completeMetaTags($row, $row['meta_title']);

        return $result;
    }

    private static function getBlogArticleMetas($id_lang)
    {
        $row = [
            'meta_title' => '',
            'meta_description' => '',
        ];

        $simpleblog_post_rewrite = Tools::getValue('rewrite', 0);
        $simple_blog_post = SimpleBlogPost::getByRewrite($simpleblog_post_rewrite, $id_lang);

        $category_name = $simple_blog_post->category;

        $title_template = Configuration::get('SEO_TEMPLATE_BLOG_ARTICLE_TITLE');
        $description_template = Configuration::get('SEO_TEMPLATE_BLOG_ARTICLE_DESCRIPTION');

        $row['meta_title'] = $simple_blog_post->meta_title;
        $row['meta_description'] = $simple_blog_post->meta_description;

        if (empty($simple_blog_post->meta_title) && $title_template) {
            $row['meta_title'] = str_replace(['%ARTICLE_TITLE%', '%BLOG_CATEGORY%'], [$simple_blog_post->title, $category_name], $title_template);
        }

        if (empty($simple_blog_post->meta_description) && $description_template) {
            $row['meta_description'] = str_replace(['%ARTICLE_TITLE%', '%BLOG_CATEGORY%'], [$simple_blog_post->title, $category_name], $description_template);
        }

        return Meta::completeMetaTags($row, $row['meta_title']);
    }

    public static function getCategoryMetas($id_category, $id_lang, $page_name, $title = '')
    {
        if (!empty($title)) {
            $title = ' - '.$title;
        }

        $page_number = (int)Tools::getValue('p');
        $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description`
				FROM `'._DB_PREFIX_.'category_lang` cl
				WHERE cl.`id_lang` = '.(int)$id_lang.'
					AND cl.`id_category` = '.(int)$id_category.Shop::addSqlRestrictionOnLang('cl');

        $cache_id = 'Meta::getCategoryMetas'.(int)$id_category.'-'.(int)$id_lang;
        if (!Cache::isStored($cache_id)) {
            if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {

                // Paginate title
                $tmp_meta_title = $row['name'] . (!empty($page_number) ? ' (' . $page_number . ')' : '');

                $title_template = Configuration::get('SEO_TEMPLATE_CATALOG_CATEGORY_TITLE');
                if (empty($row['meta_title']) && $title_template) {
                    $row['meta_title'] = str_replace('%CATEGORY_TITLE%', $tmp_meta_title, $title_template);
                }

                if (!empty($title)) {
                    $row['meta_title'] = $title;
                }

                $description_template = Configuration::get('SEO_TEMPLATE_CATALOG_CATEGORY_DESCRIPTION');

                if (empty($row['meta_description']) && $description_template) {
                    $row['meta_description'] = str_replace('%CATEGORY_TITLE%', $tmp_meta_title, $description_template);
                }

                $page_number_text = (!empty($page_number) && $page_number > 1 ? ' (страница - '.$page_number.')' : '');
                $row['meta_title'] .= $page_number_text;
                $row['meta_description'] .= $page_number_text;

                $result = Meta::completeMetaTags($row, $row['name']);
            } else {
                $result = Meta::getHomeMetas($id_lang, $page_name);
            }
            Cache::store($cache_id, $result);
            return $result;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getSeriesMetas($id_series, $id_lang)
    {
        $series = new Series($id_series, true, $id_lang);

        $row = [
            'meta_title' => $series->meta_title,
            'meta_description' => $series->meta_description,
        ];

        $category = new Category($series->id_category_default, $id_lang);
        $title_template = Configuration::get('SEO_TEMPLATE_CATALOG_SERIES_TITLE');
        $description_template = Configuration::get('SEO_TEMPLATE_CATALOG_SERIES_DESCRIPTION');

        if (empty($series->meta_title) && $title_template) {
            $row['meta_title'] = str_replace(['%SERIES_TITLE%', '%CATEGORY_TITLE%', '%MANUFACTURER_TITLE%'], [$series->name, $category->name, $series->manufacturer_name], $title_template);
        }

        if (empty($series->meta_description) && $description_template) {
            $row['meta_description'] = str_replace(['%SERIES_TITLE%', '%CATEGORY_TITLE%', '%MANUFACTURER_TITLE%'], [$series->name, $category->name, $series->manufacturer_name], $description_template);
        }

        $page_number = (int)Tools::getValue('p');
        $page_number_text = (!empty($page_number) && $page_number > 1 ? ' (страница - '.$page_number.')' : '');
        $row['meta_title'] .= $page_number_text;
        $row['meta_description'] .= $page_number_text;

        $result = Meta::completeMetaTags($row, $row['meta_title']);

        return $result;
    }

    public static function getProductMetas($id_product, $id_lang, $page_name)
    {
        $product = new Product($id_product, true, $id_lang);

        $row = [
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
        ];

        $category = new Category($product->id_category_default, $id_lang);

        $title_template = Configuration::get('SEO_TEMPLATE_CATALOG_PRODUCT_TITLE');
        $description_template = Configuration::get('SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION');

        if (empty($product->meta_title) && $title_template) {
            $row['meta_title'] = str_replace(['%PRODUCT_TITLE%', '%CATEGORY_TITLE%', '%MANUFACTURER_TITLE%'], [$product->name, $category->name, $product->manufacturer_name], $title_template);
        }

        if (empty($product->meta_description) && $description_template) {
            $row['meta_description'] = str_replace(['%PRODUCT_TITLE%', '%CATEGORY_TITLE%', '%MANUFACTURER_TITLE%'], [$product->name, $category->name, $product->manufacturer_name], $description_template);
        }

        return Meta::completeMetaTags($row, $row['meta_title']);
    }

    /**
     * Get manufacturer meta tags
     *
     * @since 1.5.0
     * @param int $id_manufacturer
     * @param int $id_lang
     * @param string $page_name
     * @return array
     */
    public static function getManufacturerMetas($id_manufacturer, $id_lang, $page_name)
    {
        $page_number = (int)Tools::getValue('p');
        $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`
                FROM `'._DB_PREFIX_.'manufacturer_lang` ml
                LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (ml.`id_manufacturer` = m.`id_manufacturer`)
                WHERE ml.id_lang = '.(int)$id_lang.'
                    AND ml.id_manufacturer = '.(int)$id_manufacturer;
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            if (!empty($row['meta_description'])) {
                $row['meta_description'] = strip_tags($row['meta_description']);
            }

            $page_number_text = (!empty($page_number) && $page_number > 1 ? ' (страница - '.$page_number.')' : '');

            $row['meta_title'] = ($row['meta_title'] ? $row['meta_title'] : $row['name']).$page_number_text;
            $row['meta_title'] .= ' - '.Configuration::get('PS_SHOP_NAME');
            $row['meta_description'] .= $page_number_text;
            return Meta::completeMetaTags($row, $row['meta_title']);
        }

        return Meta::getHomeMetas($id_lang, $page_name);
    }
}
