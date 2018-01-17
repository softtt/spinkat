<?php

require_once dirname(__FILE__) . "/../PHPExcel/PHPExcel.php";

class BlackHoleReadFilter implements PHPExcel_Reader_IReadFilter {

    public function readCell($column, $row, $worksheetName = '')
    {
        if (in_array($column, ['B', 'D', 'E'])) {
            return true;
        }

        return false;
    }
}
