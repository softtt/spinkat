<?php

/**
 * Model is a single product in Series of products.
 * Model has some different attributes than Combination itself.
 */
class Model extends Combination
{
    public $TIGE_TYPE_ATTRIBUTE_GROUP_ID = 49;

    public $TIGE_TYPE_ATTRIBUTE_TITLES = [
        'спиннинг' => 'Спиннинг',
        'кастинг' => 'Спиннинг (cast)',
    ];

    /**
     * Generate full title for product Model.
     * Title consists of product type, manufacturer name, series name and model title.
     *
     * @return string
     */
    public function getFullTitle()
    {
        $series = new Product($this->id_product);

        $manufacturer_name = $series->getWsManufacturerName();
        $series_name = Product::getProductName($this->id_product);

        $id_lang = Context::getContext()->language->id;
        $product_type_name = $this->getProductTypeName($id_lang);

        return join(' ', [$product_type_name, $manufacturer_name, $series_name, $this->title]);
    }

    public function getProductTypeName($id_lang)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT al.name
            FROM '._DB_PREFIX_.'product_attribute_combination pac
            LEFT JOIN '._DB_PREFIX_.'attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang='.(int)$id_lang.')
            LEFT JOIN '._DB_PREFIX_.'attribute pa ON (pa.id_attribute = pac.id_attribute)
            WHERE pac.id_product_attribute='.(int)$this->id.'
            AND pa.id_attribute_group = '.$this->TIGE_TYPE_ATTRIBUTE_GROUP_ID);

        if ($result) {
            return $this->TIGE_TYPE_ATTRIBUTE_TITLES[$result[0]['name']];
        } else {
            return '';
        }

    }
}
