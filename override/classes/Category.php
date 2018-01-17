<?php
class Category extends CategoryCore
{
    public $show_on_homepage;

    public $title_for_seo_h1;

    public function __construct($id_category = null, $id_lang = null, $id_shop = null)
    {
        self::$definition['fields']['show_on_homepage'] = array('type' => self::TYPE_BOOL, 'validate' => 'isBool');
        self::$definition['fields']['title_for_seo_h1'] = array('type' => self::TYPE_STRING, 'size' => 255);
        parent::__construct($id_category, $id_lang, $id_shop);
    }
    /*
    * module: categories_on_homepage
    * date: 2015-11-28 22:23:44
    * version: 1.0.0
    */
    public static function getCategoriesForHomepage()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'category` c
            '.Shop::addSqlAssociation('category', 'c').'
            LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
            WHERE `active` = 1
            AND `show_on_homepage` = 1
            ORDER BY c.`level_depth` ASC, category_shop.`position` ASC'
        );
        return $result;
    }
}
