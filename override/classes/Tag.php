<?php


class Tag extends TagCore
{
    public $title_for_seo_h1;

    public $description;

    public $meta_title;

    public $meta_description;

    public $link_rewrite;

    public $active;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'tag',
        'primary' => 'id_tag',
        'fields' => array(
            // 'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'title_for_seo_h1' => array('type' => self::TYPE_STRING, 'lang' => false, 'size' => 255),
            'link_rewrite' => array('type' => self::TYPE_STRING, 'lang' => false, 'size' => 255),
            'description' => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHtml'),
            'meta_title' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'size' => 128),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'size' => 255),
            'active' => array('type' => self::TYPE_BOOL),
        ),
    );

    public function __construct($id = null, $name = null, $id_lang = null)
    {
        // In order to get proper definition array for ObjectModel
        $this->def = Tag::getDefinition($this);

        $this->setDefinitionRetrocompatibility();

        if ($id) {
            ObjectModel::__construct($id);
        } elseif ($name && Validate::isGenericName($name)) {
            $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'tag` t
			WHERE `name` = \''.pSQL($name));

            if ($row) {
                $this->id = (int)$row['id_tag'];
                $this->id_lang = (int)$row['id_lang'];
                $this->name = $row['name'];
                $this->title_for_seo_h1 = $row['title_for_seo_h1'];
                $this->description = $row['description'];
                $this->meta_title = $row['meta_title'];
                $this->meta_description = $row['meta_description'];
                $this->link_rewrite = $row['link_rewrite'];
                $this->active = $row['active'];
            }
        }
    }

    public static function deleteTagsForCombination($id_product_attribute)
    {
       return $result = Db::getInstance()->delete('product_tag', 'id_product_attribute = '.(int)$id_product_attribute);
    }

    public static function addTags($id_lang, $id_product, $tag_list, $separator = ',')
    {
        // Set defauld language to 1
        $id_lang = 1;

        $data = '';
        if (!is_array($tag_list)) {
            $tag_list = array_filter(array_unique(array_map('trim', preg_split('#\\'.$separator.'#', $tag_list, null, PREG_SPLIT_NO_EMPTY))));
        }

        $tag_objects = [];
        foreach ($tag_list as $tag) {
            $tag_objects[] = self::getTagByName($tag);
        }

        foreach ($tag_objects as $tag) {
            $data .= '('.(int)$tag[0]['id_tag'].','.(int)$id_product.','.(int)$id_lang.'),';
        }

        $data = rtrim($data, ',');
        $result = Db::getInstance()->execute('
		INSERT INTO `'._DB_PREFIX_.'product_tag` (`id_tag`, `id_product`, `id_lang`)
		VALUES '.$data);

        return $result;
    }

    public static function addTagsToCombination($id_product_attribute, $tags, $id_lang)
    {
        $data = '';
        foreach ($tags as $tag) {
            $data .= '('.(int)$tag[0]['id_tag'].','.(int)$id_product_attribute.','.(int)$id_lang.'),';
        }

        $data = rtrim($data, ',');
        $result = Db::getInstance()->execute('
		INSERT INTO `'._DB_PREFIX_.'product_tag` (`id_tag`, `id_product_attribute`, `id_lang`)
		VALUES '.$data);

        return $result;
    }


    public static function getCombinationTags($id_product_attribute)
    {
        if (!$tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT t.`id_lang`, t.`name`
		FROM '._DB_PREFIX_.'tag t
		LEFT JOIN '._DB_PREFIX_.'product_tag pt ON (pt.id_tag = t.id_tag)
		WHERE pt.`id_product_attribute`='.(int)$id_product_attribute)) {
            return false;
        }
        $result = array();
        foreach ($tmp as $tag) {
            array_push($result,$tag['name']);
        }
        return $result;
    }

    public static function getTagByName($name) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
      SELECT *
      FROM `'._DB_PREFIX_.'tag` t
      WHERE t.`name` = "'.$name.'"
      ORDER BY t.`name` ASC');
    }

    public static function getTagById($id_lang,$id) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
      SELECT *
      FROM `'._DB_PREFIX_.'tag` t
      WHERE t.`id_lang` = '.(int)$id_lang.'
      AND t.`id_tag` = '.(int)$id.'
      ORDER BY t.`name` ASC');
    }

    public static function getTags($id_lang) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            'SELECT t.`name`
             FROM `'._DB_PREFIX_.'tag` t
             ORDER BY t.`name` ASC
        ');
    }

    public static function getProductsForFront($id_tag,$id_lang,$context)
    {
        $sql = 'SELECT p.*, lpi.price_min, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity'
            .(Combination::isFeatureActive() ? ', product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, IFNULL(product_attribute_shop.`id_product_attribute`,0) id_product_attribute' : '').'
            , pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`,
            pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
                DATEDIFF(
                    product_shop.`date_add`,
                    DATE_SUB(
                        "'.date('Y-m-d').' 00:00:00",
                        INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
                    )
                ) > 0 AS new'
            .' FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').
            (Combination::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                        ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')':'').'
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il
                ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
            LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
                ON (m.`id_manufacturer` = p.`id_manufacturer`)
            LEFT JOIN '._DB_PREFIX_.'layered_price_index lpi ON (lpi.id_product = p.id_product)
            '.Product::sqlStock('p', 0);


        $sql .= '
                WHERE pl.id_lang = '.(int)$id_lang.'
		    AND product_shop.active = 1
		    '.($id_tag ? ('AND p.id_product IN (SELECT pt.id_product FROM `'._DB_PREFIX_.'product_tag` pt WHERE pt.id_tag = '.(int)$id_tag.')') : '').'
		    ORDER BY pl.name';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return [];
        }

        return Product::getProductsProperties($id_lang, $result);
    }

    public static function getActiveTags()
    {
        $tags = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT *
        FROM `'._DB_PREFIX_.'tag` t
        WHERE t.`active` = 1');

        return $tags;
    }

    public static function getAllTags()
    {
        $tags = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT *
        FROM `'._DB_PREFIX_.'tag`');

        return $tags;
    }

    public static function getNamesAndLinks($tags,$context)
    {
        $links = [];
        foreach ($tags as $tag)
        {
            $links[$tag['name']] = $context->link->getTagLink($tag['id_tag']);
        }

        return $links;
    }

    public static function getSeriesByModels($models, $id_lang, $context)
    {
        if (! count($models)) {
            return [];
        }

        $ids = '';
        foreach ($models as $model) {
            $ids .= $model['id_product'].',';
        }

        $ids = substr($ids, 0, -1);

        $sql = 'SELECT p.*, lpi.price_min, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity'
            .(Combination::isFeatureActive() ? ', product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, IFNULL(product_attribute_shop.`id_product_attribute`,0) id_product_attribute' : '').'
            , pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`,
            pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
                DATEDIFF(
                    product_shop.`date_add`,
                    DATE_SUB(
                        "'.date('Y-m-d').' 00:00:00",
                        INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
                    )
                ) > 0 AS new'
            .' FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').
            (Combination::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                        ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')':'').'
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il
                ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
            LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
                ON (m.`id_manufacturer` = p.`id_manufacturer`)
            LEFT JOIN '._DB_PREFIX_.'layered_price_index lpi ON (lpi.id_product = p.id_product)
            '.Product::sqlStock('p', 0);


        $sql .= '
                WHERE pl.id_lang = '.(int)$id_lang.'
		    AND product_shop.active = 1
		    AND p.id_product IN ('.$ids.')
		    ORDER BY pl.name';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return [];
        }

        return Product::getProductsProperties($id_lang, $result);
    }

    public static function getModelsForFront($id_lang,$id,$context)
    {
        $filter_ids =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT pt.id_product_attribute FROM `'._DB_PREFIX_.'product_tag` pt WHERE pt.id_tag = '.(int)$id.' AND pt.id_product = 0');

        $ids = [];

        foreach ($filter_ids as $array) {
            array_push($ids,$array['id_product_attribute']);
        }

        $result = [];
        $filter_attribute_ids = '';
        if (count($ids)) {
            $filter_attribute_ids = implode(', ', $ids);
        }

        if ($filter_attribute_ids) {
            $sql = 'SELECT pa.id_product_attribute, pa.id_product, pa.default_on, pa.title, pa.short_description, pa.long_description, pa.attribute_video, pa.price, psa.quantity, psa.out_of_stock, pl.link_rewrite, p.id_category_default, p.ean13, p.available_for_order, pa.minimal_quantity as product_attribute_minimal_quantity, p.show_price, ppi.id_image as series_image, pa.top_description, pa.is_new
                FROM `'._DB_PREFIX_.'product_attribute` pa
                LEFT JOIN '._DB_PREFIX_.'stock_available psa ON psa.id_product_attribute = pa.id_product_attribute
                LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product = pa.id_product
                LEFT JOIN '._DB_PREFIX_.'image ppi ON (ppi.id_product = p.id_product AND ppi.cover = 1)
                LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = pa.id_product AND pl.id_lang = '.$id_lang.')
                WHERE pa.id_product_attribute IN ('.$filter_attribute_ids.')
                AND pa.hide = 0
                GROUP BY (pa.id_product_attribute)
                ORDER BY pa.title';

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        }

        $array = [];
        $usetax = Tax::excludeTaxeOption();

        foreach ($result as &$row) {
            $id_attribute = (int)$row['id_product_attribute'];

            $series = new Series((int)$row['id_product']);
            $models = $series->getModels($id_lang, false, 0, 0, [$id_attribute]);

            if (count($models)) {
                array_push($array, $models[0]);
            }
        }

        return $array;
    }
}
