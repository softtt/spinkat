<?php


class AkvatoriaParser extends BaseParser
{
    protected $title_cell_column = "A";
    protected $reference_cell_column = "B";
    protected $price_cell_column = "H";
    protected $quantity_cell_column = "K";

    /**
     * @param $quantity
     * @param $id_product
     * @param $id_combination
     */
    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        if ($quantity == 'есть') {
            StockAvailable::setQuantity($id_product, $id_combination, 1000);
        } else {
            StockAvailable::setQuantity($id_product, $id_combination, 0);
        }
        Hook::exec('actionProductUpdate', array('id_product' => (int)$id_product, 'product' => new Product($id_product)));
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