<?php

class Search extends SearchCore
{
    public static function exactSearch($id_lang, $expr, $page_number = 1, $page_size = 1, $order_by = 'position',
                                       $order_way = 'desc', $ajax = false, $use_cookie = true, Context $context = null)
    {
        $context = Context::getContext();
        $id_lang = (int)$context->language->id;

        $search_results = Search::getModelsByFulltextIndexes($expr, $id_lang, $context, $page_number, $page_size);

        return $search_results;
    }

    public static function getModelsByFulltextIndexes($query, $id_lang, $context, $page_number, $page_size)
    {
        $result_array = [
            'result' => [],
            'total' => 0,
        ];

        // Prepare words for search query in different mode
        $words = Search::sanitize($query, $id_lang, false, $context->language->iso_code);
        $boolean_mode_words = array_reduce(explode(' ', $words), function($carry, $word) {
            return $carry .= '+' . $word . '* ';
        });

        $search_db_instance = Db::getSearchInstance();

        // Count total matches
        $sql_count = "
            SELECT
                count(*) as count
            FROM
                ps_search_indexes
            WHERE
                MATCH (text) AGAINST ('{$boolean_mode_words}' IN BOOLEAN MODE)
                AND MATCH (text) AGAINST ('{$boolean_mode_words}')
            ORDER BY
                MATCH (text) AGAINST ('{$boolean_mode_words}' IN BOOLEAN MODE) DESC,
                MATCH (text) AGAINST ('{$words}' WITH QUERY EXPANSION) DESC,
                MATCH (text) AGAINST ('{$words}') DESC";

        $total_res = $search_db_instance->executeS($sql_count);

        if (count($total_res) && $total_res[0]['count'] == 0) {
            return $result_array;
        }

        $result_array['total'] = $total_res[0]['count'];

        // Set current page options
        if ($page_number < 1) {
            $page_number = 1;
        }

        if ($page_size < 1) {
            $page_size = 1;
        }



        $sql = "
            SELECT
                id_product, id_product_attribute, text
            FROM
                ps_search_indexes
            WHERE
                MATCH (text) AGAINST ('{$boolean_mode_words}' IN BOOLEAN MODE)
                AND MATCH (text) AGAINST ('{$boolean_mode_words}')
            ORDER BY
                MATCH (text) AGAINST ('{$boolean_mode_words}' IN BOOLEAN MODE) DESC,
                MATCH (text) AGAINST ('{$words}' WITH QUERY EXPANSION) DESC,
                MATCH (text) AGAINST ('{$words}') DESC
            LIMIT ".(int)(($page_number - 1) * $page_size).",".(int)$page_size;

        $results = $search_db_instance->executeS($sql);

        if (! is_array($results) || ! count($results)) {
            return $result_array;
        }

        // d($results);
        foreach ($results as $result_model) {
            if ($result_model['id_product_attribute'] != 0) {
                $id_attribute = (int)$result_model['id_product_attribute'];

                $series = new Series((int)$result_model['id_product']);
                $models = $series->getModels($id_lang, false, 0, 0, [$id_attribute]);

                if (count($models)) {
                    $result_array['result'][] = $models[0];
                }
            } else {
                // Todo: incapsulate product properties getter to class method
                $sql = 'SELECT p.*, product_shop.*,lpi.price_min, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
                    pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
                    image_shop.`id_image` id_image, il.`legend`, m.`name` manufacturer_name,
                    DATEDIFF(
                        p.`date_add`,
                        DATE_SUB(
                            "'.date('Y-m-d').' 00:00:00",
                            INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
                        )
                    ) > 0 new
                    FROM '._DB_PREFIX_.'product p
                    '.Shop::addSqlAssociation('product', 'p').'
                    INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                        p.`id_product` = pl.`id_product`
                        AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
                    )
                    '.(Combination::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                    ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')':'').'
                    '.Product::sqlStock('p', 0).'
                    LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
                    LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                        ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
                    LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
                    LEFT JOIN '._DB_PREFIX_.'layered_price_index lpi ON (lpi.id_product = p.id_product)

                    WHERE p.`id_product` = '.(int)$result_model['id_product'];

                $product_row = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);

                if ($product_row && count($product_row)) {
                    $result_array['result'][] = Product::getProductsProperties((int)$id_lang, $product_row)[0];
                }
            }
        }

        return $result_array;
    }
}
