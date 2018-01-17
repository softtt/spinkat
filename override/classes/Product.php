<?php

class Product extends ProductCore
{
    public $show_on_sales_page;
    public $is_series;
    public $video;
    public $is_new;
    public $show_gift_label;

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        self::$definition['fields']['show_on_sales_page'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');
        self::$definition['fields']['is_series'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');
        self::$definition['fields']['video'] = array('type' => self::TYPE_HTML);
        self::$definition['fields']['is_new'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');
        self::$definition['fields']['show_gift_label'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');
        self::$definition['fields']['reference']['size'] = 255;
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }

    public static function getPricesDrop($id_lang, $page_number = 0, $nb_products = 10, $count = false,
        $order_by = null, $order_way = null, $beginning = false, $ending = false, Context $context = null, $show_on_product_page = false)
    {
        if (!Validate::isBool($count)) {
            die(Tools::displayError());
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if ($page_number < 0) {
            $page_number = 0;
        }
        if ($nb_products < 1) {
            $nb_products = 10;
        }
        if (empty($order_by) || $order_by == 'position') {
            $order_by = 'price';
        }
        if (empty($order_way)) {
            $order_way = 'DESC';
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }
        $current_date = date('Y-m-d H:i:s');
        $ids_product = Product::_getProductIdByDate((!$beginning ? $current_date : $beginning), (!$ending ? $current_date : $ending), $context);
        $tab_id_product = array();
        foreach ($ids_product as $product) {
            if (is_array($product)) {
                $tab_id_product[] = (int)$product['id_product'];
            } else {
                $tab_id_product[] = (int)$product;
            }
        }
        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }
        $sql_groups = '';
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
                JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').')
                WHERE cp.`id_product` = p.`id_product`)';
        }
        if ($count) {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT COUNT(DISTINCT p.`id_product`)
            FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').'
            WHERE product_shop.`active` = 1
            AND product_shop.`show_price` = 1
            '.($show_on_product_page ? ' AND p.`show_on_sales_page` = 1' : '').'
            '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
            '.((!$beginning && !$ending) ? 'AND p.`id_product` IN('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
            '.$sql_groups);
        }
        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
        }
        $sql = '
        SELECT
            p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`,
            IFNULL(product_attribute_shop.id_product_attribute, 0) id_product_attribute,
            pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`,
            pl.`name`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
            DATEDIFF(
                p.`date_add`,
                DATE_SUB(
                    "'.date('Y-m-d').' 00:00:00",
                    INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
                )
            ) > 0 AS new
        FROM `'._DB_PREFIX_.'product` p
        '.Shop::addSqlAssociation('product', 'p').'
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
            ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')
        '.Product::sqlStock('p', 0, false, $context->shop).'
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
            p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
        )
        LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
            ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        WHERE product_shop.`active` = 1
        AND product_shop.`show_price` = 1
        '.($show_on_product_page ? ' AND p.`show_on_sales_page` = 1' : '').'
        '.($front ? ' AND p.`visibility` IN ("both", "catalog")' : '').'
        '.((!$beginning && !$ending) ? ' AND p.`id_product` IN ('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
        '.$sql_groups.'
        GROUP BY p.id_product
        ORDER BY '.(isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').pSQL($order_by).' '.pSQL($order_way).'
        LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$result) {
            return false;
        }
        if ($order_by == 'price') {
            Tools::orderbyPrice($result, $order_way);
        }
        return Product::getProductsProperties($id_lang, $result);
    }


    public function updateAttribute($id_product_attribute, $wholesale_price, $price, $weight, $unit, $ecotax,
        $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity = null, $available_date = null,
                                    $update_all_fields = true, array $id_shop_list = array(), $title = '', $short_description = '', $long_description = '',
                                    $video = '', $top_description = '', $is_new = '', $hide = '', $tags = '')
    {
        $combination = new Combination($id_product_attribute);

        if (!$update_all_fields) {
            $combination->setFieldsToUpdate(array(
                'price' => !is_null($price),
                'wholesale_price' => !is_null($wholesale_price),
                'ecotax' => !is_null($ecotax),
                'weight' => !is_null($weight),
                'unit_price_impact' => !is_null($unit),
                'default_on' => !is_null($default),
                'minimal_quantity' => !is_null($minimal_quantity),
                'available_date' => !is_null($available_date),
                'title' => !is_null($title),
                'short_description' => !is_null($short_description),
                'long_description' => !is_null($long_description),
                'video' => !is_null($video),
                'top_description' => !is_null($top_description),
                'is_new' => !is_null($is_new),
                'hide' => !is_null($hide),
            ));
        }

        $price = str_replace(',', '.', $price);
        $weight = str_replace(',', '.', $weight);

        $combination->price = (float)$price;
        $combination->wholesale_price = (float)$wholesale_price;
        $combination->ecotax = (float)$ecotax;
        $combination->weight = (float)$weight;
        $combination->unit_price_impact = (float)$unit;
        $combination->reference = pSQL($reference);
        $combination->location = pSQL($location);
        $combination->ean13 = pSQL($ean13);
        $combination->upc = pSQL($upc);
        $combination->default_on = (int)$default;
        $combination->minimal_quantity = (int)$minimal_quantity;
        $combination->available_date = $available_date ? pSQL($available_date) : '0000-00-00';
        $combination->title = $title;
        $combination->short_description = $short_description;
        $combination->long_description = $long_description;
        $combination->attribute_video = $video;
        $combination->top_description = $top_description;
        $combination->is_new = $is_new;
        $combination->hide = $hide;

        if (count($id_shop_list)) {
            $combination->id_shop_list = $id_shop_list;
        }

        $combination->save();

        $this->updateAttributeTags($tags, $id_product_attribute);

        if (is_array($id_images) && count($id_images)) {
            $combination->setImages($id_images);
        }

        $id_default_attribute = (int)Product::updateDefaultAttribute($this->id);
        if ($id_default_attribute) {
            $this->cache_default_attribute = $id_default_attribute;
        }

        // Sync stock Reference, EAN13 and UPC for this attribute
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && StockAvailable::dependsOnStock($this->id, Context::getContext()->shop->id)) {
            Db::getInstance()->update('stock', array(
                'reference' => pSQL($reference),
                // 'ean13'     => pSQL($ean13),
                // 'upc'        => pSQL($upc),
            ), 'id_product = '.$this->id.' AND id_product_attribute = '.(int)$id_product_attribute);
        }

        Hook::exec('actionProductAttributeUpdate', array('id_product_attribute' => (int)$id_product_attribute));
        Tools::clearColorListCache($this->id);

        return true;
    }

    public function updateAttributeTags($tags, $id_product_attribute)
    {
        $tag_success = true;

        Tag::deleteTagsForCombination($id_product_attribute);

        $tags_array = explode(',', $tags);

        $tag_objects = [];

        foreach ($tags_array as $name) {
            array_push($tag_objects, Tag::getTagByName($name));
        }

        $id_lang = Context::getContext()->language->id;
        /* Assign tags to this product_attribute */
        if ($tag_objects) {
            $tag_success &= Tag::addTagsToCombination($id_product_attribute, $tag_objects, $id_lang);
        }

        if (!$tag_success) {
            $this->errors[] = Tools::displayError('An error occurred while adding tags.');
        }

        return $tag_success;
    }
    /**
    * @param int $quantity DEPRECATED
    * @param string $supplier_reference DEPRECATED
    */
    public function addCombinationEntity($wholesale_price, $price, $weight, $unit_impact, $ecotax, $quantity,
        $id_images, $reference, $id_supplier, $ean13, $default, $location = null, $upc = null, $minimal_quantity = 1, array $id_shop_list = array(), $available_date = null, $title = '', $short_description = '', $long_description = '', $video = '', $top_description = '',
                                         $is_new = '', $hide = '',$tags = '')
    {
        $id_product_attribute = $this->addAttribute(
            $price, $weight, $unit_impact, $ecotax, $id_images,
            $reference, $ean13, $default, $location, $upc, $minimal_quantity, $id_shop_list, $available_date, $title, $short_description,
            $long_description, $video, $top_description, $is_new, $hide,$tags);
        $this->addSupplierReference($id_supplier, $id_product_attribute);
        $result = ObjectModel::updateMultishopTable('Combination', array(
            'wholesale_price' => (float)$wholesale_price,
        ), 'a.id_product_attribute = '.(int)$id_product_attribute);

        if (!$id_product_attribute || !$result) {
            return false;
        }

        return $id_product_attribute;
    }

/**
     * Add a product attribute
     * @since 1.5.0.1
     *
     * @param float $price Additional price
     * @param float $weight Additional weight
     * @param float $ecotax Additional ecotax
     * @param int $id_images Image ids
     * @param string $reference Reference
     * @param string $location Location
     * @param string $ean13 Ean-13 barcode
     * @param bool $default Is default attribute for product
     * @param int $minimal_quantity Minimal quantity to add to cart
     * @return mixed $id_product_attribute or false
     */
    public function addAttribute($price, $weight, $unit_impact, $ecotax, $id_images, $reference, $ean13,
                                 $default, $location = null, $upc = null, $minimal_quantity = 1, array $id_shop_list = array(), $available_date = null,
                                 $title = '', $short_description = '', $long_description = '', $video = '', $top_description = '', $is_new = '', $hide = '',$tags = '')
    {
        if (!$this->id) {
            return;
        }

        $price = str_replace(',', '.', $price);
        $weight = str_replace(',', '.', $weight);

        $combination = new Combination();
        $combination->id_product = (int)$this->id;
        $combination->price = (float)$price;
        $combination->ecotax = (float)$ecotax;
        $combination->quantity = 0;
        $combination->weight = (float)$weight;
        $combination->unit_price_impact = (float)$unit_impact;
        $combination->reference = pSQL($reference);
        $combination->location = pSQL($location);
        $combination->ean13 = pSQL($ean13);
        $combination->upc = pSQL($upc);
        $combination->default_on = (int)$default;
        $combination->minimal_quantity = (int)$minimal_quantity;
        $combination->available_date = $available_date;
        $combination->title = $title;
        $combination->short_description = $short_description;
        $combination->long_description = $long_description;
        $combination->attribute_video = $video;
        $combination->top_description = $top_description;
        $combination->is_new = $is_new;
        $combination->hide = $hide;

        if (count($id_shop_list)) {
            $combination->id_shop_list = array_unique($id_shop_list);
        }

        $combination->add();

        if (!$combination->id) {
            return false;
        }
        $this->updateAttributeTags($tags,$combination->id);
        $total_quantity = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT SUM(quantity) as quantity
            FROM '._DB_PREFIX_.'stock_available
            WHERE id_product = '.(int)$this->id.'
            AND id_product_attribute <> 0 '
        );

        if (!$total_quantity) {
            Db::getInstance()->update('stock_available', array('quantity' => 0), '`id_product` = '.$this->id);
        }

        $id_default_attribute = Product::updateDefaultAttribute($this->id);

        if ($id_default_attribute) {
            $this->cache_default_attribute = $id_default_attribute;
            if (!$combination->available_date) {
                $this->setAvailableDate();
            }
        }

        if (!empty($id_images)) {
            $combination->setImages($id_images);
        }

        Tools::clearColorListCache($this->id);

        if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT') != 0 && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $warehouse_location_entity = new WarehouseProductLocation();
            $warehouse_location_entity->id_product = $this->id;
            $warehouse_location_entity->id_product_attribute = (int)$combination->id;
            $warehouse_location_entity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
            $warehouse_location_entity->location = pSQL('');
            $warehouse_location_entity->save();
        }

        return (int)$combination->id;
    }

    /**
     * Get Product min or max price based on attribute combination prices
     *
     * @param  int $id_lang
     * @param  string $type Accepts values 'min', 'max', 'both'
     * @return array Array of prices
     */
    public function getCombinationsPrices($id_shop, $id_lang, $id_currency)
    {
        $combinations = $this->getWsCombinations();

        $prices = array();

        foreach ($combinations as $key => $c) {
            $prices[$c['id']] = Product::priceCalculation($id_shop, $this->id, $c['id'], null, null, null,
                $id_currency, null, null, false, 6, false, true, true,
                $specific_price_output, true);
        }

        $result = array(
            'min' => min($prices),
            'max' => max($prices),
        );

        return $result;
    }

    public function getAnchor($id_product_attribute, $with_id = false)
    {
        $attributes = Product::getAttributesParams($this->id, $id_product_attribute);
        $anchor = '#';
        $sep = Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR');
        foreach ($attributes as &$a) {
            foreach ($a as &$b) {
                $b = str_replace($sep, '_', Tools::link_rewrite($b));
            }
            if ($with_id && isset($a['id_attribute']) && $a['id_attribute']) {
                $anchor .= '/' . ((int)$a['id_attribute'] . $sep) . $a['group'] . $sep . $a['name'];
            } else {
                $anchor .= '/' . ('') . $a['group'] . $sep . $a['name'];
            }
        }
        $anchor .= '/' .$id_product_attribute. '-model_id';
        return $anchor;
    }

    public function getAllTags($id_lang = 1){
        if (!$id_lang){
            $context = Context::getContext();
            $id_lang = $context->language->id;
        }

        return Tag::getTags($id_lang);
    }

    public function getAvailableCartRules()
    {
        $context = Context::getContext();

        $sql = '
        SELECT SQL_NO_CACHE cr.*
        FROM '._DB_PREFIX_.'cart_rule cr
        LEFT JOIN '._DB_PREFIX_.'cart_rule_shop crs ON cr.id_cart_rule = crs.id_cart_rule
        '.(!$context->customer->id && Group::isFeatureActive() ? ' LEFT JOIN '._DB_PREFIX_.'cart_rule_group crg ON cr.id_cart_rule = crg.id_cart_rule' : '').'
        LEFT JOIN '._DB_PREFIX_.'cart_rule_carrier crca ON cr.id_cart_rule = crca.id_cart_rule
        LEFT JOIN '._DB_PREFIX_.'cart_rule_country crco ON cr.id_cart_rule = crco.id_cart_rule
        WHERE cr.active = 1
        AND cr.code = ""
        AND cr.quantity > 0
        AND cr.date_from < "'.date('Y-m-d H:i:s').'"
        AND cr.date_to > "'.date('Y-m-d H:i:s').'"
        AND (
            cr.id_customer = 0
            '.($context->customer->id ? 'OR cr.id_customer = '.(int)$context->customer->id : '').'
        )
        AND (
            cr.`shop_restriction` = 0
            '.((Shop::isFeatureActive() && $context->shop->id) ? 'OR crs.id_shop = '.(int)$context->shop->id : '').'
        )
        AND (
            cr.`group_restriction` = 0
            '.($context->customer->id ? 'OR EXISTS (
                SELECT 1
                FROM `'._DB_PREFIX_.'customer_group` cg
                INNER JOIN `'._DB_PREFIX_.'cart_rule_group` crg ON cg.id_group = crg.id_group
                WHERE cr.`id_cart_rule` = crg.`id_cart_rule`
                AND cg.`id_customer` = '.(int)$context->customer->id.'
                LIMIT 1
            )' : (Group::isFeatureActive() ? 'OR crg.`id_group` = '.(int)Configuration::get('PS_UNIDENTIFIED_GROUP') : '')).'
        )
        AND (
            cr.`reduction_product` <= 0
            OR EXISTS (
                SELECT 1
                FROM `'._DB_PREFIX_.'cart_product`
                WHERE `'._DB_PREFIX_.'cart_product`.`id_product` = cr.`reduction_product` AND `id_cart` = '.(int)$context->cart->id.'
            )
        )
        -- AND NOT EXISTS (SELECT 1 FROM '._DB_PREFIX_.'cart_cart_rule WHERE cr.id_cart_rule = '._DB_PREFIX_.'cart_cart_rule.id_cart_rule
        --                                                                     AND id_cart = '.(int)$context->cart->id.')
        ORDER BY priority';

        return Db::getInstance()->executeS($sql, true, false);
    }

    public function getCartRules($id_attribute = null, $min_price = null)
    {
        $cart_rules = $this->getAvailableCartRules();

        foreach ($cart_rules as $key => &$rule) {

            $cart_rule = new CartRule($rule['id_cart_rule']);
            $cart_rule_meet_conditions = true;

            // Check product groups conditions
            $product_rule_groups = $cart_rule->getProductRuleGroups();
            foreach ($product_rule_groups as $id_product_rule_group => $product_rule_group) {
                $product_rules = $product_rule_group['product_rules'];

                foreach ($product_rules as $product_rule) {
                    switch ($product_rule['type']) {
                        case 'products':
                            if (!in_array($this->id, $product_rule['values']))
                                $cart_rule_meet_conditions = false;
                            break;

                        case 'attributes':
                            if ($id_attribute) {
                                $attributes_ids = Combination::getCombinationAttributesIds($id_attribute);

                                if (!array_intersect($attributes_ids, $product_rule['values']))
                                    $cart_rule_meet_conditions = false;
                            }
                            break;

                        case 'categories':
                            $product_categories = $this->getCategories();
                            if (!array_intersect($product_categories, $product_rule['values']))
                                $cart_rule_meet_conditions = false;
                            break;

                        case 'manufacturers':
                            if (!in_array($this->id_manufacturer, $product_rule['values']))
                                $cart_rule_meet_conditions = false;
                            break;

                        case 'suppliers':
                            if (!in_array($this->id_supplier, $product_rule['values']))
                                $cart_rule_meet_conditions = false;
                            break;
                    }
                }
            }

            // Check price condition
            if (isset($cart_rule->minimum_amount) &&
                $cart_rule->minimum_amount > 0 &&
                isset($min_price) &&
                $cart_rule->minimum_amount > $min_price
            ) {
                $cart_rule_meet_conditions = false;
            }

            $rule['banner'] = $cart_rule->getBannerLink();

            if (!$cart_rule_meet_conditions)
                unset($cart_rules[$key]);
        }

        return $cart_rules;
    }

    public function getFreeGifts($id_lang, $id_attribute = 0, $min_price = null)
    {
        $context = Context::getContext();

        $cart_rules = $this->getCartRules($id_attribute, $min_price);
        $gifts = array();

        foreach ($cart_rules as $cart_rule) {
            if ($cart_rule['gift_product']) {
                $gift = new Product($cart_rule['gift_product'], false, $id_lang);
                $gift->link = $context->link->getProductLink((int)$gift->id, $gift->link_rewrite, $gift->category, $gift->ean13, null, null, $cart_rule['gift_product_attribute'], false, false, true, true);
                $gift->banner = $cart_rule['banner'];

                $gifts[] = $gift;
            }
        }

        return $gifts;
    }

    public function getWsCombinations()
    {
        $result = Db::getInstance()->executeS(
            'SELECT pa.`id_product_attribute` as id
            FROM `'._DB_PREFIX_.'product_attribute` pa
            '.Shop::addSqlAssociation('product_attribute', 'pa').'
            WHERE pa.hide = 0 AND
                pa.`id_product` = '.(int)$this->id
        );

        return $result;
    }

    public static function getByReference($reference)
    {
        if (empty($reference)) {
            return 0;
        }

        $reference = str_replace([' ', '-', '\'', '"'], '', $reference);

        $query = new DbQuery();
        $query->select('p.id_product');
        $query->from('product', 'p');
        // Remove hyphens, spaces, blockquotes and backslashes
        $query->where('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p.reference, " ", ""), "-", ""), "\'", ""), "\"", ""), "\\\", "") LIKE "'.pSQL($reference).'"');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public static function getProductProperties($id_lang, $row, Context $context = null)
    {
        $product = new Product($row['id_product']);
        $row['show_gift_label'] = $product->hasAllCombinationsFreeGift($id_lang);

        //get price reduction for series
        // if ((int)$row['is_series']) {
        //     $sql = 'SELECT pa.id_product_attribute, pa.id_product, pa.title, pa.short_description, pa.long_description, pa.attribute_video, pa.price, psa.quantity, psa.out_of_stock, pl.link_rewrite, p.id_category_default, p.ean13, p.available_for_order, pa.minimal_quantity as product_attribute_minimal_quantity, p.show_price, ppi.id_image as series_image, pa.top_description, pa.is_new
        //         FROM `'._DB_PREFIX_.'product_attribute` pa
        //         LEFT JOIN '._DB_PREFIX_.'stock_available psa ON psa.id_product_attribute = pa.id_product_attribute
        //         LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product = pa.id_product
        //         LEFT JOIN '._DB_PREFIX_.'image ppi ON (ppi.id_product = p.id_product AND ppi.cover = 1)
        //         LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = pa.id_product AND pl.id_lang = '.$id_lang.')
        //         WHERE pa.`id_product` = '.(int)$row['id_product'].'
        //         AND pa.hide = 0
        //         GROUP BY (pa.id_product_attribute)
        //         ORDER BY pa.title';

        //     $models = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);

        //     $min_price = (int)$row['price_min'];
        //     $row['price_before_reduction'] = (int)$row['price_min'];
        //     foreach ($models as $model) {

        //         $model['price'] = Tools::ps_round(
        //             Product::getPriceStatic(
        //                 (int)$model['id_product'],
        //                 true,
        //                 $model['id_product_attribute'],
        //                 6
        //             ),
        //             (int)Configuration::get('PS_PRICE_DISPLAY_PRECISION')
        //         );
        //         $model['price_without_reduction'] = Product::getPriceStatic(
        //             (int)$model['id_product'],
        //             true,
        //             $model['id_product_attribute'],
        //             6,
        //             null,
        //             false,
        //             false
        //         );

        //         if ($min_price == 0) {
        //             $min_price = $model['price'];
        //         } elseif($min_price > $model['price']) {
        //             $min_price = $model['price'];
        //         }
        //         $row['price_min'] = $min_price;
        //     }
        // }

        return parent::getProductProperties($id_lang, $row, $context);
    }

    /**
     * Get new products
     *
     * @param int $id_lang Language id
     * @param int $pageNumber Start from (optional)
     * @param int $nbProducts Number of products to return (optional)
     * @return array New products
     */
    public static function getNewProducts($id_lang, $page_number = 0, $nb_products = 10, $count = false, $order_by = null, $order_way = null, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }

        if ($page_number < 0) {
            $page_number = 0;
        }
        if ($nb_products < 1) {
            $nb_products = 10;
        }
        if (empty($order_by) || $order_by == 'position') {
            $order_by = 'date_add';
        }
        if (empty($order_way)) {
            $order_way = 'DESC';
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }

        $sql_groups = '';
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
				JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').')
				WHERE cp.`id_product` = p.`id_product`)';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }

        if ($count) {
            $sql = 'SELECT COUNT(p.`id_product`) AS nb
					FROM `'._DB_PREFIX_.'product` p
					'.Shop::addSqlAssociation('product', 'p').'
					WHERE product_shop.`active` = 1
					AND product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'"
					'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
					'.$sql_groups;
            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        $sql = new DbQuery();
        $sql->select(
            'p.*, product_shop.*,lpi.price_min, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`,
			pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
			product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new'
        );

        $sql->from('product', 'p');
        $sql->join(Shop::addSqlAssociation('product', 'p'));
        $sql->leftJoin('product_lang', 'pl', '
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl')
        );
        $sql->leftJoin('layered_price_index','lpi','lpi.`id_product` = p.`id_product`' );
        $sql->leftJoin('image_shop', 'image_shop', 'image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id);
        $sql->leftJoin('image_lang', 'il', 'image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang);
        $sql->leftJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');

        $sql->where('product_shop.`active` = 1');
        if ($front) {
            $sql->where('product_shop.`visibility` IN ("both", "catalog")');
        }
        $sql->where('product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'"');
        if (Group::isFeatureActive()) {
            $sql->join('JOIN '._DB_PREFIX_.'category_product cp ON (cp.id_product = p.id_product)');
            $sql->join('JOIN '._DB_PREFIX_.'category_group cg ON (cg.id_category = cp.id_category)');
            $sql->where('cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1'));
        }
        $sql->groupBy('product_shop.id_product');

        $sql->orderBy((isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way));
        $sql->limit($nb_products, $page_number * $nb_products);

        if (Combination::isFeatureActive()) {
            $sql->select('product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute');
            $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', 'p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id);
        }
        $sql->join(Product::sqlStock('p', 0));

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($order_by == 'price') {
            Tools::orderbyPrice($result, $order_way);
        }
        if (!$result) {
            return false;
        }

        $products_ids = array();
        foreach ($result as $row) {
            $products_ids[] = $row['id_product'];
        }
        // Thus you can avoid one query per product, because there will be only one query for all the products of the cart
        Product::cacheFrontFeatures($products_ids, $id_lang);
        return Product::getProductsProperties((int)$id_lang, $result);
    }

    public function hasAllCombinationsFreeGift($id_lang = null)
    {
        $sql = 'SELECT pa.id_product_attribute, pa.price, p.available_for_order
                FROM `'._DB_PREFIX_.'product_attribute` pa
                LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product = pa.id_product
                WHERE pa.`id_product` = '.(int)$this->id.'
                AND pa.hide = 0
                GROUP BY (pa.id_product_attribute)';

        if ($id_lang === null) {
            $id_lang = Context::getContext()->language->id;
        }

        $has_free_gift = true;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        if(empty($result))
            $has_free_gift = false;
        foreach ($result as &$row) {
            if (self::$_taxCalculationMethod == PS_TAX_EXC) {
                $row['price'] = Product::getPriceStatic(
                    (int)$this->id,
                    true,
                    $row['id_product_attribute'],
                    6
                );
            } else {
                $row['price'] = Tools::ps_round(
                    Product::getPriceStatic(
                        (int)$this->id,
                        true,
                        $row['id_product_attribute'],
                        6
                    ),
                    (int)Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                );
            }

            if(!$this->getFreeGifts($id_lang, $row['id_product_attribute'], $row['price'])) {
                $has_free_gift = false;
                break;
            }
        }

        return $has_free_gift;
    }

    public function isFreeDelivery($id_lang, $id_product_attribute, $price)
    {
        $context = Context::getContext();

        $cart_rules = $this->getCartRules($id_product_attribute, $price);
        $free_delivery = false;

        foreach ($cart_rules as $cart_rule) {
            if ($cart_rule['free_shipping']) {
                $free_delivery = true;
                break;
            }
        }

        return $free_delivery;
    }
}
