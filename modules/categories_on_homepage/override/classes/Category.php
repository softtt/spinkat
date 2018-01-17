<?php

class Category extends CategoryCore
{
    public $show_on_homepage;

    public function __construct($id_category = null, $id_lang = null, $id_shop = null)
    {
        self::$definition['fields']['show_on_homepage'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');

        parent::__construct($id_category, $id_lang, $id_shop);
    }

    public function getCategoriesForHomepage()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'category` c
            '.Shop::addSqlAssociation('category', 'c').'
            LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
            WHERE `active` = 1
            AND `show_on_homepage` = 1
            ORDER BY c.`nleft` ASC, category_shop.`position` ASC'
        );

        return $result;
    }
}
