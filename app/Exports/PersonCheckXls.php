<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PersonCheckXls implements WithMultipleSheets
{
    protected $list;
    protected $columns;
    protected $isCustom;

    public function __construct(array $list, ?array $columns = null, bool $isCustom = false)
    {
        $this->list = $list;
        $this->isCustom = $isCustom;

        $this->columns = $columns ?? [
            'div',
            'rol',
            'token',
            'names',
            'moment_enter',
            'moment_exit',
            'hours',
            'note',
            'motive_name',
        ];
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
