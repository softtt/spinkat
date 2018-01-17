<?php

/**
 *  Generate Docx document for order
 */
class DocxGenerator
{
    public $customer = null;
    public $products = null;
    public $client = null;
    public $filename = "order_details";

    public $headers = ['size' => 9, 'bold' => true];
    public $dataStyle = ['size' => 9, 'bold' => false];
    public $styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
    public $styleCell = ['valign' => 'center', 'size' => 9];
    public $tableHeader = [
        ['width' => 500, 'text' => '№'],
        ['width' => 4000, 'text' => 'Товары (Работы, услуги)'],
        ['width' => 1000, 'text' => 'Кол-во'],
        ['width' => 500, 'text' => 'Ед.'],
        ['width' => 1300, 'text' => 'Цена (руб.)'],
        ['width' => 1400, 'text' => 'Скидка (руб.)'],
        ['width' => 1300, 'text' => 'Сумма (руб.)'],
    ];

    public $months = [
        'January' => 'Января',
        'February' => 'Февраля',
        'March' => 'Марта',
        'April' => 'Апреля',
        'May' => 'Мая',
        'June' => 'Июня',
        'July' => 'Июля',
        'August' => 'Августа',
        'September' => 'Сентября',
        'October' => 'Октября',
        'November' => 'Ноября',
        'December' => 'Декабря',
    ];
    private $total = [
        'price' => 0,
        'discount' => 0,
        'summary' => 0,
    ];

    public $logoPath = '';
    public $company = "Индивидуальный предприниматель Безлюдный В.В., ИНН 910209279002 295034, Республика Крым, г. Симферополь, ул. Донская д.6, кв. 53. Телефон: +7 (917) 424-09-09. Email: info@spinkat.ru";
    public $disclaimer = [
        "Покупатель вправе отказаться от снасти надлежащего качества, не подошедшего по каким-либо причинам:",
        "до передачи товара – в любое время",
        "после передачи товара – в течение 14 дней;",
        "если информация о порядке и сроках возврата товара надлежащего качества не была предоставлена в письменной форме в момент доставки",
    ];

    function __construct($order)
    {
        require_once _PS_ROOT_DIR_ . '/vendor/autoload.php';
        $this->products = $this->prepareProduct($order);
        $this->customer = $order->getCustomer();
        $this->order = $this->prepareOrder($order);
        $this->client = $this->prepareClient($order);
        $this->logoPath = _PS_IMG_DIR_ . 'spinkat_doc_logo.jpg';

        $this->filename = "Spinkat.ru - Накладная по заказу #" . $this->order['number'];

        $this->generateStyles();
    }

    public function make($returnHeaders = true)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Add logo
        $section = $phpWord->addSection(['marginTop' => $this->m2t(10), 'marginLeft' => $this->m2t(15), 'marginRight' => $this->m2t(15)]);
        $section->addImage($this->logoPath, ['align' => 'center']);
        // $section->addTextBreak(1);

        $section->addText(htmlspecialchars("Заказ покупателя № " . $this->order['number'] . " от " . $this->order['date']), array_merge($this->headers, ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DASHHEAVY]));

        $headTable = $section->addTable(['cellMarginTop' => 10, 'cellMarginBottom' => 10]);
        $headTable->addRow();
        $headTable->addCell(2000, ['valign' => 'top'])->addText(htmlspecialchars('Исполнитель'), $this->headers);
        $headTable->addCell(8000, ['valign' => 'center'])->addText(htmlspecialchars($this->company), $this->dataStyle);

        $headTable->addRow();
        $headTable->addCell(2000, ['valign' => 'top'])->addText(htmlspecialchars('Заказчик'), $this->headers);
        $cell = $headTable->addCell(8000, ['valign' => 'center']);
        $cell->addText(htmlspecialchars($this->client['fio']), $this->dataStyle);
        $cell->addText(htmlspecialchars("Тел. " . $this->client['phone']), $this->dataStyle);
        // $section->addTextBreak(1);

        /* Table header */
        $phpWord->addTableStyle('Fancy Table', $this->styleTable);
        $table = $section->addTable('Fancy Table');
        $table->addRow();
        foreach ($this->tableHeader as $data) {
            $table->addCell($data['width'], $this->styleCell)->addText(htmlspecialchars($data['text']), ['bold' => true, 'align' => 'center', 'size' => 9]);
        }

        /* Table body */
        $i = 0;
        foreach ($this->products as $key => $value) {
            $i++;
            $table->addRow();
            $table->addCell()->addText(htmlspecialchars("{$i}"));
            foreach ($value as $k => $v) {
                if (in_array($k, ['price', 'discount', 'summary'])) {
                    $v = number_format($v, 2, ',', ' ');
                }
                $table->addCell()->addText(htmlspecialchars($v), ['align' => 'center', 'bold' => false, 'size' => 9]);
            }
            $this->total['price'] += $value['price'];
            $this->total['discount'] += $value['discount'];
            $this->total['summary'] += $value['summary'];

        }
        $table->addRow();
        $table->addCell($this->tableHeader[1]['width'], ['align' => 'right', 'valign' => 'top', 'gridSpan' => 4])->addText(htmlspecialchars("Итого без НДС"), ['bold' => true]);
        $table->addCell($this->tableHeader[4]['width'], ['valign' => 'top'])->addText(htmlspecialchars(number_format($this->total['price'], 2, ',', ' ')), ['bold' => true]);
        $table->addCell($this->tableHeader[5]['width'], ['valign' => 'top'])->addText(htmlspecialchars(number_format($this->total['discount'], 2, ',', ' ')), ['bold' => true]);
        $table->addCell($this->tableHeader[6]['width'], ['valign' => 'top'])->addText(htmlspecialchars(number_format($this->total['summary'], 2, ',', ' ')), ['bold' => true]);
        // $section->addTextBreak(1);
        $section->addText(htmlspecialchars("Всего наименований {$i} на сумму " . number_format($this->total['summary'], 2, ',', ' ') . " руб."), $this->dataStyle);
        $section->addText(htmlspecialchars($this->num2str(number_format($this->total['summary'], 2, '.', ''))), $this->headers);

        /*Disclaimer*/
        // $section->addTextBreak(1);
        $disclaimerTalbe = $section->addTable(['cellMarginTop' => 0, 'cellMarginBottom' => 0]);
        $disclaimerTalbe->addRow(20);
        $disclaimerTalbe->addCell(10000, ['gridSpan' => 2, 'borderTopSize' => 15, 'borderTopColor' => '000000'])->addText($this->disclaimer[0], ['size' => 8]);
        $disclaimerTalbe->addRow(20);
        $disclaimerTalbe->addCell(500)->addText("-");
        $disclaimerTalbe->addCell(9500)->addText($this->disclaimer[1], ['size' => 8]);
        $disclaimerTalbe->addRow(20);
        $disclaimerTalbe->addCell(500)->addText("-");
        $disclaimerTalbe->addCell(9500)->addText($this->disclaimer[2], ['size' => 8]);
        $disclaimerTalbe->addRow(20);
        $disclaimerTalbe->addCell(500, ['borderBottomSize' => 15, 'borderBottomColor' => '000000'])->addText("-");
        $disclaimerTalbe->addCell(9500, ['borderBottomSize' => 15, 'borderBottomColor' => '000000'])->addText($this->disclaimer[3], ['size' => 8]);
        // $disclaimerTalbe->addRow(20);
        // $disclaimerTalbe->addCell(10000, ['gridSpan' => 2, 'borderTopSize' => 15, 'borderTopColor' => '000000']);

        /*Signatures*/
        $section->addTextBreak(1);
        $bottomtable = $section->addTable();
        $bottomtable->addRow(20);
        $bottomtable->addCell(6000, ['align' => 'left'])->addText("Исполнитель ______________/ ИП Безлюдный В.В./", $this->headers);
        $bottomtable->addCell(4000, ['align' => 'right'])->addText("Заказчик ______________/ {$this->client['fio']}/", $this->headers);
        $bottomtable->addRow();
        $bottomtable->addCell(10000, ['gridSpan' => 2, 'align' => 'left'])->addText("М.П.", $this->headers);

        /* Save file */
        if ($returnHeaders) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . $this->filename . '.docx"');
            header('Cache-Control: max-age=0');

            $phpWord->save("php://output", 'Word2007');
        }else{
            $file = tempnam(sys_get_temp_dir(), $this->filename . '.docx');
            $phpWord->save($file);

            $file_size = filesize($file);
            $handle = fopen($file, "rb");
            $content = fread($handle, $file_size);

            fclose($handle);
            unlink($file);

            return $content;
        }
    }

    public function prepareProduct($order)
    {
        $products = [];
        foreach ($order->getProductsDetail() as $key => $value) {
            $products[] = [
                'name' => $value['product_name'],
                'count' => $value['product_quantity'],
                'unit' => 'шт',
                'price' => $value['product_price'],
                'discount' => $value['product_quantity_discount'],
                'summary' => $value['unit_price_tax_excl'],
            ];
        }
        return $products;
    }

    public function prepareClient($order)
    {
        if (!$this->customer) {
            $this->customer = $order->getCustomer();
        }

        $address = new Address($order->id_address_delivery);

        return [
            'fio' => $this->customer->firstname . " " . $this->customer->lastname,
            'phone' => ($address->phone) ? $address->phone : $address->phone_mobile,
        ];
    }

    public function prepareOrder($order)
    {
        return [
            "number" => $order->id,
            "date" => date("d ", strtotime($order->date_add)) . $this->months[date("F", strtotime($order->date_add))] . date(" Yг", strtotime($order->date_add))
        ];
    }

    public function generateStyles()
    {
        $this->headers = ['size' => 9, 'bold' => true];
        $this->dataStyle = ['size' => 9, 'bold' => false];
        $this->styleTable = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $this->styleCell = ['valign' => 'center', 'size' => 9];
        $this->tableHeader = [
            ['width' => 500, 'text' => '№'],
            ['width' => 4000, 'text' => 'Товары (Работы, услуги)'],
            ['width' => 1000, 'text' => 'Кол-во'],
            ['width' => 500, 'text' => 'Ед.'],
            ['width' => 1300, 'text' => 'Цена (руб.)'],
            ['width' => 1400, 'text' => 'Скидка (руб.)'],
            ['width' => 1300, 'text' => 'Сумма (руб.)'],
        ];
    }

    private function m2t($millimeters)
    {
        return floor($millimeters * 56.7); //1 твип равен 1/567 сантиметра
    }

    private function num2str($num)
    {
        $nul = 'ноль';
        $ten = [
            ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ];
        $a20 = ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'];
        $tens = [2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundred = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];
        $unit = [ // Units
            ['копейка', 'копейки', 'копеек', 1],
            ['рубль', 'рубля', 'рублей', 0],
            ['тысяча', 'тысячи', 'тысяч', 1],
            ['миллион', 'миллиона', 'миллионов', 0],
            ['миллиард', 'милиарда', 'миллиардов', 0],
        ];
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = [];
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1) $out[] = $this->morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            } //foreach
        } else $out[] = $nul;
        $out[] = $this->morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . $this->morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    private function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) return $f5;
        $n = $n % 10;
        if ($n > 1 && $n < 5) return $f2;
        if ($n == 1) return $f1;
        return $f5;
    }
}
