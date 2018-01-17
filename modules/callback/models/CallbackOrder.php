<?php

class CallbackOrder extends ObjectModel
{
    public $id;
    public $id_callback_order;

    /** @var string Client name */
    public $client;

    /** @var string Phone number */
    public $phone;

    /** @var string E-mail */
    public $email;

    /** @var string Order message */
    public $message;

    /** @var string Order add date */
    public $date;

    /** @var bool Order status */
    public $active;

    public static $definition = array(
        'table' => 'callback_order',
        'primary' => 'id_callback_order',
        'multilang' => false,
        'fields' => array(
            'client' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'phone' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
            'message' => array('type' => self::TYPE_STRING, 'size' => 255),
            'date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );
}
