<?php

class Manufacturer extends ManufacturerCore
{
    public $title_for_seo_h1;

    public function __construct($id = null, $id_lang = null)
    {
        self::$definition['fields']['title_for_seo_h1'] = array('type' => self::TYPE_STRING, 'size' => 255);

        parent::__construct($id, $id_lang);
    }

    public function getAddressCountryIsoCode($id_lang)
    {
        $addresses = $this->getAddresses($id_lang);
        if (count($addresses)) {
            $country_id = $addresses[0]['id_country'];

            return Country::getIsoById($country_id);
        }
    }

    public static function getProducts($id_manufacturer, $id_lang, $p, $n, $order_by = null, $order_way = null,
        $get_total = false, $active = true, $active_category = true, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }

        if ($p < 1) {
            $p = 1;
        }

        if (empty($order_by) || $order_by == 'position') {
            $order_by = 'name';
        }

        if (empty($order_way)) {
            $order_way = 'ASC';
        }

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }

        $groups = FrontController::getCurrentCustomerGroups();
        $sql_groups = count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1';

        /* Return only the number of products */
        if ($get_total) {
            $sql = '
                SELECT p.`id_product`
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                WHERE p.id_manufacturer = '.(int)$id_manufacturer
                .($active ? ' AND product_shop.`active` = 1' : '').'
                '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
                AND EXISTS (
                    SELECT 1
                    FROM `'._DB_PREFIX_.'category_group` cg
                    LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)'.
                    ($active_category ? ' INNER JOIN `'._DB_PREFIX_.'category` ca ON cp.`id_category` = ca.`id_category` AND ca.`active` = 1' : '').'
                    WHERE p.`id_product` = cp.`id_product` AND cg.`id_group` '.$sql_groups.'
                )';

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            return (int)count($result);
        }
        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
        }
        $alias = '';
        if ($order_by == 'price') {
            $alias = 'product_shop.';
        } elseif ($order_by == 'name') {
            $alias = 'pl.';
        } elseif ($order_by == 'manufacturer_name') {
            $order_by = 'name';
            $alias = 'm.';
        } elseif ($order_by == 'quantity') {
            $alias = 'stock.';
        } else {
            $alias = 'p.';
        }

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

        if (Group::isFeatureActive() || $active_category) {
            $sql .= 'JOIN `'._DB_PREFIX_.'category_product` cp ON (p.id_product = cp.id_product)';
            if (Group::isFeatureActive()) {
                $sql .= 'JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.`id_category` = cg.`id_category` AND cg.`id_group` '.$sql_groups.')';
            }
            if ($active_category) {
                $sql .= 'JOIN `'._DB_PREFIX_.'category` ca ON cp.`id_category` = ca.`id_category` AND ca.`active` = 1';
            }
        }

        $sql .= '
                WHERE p.`id_manufacturer` = '.(int)$id_manufacturer.'
                '.($active ? ' AND product_shop.`active` = 1' : '').'
                '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
                GROUP BY p.id_product
                ORDER BY '.$alias.'`'.bqSQL($order_by).'` '.pSQL($order_way).'
                LIMIT '.(((int)$p - 1) * (int)$n).','.(int)$n;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return false;
        }

        if ($order_by == 'price') {
            Tools::orderbyPrice($result, $order_way);
        }

        return Product::getProductsProperties($id_lang, $result);
    }
}
