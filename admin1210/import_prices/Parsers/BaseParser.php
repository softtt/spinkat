<?php

abstract class BaseParser
{
    protected $path_to_file;
    protected $read_filter;
    protected $worksheet;

    protected $title_cell_column;
    protected $reference_cell_column;
    protected $price_cell_column;
    protected $quantity_cell_column;

    public function __construct($path_to_file, $read_filter)
    {
        $this->path_to_file = $path_to_file;
        $this->read_filter = $read_filter;

        $php_excel = $this->loadFile($this->path_to_file, $this->read_filter);
        $this->worksheet = $php_excel->getActiveSheet();
    }

    public function parseFile()
    {
        $rows_number = $this->worksheet->getHighestRow();

        echo '<table>';
        echo "<tr>
                <td>#</td>
                <td>Артикул</td>
                <td>Название товара</td>
                <td>Старая цена</td>
                <td>Новая цена</td>
                <td>quantity</td>
                <td>ID товара</td>
                <td>ID комбинации</td>
            </tr>";

        $i = 0;

        // Amount of updated products/models
        $updated = 0;

        while ($i <= $rows_number) {
            $title_cell = "{$this->title_cell_column}{$i}";
            $reference_cell = "{$this->reference_cell_column}{$i}";
            $price_cell = "{$this->price_cell_column}{$i}";

            $combination = null;
            $id_product = 0;
            $title = $this->worksheet->getCell($title_cell)->getValue();
            $reference = $this->worksheet->getCell($reference_cell)->getFormattedValue();
            $old_price = '';

            $price = $this->calculatePrice($this->worksheet->getCell($price_cell)->getValue());

            $quantity = $this->getQuantity($i);
            if ($reference) {
                $id_combination = Combination::getByReference($reference);

                if ($id_combination) {
                    $combination = new Combination($id_combination);
                    $old_price = round($combination->price);

                    $combination->price = $price;
                    $combination->save();

                    $id_product = $combination->id_product;
                    $this->setQuantity($id_product, $quantity, $id_combination);

                    $updated++;
                } else {
                    $id_product = Product::getByReference($reference);

                    if ($id_product) {
                        $product = new Product($id_product);

                        $old_price = round($product->price);
                        $product->price = $price;
                        $product->save();

                        $this->setQuantity($id_product, $quantity, $id_combination);

                        $updated++;
                    }
                }
                echo "<tr>
                        <td>{$i}</td>
                        <td>{$reference}</td>
                        <td>{$title}</td>
                        <td>{$old_price}</td>
                        <td>{$price}</td>
                        <td>{$quantity}</td>
                        <td>{$id_product}</td>
                        <td>{$id_combination}</td>
                    </tr>";
            }


            $i++;
        }


        echo "<tr>
                <td></td>
                <td><b>Обновлено товаров:</b></td>
                <td><b>{$updated}</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>";

        echo '</table>';
    }

    abstract protected function setQuantity($id_product, $quantity, $id_combination = 0);

    abstract protected function getQuantity($i);

    public function loadFile($path_to_file, PHPExcel_Reader_IReadFilter $read_filter)
    {
        $callStartTime = microtime(true);

        if (!file_exists($path_to_file)) {
            exit("File {$path_to_file} not exists <br>");
        }

        echo date('H:i:s') , " Load from {$path_to_file} file" , "<br>";

        /**  Identify the type of $path_to_file  **/
        $inputFileType = PHPExcel_IOFactory::identify($path_to_file);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadFilter($read_filter);
        $objPHPExcel = $objReader->load($path_to_file);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        echo 'Call time to read Price was ' , sprintf('%.4f',$callTime) , " seconds" , "<br>";
        // Echo memory usage
        echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , "<br>";

        return $objPHPExcel;
    }

    public function calculatePrice($price)
    {
        return $this->roundPrice($price);
    }

    public function roundPrice($price)
    {
        if ($price) {
            $price = round($price);

            $r = $price % 10;
            if ($r > 0) {
                $price -= $r;
            }
        }

        return $price;
    }
}
