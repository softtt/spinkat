<?php 
function m2t($millimeters){
  return floor($millimeters*56.7); //1 твип равен 1/567 сантиметра
}

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('UTC');

// New Word Document
$phpWord = new \PhpOffice\PhpWord\PhpWord();

/*Vars*/
$months = [
    'January'   => 'Января',
    'February'  => 'Февраля',
    'March'     => 'Марта',
    'April'     => 'Апреля',
    'May'       => 'Мая',
    'June'      => 'Июня',
    'July'      => 'Июля',
    'August'    => 'Августа',
    'September' => 'Сентября',
    'October'   => 'Октября',
    'November'  => 'Ноября',
    'December'  => 'Декабря',
];
$filename = "order_details";
$logoPath = _PS_ROOT_DIR_.'/img/spinkatru-logo-1449936889.jpg';
$company    = "Индивидуальный предприниматель Безлюдный В.В., ИНН 910209279002 295034, Республика Крым, г. Симферополь, ул. Донская д.6, кв. 53";
$disclaimer = [
    "Покупатель вправе отказаться от снасти надлежащего качества, не подошедшего по каким-либо причинам:",
    "до передачи товара – в любое время",
    "после передачи товара – в течение 14 дней;",
    "если информация о порядке и сроках возврата товара надлежащего качества не была предоставлена в письменной форме в момент доставки",
];
$products = [];
foreach ($this->order->getProductsDetail() as $key => $value) {
    $products[] = [
        'name'      =>  $value['product_name'],
        'count'     =>  $value['product_quantity'],
        'unit'      =>  'шт',
        'price'     =>  $value['product_price'],
        'discount'  =>  $value['product_quantity_discount'],
        'summary'   =>  $value['unit_price_tax_excl'],
    ];
}

$customer = $this->order->getCustomer();
$client = ['fio' => $customer->firstname." ".$customer->lastname, 'phone' => $this->order->delivery_number,];
unset($customer);

$order = [
    "number"    => $this->order->id, 
    "date"      => date("d ",strtotime($this->order->date_add)).$months[date("F",strtotime($this->order->date_add))].date(" Yг",strtotime($this->order->date_add))];

/*Styles*/
$headers    = ['size' => 9, 'bold' => true];
$dataStyle  = ['size' => 9, 'bold' => false];
$styleTable = array('borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80);
$styleCell = array('valign' => 'center','size'=>9);

$tableHeader= [
    ['width' =>500,      'text' => '№'],
    ['width' =>4000,     'text' => 'Товары (Работы, услуги)'],
    ['width' =>1000,     'text' => 'Кол-во'],
    ['width' =>500,      'text' => 'Ед.'],
    ['width' =>1300,     'text' => 'Цена (руб.)'],
    ['width' =>1400,     'text' => 'Скидка (руб.)'],
    ['width' =>1300,     'text' => 'Сумма (руб.)'],
];


$section = $phpWord->addSection(['marginLeft'=>m2t(15),'marginRight'=>m2t(15)]);
$section->addImage($logoPath,['align'=>'center']);
$section->addTextBreak(1);
$section->addText(htmlspecialchars("Заказ покупателя № {$order['number']} от {$order['date']}"), array_merge($headers,['underline'=>\PhpOffice\PhpWord\Style\Font::UNDERLINE_DASHHEAVY]));

$headTable = $section->addTable(['cellMarginTop'=>10,'cellMarginBottom'=>10]);
$headTable->addRow();
$headTable->addCell(1500, ['valign' => 'top'])->addText(htmlspecialchars('Исполнитель'), $headers);
$headTable->addCell(8000, ['valign' => 'center'])->addText(htmlspecialchars($company), $dataStyle);
$headTable->addRow();
$headTable->addCell(1500, ['valign' => 'top'])->addText(htmlspecialchars('Заказчик'), $headers);
$cell = $headTable->addCell(8000, ['valign' => 'center']);
$cell->addText(htmlspecialchars($client['fio']), $dataStyle);
$cell->addText(htmlspecialchars("Тел. ".$client['phone']), $dataStyle);
$section->addTextBreak(1);

$phpWord->addTableStyle('Fancy Table', $styleTable);
$table = $section->addTable('Fancy Table');

$total = [
    'price'     => 0,
    'discount'  => 0,
    'summary'   => 0,
];
// Table header
$table->addRow();
foreach ($tableHeader as $data) {
    $table->addCell($data['width'], $styleCell)->addText(htmlspecialchars($data['text']), ['bold' => true, 'align' => 'center','size'=>9]);
}

// Table body
$i = 1;
foreach ($products as $key => $value) {
    $table->addRow();
    $table->addCell()->addText(htmlspecialchars("{$i}"));
    foreach ($value as $k => $v) {
        if (in_array($k, ['price','discount','summary'])) {
            $v = number_format($v, 2, ',', ' ');
        }
        $table->addCell()->addText(htmlspecialchars($v),['align'=>'center','bold' => false,'size'=>9]);
    }
    $total['price']     +=$value['price'];
    $total['discount']  +=$value['discount'];
    $total['summary']   +=$value['summary'];
    $i++;
}
$table->addRow();
$table->addCell($tableHeader[1]['width'],['align' => 'right','valign' => 'top','gridSpan' => 4])->addText(htmlspecialchars("Итого без НДС"),['bold' => true]);
$table->addCell($tableHeader[4]['width'],['valign' => 'top'])->addText(htmlspecialchars(number_format($total['price'], 2, ',', ' ')),['bold' => true]);
$table->addCell($tableHeader[5]['width'],['valign' => 'top'])->addText(htmlspecialchars(number_format($total['discount'], 2, ',', ' ')),['bold' => true]);
$table->addCell($tableHeader[6]['width'],['valign' => 'top'])->addText(htmlspecialchars(number_format($total['summary'], 2, ',', ' ')),['bold' => true]);
$section->addTextBreak(1);
$section->addText(htmlspecialchars("Всего наименований {$i} на сумму ".number_format($total['summary'], 2, ',', ' ')." руб."), $dataStyle);
$section->addText(htmlspecialchars("Сто шестьдесят две тысячи восемьсот рублей 00 копеек"), $headers);

/*Disclaimer*/
$section->addTextBreak(1);
$disclaimerTalbe = $section->addTable(['cellMarginTop'=>0,'cellMarginBottom'=>0]);
$disclaimerTalbe->addRow(20);
$disclaimerTalbe->addCell(10000, ['gridSpan' => 2,'borderTopSize'=>15, 'borderTopColor'=>'000000'])->addText($disclaimer[0], ['size'=>8]);
$disclaimerTalbe->addRow(20);
$disclaimerTalbe->addCell(500)->addText("-");
$disclaimerTalbe->addCell(4500)->addText($disclaimer[1],['size'=>8]);
$disclaimerTalbe->addRow(20);
$disclaimerTalbe->addCell(500)->addText("-");
$disclaimerTalbe->addCell(4500)->addText($disclaimer[2],['size'=>8]);
$disclaimerTalbe->addRow(20);
$disclaimerTalbe->addCell(500)->addText("-");
$disclaimerTalbe->addCell(4500)->addText($disclaimer[3],['size'=>8]);
$disclaimerTalbe->addRow(20);
$disclaimerTalbe->addCell(10000, ['gridSpan' => 2,'borderTopSize'=>15, 'borderTopColor'=>'000000']);

/*Signatures*/
$section->addTextBreak(2);
$bottomtable = $section->addTable();
$bottomtable->addRow();
$bottomtable->addCell(5000,['align'=>'left'])->addText("Исполнитель ______________/ ИП Безлюдный", $headers);
$bottomtable->addCell(4000,['align'=>'right'])->addText("В.В./ Заказчик ______________/ ФИО/", $headers);
$bottomtable->addRow();
$bottomtable->addCell(5000,['align'=>'left'])->addText("М.П.", $headers);

// Save file
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="'.$filename.'.docx"');
header('Cache-Control: max-age=0');
$phpWord->save("php://output", 'Word2007');