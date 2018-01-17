<?php


class AttributeGroup extends AttributeGroupCore
{
    public $hide;

    public static $definition = array(
        'table' => 'attribute_group',
        'primary' => 'id_attribute_group',
        'multilang' => true,
        'fields' => array(
            'is_color_group' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'hide' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'group_type' =>    array('type' => self::TYPE_STRING, 'required' => true),
            'position' =>        array('type' => self::TYPE_INT, 'validate' => 'isInt'),

            /* Lang fields */
            'name' =>            array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'public_name' =>    array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
        ),
    );
}