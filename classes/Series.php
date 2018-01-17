<?php

/**
 * Series class for Spinkat ecommerce shop.
 * Series is a collection of Models of products which are grouped in Series.
 * Models are extended Combination with specific attributes.
 */
class Series extends Product
{
    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }

    public function getFullName()
    {
        $category = new Category($this->id_category_default);

        $manufacturer_name = $this->getWsManufacturerName();

        return join(' ', [$category->getName(), $manufacturer_name, $this->name]);
    }

    public function getModels($id_lang = null, $get_total = false, $p = 0, $n = 0, $filter_ids = array(), $id_tag = null)
    {
        $context = Context::getContext();

        if (!$this->checkAccess($context->customer->id)
            || !in_array($this->visibility, ['both', 'catalog'])) {
            return false;
        }

        $tag_where = "";
        if ($id_tag) {
            $tag_where = "
                SELECT
                    pt.id_product_attribute
                FROM
                    `ps_product_tag` pt
                WHERE
                    pt.id_tag = $id_tag
                        AND id_product_attribute <> 0
                UNION SELECT
                    pa.id_product_attribute
                FROM
                    `ps_product_tag` pt
                        LEFT JOIN
                    ps_product_attribute pa ON pa.id_product = pt.id_product
                WHERE
                    pt.id_tag = {$id_tag}
                AND pt.id_product_attribute = 0
                AND pt.id_product <> 0
            ";
        }

        /** Return only the number of products */
        if ($get_total) {
            if ($id_tag && $tag_where) {
                // Select attributes filtered by tags (subquery)
                $sql = 'SELECT COUNT(pa.id_product_attribute) FROM `'._DB_PREFIX_.'product_attribute` pa WHERE `id_product` = '.(int)$this->id.
                    ' AND pa.id_product_attribute IN ('.$tag_where.')';
            } else {
                $sql = 'SELECT COUNT(pa.id_product_attribute) FROM `'._DB_PREFIX_.'product_attribute` pa WHERE `id_product` = '.(int)$this->id;
            }

            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        if ($id_lang === null) {
            $id_lang = Context::getContext()->language->id;
        }

        if ($p < 1) {
            $p = 1;
        }

        $filter_attribute_ids = '';
        if (count($filter_ids)) {
            $filter_attribute_ids = implode(', ', $filter_ids);
        }

        $sql = 'SELECT pa.id_product_attribute, pa.id_product, pa.title, pa.short_description, pa.long_description, pa.attribute_video, pa.price, psa.quantity, psa.out_of_stock, pl.link_rewrite, p.id_category_default, p.ean13, p.available_for_order, pa.minimal_quantity as product_attribute_minimal_quantity, p.show_price, ppi.id_image as series_image, pa.top_description, pa.is_new
                FROM `'._DB_PREFIX_.'product_attribute` pa
                LEFT JOIN '._DB_PREFIX_.'stock_available psa ON psa.id_product_attribute = pa.id_product_attribute
                LEFT JOIN '._DB_PREFIX_.'product p ON p.id_product = pa.id_product
                LEFT JOIN '._DB_PREFIX_.'image ppi ON (ppi.id_product = p.id_product AND ppi.cover = 1)
                LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = pa.id_product AND pl.id_lang = '.$id_lang.')
                WHERE pa.`id_product` = '.(int)$this->id.
                ($filter_attribute_ids ? ' AND pa.id_product_attribute IN ('.$filter_attribute_ids.') ' : ' ').'
                AND pa.hide = 0
                '.((($id_tag) && ($id_tag != 'undefined') && $tag_where) ? ('AND pa.id_product_attribute IN ('.$tag_where.')') : '').'
                GROUP BY (pa.id_product_attribute)
                ORDER BY pa.title'.
                ($n ? ' LIMIT '.(((int)$p - 1) * (int)$n).','.(int)$n : '');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);

        $usetax = Tax::excludeTaxeOption();

        foreach ($result as &$row) {
            $model = new Model($row['id_product_attribute']);

            $row['name'] = $model->getFullTitle();

            $image_sql = 'SELECT i.*
                FROM `ps_image` i
                LEFT JOIN ps_product_attribute_image pai ON pai.id_image = i.id_image
                WHERE id_product = '.(int)$this->id.'
                AND id_product_attribute = '.$row['id_product_attribute'].'
                ORDER BY i.position';
            $images = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($image_sql, true, false);

            if (count($images) && $images[0]) {
                $row['id_image'] = $images[0]['id_image'];
            } else {
                $row['id_image'] = $row['series_image'];
            }

            $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
            $row['model'] = true;
            $row['category'] = Category::getLinkRewrite((int)$row['id_category_default'], (int)$id_lang);
            $row['link'] = $context->link->getProductLink((int)$row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13'], null, null, $row['id_product_attribute'], false, false, true, true);

            if ($id_tag) {
                $link_array = explode('#',$row['link']);
                $link = $link_array[0].'?id_tag='.$id_tag.'#'.$link_array[1];
                $row['link'] = $link;
            }

            $row['description_short'] = $row['short_description'];
            $row['customizable'] = false;

            $row['price_tax_exc'] = Product::getPriceStatic(
                (int)$row['id_product'],
                false,
                $row['id_product_attribute'],
                (self::$_taxCalculationMethod == PS_TAX_EXC ? 2 : 6)
            );

            if (self::$_taxCalculationMethod == PS_TAX_EXC) {
                $row['price_tax_exc'] = Tools::ps_round($row['price_tax_exc'], 2);
                $row['price'] = Product::getPriceStatic(
                    (int)$row['id_product'],
                    true,
                    $row['id_product_attribute'],
                    6
                );
                $row['price_without_reduction'] = Product::getPriceStatic(
                    (int)$row['id_product'],
                    false,
                    $row['id_product_attribute'],
                    2,
                    null,
                    false,
                    false
                );
            } else {
                $row['price'] = Tools::ps_round(
                    Product::getPriceStatic(
                        (int)$row['id_product'],
                        true,
                        $row['id_product_attribute'],
                        6
                    ),
                    (int)Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                );
                $row['price_without_reduction'] = Product::getPriceStatic(
                    (int)$row['id_product'],
                    true,
                    $row['id_product_attribute'],
                    6,
                    null,
                    false,
                    false
                );
            }

            $row['reduction'] = Product::getPriceStatic(
                (int)$row['id_product'],
                (bool)$usetax,
                $row['id_product_attribute'],
                6,
                null,
                true,
                true,
                1,
                true,
                null,
                null,
                null,
                $specific_prices
            );

            $row['specific_prices'] = $specific_prices;

            $row['has_free_gift'] = (bool)$this->getFreeGifts($id_lang, $row['id_product_attribute'], $row['price']);

            // Todo: Get Meta title and description
            $category = new Category($this->id_category_default, $id_lang);

            $title_template = Configuration::get('SEO_TEMPLATE_CATALOG_PRODUCT_TITLE');
            $description_template = Configuration::get('SEO_TEMPLATE_CATALOG_PRODUCT_DESCRIPTION');

            if ($title_template) {
                $row['meta_title'] = str_replace(['%PRODUCT_TITLE%', '%CATEGORY_TITLE%', '%MANUFACTURER_TITLE%'], [$row['name'], $category->name, $this->manufacturer_name], $title_template);
            }

            if ($description_template) {
                $row['meta_description'] = str_replace(['%PRODUCT_TITLE%', '%CATEGORY_TITLE%', '%MANUFACTURER_TITLE%'], [$row['name'], $category->name, $this->manufacturer_name], $description_template);
            }
        }


        return $result;
    }

    public function getSeriesAvailableModelsAttributes($id_lang = null)
    {
        if ($id_lang === null) {
            $id_lang = Context::getContext()->language->id;
        }

        $sql = 'SELECT DISTINCT a.id_attribute_group, agl.name as group_name, ag.hide
            FROM `ps_product_attribute` pa
            LEFT JOIN `ps_product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)
            LEFT JOIN `ps_attribute` a ON (a.id_attribute = pac.id_attribute)
            LEFT JOIN `ps_attribute_group` ag ON (a.id_attribute_group = ag.id_attribute_group)
            LEFT JOIN `ps_attribute_group_lang` agl ON (agl.id_attribute_group = a.id_attribute_group)
            WHERE id_product = ' . $this->id;

        $attr_groups = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($attr_groups as &$group) {
            if ($group['id_attribute_group']) {
                $sql_models_attributes = 'SELECT pa.id_product_attribute, a.id_attribute_group, agl.name as group_name, pac.id_attribute, al.name as attribute_name
                    FROM `ps_product_attribute` pa
                    LEFT JOIN `ps_product_attribute_combination` pac ON (pac.id_product_attribute = pa.id_product_attribute)
                    LEFT JOIN `ps_attribute` a ON (a.id_attribute = pac.id_attribute)
                    LEFT JOIN `ps_attribute_lang` al ON (al.id_attribute = a.id_attribute)
                    LEFT JOIN `ps_attribute_group_lang` agl ON (agl.id_attribute_group = a.id_attribute_group)
                    WHERE pa.id_product = ' . $this->id . '
                        AND a.id_attribute_group = ' . $group['id_attribute_group'];

                $group['models_attributes'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_models_attributes);
            }
        }

        return $attr_groups;
    }
}
