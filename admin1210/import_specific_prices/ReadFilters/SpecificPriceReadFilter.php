<?php

require_once dirname(__FILE__) . "/../../import_prices/PHPExcel/PHPExcel.php";
class SpecificPriceReadFilter implements PHPExcel_Reader_IReadFilter
{
    public function readCell($column, $row, $worksheetName = '') {
        if (in_array($column, ['B', 'C', 'D', 'E', 'F'])) {
            return true;
        }
        return false;
    }
}
