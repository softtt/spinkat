<?php

class OrderOpcController extends OrderOpcControllerCore
{
    public function setMedia()
    {
        parent::setMedia();

        $this->addJS(_THEME_JS_DIR_.'quick-order.js');
    }

    /**
     * Initialize order opc controller
     * @see FrontController::init()
     */
    public function init()
    {
        ParentOrderController::init();

        if ($this->nbProducts) {
            $this->context->smarty->assign('virtual_cart', $this->context->cart->isVirtualCart());
        }

        $this->context->smarty->assign('is_multi_address_delivery', $this->context->cart->isMultiAddressDelivery() || ((int)Tools::getValue('multi-shipping') == 1));
        $this->context->smarty->assign('open_multishipping_fancybox', (int)Tools::getValue('multi-shipping') == 1);

        if ($this->context->cart->nbProducts()) {
            if (Tools::isSubmit('ajax')) {
                if (Tools::isSubmit('method')) {
                    switch (Tools::getValue('method')) {
                        case 'updateMessage':
                            if (Tools::isSubmit('message')) {
                                $txt_message = urldecode(Tools::getValue('message'));
                                $this->_updateMessage($txt_message);
                                if (count($this->errors)) {
                                    $this->ajaxDie('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
                                }
                                $this->ajaxDie(true);
                            }
                            break;

                        case 'updateCarrierAndGetPayments':
                            if ((Tools::isSubmit('delivery_option') || Tools::isSubmit('id_carrier')) && Tools::isSubmit('recyclable') && Tools::isSubmit('gift') && Tools::isSubmit('gift_message')) {
                                $this->_assignWrappingAndTOS();
                                if ($this->_processCarrier()) {
                                    $carriers = $this->context->cart->simulateCarriersOutput();
                                    $return = array_merge(array(
                                        'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
                                        'HOOK_PAYMENT' => $this->_getPaymentMethods(),
                                        'carrier_data' => $this->_getCarrierList(),
                                        'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array('carriers' => $carriers))
                                        ),
                                        $this->getFormatedSummaryDetail()
                                    );
                                    Cart::addExtraCarriers($return);
                                    $this->ajaxDie(Tools::jsonEncode($return));
                                } else {
                                    $this->errors[] = Tools::displayError('An error occurred while updating the cart.');
                                }
                                if (count($this->errors)) {
                                    $this->ajaxDie('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
                                }
                                exit;
                            }
                            break;

                        case 'updateTOSStatusAndGetPayments':
                            if (Tools::isSubmit('checked')) {
                                $this->context->cookie->checkedTOS = (int)Tools::getValue('checked');
                                $this->ajaxDie(Tools::jsonEncode(array(
                                    'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
                                    'HOOK_PAYMENT' => $this->_getPaymentMethods()
                                )));
                            }
                            break;

                        case 'getCarrierList':
                            $this->ajaxDie(Tools::jsonEncode($this->_getCarrierList()));
                            break;

                        case 'editCustomer':
                            if (!$this->isLogged) {
                                exit;
                            }

                            $_POST['firstname'] = $_POST['name'];

                            $this->errors = array_merge($this->errors, $this->context->customer->validateController());
                            $this->context->customer->newsletter = (int)Tools::isSubmit('newsletter');
                            $this->context->customer->optin = (int)Tools::isSubmit('optin');

                            $return = array(
                                'hasError' => !empty($this->errors),
                                'errors' => $this->errors,
                                'id_customer' => (int)$this->context->customer->id,
                                'token' => Tools::getToken(false)
                            );
                            if (!count($this->errors)) {
                                $return['isSaved'] = (bool)$this->context->customer->update();
                            } else {
                                $return['isSaved'] = false;
                            }
                            $this->ajaxDie(Tools::jsonEncode($return));
                            break;

                        case 'getAddressBlockAndCarriersAndPayments':
                            if ($this->context->customer->isLogged() || $this->context->customer->isGuest()) {
                                // check if customer have addresses
                                if (!Customer::getAddressesTotalById($this->context->customer->id)) {
                                    $this->ajaxDie(Tools::jsonEncode(array('no_address' => 1)));
                                }
                                if (file_exists(_PS_MODULE_DIR_.'blockuserinfo/blockuserinfo.php')) {
                                    include_once(_PS_MODULE_DIR_.'blockuserinfo/blockuserinfo.php');
                                    $block_user_info = new BlockUserInfo();
                                }
                                $this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
                                $this->_processAddressFormat();
                                $this->_assignAddress();

                                if (!($formated_address_fields_values_list = $this->context->smarty->getTemplateVars('formatedAddressFieldsValuesList'))) {
                                    $formated_address_fields_values_list = array();
                                }

                                // Wrapping fees
                                $wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
                                $wrapping_fees_tax_inc = $this->context->cart->getGiftWrappingPrice();
                                $is_adv_api = Tools::getValue('isAdvApi');

                                if ($is_adv_api) {
                                    $tpl = 'order-address-advanced.tpl';
                                    $this->context->smarty->assign(
                                        array('products' => $this->context->cart->getProducts())
                                    );
                                } else {
                                    $tpl = 'order-address.tpl';
                                }

                                $return = array_merge(array(
                                    'order_opc_adress' => $this->context->smarty->fetch(_PS_THEME_DIR_.$tpl),
                                    'block_user_info' => (isset($block_user_info) ? $block_user_info->hookDisplayTop(array()) : ''),
                                    'block_user_info_nav' => (isset($block_user_info) ? $block_user_info->hookDisplayNav(array()) : ''),
                                    'formatedAddressFieldsValuesList' => $formated_address_fields_values_list,
                                    'carrier_data' => ($is_adv_api ? '' : $this->_getCarrierList()),
                                    'HOOK_TOP_PAYMENT' => ($is_adv_api ? '' : Hook::exec('displayPaymentTop')),
                                    'HOOK_PAYMENT' => ($is_adv_api ? '' : $this->_getPaymentMethods()),
                                    'no_address' => 0,
                                    'gift_price' => Tools::displayPrice(Tools::convertPrice(
                                        Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc,
                                        new Currency((int)$this->context->cookie->id_currency)))
                                    ),
                                    $this->getFormatedSummaryDetail()
                                );
                                $this->ajaxDie(Tools::jsonEncode($return));
                            }
                            die(Tools::displayError());
                            break;

                        case 'makeFreeOrder':
                            /* Bypass payment step if total is 0 */
                            if (($id_order = $this->_checkFreeOrder()) && $id_order) {
                                $order = new Order((int)$id_order);
                                $email = $this->context->customer->email;
                                if ($this->context->customer->is_guest) {
                                    $this->context->customer->logout();
                                } // If guest we clear the cookie for security reason
                                $this->ajaxDie('freeorder:'.$order->reference.':'.$email);
                            }
                            exit;
                            break;

                        case 'updateAddressesSelected':
                            if ($this->context->customer->isLogged(true)) {
                                $address_delivery = new Address((int)Tools::getValue('id_address_delivery'));
                                $this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
                                $address_invoice = ((int)Tools::getValue('id_address_delivery') == (int)Tools::getValue('id_address_invoice') ? $address_delivery : new Address((int)Tools::getValue('id_address_invoice')));
                                if ($address_delivery->id_customer != $this->context->customer->id || $address_invoice->id_customer != $this->context->customer->id) {
                                    $this->errors[] = Tools::displayError('This address is not yours.');
                                } elseif (!Address::isCountryActiveById((int)Tools::getValue('id_address_delivery'))) {
                                    $this->errors[] = Tools::displayError('This address is not in a valid area.');
                                } elseif (!Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted) {
                                    $this->errors[] = Tools::displayError('This address is invalid.');
                                } else {
                                    $this->context->cart->id_address_delivery = (int)Tools::getValue('id_address_delivery');
                                    $this->context->cart->id_address_invoice = Tools::isSubmit('same') ? $this->context->cart->id_address_delivery : (int)Tools::getValue('id_address_invoice');
                                    if (!$this->context->cart->update()) {
                                        $this->errors[] = Tools::displayError('An error occurred while updating your cart.');
                                    }

                                    $infos = Address::getCountryAndState((int)$this->context->cart->id_address_delivery);
                                    if (isset($infos['id_country']) && $infos['id_country']) {
                                        $country = new Country((int)$infos['id_country']);
                                        $this->context->country = $country;
                                    }

                                    // Address has changed, so we check if the cart rules still apply
                                    $cart_rules = $this->context->cart->getCartRules();
                                    CartRule::autoRemoveFromCart($this->context);
                                    CartRule::autoAddToCart($this->context);
                                    if ((int)Tools::getValue('allow_refresh')) {
                                        // If the cart rules has changed, we need to refresh the whole cart
                                        $cart_rules2 = $this->context->cart->getCartRules();
                                        if (count($cart_rules2) != count($cart_rules)) {
                                            $this->ajax_refresh = true;
                                        } else {
                                            $rule_list = array();
                                            foreach ($cart_rules2 as $rule) {
                                                $rule_list[] = $rule['id_cart_rule'];
                                            }
                                            foreach ($cart_rules as $rule) {
                                                if (!in_array($rule['id_cart_rule'], $rule_list)) {
                                                    $this->ajax_refresh = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    if (!$this->context->cart->isMultiAddressDelivery()) {
                                        $this->context->cart->setNoMultishipping();
                                    } // As the cart is no multishipping, set each delivery address lines with the main delivery address

                                    if (!count($this->errors)) {
                                        $result = $this->_getCarrierList();
                                        // Wrapping fees
                                        $wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
                                        $wrapping_fees_tax_inc = $this->context->cart->getGiftWrappingPrice();
                                        $result = array_merge($result, array(
                                            'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
                                            'HOOK_PAYMENT' => $this->_getPaymentMethods(),
                                            'gift_price' => Tools::displayPrice(Tools::convertPrice(Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc, new Currency((int)$this->context->cookie->id_currency))),
                                            'carrier_data' => $this->_getCarrierList(),
                                            'refresh' => (bool)$this->ajax_refresh),
                                            $this->getFormatedSummaryDetail()
                                        );
                                        $this->ajaxDie(Tools::jsonEncode($result));
                                    }
                                }
                                if (count($this->errors)) {
                                    $this->ajaxDie(Tools::jsonEncode(array(
                                        'hasError' => true,
                                        'errors' => $this->errors
                                    )));
                                }
                            }
                            die(Tools::displayError());
                            break;

                        case 'multishipping':
                            $this->_assignSummaryInformations();
                            $this->context->smarty->assign('product_list', $this->context->cart->getProducts());

                            if ($this->context->customer->id) {
                                $this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
                            } else {
                                $this->context->smarty->assign('address_list', array());
                            }
                            $this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping-products.tpl');
                            $this->display();
                            $this->ajaxDie();
                            break;

                        case 'cartReload':
                            $this->_assignSummaryInformations();
                            if ($this->context->customer->id) {
                                $this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
                            } else {
                                $this->context->smarty->assign('address_list', array());
                            }
                            $this->context->smarty->assign('opc', true);
                            $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
                            $this->display();
                            $this->ajaxDie();
                            break;

                        case 'noMultiAddressDelivery':
                            $this->context->cart->setNoMultishipping();
                            $this->ajaxDie();
                            break;

                        default:
                            throw new PrestaShopException('Unknown method "'.Tools::getValue('method').'"');
                    }
                } else {
                    throw new PrestaShopException('Method is not defined');
                }
            }
        } elseif (Tools::isSubmit('ajax')) {
            $this->errors[] = Tools::displayError('There is no product in your cart.');
            $this->ajaxDie('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
        }
    }

    public function initContent()
    {
        ParentOrderController::initContent();

        /* id_carrier is not defined in database before choosing a carrier, set it to a default one to match a potential cart _rule */
        if (empty($this->context->cart->id_carrier)) {
            $checked = $this->context->cart->simulateCarrierSelectedOutput();
            $checked = ((int)Cart::desintifier($checked));
            $this->context->cart->id_carrier = $checked;
            $this->context->cart->update();
            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);
        }

        // SHOPPING CART
        $this->_assignSummaryInformations();
        // WRAPPING AND TOS
        $this->_assignWrappingAndTOS();

        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        } else {
            $countries = Country::getCountries($this->context->language->id, true);
        }

        // If a rule offer free-shipping, force hidding shipping prices
        $free_shipping = false;
        foreach ($this->context->cart->getCartRules() as $rule) {
            if ($rule['free_shipping'] && !$rule['carrier_restriction']) {
                $free_shipping = true;
                break;
            }
        }

        $this->context->smarty->assign(array(
            'free_shipping' => $free_shipping,
            'isGuest' => isset($this->context->cookie->is_guest) ? $this->context->cookie->is_guest : 0,
            'countries' => $countries,
            'sl_country' => (int)Tools::getCountry(),
            'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
            'errorCarrier' => Tools::displayError('You must choose a carrier.', false),
            'errorTOS' => Tools::displayError('You must accept the Terms of Service.', false),
            'isPaymentStep' => isset($_GET['isPaymentStep']) && $_GET['isPaymentStep'],
            'genders' => Gender::getGenders(),
            'one_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'),
            'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
            'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
        ));
        $years = Tools::dateYears();
        $months = Tools::dateMonths();
        $days = Tools::dateDays();
        $this->context->smarty->assign(array(
            'years' => $years,
            'months' => $months,
            'days' => $days,
        ));

        /* Load guest informations */
        if ($this->isLogged && $this->context->cookie->is_guest) {
            $this->context->smarty->assign('guestInformations', $this->_getGuestInformations());
        }
        // ADDRESS
        if ($this->isLogged) {
            $this->context->smarty->assign('guestInformations', $this->_getGuestInformations());
            // $this->_assignAddress();
        }
        // CARRIER
        $this->_assignCarrier();
        // PAYMENT
        $this->_assignPayment();
        Tools::safePostVars();

        $newsletter = Configuration::get('PS_CUSTOMER_NWSL') || (Module::isInstalled('blocknewsletter') && Module::getInstanceByName('blocknewsletter')->active);
        $this->context->smarty->assign('newsletter', $newsletter);
        $this->context->smarty->assign('optin', (bool)Configuration::get('PS_CUSTOMER_OPTIN'));
        $this->context->smarty->assign('field_required', $this->context->customer->validateFieldsRequiredDatabase());

        $this->_processAddressFormat();

        if ((bool)Configuration::get('PS_ADVANCED_PAYMENT_API')) {
            $this->addJS(_THEME_JS_DIR_ . 'advanced-payment-api.js');
            $this->setTemplate(_PS_THEME_DIR_ . 'order-opc-advanced.tpl');
        } else {
            $this->setTemplate(_PS_THEME_DIR_.'order-opc.tpl');
        }
    }

    protected function _getPaymentMethods()
    {
        $return = Hook::exec('displayPayment');
        if (!$return) {
            return '<p class="warning">'.Tools::displayError('No payment method is available for use at this time. ').'</p>';
        }
        return $return;
    }
}
