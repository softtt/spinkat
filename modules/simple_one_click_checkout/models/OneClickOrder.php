<?php

class OneClickOrder extends ObjectModel
{
    public $id;
    public $id_one_click_order;

    /** @var string Client name */
    public $client;

    /** @var string Phone number */
    public $phone;

    /** @var string E-mail */
    public $email;

    /** @var string Address */
    public $address;

    /** @var string Order message */
    public $message;

    /** @var int */
    public $id_product;

    /** @var int */
    public $id_product_attribute;

    /** @var int */
    public $quantity;

    /** @var string Order add date */
    public $date;

    /** @var bool Order status */
    public $proceed;

    public static $definition = array(
        'table' => 'one_click_order',
        'primary' => 'id_one_click_order',
        'multilang' => false,
        'fields' => array(
            'client' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'phone' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
            'address' => array('type' => self::TYPE_STRING),
            'message' => array('type' => self::TYPE_STRING, 'size' => 255),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'proceed' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );
}
