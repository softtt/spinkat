<?php

class Link extends LinkCore
{
    public function getSeriesLink($series, $alias = null, $id_lang = null, $selected_filters = null, $id_shop = null, $relative_protocol = false, $category = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

        if (!is_object($series)) {
            $series = new Series($series, false, $id_lang);
        }

        // Set available keywords
        $params = array();
        $params['id'] = $series->id;
        $params['rewrite'] = (!$alias) ? $series->link_rewrite : $alias;
        $params['meta_keywords'] =    Tools::str2url($series->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($series->getFieldByLang('meta_title'));
        // Selected filters is used by the module blocklayered
        $selected_filters = is_null($selected_filters) ? '' : $selected_filters;

        if (empty($selected_filters)) {
            $rule = 'series_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selected_filters;
        }

        $dispatcher = Dispatcher::getInstance();

        if ($dispatcher->hasKeyword('series_rule', $id_lang, 'category', $id_shop)) {
            $params['category'] = (!is_null($series->category) && !empty($series->category)) ? $series->category : $category;
        }

        if ($dispatcher->hasKeyword('series_rule', $id_lang, 'id_category', $id_shop)) {
            $params['id_category'] = (!is_null($series->id_category_default) && !empty($series->id_category_default)) ? $series->id_category_default : $category->id;
        }

        return $url.Dispatcher::getInstance()->createUrl($rule, $id_lang, $params, $this->allow, '', $id_shop);
    }


    public function getProductLink($product, $alias = null, $category = null, $ean13 = null, $id_lang = null, $id_shop = null, $ipa = 0, $force_routes = false, $relative_protocol = false, $add_anchor = false, $get_product_link = false)
    {
        if (!is_object($product)) {
            if (is_array($product) && isset($product['id_product'])) {
                $product = new Product($product['id_product'], false, $id_lang, $id_shop);
            } elseif ((int)$product) {
                $product = new Product((int)$product, false, $id_lang, $id_shop);
            } else {
                throw new PrestaShopException('Invalid product vars');
            }
        }

        if ($product->is_series && !$get_product_link) {
            return self::getSeriesLink($product, $alias, $id_lang, null, $id_shop, $relative_protocol, $category);
        } else {
            return parent::getProductLink($product, $alias, $category, $ean13, $id_lang, $id_shop, $ipa, $force_routes, $relative_protocol, $add_anchor);
        }
    }

    // Get product link page, not Series
    public function getProductPageLink($product, $alias = null, $category = null) {
        return parent::getProductLink($product, $alias, $category, null, null, null, 0, false, false, false);
    }

    public function getTagLink($tag, $alias = null, $id_lang = null, $id_shop = null)
    {
        $url = $this->getBaseLink($id_shop, null, false).$this->getLangLink($id_lang, null, $id_shop);

        if (!is_object($tag)) {
            if (is_array($tag) && isset($tag['id'])) {
                $tag = new Tag($tag['id']);
            } elseif ((int)$tag) {
                $tag = new Tag((int)$tag);
            } else {
                throw new PrestaShopException('Invalid tag vars');
            }
        }

        $params = array();
        $params['id'] = $tag->id;
        $params['rewrite'] = $tag->link_rewrite;

        $dispatcher = Dispatcher::getInstance();

        return $url.$dispatcher->createUrl('tags_rule',$id_lang,$params);
    }
}
