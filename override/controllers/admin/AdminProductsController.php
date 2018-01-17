<?php
class AdminProductsController extends AdminProductsControllerCore
{
    public function __construct()
    {
        parent::__construct();

        unset($this->available_tabs_lang['Customization']);
        unset($this->available_tabs_lang['Suppliers']);
        unset($this->available_tabs['Customization']);
        unset($this->available_tabs['Suppliers']);
        unset($this->available_tabs['VirtualProduct']);
        unset($this->available_tabs['Shipping']);
    }

    /**
     * @param Product $product
     * @throws Exception
     * @throws SmartyException
     */
    /*
    * module: ext_sales_page
    * date: 2015-10-31 20:19:30
    * version: 1.1.0
    */
    public function initFormInformations($product)
    {
        $product->show_on_sales_page = $this->getFieldValue($product, 'show_on_sales_page');
        $product->is_series = $this->getFieldValue($product, 'is_series');
        $product->is_new = $this->getFieldValue($product, 'is_new');
        $product->show_gift_label = $this->getFieldValue($product, 'show_gift_label');
        parent::initFormInformations($product);
    }

    /**
     * @param Product|ObjectModel $object
     * @param string              $table
     */
    /*
    * module: ext_sales_page
    * date: 2015-10-31 20:19:30
    * version: 1.1.0
    */
    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);

        if ($this->isTabSubmitted('Informations')) {
            $object->is_series = (int)Tools::getValue('is_series');
            $object->is_new = (int)Tools::getValue('is_new');
            $object->show_gift_label = (int)Tools::getValue('show_gift_label');
        }

        if ($this->isTabSubmitted('Prices')) {
            $object->show_on_sales_page = (int)Tools::getValue('show_on_sales_page');
        }
    }

    public function processProductAttribute()
    {
        // Don't process if the combination fields have not been submitted
        if (!Combination::isFeatureActive() || !Tools::getValue('attribute_combination_list')) {
            return;
        }

        if (Validate::isLoadedObject($product = $this->object)) {
            if ($this->isProductFieldUpdated('attribute_price') && (!Tools::getIsset('attribute_price') || Tools::getIsset('attribute_price') == null)) {
                $this->errors[] = Tools::displayError('The price attribute is required.');
            }
            if (!Tools::getIsset('attribute_combination_list') || Tools::isEmpty(Tools::getValue('attribute_combination_list'))) {
                $this->errors[] = Tools::displayError('You must add at least one attribute.');
            }

            $array_checks = array(
                'reference' => 'isReference',
                'supplier_reference' => 'isReference',
                'location' => 'isReference',
                'ean13' => 'isEan13',
                'upc' => 'isUpc',
                'wholesale_price' => 'isPrice',
                'price' => 'isPrice',
                'ecotax' => 'isPrice',
                'quantity' => 'isInt',
                'weight' => 'isUnsignedFloat',
                'unit_price_impact' => 'isPrice',
                'default_on' => 'isBool',
                'minimal_quantity' => 'isUnsignedInt',
                'available_date' => 'isDateFormat'
            );
            foreach ($array_checks as $property => $check) {
                if (Tools::getValue('attribute_'.$property) !== false && !call_user_func(array('Validate', $check), Tools::getValue('attribute_'.$property))) {
                    $this->errors[] = sprintf(Tools::displayError('Field %s is not valid'), $property);
                }
            }

            if (!count($this->errors)) {
                if (!isset($_POST['attribute_wholesale_price'])) {
                    $_POST['attribute_wholesale_price'] = 0;
                }
                if (!isset($_POST['attribute_price_impact'])) {
                    $_POST['attribute_price_impact'] = 0;
                }
                if (!isset($_POST['attribute_weight_impact'])) {
                    $_POST['attribute_weight_impact'] = 0;
                }
                if (!isset($_POST['attribute_ecotax'])) {
                    $_POST['attribute_ecotax'] = 0;
                }
                if (Tools::getValue('attribute_default')) {
                    $product->deleteDefaultAttributes();
                }

                // Change existing one
                if (($id_product_attribute = (int)Tools::getValue('id_product_attribute'))) {
                    if ($this->tabAccess['edit'] === '1') {
                        if ($this->isProductFieldUpdated('available_date_attribute') && (Tools::getValue('available_date_attribute') != '' &&!Validate::isDateFormat(Tools::getValue('available_date_attribute')))) {
                            $this->errors[] = Tools::displayError('Invalid date format.');
                        } else {
                            $product->updateAttribute((int)$id_product_attribute,
                                $this->isProductFieldUpdated('attribute_wholesale_price') ? Tools::getValue('attribute_wholesale_price') : null,
                                $this->isProductFieldUpdated('attribute_price_impact') ? Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact') : null,
                                $this->isProductFieldUpdated('attribute_weight_impact') ? Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact') : null,
                                $this->isProductFieldUpdated('attribute_unit_impact') ? Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact') : null,
                                $this->isProductFieldUpdated('attribute_ecotax') ? Tools::getValue('attribute_ecotax') : null,
                                Tools::getValue('id_image_attr'),
                                Tools::getValue('attribute_reference'),
                                Tools::getValue('attribute_ean13'),
                                $this->isProductFieldUpdated('attribute_default') ? Tools::getValue('attribute_default') : null,
                                Tools::getValue('attribute_location'),
                                Tools::getValue('attribute_upc'),
                                $this->isProductFieldUpdated('attribute_minimal_quantity') ? Tools::getValue('attribute_minimal_quantity') : null,
                                $this->isProductFieldUpdated('available_date_attribute') ? Tools::getValue('available_date_attribute') : null,
                                false,
                                array(),
                                Tools::getValue('attribute_title', ''),
                                Tools::getValue('attribute_short_description', ''),
                                Tools::getValue('attribute_long_description', ''),
                                Tools::getValue('attribute_video', ''),
                                Tools::getValue('attribute_top_description', ''),
                                Tools::getValue('attribute_is_new', ''),
                                Tools::getValue('attribute_hide', ''),
                                Tools::getValue('combination_tags', '')
                            );
                            StockAvailable::setProductDependsOnStock((int)$product->id, $product->depends_on_stock, null, (int)$id_product_attribute);
                            StockAvailable::setProductOutOfStock((int)$product->id, $product->out_of_stock, null, (int)$id_product_attribute);
                        }
                    } else {
                        $this->errors[] = Tools::displayError('You do not have permission to add this.');
                    }
                }
                // Add new
                else {
                    if ($this->tabAccess['add'] === '1') {

                        $id_product_attribute = $product->addCombinationEntity(
                            Tools::getValue('attribute_wholesale_price'),
                            Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact'),
                            Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact'),
                            Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact'),
                            Tools::getValue('attribute_ecotax'),
                            0,
                            Tools::getValue('id_image_attr'),
                            Tools::getValue('attribute_reference'),
                            null,
                            Tools::getValue('attribute_ean13'),
                            Tools::getValue('attribute_default'),
                            Tools::getValue('attribute_location'),
                            Tools::getValue('attribute_upc'),
                            Tools::getValue('attribute_minimal_quantity'),
                            array(),
                            Tools::getValue('available_date_attribute'),
                            Tools::getValue('attribute_title', ''),
                            Tools::getValue('attribute_short_description', ''),
                            Tools::getValue('attribute_long_description', ''),
                            Tools::getValue('attribute_video', ''),
                            Tools::getValue('attribute_top_description', ''),
                            Tools::getValue('attribute_is_new', ''),
                            Tools::getValue('attribute_hide', ''),
                            Tools::getValue('combination_tags', '')
                            );
                        StockAvailable::setProductDependsOnStock((int)$product->id, $product->depends_on_stock, null, (int)$id_product_attribute);
                        StockAvailable::setProductOutOfStock((int)$product->id, $product->out_of_stock, null, (int)$id_product_attribute);
                    }
                }
                if (!count($this->errors)) {
                    $combination = new Combination((int)$id_product_attribute);
                    $combination->setAttributes(Tools::getValue('attribute_combination_list'));

                    // images could be deleted before
                    $id_images = Tools::getValue('id_image_attr');
                    if (!empty($id_images)) {
                        $combination->setImages($id_images);
                    }

                    $product->checkDefaultAttributes();
                    if (Tools::getValue('attribute_default')) {
                        Product::updateDefaultAttribute((int)$product->id);
                        if (isset($id_product_attribute)) {
                            $product->cache_default_attribute = (int)$id_product_attribute;
                        }

                        if ($available_date = Tools::getValue('available_date_attribute')) {
                            $product->setAvailableDate($available_date);
                        } else {
                            $product->setAvailableDate();
                        }
                    }
                }
            }
        }
    }

    public function ajaxProcessEditProductAttribute()
    {
        if ($this->tabAccess['edit'] === '1') {
            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $tags = Tag::getCombinationTags($id_product_attribute);
            $tags = implode(',',$tags);
            if ($id_product && Validate::isUnsignedId($id_product) && Validate::isLoadedObject($product = new Product((int)$id_product))) {
                $combinations = $product->getAttributeCombinationsById($id_product_attribute, $this->context->language->id);
                foreach ($combinations as $key => $combination) {
                    $combinations[$key]['attributes'][] = array($combination['group_name'], $combination['attribute_name'], $combination['id_attribute']);
                    $combinations[$key]['tags'] = $tags;
                }

                die(Tools::jsonEncode($combinations));
            }
        }
    }

    public function ajaxProcessCloneProductAttribute()
    {

        if (!Combination::isFeatureActive()) {
            return;
        }

            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $combination = new Combination((int)$id_product_attribute);

            $values = array_map(function($item){
                return $item['id'];
            },$combination->getWsProductOptionValues());

            $images = array_map(function($item){
                return $item['id'];
            },$combination->getWsImages());

            $price = $combination->getPrice($id_product_attribute);
            if ($id_product && Validate::isUnsignedId($id_product) && Validate::isLoadedObject($product = new Product($id_product))) {
                if (($depends_on_stock = StockAvailable::dependsOnStock($id_product)) && StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute)) {
                    $json = array(
                        'status' => 'error',
                        'message'=> $this->l('It is not possible to delete a combination while it still has some quantities in the Advanced Stock Management. You must delete its stock first.')
                    );
                } else {

                    // cloning process
                    $id_product_attribute = $product->addCombinationEntity(
                        $combination->wholesale_price,
                        $price,
                        $combination->weight * $combination->weight_impact,
                        $combination->unity * $combination->unit_price_impact,
                        $combination->ecotax,
                        0,
                        Tools::getValue('id_image_attr'),
                        $combination->reference,
                        null,
                        $combination->ean13,
                        $combination->default,
                        $combination->location,
                        $combination->upc,
                        $combination->minimal_quantity,
                        array(),
                        $combination->available_date,
                        $combination->title,
                        $combination->short_description,
                        $combination->long_description,
                        $combination->attribute_video,
                        $combination->top_description,
                        $combination->is_new,
                        $combination->hide
                    );
                    StockAvailable::setProductDependsOnStock((int)$product->id, $product->depends_on_stock, null, (int)$id_product_attribute);
                    StockAvailable::setProductOutOfStock((int)$product->id, $product->out_of_stock, null, (int)$id_product_attribute);

                    $combination = new Combination((int)$id_product_attribute);

                    $combination->setAttributes($values);

                    // images could be deleted before

                    if (!empty($images)) {
                        $combination->setImages($images);
                    }
                    if ($depends_on_stock && !Stock::deleteStockByIds($id_product, $id_product_attribute)) {
                        $json = array(
                            'status' => 'error',
                            'message'=> $this->l('Error while deleting the stock')
                        );
                    } else {
                        $currency = $this->context->currency;

                        $json = array(
                            'status' => 'ok',
                            'message' => 'Копирование завершено',
                            'models_table' => $this->renderListAttributes($product, $currency),
                            'link' => $this->context->link->getAdminLink('AdminProducts').'&id_product='.$id_product.'&key_tab=Combinations&updateproduct'
                        );
                    }
                }
            } else {
                $json = array(
                    'status' => 'error',
                    'message'=> 'Ошибка при клонировании модели',
                    );
            }
        die(Tools::jsonEncode($json));
    }

    public function displayCloneLink($token = null, $id, $name = null)
    {
        $id_product_attribute = Tools::getValue('id_product_attribute');
        $tpl = $this->createTemplate('helpers/list/list_action_clone.tpl');
        $tpl->assign(array(
            'href' => self::$currentIndex.'&id_product_attribute'.'='.$id.'&cloneconfiguration&token='.($token != null ? $token : $this->token),
            'action' => $this->l('Клонировать'),
            'id' => $id
        ));

        return $tpl->fetch();
    }

    /**
     * @param Product $obj
     * @throws Exception
     * @throws SmartyException
     */
    public function initFormPrices($obj)
    {
        $data = $this->createTemplate($this->tpl_form);
        $product = $obj;
        if ($obj->id) {
            $shops = Shop::getShops();
            $countries = Country::getCountries($this->context->language->id);
            $groups = Group::getGroups($this->context->language->id);
            $currencies = Currency::getCurrencies();
            $attributes = $obj->getAttributesGroups((int)$this->context->language->id);
            $combinations = array();
            foreach ($attributes as $attribute) {
                $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
                $combinations[$attribute['id_product_attribute']]['reference'] = $attribute['reference'];
                if (!isset($combinations[$attribute['id_product_attribute']]['attributes'])) {
                    $combinations[$attribute['id_product_attribute']]['attributes'] = '';
                }
                $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';

                $combinations[$attribute['id_product_attribute']]['price'] = Tools::displayPrice(
                    Tools::convertPrice(
                        Product::getPriceStatic((int)$obj->id, false, $attribute['id_product_attribute']),
                        $this->context->currency
                    ), $this->context->currency
                );
            }
            foreach ($combinations as &$combination) {
                $combination['attributes'] = rtrim($combination['attributes'], ' - ');
            }
            $data->assign('specificPriceModificationForm', $this->_displaySpecificPriceModificationForm(
                $this->context->currency, $shops, $currencies, $countries, $groups)
            );

            $data->assign('ecotax_tax_excl', (float)$obj->ecotax);
            $this->_applyTaxToEcotax($obj);

            $data->assign(array(
                'shops' => $shops,
                'admin_one_shop' => count($this->context->employee->getAssociatedShops()) == 1,
                'currencies' => $currencies,
                'countries' => $countries,
                'groups' => $groups,
                'combinations' => $combinations,
                'multi_shop' => Shop::isFeatureActive(),
                'link' => new Link(),
                'pack' => new Pack()
            ));
        } else {
            $this->displayWarning($this->l('You must save this product before adding specific pricing'));
            $product->id_tax_rules_group = (int)Product::getIdTaxRulesGroupMostUsed();
            $data->assign('ecotax_tax_excl', 0);
        }

        $address = new Address();
        $address->id_country = (int)$this->context->country->id;
        $tax_rules_groups = TaxRulesGroup::getTaxRulesGroups(true);
        $tax_rates = array(
            0 => array(
                'id_tax_rules_group' => 0,
                'rates' => array(0),
                'computation_method' => 0
            )
        );

        foreach ($tax_rules_groups as $tax_rules_group) {
            $id_tax_rules_group = (int)$tax_rules_group['id_tax_rules_group'];
            $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();
            $tax_rates[$id_tax_rules_group] = array(
                'id_tax_rules_group' => $id_tax_rules_group,
                'rates' => array(),
                'computation_method' => (int)$tax_calculator->computation_method
            );

            if (isset($tax_calculator->taxes) && count($tax_calculator->taxes)) {
                foreach ($tax_calculator->taxes as $tax) {
                    $tax_rates[$id_tax_rules_group]['rates'][] = (float)$tax->rate;
                }
            } else {
                $tax_rates[$id_tax_rules_group]['rates'][] = 0;
            }
        }

        // prices part
        $data->assign(array(
            'link' => $this->context->link,
            'currency' => $currency = $this->context->currency,
            'tax_rules_groups' => $tax_rules_groups,
            'taxesRatesByGroup' => $tax_rates,
            'ecotaxTaxRate' => Tax::getProductEcotaxRate(),
            'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
            'ps_use_ecotax' => Configuration::get('PS_USE_ECOTAX'),
        ));

        $product->price = Tools::convertPrice($product->price, $this->context->currency, true, $this->context);
        if ($product->unit_price_ratio != 0) {
            $data->assign('unit_price', Tools::ps_round($product->price / $product->unit_price_ratio, 6));
        } else {
            $data->assign('unit_price', 0);
        }
        $data->assign('ps_tax', Configuration::get('PS_TAX'));

        $data->assign('country_display_tax_label', $this->context->country->display_tax_label);
        $data->assign(array(
            'currency', $this->context->currency,
            'product' => $product,
            'token' => $this->token
        ));

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    protected function _displaySpecificPriceModificationForm($defaultCurrency, $shops, $currencies, $countries, $groups)
    {
        /** @var Product $obj */
        if (!($obj = $this->loadObject())) {
            return;
        }

        $page = (int)Tools::getValue('page');
        $content = '';
        $specific_prices = SpecificPrice::getByProductId((int)$obj->id);
        $specific_price_priorities = SpecificPrice::getPriority((int)$obj->id);

        $tmp = array();
        foreach ($shops as $shop) {
            $tmp[$shop['id_shop']] = $shop;
        }
        $shops = $tmp;
        $tmp = array();
        foreach ($currencies as $currency) {
            $tmp[$currency['id_currency']] = $currency;
        }
        $currencies = $tmp;

        $tmp = array();
        foreach ($countries as $country) {
            $tmp[$country['id_country']] = $country;
        }
        $countries = $tmp;

        $tmp = array();
        foreach ($groups as $group) {
            $tmp[$group['id_group']] = $group;
        }
        $groups = $tmp;

        $length_before = strlen($content);
        if (is_array($specific_prices) && count($specific_prices)) {
            $i = 0;
            foreach ($specific_prices as $specific_price) {
                $id_currency = $specific_price['id_currency'] ? $specific_price['id_currency'] : $defaultCurrency->id;
                if (!isset($currencies[$id_currency])) {
                    continue;
                }

                $current_specific_currency = $currencies[$id_currency];
                if ($specific_price['reduction_type'] == 'percentage') {
                    $impact = '- '.($specific_price['reduction'] * 100).' %';
                } elseif ($specific_price['reduction'] > 0) {
                    $impact = '- '.Tools::displayPrice(Tools::ps_round($specific_price['reduction'], 2), $current_specific_currency).' ';
                    if ($specific_price['reduction_tax']) {
                        $impact .= '('.$this->l('Tax incl.').')';
                    } else {
                        $impact .= '('.$this->l('Tax excl.').')';
                    }
                } else {
                    $impact = '--';
                }

                if ($specific_price['from'] == '0000-00-00 00:00:00' && $specific_price['to'] == '0000-00-00 00:00:00') {
                    $period = $this->l('Unlimited');
                } else {
                    $period = $this->l('From').' '.($specific_price['from'] != '0000-00-00 00:00:00' ? $specific_price['from'] : '0000-00-00 00:00:00').'<br />'.$this->l('To').' '.($specific_price['to'] != '0000-00-00 00:00:00' ? $specific_price['to'] : '0000-00-00 00:00:00');
                }
                if ($specific_price['id_product_attribute']) {
                    $combination = new Combination((int)$specific_price['id_product_attribute']);
                    $reference = $combination->reference;
                    $attributes = $combination->getAttributesName((int)$this->context->language->id);
                    $attributes_name = '';
                    foreach ($attributes as $attribute) {
                        $attributes_name .= $attribute['name'].' - ';
                    }
                    $attributes_name = rtrim($attributes_name, ' - ');
                } else {
                    $attributes_name = $this->l('All combinations');
                    $reference = '';
                }

                $rule = new SpecificPriceRule((int)$specific_price['id_specific_price_rule']);
                $rule_name = ($rule->id ? $rule->name : '--');

                if ($specific_price['id_customer']) {
                    $customer = new Customer((int)$specific_price['id_customer']);
                    if (Validate::isLoadedObject($customer)) {
                        $customer_full_name = $customer->firstname.' '.$customer->lastname;
                    }
                    unset($customer);
                }

                if (!$specific_price['id_shop'] || in_array($specific_price['id_shop'], Shop::getContextListShopID())) {
                    $content .= '
					<tr '.($i % 2 ? 'class="alt_row"' : '').'>
						<td>'.$rule_name.'</td>
						<td>'.$attributes_name.'</td>
                        <td>'.$reference.'</td>';

                    $can_delete_specific_prices = true;
                    if (Shop::isFeatureActive()) {
                        $id_shop_sp = $specific_price['id_shop'];
                        $can_delete_specific_prices = (count($this->context->employee->getAssociatedShops()) > 1 && !$id_shop_sp) || $id_shop_sp;
                        $content .= '
						<td>'.($id_shop_sp ? $shops[$id_shop_sp]['name'] : $this->l('All shops')).'</td>';
                    }
                    $price = Tools::ps_round($specific_price['price'], 2);
                    $fixed_price = ($price == Tools::ps_round($obj->price, 2) || $specific_price['price'] == -1) ? '--' : Tools::displayPrice($price, $current_specific_currency);
                    $content .= '
						<td>'.($specific_price['id_currency'] ? $currencies[$specific_price['id_currency']]['name'] : $this->l('All currencies')).'</td>
						<td>'.($specific_price['id_country'] ? $countries[$specific_price['id_country']]['name'] : $this->l('All countries')).'</td>
						<td>'.($specific_price['id_group'] ? $groups[$specific_price['id_group']]['name'] : $this->l('All groups')).'</td>
						<td title="'.$this->l('ID:').' '.$specific_price['id_customer'].'">'.(isset($customer_full_name) ? $customer_full_name : $this->l('All customers')).'</td>
						<td>'.$fixed_price.'</td>
						<td>'.$impact.'</td>
						<td>'.$period.'</td>
						<td>'.$specific_price['from_quantity'].'</th>
						<td>'.((!$rule->id && $can_delete_specific_prices) ? '<a class="btn btn-default" name="delete_link" href="'.self::$currentIndex.'&id_product='.(int)Tools::getValue('id_product').'&action=deleteSpecificPrice&id_specific_price='.(int)($specific_price['id_specific_price']).'&token='.Tools::getValue('token').'"><i class="icon-trash"></i></a>': '').'</td>
					</tr>';
                    $i++;
                    unset($customer_full_name);
                }
            }
        }

        if ($length_before === strlen($content)) {
            $content .= '
				<tr>
					<td class="text-center" colspan="13"><i class="icon-warning-sign"></i>&nbsp;'.$this->l('No specific prices.').'</td>
				</tr>';
        }

        $content .= '
				</tbody>
			</table>
			</div>
			<div class="panel-footer">
				<a href="'.$this->context->link->getAdminLink('AdminProducts').($page > 1 ? '&submitFilter'.$this->table.'='.(int)$page : '').'" class="btn btn-default"><i class="process-icon-cancel"></i> '.$this->l('Cancel').'</a>
				<button id="product_form_submit_btn"  type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> '.$this->l('Save') .'</button>
				<button id="product_form_submit_btn"  type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> '.$this->l('Save and stay') .'</button>
			</div>
		</div>';

        $content .= '
		<script type="text/javascript">
			var currencies = new Array();
			currencies[0] = new Array();
			currencies[0]["sign"] = "'.$defaultCurrency->sign.'";
			currencies[0]["format"] = '.intval($defaultCurrency->format).';
			';
        foreach ($currencies as $currency) {
            $content .= '
				currencies['.$currency['id_currency'].'] = new Array();
				currencies['.$currency['id_currency'].']["sign"] = "'.$currency['sign'].'";
				currencies['.$currency['id_currency'].']["format"] = '.intval($currency['format']).';
				';
        }
        $content .= '
		</script>
		';

        // Not use id_customer
        if ($specific_price_priorities[0] == 'id_customer') {
            unset($specific_price_priorities[0]);
        }
        // Reindex array starting from 0
        $specific_price_priorities = array_values($specific_price_priorities);

        $content .= '<div class="panel">
		<h3>'.$this->l('Priority management').'</h3>
		<div class="alert alert-info">
				'.$this->l('Sometimes one customer can fit into multiple price rules. Priorities allow you to define which rule applies to the customer.').'
		</div>';

        $content .= '
		<div class="form-group">
			<label class="control-label col-lg-3" for="specificPricePriority1">'.$this->l('Priorities').'</label>
			<div class="input-group col-lg-9">
				<select id="specificPricePriority1" name="specificPricePriority[]">
					<option value="id_shop"'.($specific_price_priorities[0] == 'id_shop' ? ' selected="selected"' : '').'>'.$this->l('Shop').'</option>
					<option value="id_currency"'.($specific_price_priorities[0] == 'id_currency' ? ' selected="selected"' : '').'>'.$this->l('Currency').'</option>
					<option value="id_country"'.($specific_price_priorities[0] == 'id_country' ? ' selected="selected"' : '').'>'.$this->l('Country').'</option>
					<option value="id_group"'.($specific_price_priorities[0] == 'id_group' ? ' selected="selected"' : '').'>'.$this->l('Group').'</option>
				</select>
				<span class="input-group-addon"><i class="icon-chevron-right"></i></span>
				<select name="specificPricePriority[]">
					<option value="id_shop"'.($specific_price_priorities[1] == 'id_shop' ? ' selected="selected"' : '').'>'.$this->l('Shop').'</option>
					<option value="id_currency"'.($specific_price_priorities[1] == 'id_currency' ? ' selected="selected"' : '').'>'.$this->l('Currency').'</option>
					<option value="id_country"'.($specific_price_priorities[1] == 'id_country' ? ' selected="selected"' : '').'>'.$this->l('Country').'</option>
					<option value="id_group"'.($specific_price_priorities[1] == 'id_group' ? ' selected="selected"' : '').'>'.$this->l('Group').'</option>
				</select>
				<span class="input-group-addon"><i class="icon-chevron-right"></i></span>
				<select name="specificPricePriority[]">
					<option value="id_shop"'.($specific_price_priorities[2] == 'id_shop' ? ' selected="selected"' : '').'>'.$this->l('Shop').'</option>
					<option value="id_currency"'.($specific_price_priorities[2] == 'id_currency' ? ' selected="selected"' : '').'>'.$this->l('Currency').'</option>
					<option value="id_country"'.($specific_price_priorities[2] == 'id_country' ? ' selected="selected"' : '').'>'.$this->l('Country').'</option>
					<option value="id_group"'.($specific_price_priorities[2] == 'id_group' ? ' selected="selected"' : '').'>'.$this->l('Group').'</option>
				</select>
				<span class="input-group-addon"><i class="icon-chevron-right"></i></span>
				<select name="specificPricePriority[]">
					<option value="id_shop"'.($specific_price_priorities[3] == 'id_shop' ? ' selected="selected"' : '').'>'.$this->l('Shop').'</option>
					<option value="id_currency"'.($specific_price_priorities[3] == 'id_currency' ? ' selected="selected"' : '').'>'.$this->l('Currency').'</option>
					<option value="id_country"'.($specific_price_priorities[3] == 'id_country' ? ' selected="selected"' : '').'>'.$this->l('Country').'</option>
					<option value="id_group"'.($specific_price_priorities[3] == 'id_group' ? ' selected="selected"' : '').'>'.$this->l('Group').'</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-lg-9 col-lg-offset-3">
				<p class="checkbox">
					<label for="specificPricePriorityToAll"><input type="checkbox" name="specificPricePriorityToAll" id="specificPricePriorityToAll" />'.$this->l('Apply to all products').'</label>
				</p>
			</div>
		</div>
		<div class="panel-footer">
				<a href="'.$this->context->link->getAdminLink('AdminProducts').($page > 1 ? '&submitFilter'.$this->table.'='.(int)$page : '').'" class="btn btn-default"><i class="process-icon-cancel"></i> '.$this->l('Cancel').'</a>
				<button id="product_form_submit_btn"  type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> '.$this->l('Save') .'</button>
				<button id="product_form_submit_btn"  type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> '.$this->l('Save and stay') .'</button>
			</div>
		</div>
		';
        return $content;
    }

    public function renderListAttributes($product, $currency)
    {
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
        $this->addRowAction('edit');
        $this->addRowAction('default');
        $this->addRowAction('delete');
        $this->addRowAction('clone');

        $default_class = 'highlighted';

        $this->fields_list = array(
            'title' => array('title' => $this->l('Model title'), 'align' => 'left'),
            'short_description' => array('title' => $this->l('Short description'), 'align' => 'left'),
            // 'long_description' => array('title' => $this->l('Long description'), 'align' => 'left'),
            // 'attribute_video' => array('title' => $this->l('Video'), 'align' => 'left'),
            'attributes' => array('title' => $this->l('Attribute - value pair'), 'align' => 'left'),
            'price' => array('title' => 'Цена', 'type' => 'price', 'align' => 'left'),
            // 'weight' => array('title' => $this->l('Impact on weight'), 'align' => 'left'),
            'reference' => array('title' => $this->l('Reference'), 'align' => 'left'),
            // 'ean13' => array('title' => $this->l('EAN-13'), 'align' => 'left'),
            // 'upc' => array('title' => $this->l('UPC'), 'align' => 'left')
        );

        if ($product->id) {
            /* Build attributes combinations */
            $combinations = $product->getAttributeCombinations($this->context->language->id);
            $groups = array();
            $comb_array = array();
            if (is_array($combinations)) {
                $combination_images = $product->getCombinationImages($this->context->language->id);
                foreach ($combinations as $k => $combination) {
                    $price_to_convert = Tools::convertPrice($combination['price'], $currency);
                    $price = Tools::displayPrice($price_to_convert, $currency);

                    $comb_array[$combination['id_product_attribute']]['title'] = $combination['title'];
                    $comb_array[$combination['id_product_attribute']]['short_description'] = $combination['short_description'];
                    $comb_array[$combination['id_product_attribute']]['long_description'] = $combination['long_description'];
                    $comb_array[$combination['id_product_attribute']]['top_description'] = $combination['top_description'];
                    $comb_array[$combination['id_product_attribute']]['attribute_video'] = $combination['attribute_video'];
                    $comb_array[$combination['id_product_attribute']]['is_new'] = $combination['is_new'];
                    $comb_array[$combination['id_product_attribute']]['hide'] = $combination['hide'];
                    $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_name'], $combination['attribute_name'], $combination['id_attribute']);
                    $comb_array[$combination['id_product_attribute']]['wholesale_price'] = $combination['wholesale_price'];
                    $comb_array[$combination['id_product_attribute']]['price'] = $price;
                    $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'].Configuration::get('PS_WEIGHT_UNIT');
                    $comb_array[$combination['id_product_attribute']]['unit_impact'] = $combination['unit_price_impact'];
                    $comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
                    $comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
                    $comb_array[$combination['id_product_attribute']]['upc'] = $combination['upc'];
                    $comb_array[$combination['id_product_attribute']]['id_image'] = isset($combination_images[$combination['id_product_attribute']][0]['id_image']) ? $combination_images[$combination['id_product_attribute']][0]['id_image'] : 0;
                    $comb_array[$combination['id_product_attribute']]['available_date'] = strftime($combination['available_date']);
                    $comb_array[$combination['id_product_attribute']]['default_on'] = $combination['default_on'];
                    if ($combination['is_color_group']) {
                        $groups[$combination['id_attribute_group']] = $combination['group_name'];
                    }
                }
            }

            if (isset($comb_array)) {
                foreach ($comb_array as $id_product_attribute => $product_attribute) {
                    $list = '';

                    /* In order to keep the same attributes order */
                    asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $list .= $attribute[0].' - '.$attribute[1].', ';
                    }

                    $list = rtrim($list, ', ');
                    $comb_array[$id_product_attribute]['image'] = $product_attribute['id_image'] ? new Image($product_attribute['id_image']) : false;
                    $comb_array[$id_product_attribute]['available_date'] = $product_attribute['available_date'] != 0 ? date('Y-m-d', strtotime($product_attribute['available_date'])) : '0000-00-00';
                    $comb_array[$id_product_attribute]['attributes'] = $list;
                    $comb_array[$id_product_attribute]['name'] = $list;

                    if ($product_attribute['default_on']) {
                        $comb_array[$id_product_attribute]['class'] = $default_class;
                    }
                }
            }
        }

        foreach ($this->actions_available as $action) {
            if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action) {
                $this->actions[] = $action;
            }
        }

        $helper = new HelperList();
        $helper->identifier = 'id_product_attribute';
        $helper->table_id = 'combinations-list';
        $helper->token = $this->token;
        $helper->currentIndex = self::$currentIndex;
        $helper->no_link = true;
        $helper->simple_header = true;
        $helper->show_toolbar = false;
        $helper->shopLinkType = $this->shopLinkType;
        $helper->actions = $this->actions;
        $helper->list_skip_actions = $this->list_skip_actions;
        $helper->colorOnBackground = true;
        $helper->override_folder = $this->tpl_folder.'combination/';

        return $helper->generateList($comb_array, $this->fields_list);
    }

    public function initFormQuantities($obj)
    {
        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        $data = $this->createTemplate($this->tpl_form);
        $data->assign('default_form_language', $this->default_form_language);

        if ($obj->id) {
            if ($this->product_exists_in_shop) {
                // Get all id_product_attribute
                $attributes = $obj->getAttributesResume($this->context->language->id);
                if (empty($attributes)) {
                    $attributes[] = array(
                        'id_product_attribute' => 0,
                        'attribute_designation' => ''
                    );
                }

                // Get available quantities
                $available_quantity = array();
                $product_designation = array();

                foreach ($attributes as $attribute) {
                    // Get available quantity for the current product attribute in the current shop
                    $available_quantity[$attribute['id_product_attribute']] = isset($attribute['id_product_attribute']) && $attribute['id_product_attribute'] ? (int)$attribute['quantity'] : (int)$obj->quantity;
                    // Get all product designation
                    $product_designation[$attribute['id_product_attribute']] = rtrim(
                        $obj->name[$this->context->language->id].' - '.$attribute['title'].' - '.$attribute['short_description'],
                        ' - '
                    );
                }

                $show_quantities = true;
                $shop_context = Shop::getContext();
                $shop_group = new ShopGroup((int)Shop::getContextShopGroupID());

                // if we are in all shops context, it's not possible to manage quantities at this level
                if (Shop::isFeatureActive() && $shop_context == Shop::CONTEXT_ALL) {
                    $show_quantities = false;
                }
                // if we are in group shop context
                elseif (Shop::isFeatureActive() && $shop_context == Shop::CONTEXT_GROUP) {
                    // if quantities are not shared between shops of the group, it's not possible to manage them at group level
                    if (!$shop_group->share_stock) {
                        $show_quantities = false;
                    }
                }
                // if we are in shop context
                elseif (Shop::isFeatureActive()) {
                    // if quantities are shared between shops of the group, it's not possible to manage them for a given shop
                    if ($shop_group->share_stock) {
                        $show_quantities = false;
                    }
                }

                $data->assign('ps_stock_management', Configuration::get('PS_STOCK_MANAGEMENT'));
                $data->assign('has_attribute', $obj->hasAttributes());
                // Check if product has combination, to display the available date only for the product or for each combination
                if (Combination::isFeatureActive()) {
                    $data->assign('countAttributes', (int)Db::getInstance()->getValue('SELECT COUNT(id_product) FROM '._DB_PREFIX_.'product_attribute WHERE id_product = '.(int)$obj->id));
                } else {
                    $data->assign('countAttributes', false);
                }
                // if advanced stock management is active, checks associations
                $advanced_stock_management_warning = false;
                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && $obj->advanced_stock_management) {
                    $p_attributes = Product::getProductAttributesIds($obj->id);
                    $warehouses = array();

                    if (!$p_attributes) {
                        $warehouses[] = Warehouse::getProductWarehouseList($obj->id, 0);
                    }

                    foreach ($p_attributes as $p_attribute) {
                        $ws = Warehouse::getProductWarehouseList($obj->id, $p_attribute['id_product_attribute']);
                        if ($ws) {
                            $warehouses[] = $ws;
                        }
                    }
                    $warehouses = Tools::arrayUnique($warehouses);

                    if (empty($warehouses)) {
                        $advanced_stock_management_warning = true;
                    }
                }
                if ($advanced_stock_management_warning) {
                    $this->displayWarning($this->l('If you wish to use the advanced stock management, you must:'));
                    $this->displayWarning('- '.$this->l('associate your products with warehouses.'));
                    $this->displayWarning('- '.$this->l('associate your warehouses with carriers.'));
                    $this->displayWarning('- '.$this->l('associate your warehouses with the appropriate shops.'));
                }

                $pack_quantity = null;
                // if product is a pack
                if (Pack::isPack($obj->id)) {
                    $items = Pack::getItems((int)$obj->id, Configuration::get('PS_LANG_DEFAULT'));

                    // gets an array of quantities (quantity for the product / quantity in pack)
                    $pack_quantities = array();
                    foreach ($items as $item) {
                        /** @var Product $item */
                        if (!$item->isAvailableWhenOutOfStock((int)$item->out_of_stock)) {
                            $pack_id_product_attribute = Product::getDefaultAttribute($item->id, 1);
                            $pack_quantities[] = Product::getQuantity($item->id, $pack_id_product_attribute) / ($item->pack_quantity !== 0 ? $item->pack_quantity : 1);
                        }
                    }

                    // gets the minimum
                    if (count($pack_quantities)) {
                        $pack_quantity = $pack_quantities[0];
                        foreach ($pack_quantities as $value) {
                            if ($pack_quantity > $value) {
                                $pack_quantity = $value;
                            }
                        }
                    }

                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && !Warehouse::getPackWarehouses((int)$obj->id)) {
                        $this->displayWarning($this->l('You must have a common warehouse between this pack and its product.'));
                    }
                }

                $data->assign(array(
                    'attributes' => $attributes,
                    'available_quantity' => $available_quantity,
                    'pack_quantity' => $pack_quantity,
                    'stock_management_active' => Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'),
                    'product_designation' => $product_designation,
                    'product' => $obj,
                    'show_quantities' => $show_quantities,
                    'order_out_of_stock' => Configuration::get('PS_ORDER_OUT_OF_STOCK'),
                    'pack_stock_type' => Configuration::get('PS_PACK_STOCK_TYPE'),
                    'token_preferences' => Tools::getAdminTokenLite('AdminPPreferences'),
                    'token' => $this->token,
                    'languages' => $this->_languages,
                    'id_lang' => $this->context->language->id
                ));
            } else {
                $this->displayWarning($this->l('You must save the product in this shop before managing quantities.'));
            }
        } else {
            $this->displayWarning($this->l('You must save this product before managing quantities.'));
        }

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }


    /**
     * @param Product $product
     * @throws Exception
     * @throws SmartyException
     */
    public function initFormAttributes($product)
    {

        $data = $this->createTemplate($this->tpl_form);
        if (!Combination::isFeatureActive()) {
            $this->displayWarning($this->l('This feature has been disabled. ').
                ' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance').'#featuresDetachables">'.$this->l('Performances').'</a>');
        } elseif (Validate::isLoadedObject($product)) {
            if ($this->product_exists_in_shop) {
                if ($product->is_virtual) {
                    $data->assign('product', $product);
                    $this->displayWarning($this->l('A virtual product cannot have combinations.'));
                } else {
                    $attribute_js = array();
                    $attributes = Attribute::getAttributes($this->context->language->id, true);
                    foreach ($attributes as $k => $attribute) {
                        $attribute_js[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
                        natsort($attribute_js[$attribute['id_attribute_group']]);
                    }

                    $currency = $this->context->currency;

                    $data->assign('attributeJs', $attribute_js);
                    $data->assign('attributes_groups', AttributeGroup::getAttributesGroups($this->context->language->id));

                    $data->assign('currency', $currency);

                    $images = Image::getImages($this->context->language->id, $product->id);

                    $data->assign('tax_exclude_option', Tax::excludeTaxeOption());
                    $data->assign('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT'));

                    $data->assign('ps_use_ecotax', Configuration::get('PS_USE_ECOTAX'));
                    $data->assign('field_value_unity', $this->getFieldValue($product, 'unity'));

                    $data->assign('reasons', $reasons = StockMvtReason::getStockMvtReasons($this->context->language->id));
                    $data->assign('ps_stock_mvt_reason_default', $ps_stock_mvt_reason_default = Configuration::get('PS_STOCK_MVT_REASON_DEFAULT'));
                    $data->assign('minimal_quantity', $this->getFieldValue($product, 'minimal_quantity') ? $this->getFieldValue($product, 'minimal_quantity') : 1);
                    $data->assign('available_date', ($this->getFieldValue($product, 'available_date') != 0) ? stripslashes(htmlentities($this->getFieldValue($product, 'available_date'), $this->context->language->id)) : '0000-00-00');

                    $i = 0;
                    $type = ImageType::getByNameNType('%', 'products', 'height');
                    if (isset($type['name'])) {
                        $data->assign('imageType', $type['name']);
                    } else {
                        $data->assign('imageType', ImageType::getFormatedName('small'));
                    }
                    $data->assign('imageWidth', (isset($image_type['width']) ? (int)($image_type['width']) : 64) + 25);
                    foreach ($images as $k => $image) {
                        $images[$k]['obj'] = new Image($image['id_image']);
                        ++$i;
                    }
                    $data->assign('images', $images);

                    $data->assign($this->tpl_form_vars);
                    $data->assign(array(
                        'languages' => $this->_languages,
                        'default_form_language' => $this->default_form_language,
                        'id_lang' => $this->context->language->id,
                        'list' => $this->renderListAttributes($product, $currency),
                        'product' => $product,
                        'id_category' => $product->getDefaultCategory(),
                        'token_generator' => Tools::getAdminTokenLite('AdminAttributeGenerator'),
                        'combination_exists' => (Shop::isFeatureActive() && (Shop::getContextShopGroup()->share_stock) && count(AttributeGroup::getAttributesGroups($this->context->language->id)) > 0 && $product->hasAttributes())
                    ));
                }
            } else {
                $this->displayWarning($this->l('You must save the product in this shop before adding combinations.'));
            }
        } else {
            $data->assign('product', $product);
            $this->displayWarning($this->l('You must save this product before adding combinations.'));
        }

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function processUpdate()
    {
        $existing_product = $this->object;

        $this->checkProduct();

        if (!empty($this->errors)) {
            $this->display = 'edit';
            return false;
        }

        $id = (int)Tools::getValue('id_'.$this->table);
        /* Update an existing product */
        if (isset($id) && !empty($id)) {
            /** @var Product $object */
            $object = new $this->className((int)$id);
            $this->object = $object;

            if (Validate::isLoadedObject($object)) {
                $this->_removeTaxFromEcotax();
                $product_type_before = $object->getType();
                $this->copyFromPost($object, $this->table);
                $object->indexed = 0;

                if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
                    $object->setFieldsToUpdate((array)Tools::getValue('multishop_check', array()));
                }

                // Duplicate combinations if not associated to shop
                if ($this->context->shop->getContext() == Shop::CONTEXT_SHOP && !$object->isAssociatedToShop()) {
                    $is_associated_to_shop = false;
                    $combinations = Product::getProductAttributesIds($object->id);
                    if ($combinations) {
                        foreach ($combinations as $id_combination) {
                            $combination = new Combination((int)$id_combination['id_product_attribute']);
                            $default_combination = new Combination((int)$id_combination['id_product_attribute'], null, (int)$this->object->id_shop_default);

                            $def = ObjectModel::getDefinition($default_combination);
                            foreach ($def['fields'] as $field_name => $row) {
                                $combination->$field_name = ObjectModel::formatValue($default_combination->$field_name, $def['fields'][$field_name]['type']);
                            }

                            $combination->save();
                        }
                    }
                } else {
                    $is_associated_to_shop = true;
                }

                if ($object->update()) {
                    // If the product doesn't exist in the current shop but exists in another shop
                    if (Shop::getContext() == Shop::CONTEXT_SHOP && !$existing_product->isAssociatedToShop($this->context->shop->id)) {
                        $out_of_stock = StockAvailable::outOfStock($existing_product->id, $existing_product->id_shop_default);
                        $depends_on_stock = StockAvailable::dependsOnStock($existing_product->id, $existing_product->id_shop_default);
                        StockAvailable::setProductOutOfStock((int)$this->object->id, $out_of_stock, $this->context->shop->id);
                        StockAvailable::setProductDependsOnStock((int)$this->object->id, $depends_on_stock, $this->context->shop->id);
                    }

                    if ($this->object->is_new == true) {
                        $combinations = Product::getProductAttributesIds($object->id);
                        if ($combinations) {
                            foreach ($combinations as $id_combination) {
                                $combination = new Combination((int)$id_combination['id_product_attribute']);

                                $combination->is_new = true;

                                $combination->save();
                            }
                        }
                    }

                    PrestaShopLogger::addLog(sprintf($this->l('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int)$this->object->id, true, (int)$this->context->employee->id);
                    if (in_array($this->context->shop->getContext(), array(Shop::CONTEXT_SHOP, Shop::CONTEXT_ALL))) {
                        if ($this->isTabSubmitted('Shipping')) {
                            $this->addCarriers();
                        }
                        if ($this->isTabSubmitted('Associations')) {
                            $this->updateAccessories($object);
                        }
                        if ($this->isTabSubmitted('Suppliers')) {
                            $this->processSuppliers();
                        }
                        if ($this->isTabSubmitted('Features')) {
                            $this->processFeatures();
                        }
                        if ($this->isTabSubmitted('Combinations')) {
                            $this->processProductAttribute();
                        }
                        if ($this->isTabSubmitted('Prices')) {
                            $this->processPriceAddition();
                            $this->processSpecificPricePriorities();
                        }
                        if ($this->isTabSubmitted('Customization')) {
                            $this->processCustomizationConfiguration();
                        }
                        if ($this->isTabSubmitted('Attachments')) {
                            $this->processAttachments();
                        }
                        if ($this->isTabSubmitted('Images')) {
                            $this->processImageLegends();
                        }

                        $this->updatePackItems($object);
                        // Disallow avanced stock management if the product become a pack
                        if ($product_type_before == Product::PTYPE_SIMPLE && $object->getType() == Product::PTYPE_PACK) {
                            StockAvailable::setProductDependsOnStock((int)$object->id, false);
                        }
                        $this->updateDownloadProduct($object, 1);
                        $this->updateTags(Language::getLanguages(false), $object);

                        if ($this->isProductFieldUpdated('category_box') && !$object->updateCategories(Tools::getValue('categoryBox'))) {
                            $this->errors[] = Tools::displayError('An error occurred while linking the object.').' <b>'.$this->table.'</b> '.Tools::displayError('To categories');
                        }
                    }

                    if ($this->isTabSubmitted('Warehouses')) {
                        $this->processWarehouses();
                    }
                    if (empty($this->errors)) {
                        if (in_array($object->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION')) {
                            Search::indexation(false, $object->id);
                        }

                        // Save and preview
                        if (Tools::isSubmit('submitAddProductAndPreview')) {
                            $this->redirect_after = $this->getPreviewUrl($object);
                        } else {
                            $page = (int)Tools::getValue('page');
                            // Save and stay on same form
                            if ($this->display == 'edit') {
                                $this->confirmations[] = $this->l('Update successful');
                                $this->redirect_after = self::$currentIndex.'&id_product='.(int)$this->object->id
                                    .(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '')
                                    .'&updateproduct&conf=4&key_tab='.Tools::safeOutput(Tools::getValue('key_tab')).($page > 1 ? '&page='.(int)$page : '').'&token='.$this->token;
                            } else {
                                // Default behavior (save and back)
                                $this->redirect_after = self::$currentIndex.(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '').'&conf=4'.($page > 1 ? '&submitFilterproduct='.(int)$page : '').'&token='.$this->token;
                            }
                        }
                    }
                    // if errors : stay on edit page
                    else {
                        $this->display = 'edit';
                    }
                } else {
                    if (!$is_associated_to_shop && $combinations) {
                        foreach ($combinations as $id_combination) {
                            $combination = new Combination((int)$id_combination['id_product_attribute']);
                            $combination->delete();
                        }
                    }
                    $this->errors[] = Tools::displayError('An error occurred while updating an object.').' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
                }
            } else {
                $this->errors[] = Tools::displayError('An error occurred while updating an object.').' <b>'.$this->table.'</b> ('.Tools::displayError('The object cannot be loaded. ').')';
            }
            return $object;
        }
    }
}
