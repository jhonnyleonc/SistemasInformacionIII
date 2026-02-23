<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', '¡PhpSpreadsheet funciona!');

$writer = new Xlsx($spreadsheet);
$writer->save('test_excel.xlsx');

echo "Excel creado exitosamente! Verifica el archivo test_excel.xlsx";