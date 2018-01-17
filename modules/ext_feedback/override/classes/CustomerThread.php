<?php

class CustomerThread extends CustomerThreadCore
{
    public $name;
    public $subject;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        self::$definition['fields']['name'] = array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255);
        self::$definition['fields']['subject'] = array('type' => self::TYPE_STRING, 'size' => 255);
        self::$definition['fields']['id_contact']['required'] = false;

        parent::__construct($id, $id_lang, $id_shop);
    }
}
