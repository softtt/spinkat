<?php

require_once dirname(__FILE__) . "/../PHPExcel/PHPExcel.php";

class AikoReadFilter implements PHPExcel_Reader_IReadFilter {

    public function readCell($column, $row, $worksheetName = '') {
        if (in_array($column, ['B', 'I', 'D', 'F'])) {
            return true;
        }
        return false;
    }
}
