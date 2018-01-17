<?php


class RaymarineParser extends BaseParser
{
    protected $title_cell_column = "C";
    protected $reference_cell_column = "B";
    protected $price_cell_column = "E";
    protected $quantity_cell_column = null;

    /**
     * @param $id_product
     * @param $id_combination
     */
    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        StockAvailable::setQuantity($id_product, $id_combination, 1000);
        Hook::exec('actionProductUpdate', array('id_product' => (int)$id_product, 'product' => new Product($id_product)));
    }

    protected function getQuantity($i)
    {
        return 1000;
    }
}
