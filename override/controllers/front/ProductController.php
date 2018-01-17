<?php

class ProductController extends ProductControllerCore
{
    public function setMedia()
    {
        parent::setMedia();

        $this->addjqueryPlugin('trunk8');
    }

    public function canonicalRedirection($canonical_url = '')
    {
        if (Tools::getValue('live_edit')) {
            return;
        }
        if (Validate::isLoadedObject($this->product)) {
            FrontController::canonicalRedirection($this->context->link->getProductPageLink($this->product));
        }
    }

    public function initContent()
    {
        parent::initContent();

        $manufacturer = new Manufacturer((int)$this->product->id_manufacturer, $this->context->language->id);

        $manufacturer_image = $this->context->language->iso_code.'-default';

        if (file_exists(_PS_MANU_IMG_DIR_.$this->product->id_manufacturer.'-'.ImageType::getFormatedName('medium').'.jpg'))
            $manufacturer_image = $this->product->id_manufacturer;

        $this->context->smarty->assign(array(
            'manufacturer_image' => $manufacturer_image,
            'manufacturer_country_iso_code' => $manufacturer->getAddressCountryIsoCode($this->context->language->id),
        ));
        //allow tags only on spinning pages 13 - spinning id
        if ($this->category->id == 13) {
           $this->context->smarty->assign(array(
               'allow_tags' => true
           )); 
        }
        if ($this->product->is_series) {
            $series = new Series($this->product->id, true);
            $id_tag = Tools::getValue('id_tag');
            $models = $series->getModels(null, false, 0, 0, array(), $id_tag);
            $models = Product::getProductsProperties($this->context->language->id, $models);

            $models_array = array();

            foreach ($models as $model) {
                $model['gift'] = $this->product->getFreeGifts($this->context->language->id, $model['id_product_attribute'], $model['price']);

                $model['free_delivery'] = $this->product->isFreeDelivery($this->context->language->id, $model['id_product_attribute'], $model['price']);

                $models_array[$model['id_product_attribute']] = $model;
            }

            $attr_groups = $series->getSeriesAvailableModelsAttributes();

            $meta_tags = Meta::getProductMetas($this->product->id, $this->context->language->id, '');

            $og_image = $this->context->link->getImageLink($model['link_rewrite'],$model['id_image']);
            $this->context->smarty->assign(array(
                'og_image' => $og_image
            ));

            $this->context->smarty->assign(array(
                'models' => $models_array,
                'attr_groups' => $attr_groups,
                'meta_title' => $meta_tags['meta_title'],
                'meta_description' => $meta_tags['meta_description'],
            ));
        } else {
            $gift = $this->product->getFreeGifts($this->context->language->id, 0, $model['price']);

            if ($gift && count($gift)) {
                $this->context->smarty->assign(array(
                    'gift' => $gift[0],
                    'free_delivery' => $this->product->isFreeDelivery($this->context->language->id, 0, $model['price']),
                ));
            }
        }
    }

    /**
     * Initialize product controller
     * @see FrontController::init()
     */
    public function init()
    {
        FrontController::init();

        if ($id_product = (int)Tools::getValue('id_product')) {
            $this->product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
        }

        if (!Validate::isLoadedObject($this->product)) {
            header('HTTP/1.1 404 Not Found');
            header('Status: 404 Not Found');
            $this->errors[] = Tools::displayError('Product not found');
        } else {
            $this->canonicalRedirection();
            /*
             * If the product is associated to the shop
             * and is active or not active but preview mode (need token + file_exists)
             * allow showing the product
             * In all the others cases => 404 "Product is no longer available"
             */
            if (!$this->product->isAssociatedToShop() || !$this->product->active) {
                if (Tools::getValue('adtoken') == Tools::getAdminToken('AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)Tools::getValue('id_employee')) && $this->product->isAssociatedToShop()) {
                    // If the product is not active, it's the admin preview mode
                    $this->context->smarty->assign('adminActionDisplay', true);
                } else {
                    $this->context->smarty->assign('adminActionDisplay', false);
                    if (!$this->product->id_product_redirected || $this->product->id_product_redirected == $this->product->id) {
                        $this->product->redirect_type = '404';
                    }

                    switch ($this->product->redirect_type) {
                        case '301':
                            header('HTTP/1.1 301 Moved Permanently');
                            header('Location: '.$this->context->link->getProductPageLink($this->product->id_product_redirected));
                            exit;
                        break;
                        case '302':
                            header('HTTP/1.1 302 Moved Temporarily');
                            header('Cache-Control: no-cache');
                            header('Location: '.$this->context->link->getProductPageLink($this->product->id_product_redirected));
                            exit;
                        break;
                        case '404':
                        default:
                            header('HTTP/1.1 404 Not Found');
                            header('Status: 404 Not Found');
                            $this->errors[] = Tools::displayError('This product is no longer available.');
                        break;
                    }
                }
            } elseif (!$this->product->checkAccess(isset($this->context->customer->id) && $this->context->customer->id ? (int)$this->context->customer->id : 0)) {
                header('HTTP/1.1 403 Forbidden');
                header('Status: 403 Forbidden');
                $this->errors[] = Tools::displayError('You do not have access to this product.');
            } else {
                // Load category
                $id_category = false;
                if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == Tools::secureReferrer($_SERVER['HTTP_REFERER']) // Assure us the previous page was one of the shop
                    && preg_match('~^.*(?<!\/content)\/([0-9]+)\-(.*[^\.])|(.*)id_(category|product)=([0-9]+)(.*)$~', $_SERVER['HTTP_REFERER'], $regs)) {
                    // If the previous page was a category and is a parent category of the product use this category as parent category
                    $id_object = false;
                    if (isset($regs[1]) && is_numeric($regs[1])) {
                        $id_object = (int)$regs[1];
                    } elseif (isset($regs[5]) && is_numeric($regs[5])) {
                        $id_object = (int)$regs[5];
                    }
                    if ($id_object) {
                        $referers = array($_SERVER['HTTP_REFERER'],urldecode($_SERVER['HTTP_REFERER']));
                        if (in_array($this->context->link->getCategoryLink($id_object), $referers)) {
                            $id_category = (int)$id_object;
                        } elseif (isset($this->context->cookie->last_visited_category) && (int)$this->context->cookie->last_visited_category && in_array($this->context->link->getProductLink($id_object), $referers)) {
                            $id_category = (int)$this->context->cookie->last_visited_category;
                        }
                    }
                }
                if (!$id_category || !Category::inShopStatic($id_category, $this->context->shop) || !Product::idIsOnCategoryId((int)$this->product->id, array('0' => array('id_category' => $id_category)))) {
                    $id_category = (int)$this->product->id_category_default;
                }
                $this->category = new Category((int)$id_category, (int)$this->context->cookie->id_lang);
                if (isset($this->context->cookie) && isset($this->category->id_category) && !(Module::isInstalled('blockcategories') && Module::isEnabled('blockcategories'))) {
                    $this->context->cookie->last_visited_category = (int)$this->category->id_category;
                }
            }
        }
    }

    /**
     * Assign template vars related to category
     */
    protected function assignCategory()
    {
        // Assign category to the template
        if ($this->category !== false && Validate::isLoadedObject($this->category) && $this->category->inShop() && $this->category->isAssociatedToShop()) {
            $path = Tools::getPath($this->category->id, $this->product->name, true, 'products', null, $this->product->id_manufacturer);
        } elseif (Category::inShopStatic($this->product->id_category_default, $this->context->shop)) {
            $this->category = new Category((int)$this->product->id_category_default, (int)$this->context->language->id);
            if (Validate::isLoadedObject($this->category) && $this->category->active && $this->category->isAssociatedToShop()) {
                $path = Tools::getPath((int)$this->product->id_category_default, $this->product->name, false, 'products', null, $this->product->id_manufacturer);
            }
        }
        if (!isset($path) || !$path) {
            $path = Tools::getPath((int)$this->context->shop->id_category, $this->product->name, false, 'products', null, $this->product->id_manufacturer);
        }

        $sub_categories = array();
        if (Validate::isLoadedObject($this->category)) {
            $sub_categories = $this->category->getSubCategories($this->context->language->id, true);

            // various assignements before Hook::exec
            $this->context->smarty->assign(array(
                'path' => $path,
                'category' => $this->category,
                'subCategories' => $sub_categories,
                'id_category_current' => (int)$this->category->id,
                'id_category_parent' => (int)$this->category->id_parent,
                'return_category_name' => Tools::safeOutput($this->category->getFieldByLang('name')),
                'categories' => Category::getHomeCategories($this->context->language->id, true, (int)$this->context->shop->id)
            ));
        }
        $this->context->smarty->assign(array('HOOK_PRODUCT_FOOTER' => Hook::exec('displayFooterProduct', array('product' => $this->product, 'category' => $this->category))));
    }

    /**
     * Assign template vars related to attribute groups and colors
     */
    protected function assignAttributesGroups()
    {
        $colors = array();
        $groups = array();

        // @todo (RM) should only get groups and not all declination ?
        $attributes_groups = $this->product->getAttributesGroups($this->context->language->id);
        if (is_array($attributes_groups) && $attributes_groups) {
            $combination_images = $this->product->getCombinationImages($this->context->language->id);
            $combination_prices_set = array();
            foreach ($attributes_groups as $k => $row) {
                // Color management
                if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) {
                    $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                    $colors[$row['id_attribute']]['name'] = $row['attribute_name'];
                    if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                        $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                    }
                    $colors[$row['id_attribute']]['attributes_quantity'] += (int)$row['quantity'];
                }
                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = array(
                        'group_name' => $row['group_name'],
                        'name' => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default' => -1,
                    );
                }

                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int)$row['id_attribute'];
                }
                if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
                }
                $groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];

                $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
                $combinations[$row['id_product_attribute']]['attributes'][] = (int)$row['id_attribute'];
                $combinations[$row['id_product_attribute']]['price'] = (float)$row['price'];

                // Call getPriceStatic in order to set $combination_specific_price
                if (!isset($combination_prices_set[(int)$row['id_product_attribute']])) {
                    Product::getPriceStatic((int)$this->product->id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
                    $combination_prices_set[(int)$row['id_product_attribute']] = true;
                    $combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
                }
                $combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
                $combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
                $combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
                // $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
                $combinations[$row['id_product_attribute']]['reference'] = '';
                $combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
                $combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
                if ($row['available_date'] != '0000-00-00' && Validate::isDate($row['available_date'])) {
                    $combinations[$row['id_product_attribute']]['available_date'] = $row['available_date'];
                    $combinations[$row['id_product_attribute']]['date_formatted'] = Tools::displayDate($row['available_date']);
                } else {
                    $combinations[$row['id_product_attribute']]['available_date'] = $combinations[$row['id_product_attribute']]['date_formatted'] = '';
                }

                if (!isset($combination_images[$row['id_product_attribute']][0]['id_image']) &&
                    $this->context->smarty->tpl_vars['cover']->value
                ) {
                    $cover = $this->context->smarty->tpl_vars['cover']->value;
                    $combination_images[$row['id_product_attribute']][0] = array(
                        'id_image' => $cover['id_image'],
                        'id_product_attribute' => $row['id_product_attribute'],
                        'legend' => $cover['legend']
                    );
                } else {
                    $combinations[$row['id_product_attribute']]['id_image'] = -1;
                }

                $combinations[$row['id_product_attribute']]['id_image'] = $id_image = (int)$combination_images[$row['id_product_attribute']][0]['id_image'];
                if ($row['default_on']) {
                    if (isset($this->context->smarty->tpl_vars['cover']->value)) {
                        $current_cover = $this->context->smarty->tpl_vars['cover']->value;
                    }

                    if (is_array($combination_images[$row['id_product_attribute']])) {
                        foreach ($combination_images[$row['id_product_attribute']] as $tmp) {
                            if ($tmp['id_image'] == $current_cover['id_image']) {
                                $combinations[$row['id_product_attribute']]['id_image'] = $id_image = (int)$tmp['id_image'];
                                break;
                            }
                        }
                    }

                    if ($id_image > 0) {
                        if (isset($this->context->smarty->tpl_vars['images']->value)) {
                            $product_images = $this->context->smarty->tpl_vars['images']->value;
                        }
                        if (isset($product_images) && is_array($product_images) && isset($product_images[$id_image])) {
                            $product_images[$id_image]['cover'] = 1;
                            $this->context->smarty->assign('mainImage', $product_images[$id_image]);
                            if (count($product_images)) {
                                $this->context->smarty->assign('images', $product_images);
                            }
                        }
                        if (isset($this->context->smarty->tpl_vars['cover']->value)) {
                            $cover = $this->context->smarty->tpl_vars['cover']->value;
                        }
                        if (isset($cover) && is_array($cover) && isset($product_images) && is_array($product_images)) {
                            $product_images[$cover['id_image']]['cover'] = 0;
                            if (isset($product_images[$id_image])) {
                                $cover = $product_images[$id_image];
                            }
                            $cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$id_image) : (int)$id_image);
                            $cover['id_image_only'] = (int)$id_image;
                            $this->context->smarty->assign('cover', $cover);
                        }
                    }
                }
            }

            // wash attributes list (if some attributes are unavailables and if allowed to wash it)
            if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                foreach ($groups as &$group) {
                    foreach ($group['attributes_quantity'] as $key => &$quantity) {
                        if ($quantity <= 0) {
                            unset($group['attributes'][$key]);
                        }
                    }
                }

                foreach ($colors as $key => $color) {
                    if ($color['attributes_quantity'] <= 0) {
                        unset($colors[$key]);
                    }
                }
            }
            foreach ($combinations as $id_product_attribute => $comb) {
                $attribute_list = '';
                foreach ($comb['attributes'] as $id_attribute) {
                    $attribute_list .= '\''.(int)$id_attribute.'\',';
                }
                $attribute_list = rtrim($attribute_list, ',');
                $combinations[$id_product_attribute]['list'] = $attribute_list;
            }

            $this->context->smarty->assign(array(
                'groups' => $groups,
                'colors' => (count($colors)) ? $colors : false,
                'combinations' => $combinations,
                'combinationImages' => $combination_images
            ));
        }
    }
}
