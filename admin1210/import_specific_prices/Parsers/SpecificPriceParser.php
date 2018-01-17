<?php


class SpecificPriceParser
{
    protected $path_to_file;
    protected $read_filter;
    protected $worksheet;

    protected $reference_cell_column = "B";
    protected $product_name_cell = "C";
    protected $reduction_cell_column = "D";
    protected $beginning_cell_column = "E";
    protected $ending_cell_column = "F";

    protected $id_product_attribute;
    protected $from;
    protected $to;
    protected $value;

    public function __construct($path_to_file, $read_filter = null)
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
                <td>ID товара</td>
                <td>ID комбинации</td>
            </tr>";

        $i = 2;
        $table_strings_iterator = 1;
        // Amount of updated products/models
        $updated = 0;

        while ($i <= $rows_number) {
            $reference_cell = "{$this->reference_cell_column}{$i}";
            $name_cell = "{$this->product_name_cell}{$i}";
            $reduction_cell = "{$this->reduction_cell_column}{$i}";
            $beginning_cell = "{$this->beginning_cell_column}{$i}";
            $ending_cell = "{$this->ending_cell_column}{$i}";

            $combination = null;
            $id_product = 0;
            $reference = $this->worksheet->getCell($reference_cell)->getFormattedValue();
            $reduction = $this->worksheet->getCell($reduction_cell)->getFormattedValue();
            $name = $this->worksheet->getCell($name_cell)->getFormattedValue();
            $from = new DateTime($this->worksheet->getCell($beginning_cell)->getFormattedValue());
            $to = new DateTime($this->worksheet->getCell($ending_cell)->getFormattedValue());

            if ($reference) {
                $id_combination = Combination::getByReference($reference);

                if ($id_combination) {
                    $combination = new Combination($id_combination);
                    $id_product = $combination->id_product;
                    $specificPriceArray = SpecificPriceCore::getByProductId($id_product, $id_combination);
                    if (count($specificPriceArray)) {
                        $specificPrice = new SpecificPriceCore($specificPriceArray[0]['id_specific_price']);
                        if ($specificPrice) {
                            $specificPrice->reduction = (float)($reduction / 100);
                            $specificPrice->from = $from->format('Y-m-d H:i:s');
                            $specificPrice->to = $to->format('Y-m-d H:i:s');
                            if (!$specificPrice->update()) {
                                $this->errors[] = Tools::displayError('An error occurred while updating the specific price.');
                            }
                        }
                    } else {
                        $specificPrice = new SpecificPrice();
                        $specificPrice->id_product = (int)$id_product;
                        $specificPrice->id_product_attribute = (int)$id_combination;
                        $specificPrice->id_shop = 0;
                        $specificPrice->id_currency = 0;
                        $specificPrice->id_country = 0;
                        $specificPrice->id_group = 0;
                        $specificPrice->id_customer = 0;
                        $specificPrice->price = (float)-1;
                        $specificPrice->from_quantity = 1;
                        $specificPrice->reduction = (float)($reduction / 100);
                        $specificPrice->reduction_tax = 1;
                        $specificPrice->reduction_type = 'percentage';
                        $specificPrice->from = $from->format('Y-m-d H:i:s');
                        $specificPrice->to = $to->format('Y-m-d H:i:s');
                        if (!$specificPrice->add()) {
                            $this->errors[] = Tools::displayError('An error occurred while updating the specific price.');
                        }
                    }


                    $updated++;
                }
                echo "<tr>
                        <td>{$table_strings_iterator}</td>
                        <td>{$reference}</td>
                        <td>{$name}</td>
                        <td>{$id_product}</td>
                        <td>{$id_combination}</td>
                    </tr>";
            }

            $table_strings_iterator++;
            $i++;
        }


        echo "<tr>
                <td></td>
                <td><b>Обновлено товаров:</b></td>
                <td><b>{$updated}</b></td>
            </tr>";
        echo '</table>';
    }

    public function loadFile($path_to_file, PHPExcel_Reader_IReadFilter $read_filter)
    {
        $callStartTime = microtime(true);

        if (!file_exists($path_to_file)) {
            exit("File {$path_to_file} not exists <br>");
        }

        echo date('H:i:s'), " Load from {$path_to_file} file", "<br>";

        /**  Identify the type of $path_to_file  **/
        $inputFileType = PHPExcel_IOFactory::identify($path_to_file);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadFilter($read_filter);
        $objPHPExcel = $objReader->load($path_to_file);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        echo 'Call time to read Price was ', sprintf('%.4f', $callTime), " seconds", "<br>";
        // Echo memory usage
        echo date('H:i:s'), ' Current memory usage: ', (memory_get_usage(true) / 1024 / 1024), " MB", "<br>";

        return $objPHPExcel;
    }

}
