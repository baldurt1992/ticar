<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PersonCheckXls implements WithMultipleSheets
{
    protected $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function sheets(): array
    {
        $agrupados = collect($this->list)->groupBy('token');

        $sheets = [];

        foreach ($agrupados as $token => $registros) {
            $sheets[] = new PersonCheckPerUserSheet($token, $registros);
        }

        return $sheets;
    }
}
