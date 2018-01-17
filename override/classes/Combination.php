<?php

class Combination extends CombinationCore
{
    public $title;
    public $short_description;
    public $top_description;
    public $long_description;
    public $attribute_video;
    public $is_new;
    public $hide;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'product_attribute',
        'primary' => 'id_product_attribute',
        'fields' => array(
            'id_product' =>        array('type' => self::TYPE_INT, 'shop' => 'both', 'validate' => 'isUnsignedId', 'required' => true),
            'location' =>            array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64),
            'ean13' =>                array('type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13),
            'upc' =>                array('type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12),
            'quantity' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 10),
            'reference' =>            array('type' => self::TYPE_STRING, 'size' => 255),
            'supplier_reference' => array('type' => self::TYPE_STRING, 'size' => 32),

            /* Shop fields */
            'wholesale_price' =>    array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'size' => 27),
            'price' =>                array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isNegativePrice', 'size' => 20),
            'ecotax' =>            array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'size' => 20),
            'weight' =>            array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isFloat'),
            'unit_price_impact' =>    array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isNegativePrice', 'size' => 20),
            'minimal_quantity' =>    array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId', 'required' => true),
            'default_on' =>        array('type' => self::TYPE_BOOL, 'allow_null' => true, 'shop' => true, 'validate' => 'isBool'),
            'available_date' =>    array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'),

            'title' => array('type' => self::TYPE_STRING, 'size' => 250),
            'short_description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'top_description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'long_description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'attribute_video' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'is_new' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'hide' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    public static function getCombinationAttributesIds($id_combination)
    {
        $attributes_ids = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT id_attribute
            FROM '._DB_PREFIX_.'product_attribute_combination pac
            WHERE pac.id_product_attribute='.(int)$id_combination);

        $get_id = function($e) {
            return $e['id_attribute'];
        };

        $ids = array_map($get_id, $attributes_ids);

        return $ids;
    }

    public static function getByReference($reference)
    {
        if (empty($reference)) {
            return 0;
        }

        $reference = str_replace([' ', '-', '\'', '"'], '', $reference);

        $query = new DbQuery();
        $query->select('pa.id_product_attribute, pa.reference');
        $query->from('product_attribute', 'pa');
        // Remove hyphens, spaces, blockquotes and backslashes
        $query->where('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pa.reference, " ", ""), "-", ""), "\'", ""), "\"", ""), "\\\", "") LIKE "'.pSQL($reference).'"');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
}
