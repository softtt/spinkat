<?php


class SpinkatParser extends BaseParser
{
    protected $title_cell_column = "B";
    protected $reference_cell_column = "C";
    protected $price_cell_column = "F";
    protected $quantity_cell_column = "E";

    /**
     * @param $quantity
     * @param $id_product
     * @param $id_combination
     */
    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        StockAvailable::setQuantity($id_product, $id_combination, $quantity);
    }

    /**
     * @param $i
     * @return mixed
     */
    protected function getQuantity($i)
    {
        if ($this->quantity_cell_column) {
            $quantity_cell = "{$this->quantity_cell_column}{$i}";
            $quantity = $this->worksheet->getCell($quantity_cell)->getValue();
            return $quantity;
        }
        return 0;
    }
}