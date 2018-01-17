<?php


class MidaParser extends BaseParser
{
    protected $title_cell_column = "D";
    protected $reference_cell_column = "C";
    protected $price_cell_column = "E";
    protected $quantity_cell_column = "F";

    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        if ($quantity == '> 20') {
            $amount = 1000;
        } else {
            $amount = $quantity;
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
        $price = $price * 1.5;

        return parent::calculatePrice($price);
    }
}