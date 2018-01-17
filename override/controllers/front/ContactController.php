<?php
class ContactController extends ContactControllerCore
{
    /**
    * Assign template vars related to page content
    * @see FrontController::initContent()
    */
    /*
    * module: ext_feedback
    * date: 2015-10-30 19:41:23
    * version: 1.1.0
    */
    public function initContent()
    {
        parent::initContent();
        $default_name = '';
        if (isset($this->context->cookie) &&
            isset($this->context->cookie->customer_lastname) &&
            isset($this->context->cookie->customer_firstname)
        ) {
            $default_name = $this->context->cookie->customer_firstname . ' ' . $this->context->cookie->customer_lastname;
        }
        $name = Tools::safeOutput(Tools::getValue('name', $default_name));
        $subject = Tools::safeOutput(Tools::getValue('subject', ''));
        $contact_info = Configuration::get('EXT_FEEDBACK_CONTACT_INFO_TEXT', $this->context->language->id);
        $this->context->smarty->assign(array(
            'name' => $name,
            'subject' => $subject,
            'contact_info' => $contact_info
        ));
    }
    /**
    * Start forms process
    * @see FrontController::postProcess()
    */
    /*
    * module: ext_feedback
    * date: 2015-10-30 19:41:23
    * version: 1.1.0
    */
    public function postProcess()
    {
        if (Tools::isSubmit('submitMessage')) {
            $subject = Tools::getValue('subject', '');
            $message = Tools::getValue('message'); // Html entities is not usefull, iscleanHtml check there is no bad html tags.
            if (!($from = trim(Tools::getValue('from'))) || !Validate::isEmail($from)) {
                $this->errors[] = Tools::displayError('Invalid email address.');
            }
            if (!($name = Tools::getValue('name'))) {
                $this->errors[] = Tools::displayError('The name cannot be blank.');
            }
            if (!$message) {
                $this->errors[] = Tools::displayError('The message cannot be blank.');
            }
            if (!Validate::isCleanHtml($message)) {
                $this->errors[] = Tools::displayError('Invalid message');
            }
            if (!$this->errors) {
                $customer = $this->context->customer;
                if (!$customer->id) {
                    $customer->getByEmail($from);
                }
                $id_order = (int)$this->getOrder();
                if (!((
                        ($id_customer_thread = (int)Tools::getValue('id_customer_thread'))
                        && (int)Db::getInstance()->getValue('
                        SELECT cm.id_customer_thread FROM '._DB_PREFIX_.'customer_thread cm
                        WHERE cm.id_customer_thread = '.(int)$id_customer_thread.' AND cm.id_shop = '.(int)$this->context->shop->id.' AND token = \''.pSQL(Tools::getValue('token')).'\'')
                    ) || (
                        $id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($from, $id_order)
                    ))) {
                    $fields = Db::getInstance()->executeS('
                    SELECT cm.id_customer_thread, cm.id_contact, cm.id_customer, cm.id_order, cm.id_product, cm.email
                    FROM '._DB_PREFIX_.'customer_thread cm
                    WHERE email = \''.pSQL($from).'\' AND cm.id_shop = '.(int)$this->context->shop->id.' AND ('.
                        ($customer->id ? 'id_customer = '.(int)$customer->id.' OR ' : '').'
                        id_order = '.(int)$id_order.')');
                    $score = 0;
                    foreach ($fields as $key => $row) {
                        $tmp = 0;
                        if ((int)$row['id_customer'] && $row['id_customer'] != $customer->id && $row['email'] != $from) {
                            continue;
                        }
                        if ($row['id_order'] != 0 && $id_order != $row['id_order']) {
                            continue;
                        }
                        if ($row['email'] == $from) {
                            $tmp += 4;
                        }
                        if (Tools::getValue('id_product') != 0 && $row['id_product'] == Tools::getValue('id_product')) {
                            $tmp += 2;
                        }
                        if ($tmp >= 5 && $tmp >= $score) {
                            $score = $tmp;
                            $id_customer_thread = $row['id_customer_thread'];
                        }
                    }
                }
                $old_message = Db::getInstance()->getValue('
                    SELECT cm.message FROM '._DB_PREFIX_.'customer_message cm
                    LEFT JOIN '._DB_PREFIX_.'customer_thread cc on (cm.id_customer_thread = cc.id_customer_thread)
                    WHERE cc.id_customer_thread = '.(int)$id_customer_thread.' AND cc.id_shop = '.(int)$this->context->shop->id.'
                    ORDER BY cm.date_add DESC');
                if ($old_message == $message) {
                    $this->context->smarty->assign('alreadySent', 1);
                }
                if ((int)$id_customer_thread) {
                    $ct = new CustomerThread($id_customer_thread);
                    $ct->status = 'open';
                    $ct->id_lang = (int)$this->context->language->id;
                    $ct->id_order = (int)$id_order;
                    if ($id_product = (int)Tools::getValue('id_product')) {
                        $ct->id_product = $id_product;
                    }
                    if ($id_product_attribute = (int)Tools::getValue('id_product_attribute')) {
                        $ct->id_product_attribute = $id_product_attribute;
                    }
                    $ct->update();
                } else {
                    $ct = new CustomerThread();
                    if (isset($customer->id)) {
                        $ct->id_customer = (int)$customer->id;
                    }
                    $ct->id_shop = (int)$this->context->shop->id;
                    $ct->id_order = (int)$id_order;
                    if ($id_product = (int)Tools::getValue('id_product')) {
                        $ct->id_product = $id_product;
                    }
                    if ($id_product_attribute = (int)Tools::getValue('id_product_attribute')) {
                        $ct->id_product_attribute = $id_product_attribute;
                    }
                    $ct->id_lang = (int)$this->context->language->id;
                    $ct->email = $from;
                    $ct->status = 'open';
                    $ct->name = $name;
                    $ct->subject = $subject;
                    $ct->token = Tools::passwdGen(12);
                    $ct->add();
                }
                if ($ct->id) {
                    $cm = new CustomerMessage();
                    $cm->id_customer_thread = $ct->id;
                    $cm->message = $message;
                    $cm->ip_address = (int)ip2long(Tools::getRemoteAddr());
                    $cm->user_agent = $_SERVER['HTTP_USER_AGENT'];
                    if (!$cm->add()) {
                        $this->errors[] = Tools::displayError('An error occurred while sending the message.');
                    }
                } else {
                    $this->errors[] = Tools::displayError('An error occurred while sending the message.');
                }
                if (!count($this->errors)) {
                    $var_list = array(
                                    '{order_name}' => '-',
                                    '{message}' => Tools::nl2br(stripslashes($message)),
                                    '{email}' =>  $from,
                                    '{product_name}' => '',
                                    '{name}' => $name,
                                    '{subject}' => $subject,
                                );
                    $id_product = (int)Tools::getValue('id_product');
                    if (isset($ct) && Validate::isLoadedObject($ct) && $ct->id_order) {
                        $order = new Order((int)$ct->id_order);
                        $var_list['{order_name}'] = $order->getUniqReference();
                        $var_list['{id_order}'] = (int)$order->id;
                    }
                    if ($id_product) {
                        if ($id_product_attribute) {
                            $model = new Model($id_product_attribute);
                            $var_list['{product_name}'] = $model->getFullTitle();
                        } else {
                            $product = new Product((int)$id_product);
                            if (Validate::isLoadedObject($product) && isset($product->name[Context::getContext()->language->id])) {
                                $var_list['{product_name}'] = $product->name[Context::getContext()->language->id];
                            }
                        }
                    }
                    $contacts_emails = Contact::getCustomerServiceContactsEmails();
                    if (!$contacts_emails) {
                        Mail::Send($this->context->language->id, 'contact_form', ((isset($ct) && Validate::isLoadedObject($ct)) ? sprintf(Mail::l('Your message has been correctly sent #ct%1$s #tc%2$s'), $ct->id, $ct->token) : Mail::l('Your message has been correctly sent')), $var_list, $from, null, null, null, null);
                    } else {
                        if (!Mail::Send($this->context->language->id, 'contact', Mail::l('Message from contact form').' [no_sync]',
                            $var_list, $contacts_emails, $contact->name, null, null,
                                    null, null, _PS_MAIL_DIR_, false, null, null, $from) ||
                                !Mail::Send($this->context->language->id, 'contact_form', ((isset($ct) && Validate::isLoadedObject($ct)) ? sprintf(Mail::l('Your message has been correctly sent #ct%1$s #tc%2$s'), $ct->id, $ct->token) : Mail::l('Your message has been correctly sent')), $var_list, $from, null, null, null, null, null, _PS_MAIL_DIR_, false, null, null, $contacts_emails[0])) {
                            $this->errors[] = Tools::displayError('An error occurred while sending the message.');
                        }
                    }
                }
                if (count($this->errors) > 1) {
                    array_unique($this->errors);
                } elseif (!count($this->errors)) {
                    $this->context->smarty->assign('confirmation', 1);
                }

                if (Tools::getIsset('back') && Tools::getValue('back')) {
                    Tools::redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }
}
