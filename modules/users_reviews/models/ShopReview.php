<?php

class ShopReview extends ObjectModel
{
    public $id;
    public $id_shop_review;

    /** @var string Name of customer who leaved review */
    public $customer_name;

    /** @var string Customer email */
    public $email;

    /** @var int Review grade */
    public $grade = 5;

    /** @var string Review text */
    public $text;

    /** @var string Review status */
    public $active = false;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'shop_review',
        'primary' => 'id_shop_review',
        'fields' => array(
            'customer_name' => array('type' => self::TYPE_STRING),
            'email' =>         array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128),
            'text' =>          array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 65535, 'required' => true),
            'grade' =>         array('type' => self::TYPE_FLOAT, 'validate' => 'isInt'),
            'active' =>        array('type' => self::TYPE_BOOL),
        )
    );

    public static function getReviews($limit = 50, $random = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'shop_review`
            WHERE active = 1
            '.($random ? 'ORDER BY rand()' : '').'
            '.($limit ? 'limit '.$limit : '');

        $result = Db::getInstance()->executeS($sql);

        return $result;
    }
}
