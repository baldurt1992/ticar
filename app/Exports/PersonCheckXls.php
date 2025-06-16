<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PersonCheckXls implements WithMultipleSheets
{
    protected $list;
    protected $columns;

    public function __construct(array $list, ?array $columns = null)
    {
        $this->list = $list;
        $this->columns = $columns ?? [
            'division',
            'role',
            'token',
            'name',
            'moment_enter',
            'moment_exit',
            'hours'
        ];
        ;
    }

    public function sheets(): array
    {
        $agrupados = collect($this->list)->groupBy('token');

        $sheets = [];

        foreach ($agrupados as $token => $registros) {
            $sheets[] = new PersonCheckPerUserSheet($token, $registros, $this->columns);
        }

        return $sheets;
    }
}
