<?php

require_once dirname(__FILE__) . "/../PHPExcel/PHPExcel.php";

class MidaReadFilter implements PHPExcel_Reader_IReadFilter {

    public function readCell($column, $row, $worksheetName = '') {
        if (in_array($column, ['C', 'D', 'E', 'F'])) {
            return true;
        }
        return false;
    }
}