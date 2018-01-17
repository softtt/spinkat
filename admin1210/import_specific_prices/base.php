<?php

echo '<pre>';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/Moscow');

// Prestashop system loading files
require_once __DIR__ . '/../../config/config.inc.php';
require_once __DIR__ . '/../../init.php';

require_once __DIR__ . '/../import_prices/PHPExcel/PHPExcel/IOFactory.php';

require_once __DIR__ . '/Parsers/SpecificPriceParser.php';

require_once __DIR__ . '/ReadFilters/SpecificPriceReadFilter.php';