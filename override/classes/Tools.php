<?php

class Tools extends ToolsCore
{
    /**
    * Return price with currency sign for a given product
    *
    * @param float $price Product price
    * @param object|array $currency Current currency (object, id_currency, NULL => context currency)
    * @return string Price correctly formated (sign, decimal separator...)
    */
    public static function displayPrice($price, $currency = null, $no_utf8 = false, Context $context = null)
    {
        if (!is_numeric($price)) {
            return $price;
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if ($currency === null) {
            $currency = $context->currency;
        }
        // if you modified this function, don't forget to modify the Javascript function formatCurrency (in tools.js)
        elseif (is_int($currency)) {
            $currency = Currency::getCurrencyInstance((int)$currency);
        }

        if (is_array($currency)) {
            $c_char = $currency['sign'];
            $c_format = $currency['format'];
            $c_decimals = (int)$currency['decimals'] * _PS_PRICE_DISPLAY_PRECISION_;
            $c_blank = $currency['blank'];
        } elseif (is_object($currency)) {
            $c_char = $currency->sign;
            $c_format = $currency->format;
            $c_decimals = (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_;
            $c_blank = $currency->blank;
        } else {
            return false;
        }
        $c_char = '<span>' . $c_char . '</span>';

        $blank = ($c_blank ? ' ' : '');
        $ret = 0;
        if (($is_negative = ($price < 0))) {
            $price *= -1;
        }
        $price = Tools::ps_round($price, $c_decimals);

        /*
        * If the language is RTL and the selected currency format contains spaces as thousands separator
        * then the number will be printed in reverse since the space is interpreted as separating words.
        * To avoid this we replace the currency format containing a space with the one containing a comma (,) as thousand
        * separator when the language is RTL.
        *
        * TODO: This is not ideal, a currency format should probably be tied to a language, not to a currency.
        */
        if (($c_format == 2) && ($context->language->is_rtl == 1)) {
            $c_format = 4;
        }

        switch ($c_format) {
            /* X 0,000.00 */
            case 1:
                $ret = $c_char.$blank.number_format($price, $c_decimals, '.', ',');
                break;
            /* 0 000,00 X*/
            case 2:
                $ret = number_format($price, $c_decimals, ',', ' ').$blank.$c_char;
                break;
            /* X 0.000,00 */
            case 3:
                $ret = $c_char.$blank.number_format($price, $c_decimals, ',', '.');
                break;
            /* 0,000.00 X */
            case 4:
                $ret = number_format($price, $c_decimals, '.', ',').$blank.$c_char;
                break;
            /* X 0'000.00  Added for the switzerland currency */
            case 5:
                $ret = number_format($price, $c_decimals, '.', "'").$blank.$c_char;
                break;
        }
        if ($is_negative) {
            $ret = '-'.$ret;
        }
        if ($no_utf8) {
            return str_replace('€', chr(128), $ret);
        }
        return $ret;
    }

    /**
    * Get the user's journey
    *
    * @param int $id_category Category ID
    * @param string $path Path end
    * @param bool $linkOntheLastItem Put or not a link on the current category
    * @param string [optionnal] $categoryType defined what type of categories is used (products or cms)
    */
    public static function getPath($id_category, $path = '', $link_on_the_item = false, $category_type = 'products', Context $context = null, $manufacturer_id = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $id_category = (int)$id_category;
        if ($id_category == 1) {
            return '<span class="navigation_end">'.$path.'</span>';
        }

        $pipe = Configuration::get('PS_NAVIGATION_PIPE');
        if (empty($pipe)) {
            $pipe = '>';
        }

        $full_path = '';
        if ($category_type === 'products') {
            $interval = Category::getInterval($id_category);
            $id_root_category = $context->shop->getCategory();
            $interval_root = Category::getInterval($id_root_category);
            if ($interval) {
                $sql = 'SELECT c.id_category, cl.name, cl.link_rewrite
                        FROM '._DB_PREFIX_.'category c
                        LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category'.Shop::addSqlRestrictionOnLang('cl').')
                        '.Shop::addSqlAssociation('category', 'c').'
                        WHERE c.nleft <= '.$interval['nleft'].'
                            AND c.nright >= '.$interval['nright'].'
                            AND c.nleft >= '.$interval_root['nleft'].'
                            AND c.nright <= '.$interval_root['nright'].'
                            AND cl.id_lang = '.(int)$context->language->id.'
                            AND c.active = 1
                            AND c.level_depth > '.(int)$interval_root['level_depth'].'
                        ORDER BY c.level_depth ASC';
                $categories = Db::getInstance()->executeS($sql);

                $n = 1;
                $n_categories = count($categories);
                foreach ($categories as $category) {
                    $full_path .=
                    (($n < $n_categories || $link_on_the_item) ? '<a href="'.Tools::safeOutput($context->link->getCategoryLink((int)$category['id_category'], $category['link_rewrite'])).'" title="'.htmlentities($category['name'], ENT_NOQUOTES, 'UTF-8').'" data-gg="">' : '').
                    htmlentities($category['name'], ENT_NOQUOTES, 'UTF-8').
                    (($n < $n_categories || $link_on_the_item) ? '</a>' : '').
                    (($n++ != $n_categories || !empty($path)) ? '<span class="navigation-pipe">'.$pipe.'</span>' : '');
                }

                if ($manufacturer_id && $manufacturer = new Manufacturer($manufacturer_id, (int)$context->language->id)) {
                    $full_path .= '<a href="' . Tools::safeOutput($context->link->getManufacturerLink($manufacturer)) . '">' . $manufacturer->name . '</a>' . ((!empty($path)) ? '<span class="navigation-pipe">' . $pipe . '</span>' : '');
                }

                return $full_path.$path;
            }
        } elseif ($category_type === 'CMS') {
            $category = new CMSCategory($id_category, $context->language->id);
            if (!Validate::isLoadedObject($category)) {
                die(Tools::displayError());
            }
            $category_link = $context->link->getCMSCategoryLink($category);

            if ($path != $category->name) {
                $full_path .= '<a href="'.Tools::safeOutput($category_link).'" data-gg="">'.htmlentities($category->name, ENT_NOQUOTES, 'UTF-8').'</a><span class="navigation-pipe">'.$pipe.'</span>'.$path;
            } else {
                $full_path = ($link_on_the_item ? '<a href="'.Tools::safeOutput($category_link).'" data-gg="">' : '').htmlentities($path, ENT_NOQUOTES, 'UTF-8').($link_on_the_item ? '</a>' : '');
            }

            return Tools::getPath($category->id_parent, $full_path, $link_on_the_item, $category_type);
        }
    }
}
