<?php

class simple_one_click_checkoutOneClickOrderModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        $this->context = Context::getContext();
        include_once $this->module->getLocalPath().'models/OneClickOrder.php';
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('ajax') && Tools::isSubmit('placeOrder'))
            $this->placeOrder();
        else
            die(Tools::jsonEncode(array('error' => 'method doesn\'t exist')));
    }

    public function placeOrder()
    {
        // TODO: Validate passed data from request.
        // Save order if data is OK.
        // Return Error messages if errors occured.


        if (Tools::isSubmit('id_product')
            && Tools::isSubmit('id_product_attribute')
            && Tools::isSubmit('quantity')
            && Tools::isSubmit('customer_data')
        ) {
            $customer_data = Tools::getValue('customer_data');

            if ($customer_data['client']
                && $customer_data['phone']
            ) {
                $order = new OneClickOrder();
                $order->client = $customer_data['client'];
                $order->phone = $customer_data['phone'];
                $order->email = $customer_data['email'];
                $order->message = $customer_data['message'];
                $order->id_product = Tools::getValue('id_product');
                $order->id_product_attribute = Tools::getValue('id_product_attribute');
                $order->quantity = Tools::getValue('quantity');
                $order->date = date('Y-m-d H:i:s');

                $order->add();

                die(Tools::jsonEncode(array('errors' => false, 'order_placed' => true)));
            } else {
                die(Tools::jsonEncode(array('errors' => true, 'error_type' => 'error_validation')));
            }
        } else {
            die(Tools::jsonEncode(array('errors' => true, 'error_type' => 'error_validation')));
        }
    }
}
