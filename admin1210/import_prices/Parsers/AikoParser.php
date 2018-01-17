<?php


class AikoParser extends BaseParser
{
    protected $title_cell_column = "B";
    protected $reference_cell_column = "I";
    protected $price_cell_column = "D";
    protected $quantity_cell_column = "F";

    /**
     * @param $id_product
     * @param $id_combination
     */
    protected function setQuantity($id_product, $quantity, $id_combination = 0)
    {
        if ($quantity == 'Более 5') {
            StockAvailable::setQuantity($id_product, $id_combination, 1000);
        } else {
            StockAvailable::setQuantity($id_product, $id_combination, 4);
        }
        // StockAvailable::setQuantity($id_product, $id_combination, 1000);
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
}
