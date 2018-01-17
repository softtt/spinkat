<?php

class SimpleBlogAuthor extends ObjectModel
{
    public $id;
    public $id_simpleblog_author;
    public $name;
    public $cover;
    public $active = 1;
    public $description;
    public $description_short;
    public $link_rewrite;
    public $meta_title;
    public $meta_keywords;
    public $meta_description;

    public $url;
    public $image;
    public $image_thumb = '';

    public static $definition = array(
        'table' => 'simpleblog_author',
        'primary' => 'id_simpleblog_author',
        'multilang' => true,
        'fields' => array(
            'cover'  =>             array('type' => self::TYPE_STRING),
            'active' =>             array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),

            // Lang fields
            'name' =>               array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 255),
            'link_rewrite' =>       array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 255),
            'description' =>        array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'description_short' =>  array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'meta_title' =>         array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
            'meta_description' =>   array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_keywords' =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
        ),
    );

    public function __construct($id_simpleblog_author = null, $id_lang = null)
    {
        parent::__construct($id_simpleblog_author, $id_lang);

        $this->url = self::getLink($this->link_rewrite, $id_lang);

        if (file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/' .$this->id_simpleblog_author. '.'.$this->cover)) {
            $this->image = _MODULE_DIR_ . 'ph_simpleblog/covers_authors/' .$this->id_simpleblog_author. '.'.$this->cover;
            $this->image_thumb = _MODULE_DIR_ . 'ph_simpleblog/covers_authors/' .$this->id_simpleblog_author. '-thumb.'.$this->cover;
        }
    }

    public static function getAuthors($id_lang, $active = true, $orderby = false, $orderway = false, $exclude = null)
    {
        $context = Context::getContext();

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('simpleblog_author', 'sbp');
        if ($id_lang)
            $sql->innerJoin('simpleblog_author_lang', 'l', 'sbp.id_simpleblog_author = l.id_simpleblog_author AND l.id_lang = '.(int)$id_lang);

        if ($active)
            $sql->where('sbp.active = 1');

        if ($exclude)
        {
            $sql->where('sbp.id_simpleblog_author != '.(int)$exclude);
        }

        if (!$orderby)
            $orderby = 'l.name';

        if (!$orderway)
            $orderway = 'DESC';

        $sql->orderBy($orderby.' '.$orderway);

        $result = Db::getInstance()->executeS($sql);

        if (sizeof($result))
        {
            foreach ($result as &$row)
            {
                if (file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$row['id_simpleblog_author'].'.'.$row['cover']))
                {
                    $row['banner'] = _MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$row['id_simpleblog_author'].'.'.$row['cover'];
                    $row['banner_thumb'] = _MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$row['id_simpleblog_author'].'-thumb.'.$row['cover'];
                    $row['banner_wide'] = _MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$row['id_simpleblog_author'].'-wide.'.$row['cover'];
                }

                $row['url'] = self::getLink($row['link_rewrite'], $id_lang);
                $row['id'] = $row['id_simpleblog_author'];
            }
        }
        else
        {
            return;
        }

        return $result;
    }

    public static function getLink($rewrite)
    {
        return Context::getContext()->link->getModuleLink('ph_simpleblog', 'authorsingle', array('rewrite' => $rewrite));
    }

    public static function getByRewrite($rewrite = null, $id_lang)
    {
        if (!$rewrite) return;

        $sql = new DbQuery();
        $sql->select('l.id_simpleblog_author');
        $sql->from('simpleblog_author_lang', 'l');

        if ($id_lang)
            $sql->where('l.link_rewrite = \''.$rewrite.'\' AND l.id_lang = '.(int)$id_lang);
        else
            $sql->where('l.link_rewrite = \''.$rewrite.'\'');

        $result = Db::getInstance()->getValue($sql);

        if (!$result)
        {
            $sql = new DbQuery();
            $sql->select('l.id_simpleblog_author');
            $sql->from('simpleblog_author_lang', 'l');
            $sql->where('l.link_rewrite = \''.$rewrite.'\'');
            $searched_author = Db::getInstance()->getValue($sql);

            if ($searched_author)
            {
                $sql = new DbQuery();
                $sql->select('l.link_rewrite');
                $sql->from('simpleblog_author_lang', 'l');
                $sql->where('l.id_lang = '.(int)$id_lang.' AND l.id_simpleblog_author = '.(int)$searched_author);
                $rewrite = Db::getInstance()->getValue($sql);
            }

            if ($rewrite)
            {
                $sql = new DbQuery();
                $sql->select('l.id_simpleblog_author');
                $sql->from('simpleblog_author_lang', 'l');

                if ($id_lang)
                    $sql->where('l.link_rewrite = \''.$rewrite.'\' AND l.id_lang = '.(int)$id_lang);
                else
                    $sql->where('l.link_rewrite = \''.$rewrite.'\'');

                $author = new SimpleBlogAuthor(Db::getInstance()->getValue($sql), $id_lang);
                return $author;
            }
            else
                return '404';
        }
        else
        {
            $author = new SimpleBlogAuthor(Db::getInstance()->getValue($sql), $id_lang);
            return $author;
        }
    }

    public static function deleteCover($object)
    {
        $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_authors_'.$object->id.'.'.$object->cover;
        if (file_exists($tmp_location))
            @unlink($tmp_location);

        $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'.'.$object->cover;
        $thumb = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'-thumb.'.$object->cover;
        $thumbWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_authors/'.$object->id.'-wide.'.$object->cover;

        if (file_exists($orig_location))
            @unlink($orig_location);

        if (file_exists($thumb))
            @unlink($thumb);

        if (file_exists($thumbWide))
            @unlink($thumbWide);

        $object->cover = NULL;
        $object->update();

        return true;
    }

    /**
      * Return categories with posts by Author
      *
      * @param integer $id_lang Language ID
      * @param boolean $active return only active categories
      * @return array Categories
      */
    public static function getCategories($id_simpleblog_author, $id_lang, $active = true, $without_parent = true)
    {
        $categories = SimpleBlogCategory::getCategories($id_lang, $active, $without_parent);

        foreach ($categories as &$category)
        {
            $category['posts'] = SimpleBlogPost::getPosts($id_lang, 99999, $category['id'], null, true, 'title', 'ASC', null, false, false, null, false, array(), true, $id_simpleblog_author);
        }

        return $categories;
    }
}
