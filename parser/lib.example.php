<?php
    include(dirname(__FILE__).'/../config/config.inc.php');
    include(dirname(__FILE__).'/../init.php');

    $pdo = new PDO(
        'mysql:host=localhost; dbname=spinkat_old',
        'spinkat_old',
        'spinkat_old',

        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );

    @error_reporting(E_ALL | E_STRICT);

    function rus2translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
            '°' => '',    '±' => '',   '”' => '',
            '–' => '', '-' => '', ' ' => '_', '&'=>'',
            '¹' => '', '«'=>'', '»'=>'',

        );
        return strtr($string, $converter);
    }

    function str2url($str, $id=0, $str2='', $id2=0) {

        $str = rus2translit($str);
        $str = htmlentities($str, null, 'UTF-8');
        $str2 = rus2translit($str2);

        $str = strtolower($str);
        $str2 = strtolower($str2);

        $str = preg_replace('~[^-a-z0-9_]+~u', '', $str);
        $str = str_replace('_', '-', $str);

        $str = trim($str, "-");

        $str2 = preg_replace('~[^-a-z0-9_]+~u', '', $str2);
        $str2 = str_replace('_', '-', $str2);

        $str2 = trim($str2, "-");


        if($str2 && $id2)
        return $str.'_'.$str2.'_'.$id.'_'.$id2;
        else
        return $str.'_'.$id;

    }

    $thumbX = Configuration::get('PH_BLOG_THUMB_X');
    $thumbY = Configuration::get('PH_BLOG_THUMB_Y');

    $thumb_wide_X = Configuration::get('PH_BLOG_THUMB_X_WIDE');
    $thumb_wide_Y = Configuration::get('PH_BLOG_THUMB_Y_WIDE');

    $thumbMethod = Configuration::get('PH_BLOG_THUMB_METHOD');

    foreach (Language::getLanguages(true) as $lang){
        $groups = Group::getGroups($lang['id_lang']);
    }

    foreach ($groups as $group) {
        $access[$group['id_group']] = true;
    }

    $access = serialize($access);

function mb_ucfirst($string, $encoding)
{
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}
