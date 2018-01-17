<?php

class ParentOrderController extends ParentOrderControllerCore
{
    public function init()
    {
        $this->isLogged = $this->context->customer->id && Customer::customerIdExistsStatic((int)$this->context->cookie->id_customer);

        FrontController::init();

        /* Disable some cache related bugs on the cart/order */
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        $this->nbProducts = $this->context->cart->nbProducts();

        if (!$this->context->customer->isLogged(true) && $this->useMobileTheme() && Tools::getValue('step')) {
            Tools::redirect($this->context->link->getPageLink('authentication', true, (int)$this->context->language->id));
        }

        // Redirect to the good order process
        if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 0 && Dispatcher::getInstance()->getController() != 'order') {
            Tools::redirect('index.php?controller=order');
        }

        if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc') {
            if (Tools::getIsset('step') && Tools::getValue('step') == 3) {
                Tools::redirect('index.php?controller=order-opc&isPaymentStep=true');
            }
            Tools::redirect('index.php?controller=order-opc');
        }

        if (Configuration::get('PS_CATALOG_MODE')) {
            $this->errors[] = Tools::displayError('This store has not accepted your new order.');
        }

        if (Tools::isSubmit('submitReorder') && $id_order = (int)Tools::getValue('id_order')) {
            $oldCart = new Cart(Order::getCartIdStatic($id_order, $this->context->customer->id));
            $duplication = $oldCart->duplicate();
            if (!$duplication || !Validate::isLoadedObject($duplication['cart'])) {
                $this->errors[] = Tools::displayError('Sorry. We cannot renew your order.');
            } elseif (!$duplication['success']) {
                $this->errors[] = Tools::displayError('Some items are no longer available, and we are unable to renew your order.');
            } else {
                $this->context->cookie->id_cart = $duplication['cart']->id;
                $context = $this->context;
                $context->cart = $duplication['cart'];
                CartRule::autoAddToCart($context);
                $this->context->cookie->write();
                if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                    Tools::redirect('index.php?controller=order-opc');
                }
                Tools::redirect('index.php?controller=order');
            }
        }

        if ($this->nbProducts) {
            // if (CartRule::isFeatureActive()) {
                if (Tools::isSubmit('submitAddDiscount')) {
                    if (!($code = trim(Tools::getValue('discount_name')))) {
                        $this->errors[] = Tools::displayError('You must enter a voucher code.');
                    } elseif (!Validate::isCleanHtml($code)) {
                        $this->errors[] = Tools::displayError('The voucher code is invalid.');
                    } else {
                        // if (($cartRule = new CartRule(CartRule::getIdByCode($code))) && Validate::isLoadedObject($cartRule)) {
                        //     if ($error = $cartRule->checkValidity($this->context, false, true)) {
                        //         $this->errors[] = $error;
                        //     } else {
                        //         $this->context->cart->addCartRule($cartRule->id);
                        //         if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                        //             Tools::redirect('index.php?controller=order-opc');
                        //         }
                        //         Tools::redirect('index.php?controller=order');
                        //     }
                        // } else {
                        //     $this->errors[] = Tools::displayError('This voucher does not exists.');
                        // }

                        $this->context->cart->promocode = $code;
                        if (!$this->context->cart->update())
                            $this->errors[] = Tools::displayError('Error occured on saving promocode.');
                    }
                    $this->context->smarty->assign(array(
                        'errors' => $this->errors,
                        'discount_name' => Tools::safeOutput($code)
                    ));
                } elseif (($id_cart_rule = (int)Tools::getValue('deleteDiscount')) && Validate::isUnsignedId($id_cart_rule)) {
                    $this->context->cart->removeCartRule($id_cart_rule);
                    CartRule::autoAddToCart($this->context);
                    Tools::redirect('index.php?controller=order-opc');
                }
            // }
            /* Is there only virtual product in cart */
            if ($isVirtualCart = $this->context->cart->isVirtualCart()) {
                $this->setNoCarrier();
            }
        }

        if ($this->context->cart->promocode) {
            $this->context->smarty->assign(array(
                'discount_name' => Tools::safeOutput($this->context->cart->promocode),
            ));
        }


        $this->context->smarty->assign('back', Tools::safeOutput(Tools::getValue('back')));
    }
}
