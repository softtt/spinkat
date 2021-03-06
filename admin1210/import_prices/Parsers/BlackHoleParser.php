<?php


class BlackHoleParser extends BaseParser
{
    protected $title_cell_column = "D";
    protected $reference_cell_column = "B";
    protected $price_cell_column = "E";

    /**
     * @param $quantity
     * @param $id_product
     * @param $id_combination
     */
    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        StockAvailable::setQuantity($id_product, $id_combination, 1000);

        Hook::exec('actionProductUpdate', array('id_product' => (int)$id_product, 'product' => new Product($id_product)));
    }

    /**
     * @param $i
     * @return mixed
     */
    protected function getQuantity($i)
    {
        return 1000;
    }

    public function calculatePrice($price)
    {
        $price = $price * 1.4;

        return parent::calculatePrice($price);
    }
}
