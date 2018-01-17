<?php

require_once dirname(__FILE__) . "/../PHPExcel/PHPExcel.php";

class NormarkReadFilter implements PHPExcel_Reader_IReadFilter {

    public function readCell($column, $row, $worksheetName = '') {
        // if (in_array($column, ['B', 'C', 'I', 'K'])) {
        //     return true;
        // }
        // return false;

        return true;
    }
}
