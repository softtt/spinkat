<?php

echo '<pre>';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/Moscow');

// Prestashop system loading files
require_once __DIR__ . '/../../config/config.inc.php';
require_once __DIR__ . '/../../init.php';

require_once __DIR__ . '/PHPExcel/PHPExcel/IOFactory.php';

require_once __DIR__ . "/ReadFilters/AkvatoriaReadFilter.php";
require_once __DIR__ . "/ReadFilters/SmartFishingReadFilter.php";
require_once __DIR__ . "/ReadFilters/NormarkReadFilter.php";
require_once __DIR__ . "/ReadFilters/MidaReadFilter.php";
require_once __DIR__ . "/ReadFilters/AikoReadFilter.php";
require_once __DIR__ . "/ReadFilters/RaymarineReadFilter.php";
require_once __DIR__ . "/ReadFilters/BlackHoleReadFilter.php";
require_once __DIR__ . "/ReadFilters/SpinkatReadFilter.php";

require_once __DIR__ . '/Parsers/BaseParser.php';
require_once __DIR__ . '/Parsers/AkvatoriaParser.php';
require_once __DIR__ . '/Parsers/SmartFishingParser.php';
require_once __DIR__ . '/Parsers/NormarkParser.php';
require_once __DIR__ . '/Parsers/MidaParser.php';
require_once __DIR__ . '/Parsers/AikoParser.php';
require_once __DIR__ . '/Parsers/RaymarineParser.php';
require_once __DIR__ . '/Parsers/BlackHoleParser.php';
require_once __DIR__ . '/Parsers/SpinkatParser.php';
