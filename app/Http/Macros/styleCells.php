<?php

use \Maatwebsite\Excel\Sheet;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
$sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});
