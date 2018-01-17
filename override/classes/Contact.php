<?php
class Contact extends ContactCore
{
    /*
    * module: ext_feedback
    * date: 2015-10-30 19:22:23
    * version: 1.1.0
    */
    public static function getCustomerServiceContactsEmails()
    {
        $contacts_emails = array();
        $sql = 'SELECT `email` FROM `'._DB_PREFIX_.'contact` WHERE `customer_service` = 1';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ($result as $key => $value) {
            $contacts_emails[] = $value['email'];
        }
        return $contacts_emails;
    }
}
