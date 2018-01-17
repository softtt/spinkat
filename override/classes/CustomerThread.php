<?php
class CustomerThread extends CustomerThreadCore
{
    /*
    * module: ext_feedback
    * date: 2015-10-30 19:22:23
    * version: 1.1.0
    */
    public $name;
    /*
    * module: ext_feedback
    * date: 2015-10-30 19:22:23
    * version: 1.1.0
    */
    public $subject;
    /*
    * module: ext_feedback
    * date: 2015-10-30 19:22:23
    * version: 1.1.0
    */

    public $id_product_attribute;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        self::$definition['fields']['name'] = array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255);
        self::$definition['fields']['subject'] = array('type' => self::TYPE_STRING, 'size' => 255);
        self::$definition['fields']['id_contact']['required'] = false;
        self::$definition['fields']['id_product_attribute'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId');

        parent::__construct($id, $id_lang, $id_shop);
    }
}
