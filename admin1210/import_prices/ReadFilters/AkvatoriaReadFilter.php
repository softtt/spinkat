<?php

require_once dirname(__FILE__) . "/../PHPExcel/PHPExcel.php";

class AkvatoriaReadFilter implements PHPExcel_Reader_IReadFilter {

    public function readCell($column, $row, $worksheetName = '') {
        if (in_array($column, ['A', 'B', 'H', 'K'])) {
            return true;
        }
        return false;
    }
}