<?php

class Contact extends ContactCore
{
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
