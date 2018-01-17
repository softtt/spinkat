<?php


class NormarkParser extends BaseParser
{
    protected $title_cell_column = "C";
    protected $reference_cell_column = "E";
    protected $price_cell_column = "M";
    protected $quantity_cell_column = "K";

    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        if ($quantity == 'В наличии') {
            $amount = 1000;
        } elseif ($quantity == 'Меньше 3 шт') {
            $amount = 2;
        } else {
            $amount = 0;
        }

        StockAvailable::setQuantity($id_product, $id_combination, $amount);
        Hook::exec('actionProductUpdate', array('id_product' => (int)$id_product, 'product' => new Product($id_product)));
    }

    protected function getQuantity($i)
    {
        if ($this->quantity_cell_column) {
            $quantity_cell = "{$this->quantity_cell_column}{$i}";
            $quantity = $this->worksheet->getCell($quantity_cell)->getValue();
            return $quantity;
        }
        return 0;
    }

    public function calculatePrice($price)
    {
        return $price;
    }
}
