<?php

require_once dirname(__FILE__) . "/../PHPExcel/PHPExcel.php";

class RaymarineReadFilter implements PHPExcel_Reader_IReadFilter {

    public function readCell($column, $row, $worksheetName = '') {
        if (in_array($column, ['B', 'C', 'E'])) {
            return true;
        }
        return false;
    }
}